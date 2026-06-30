<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Groupe;
use App\Models\Stagiaire;
use App\Models\Absence;
use App\Models\Justification;
use App\Imports\AbsencesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GestionnaireController extends Controller
{
    /**
     * Tableau de bord principal du gestionnaire.
     */
    public function index(Request $request)
    {
        $gestionnaire = Auth::user();

        // Sécurité : Vérifier le rôle de gestionnaire
        if (!$gestionnaire->isGestionnaire()) {
            abort(403, 'Accès non autorisé.');
        }

        $poleId = $gestionnaire->pole_id;

        // 1. Récupérer les groupes du pôle de compétence
        $groupes = Groupe::where('pole_id', $poleId)->get();

        // Filtres
        $selectedGroupeId = $request->input('groupe_id');
        $search = $request->input('search');
        $alertFilter = $request->input('alert'); // 'stable', 'warning', 'critical'

        // 2. Construire la requête pour les stagiaires de son pôle
        $query = Stagiaire::whereHas('groupe', function ($q) use ($poleId) {
            $q->where('pole_id', $poleId);
        });

        if ($selectedGroupeId) {
            $query->where('groupe_id', $selectedGroupeId);
        }

        $search = $request->input('search');
        if ($search) {
            $search = trim($search);

            // Recherche multi-champs (nom, prénom, email, cef)
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cef', 'like', "%{$search}%");
            });
        }

        // Charger les relations nécessaires pour éviter les requêtes N+1
        $stagiaires = $query->with([
            'groupe',
            'absences.justification',
            'absences.seance.science',
            'absences.seance.module.science',
        ])->get();

        // 3. Calculer les statistiques globales du pôle
        $totalStagiaires = Stagiaire::whereHas('groupe', function ($q) use ($poleId) {
            $q->where('pole_id', $poleId);
        })->count();

        // Préparer données sciences/modules manquants (affichage 'Show' dans la vue)
        // Logique : pour chaque stagiaire, on liste les modules programmés du groupe
        // et on récupère les séances validées manquées.
        // On limite à un calcul “lazy” côté vue via la relation absences déjà chargée.
        // (Pour performance, on ne fait pas encore de requêtes supplémentaires ici.)

        $nbAlerteWarning = 0; // 0h < absences successives <= 5h (ou 7.5h selon la politique)
        $nbAlerteCritical = 0; // absences successives > 5h (ou 7.5h)

        foreach ($stagiaires as $stagiaire) {
            $stagiaire->absences_par_module_recent = $this->absencesParModuleRecent($stagiaire);

            $successives = $stagiaire->heures_absences_successives;
            if ($successives > 0 && $successives < 7.5) {
                $nbAlerteWarning++;
            } elseif ($successives >= 7.5) {
                $nbAlerteCritical++;
            }
        }

        $nbJustificationsAttente = Justification::where('est_valide', false)
            ->whereHas('absence.stagiaire.groupe', function ($q) use ($poleId) {
                $q->where('pole_id', $poleId);
            })
            ->count();

        // 4. Filtrer la collection en mémoire par niveau d'alerte (car calculé dynamiquement par Eloquent)
        if ($alertFilter) {
            $stagiaires = $stagiaires->filter(function ($stagiaire) use ($alertFilter) {
                $successives = $stagiaire->heures_absences_successives;
                if ($alertFilter === 'stable') {
                    return $successives == 0;
                }
                if ($alertFilter === 'warning') {
                    return $successives > 0 && $successives < 7.5;
                }
                if ($alertFilter === 'critical') {
                    return $successives >= 7.5;
                }
                return true;
            });
        }

        return view('gestionnaire.dashboard', compact(
            'groupes',
            'stagiaires',
            'totalStagiaires',
            'nbAlerteWarning',
            'nbAlerteCritical',
            'nbJustificationsAttente',
            'selectedGroupeId',
            'search',
            'alertFilter'
        ));
    }

    /**
     * Regroupe les absences validees recentes d'un stagiaire par science/module.
     */
    private function absencesParModuleRecent(Stagiaire $stagiaire)
    {
        return $stagiaire->absences
            ->filter(fn ($absence) => $absence->seance && $absence->seance->est_validee)
            ->sortByDesc(fn ($absence) => $absence->seance->date_debut)
            ->groupBy(function ($absence) {
                $seance = $absence->seance;

                return ($seance->science_id ?: 'science_null') . '-' . ($seance->module_id ?: 'module_null');
            })
            ->map(function ($absences) {
                $latest = $absences->first();
                $seance = $latest->seance;
                $module = $seance->module;
                $science = $seance->science ?: $module?->science;

                return [
                    'science' => $science?->nom ?? 'Science non renseignee',
                    'module' => $module?->nom ?? 'Module non renseigne',
                    'total_heures' => round($absences->sum(fn ($absence) => (float) $absence->seance->duree_heures), 1),
                    'nb_seances' => $absences->count(),
                    'derniere_absence' => $seance->date_debut->format('d/m/Y a H:i'),
                    'details' => $absences->take(5)->map(fn ($absence) => [
                        'date' => $absence->seance->date_debut->format('d/m/Y a H:i'),
                        'duree' => (float) $absence->seance->duree_heures,
                    ])->values(),
                ];
            })
            ->values();
    }

    /**
     * Enregistre ou modifie un justificatif d'absence.
     */
    public function ajouterJustificatif(Request $request, Absence $absence)
    {
        $request->validate([
            'motif' => 'required|string|max:1000',
            'fichier' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:2048', // Max 2Mo
        ]);

        $gestionnaire = Auth::user();
        
        // Sécurité : Vérifier que l'absence appartient au pôle du gestionnaire
        if ($absence->stagiaire->groupe->pole_id !== $gestionnaire->pole_id) {
            abort(403, 'Action non autorisée.');
        }

        $fichierChemin = null;
        if ($request->hasFile('fichier')) {
            // Stocker dans storage/app/public/justificatifs
            $fichierChemin = $request->file('fichier')->store('justificatifs', 'public');
        }

        // Si un justificatif existe déjà, on supprime l'ancien fichier s'il y en a un nouveau
        $justificationExistante = Justification::where('absence_id', $absence->id)->first();
        if ($justificationExistante && $fichierChemin && $justificationExistante->fichier_joint) {
            Storage::disk('public')->delete($justificationExistante->fichier_joint);
        }

        Justification::updateOrCreate(
            ['absence_id' => $absence->id],
            [
                'motif' => $request->motif,
                'fichier_joint' => $fichierChemin ?? ($justificationExistante->fichier_joint ?? null),
                'est_valide' => true, // Devient justifié directement (validé d'office par le gestionnaire)
            ]
        );

        // Désactiver la possibilité d'entrer sans justificatif puisque l'absence est déjà justifiée
        $absence->update([
            'autorisation_suivante' => false
        ]);

        return redirect()->back()->with('success', 'Justificatif enregistré et validé avec succès.');
    }

    /**
     * Affiche toutes les absences d'un stagiaire (filtrables, avec statistiques).
     */
    public function show(Request $request, Stagiaire $stagiaire)
    {
        $gestionnaire = Auth::user();
        if (!$gestionnaire->isGestionnaire()) {
            abort(403, 'Accès non autorisé.');
        }

        // Sécurité : vérifier que le stagiaire appartient au pôle du gestionnaire
        if ($stagiaire->groupe->pole_id !== $gestionnaire->pole_id) {
            abort(403, 'Accès non autorisé.');
        }

        // Filtres pour les absences
        $statusFilter = $request->input('status'); // 'justified', 'unjustified'
        $searchModule = $request->input('search_module');
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');

        // Construire la requête d'absences du stagiaire
        $absencesQuery = $stagiaire->absences()->with(['seance.module', 'justification']);

        // Filtrer par statut
        if ($statusFilter === 'justified') {
            $absencesQuery->whereHas('justification', function ($q) {
                $q->where('est_valide', true);
            });
        } elseif ($statusFilter === 'unjustified') {
            $absencesQuery->where(function ($q) {
                $q->whereDoesntHave('justification')
                  ->orWhereHas('justification', function ($sq) {
                      $sq->where('est_valide', false);
                  });
            });
        }

        // Filtrer par module / science
        if ($searchModule) {
            $absencesQuery->whereHas('seance.module', function ($q) use ($searchModule) {
                $q->where('nom', 'like', "%{$searchModule}%");
            });
        }

        // Filtrer par plage de date
        if ($dateDebut) {
            $absencesQuery->whereHas('seance', function ($q) use ($dateDebut) {
                $q->whereDate('date_debut', '>=', $dateDebut);
            });
        }
        if ($dateFin) {
            $absencesQuery->whereHas('seance', function ($q) use ($dateFin) {
                $q->whereDate('date_debut', '<=', $dateFin);
            });
        }

        // Récupérer toutes les absences du stagiaire (pour les durées globales)
        $allAbsences = $stagiaire->absences()->with(['seance', 'justification'])->get();

        // Calculer les durées totales
        $totalAbsencesCount = $allAbsences->count();
        $totalHoursJustified = 0.0;
        $totalHoursUnjustified = 0.0;

        foreach ($allAbsences as $absence) {
            $duree = (float)($absence->seance->duree_heures ?? 2.5);
            $isJustified = $absence->justification && $absence->justification->est_valide;
            if ($isJustified) {
                $totalHoursJustified += $duree;
            } else {
                $totalHoursUnjustified += $duree;
            }
        }

        $totalHours = $totalHoursJustified + $totalHoursUnjustified;

        // Récupérer les absences filtrées pour l'affichage (triées par date début seance)
        $absences = $absencesQuery->join('seances', 'absences.seance_id', '=', 'seances.id')
            ->orderBy('seances.date_debut', 'desc')
            ->select('absences.*')
            ->get();

        return view('gestionnaire.show', compact(
            'stagiaire',
            'absences',
            'totalAbsencesCount',
            'totalHoursJustified',
            'totalHoursUnjustified',
            'totalHours',
            'statusFilter',
            'searchModule',
            'dateDebut',
            'dateFin'
        ));
    }

    /**
     * Valide un justificatif existant.
     */
    public function validerJustificatif(Request $request, Justification $justification)
    {
        $gestionnaire = Auth::user();

        // Sécurité : Vérifier le pôle
        if ($justification->absence->stagiaire->groupe->pole_id !== $gestionnaire->pole_id) {
            abort(403, 'Action non autorisée.');
        }

        $justification->update([
            'est_valide' => true
        ]);

        return redirect()->back()->with('success', 'Le justificatif a été validé.');
    }

    /**
     * Permet au stagiaire d'entrer à la séance suivante sans justificatif d'absence.
     */
    public function autoriserSeanceSuivante(Absence $absence)
    {
        $gestionnaire = Auth::user();

        // Sécurité : Vérifier le pôle
        if ($absence->stagiaire->groupe->pole_id !== $gestionnaire->pole_id) {
            abort(403, 'Action non autorisée.');
        }

        $absence->update([
            'autorisation_suivante' => true
        ]);

        return redirect()->back()->with('success', 'Le stagiaire a été autorisé à entrer à la séance suivante sans justification.');
    }

    /**
     * Importation d'un fichier Excel/CSV pour insérer des absences en masse.
     */
    public function importerExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120', // Max 5Mo
        ]);

        try {
            Excel::import(new AbsencesImport, $request->file('excel_file'));
            return redirect()->back()->with('success', 'Le fichier Excel a été importé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue lors de l\'importation : ' . $e->getMessage());
        }
    }
}
