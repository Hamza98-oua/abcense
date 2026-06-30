<?php

namespace App\Http\Controllers;

use App\Models\Groupe;
use App\Models\Module;
use App\Models\Seance;
use App\Models\Absence;
use App\Models\Stagiaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormateurController extends Controller
{
    /**
     * 1. Afficher l'interface de saisie des absences pour le formateur connecté.
     */
    /**
     * 1. Afficher l'interface de saisie des absences pour le formateur connecté.
     */
    public function index(Request $request)
    {
        $formateur = Auth::user(); 
        $groupes = $formateur->groupes; 
        
        $groupeId = $request->input('groupe_id');
        $date = $request->input('date', now()->format('Y-m-d'));
        $cef = $request->input('cef');

        // If no group is explicitly selected but there are groups, default to the first group
        if (!$groupeId && $groupes->isNotEmpty()) {
            $groupeId = $groupes->first()->id;
        }

        $groupe = null;
        $stagiaires = collect();
        $modules = collect();

        if ($groupeId) {
            $groupe = Groupe::find($groupeId);
            if ($groupe) {
                $stagiairesQuery = Stagiaire::where('groupe_id', $groupeId);
                if ($cef) {
                    $stagiairesQuery->where('cef', 'like', '%' . $cef . '%');
                }
                $stagiaires = $stagiairesQuery->orderBy('nom', 'asc')->get();

                // Fetch modules for this group
                $modules = Module::whereHas('groupes', function($query) use ($groupeId) {
                    $query->where('groupe_id', $groupeId);
                })->get();
            }
        }

        // Calculate suggestedSessionNum
        $suggestedSessionNum = 1;
        if ($groupeId) {
            $recordedSessions = Seance::where('groupe_id', $groupeId)
                ->where('formateur_id', $formateur->id)
                ->whereDate('date_debut', $date)
                ->pluck('num_seance')
                ->toArray();

            for ($i = 1; $i <= 4; $i++) {
                if (!in_array($i, $recordedSessions)) {
                    $suggestedSessionNum = $i;
                    break;
                }
            }
        }

        // Calculate previousSessionStatuses
        $previousSessionStatuses = [
            1 => [],
            2 => [],
            3 => [],
            4 => []
        ];

        if ($groupeId) {
            for ($s = 2; $s <= 4; $s++) {
                $prevSessionNum = $s - 1;
                // Find previous seance
                $prevSeance = Seance::where('groupe_id', $groupeId)
                    ->where('formateur_id', $formateur->id)
                    ->whereDate('date_debut', $date)
                    ->where('num_seance', $prevSessionNum)
                    ->first();

                if ($prevSeance) {
                    foreach ($prevSeance->absences as $absence) {
                        // Check if justified
                        $isJustified = $absence->justification && $absence->justification->est_valide;
                        // Check if permitted
                        $isPermitted = $absence->autorisation_suivante;

                        if (!$isJustified && !$isPermitted) {
                            $previousSessionStatuses[$s][$absence->stagiaire_id] = $absence->type;
                        }
                    }
                }
            }
        }

        return view('formateur.appel', compact(
            'groupes',
            'groupe',
            'date',
            'stagiaires',
            'modules',
            'suggestedSessionNum',
            'previousSessionStatuses',
            'cef'
        ));
    }

    /**
     * L'interface de saisie des absences (obsolète mais préservée si besoin)
     */
    public function create()
    {
        $formateur = Auth::user(); 
        $groupes = $formateur->groupes; 

        return view('absences.saisie', compact('groupes'));
    }

    /**
     * Enregistrer la séance de 2.5 heures et les présences/absences/retards.
     */
    public function validerAppel(Request $request)
    {
        // Validation stricte du formulaire
        $request->validate([
            'groupe_id'  => 'required|exists:groupes,id',
            'date'       => 'required|date_format:Y-m-d',
            'num_seance' => 'required|in:1,2,3,4',
            'module_id'  => 'nullable|exists:modules,id',
            'statuses'   => 'required|array',
            'statuses.*' => 'required|in:present,absent,retard'
        ]);

        // Définition automatique des horaires selon la séance choisie (Durée: 2,5 heures)
        $dateJour = $request->date;
        $time = '';

        switch ($request->num_seance) {
            case 1:
            case '1':
                $time = '08:30:00';
                break;
            case 2:
            case '2':
                $time = '11:00:00';
                break;
            case 3:
            case '3':
                $time = '13:30:00';
                break;
            case 4:
            case '4':
                $time = '16:00:00';
                break;
            default:
                $time = '08:30:00';
        }
        $dateDebut = $dateJour . ' ' . $time;

        // Utilisation d'une Transaction Database pour s'assurer que tout s'enregistre sans erreur
        DB::transaction(function () use ($request, $dateDebut) {
            
            // Supprimer la séance existante pour éviter les doublons lors d'une resoumission
            Seance::where('groupe_id', $request->groupe_id)
                ->where('date_debut', $dateDebut)
                ->where('num_seance', $request->num_seance)
                ->delete();

            // a. Création de la séance
            $seance = Seance::create([
                'module_id'    => $request->module_id ?: null,
                'groupe_id'    => $request->groupe_id,
                'formateur_id' => Auth::id(), // ID du formateur authentifié
                'date_debut'   => $dateDebut,
                'duree_heures' => 2.50,
                'est_validee'  => true,
                'num_seance'   => $request->num_seance,
            ]);

            // b. Enregistrement des absences et retards
            foreach ($request->statuses as $stagiaireId => $status) {
                if ($status === 'absent' || $status === 'retard') {
                    Absence::create([
                        'seance_id'             => $seance->id,
                        'stagiaire_id'          => $stagiaireId,
                        'type'                  => $status === 'absent' ? 'absence' : $status, // 'absence' ou 'retard'
                        'autorisation_suivante' => false
                    ]);
                }
            }
        });

        $nextSeance = (int)$request->num_seance + 1;
        if ($nextSeance > 4) {
            $nextSeance = 4;
        }

        return redirect()->route('formateur.dashboard', [
            'groupe_id'  => $request->groupe_id,
            'date'       => $request->date,
            'num_seance' => $nextSeance
        ])->with('success', 'L\'appel a été enregistré avec succès ! (2.5 heures comptabilisées)');
    }

    /**
     * 3. [API] Retourne les modules d'un groupe spécifique pour le fetch JavaScript.
     */
    public function getModulesByGroupe($groupeId)
    {
        $modules = Module::whereHas('groupes', function($query) use ($groupeId) {
            $query->where('groupe_id', $groupeId);
        })->get(['id', 'nom']);

        return response()->json($modules);
    }

    /**
     * 4. [API] Retourne les stagiaires d'un groupe spécifique pour le fetch JavaScript.
     */
    public function getStagiairesByGroupe($groupeId)
    {
        $stagiaires = Stagiaire::where('groupe_id', $groupeId)
            ->orderBy('nom', 'asc')
            ->get(['id', 'nom', 'prenom']);

        return response()->json($stagiaires);
    }
}