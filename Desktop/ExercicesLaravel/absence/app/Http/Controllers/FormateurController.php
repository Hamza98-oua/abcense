<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Groupe;
use App\Models\Seance;
use App\Models\Absence;
use App\Models\Module;
use App\Models\Science;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FormateurController extends Controller
{
    /**
     * Affiche l'écran de saisie rapide de l'appel pour le formateur connecté.
     */
    public function index(Request $request)
    {
        $formateur = Auth::user();

        // 1. Récupérer uniquement les groupes assignés à ce formateur
        $groupes = $formateur->groupes;

        // 2. Sélectionner le groupe demandé (ou le premier par défaut)
        $selectedGroupeId = $request->input('groupe_id');
        $selectedGroupe = $selectedGroupeId
            ? $groupes->firstWhere('id', $selectedGroupeId)
            : $groupes->first();

        // 3. Récupérer les stagiaires de ce groupe
        $stagiaires = $selectedGroupe
            ? $selectedGroupe->stagiaires()->orderBy('nom')->orderBy('prenom')->get()
            : collect();

        // 4. Sciences + modules disponibles pour ce groupe (programme attendu)
        $sciences = $selectedGroupe ? $selectedGroupe->sciences()->orderBy('nom')->get() : collect();
        $modules = $selectedGroupe ? $selectedGroupe->modules()->with('science')->orderBy('nom')->get() : collect();

        $selectedScienceId = $request->input('science_id');
        $filteredModules = $selectedScienceId
            ? $modules->where('science_id', $selectedScienceId)->values()
            : $modules;

        return view('formateur.appel', compact(
            'groupes',
            'selectedGroupe',
            'stagiaires',
            'sciences',
            'modules',
            'filteredModules',
            'selectedScienceId'
        ));
    }

    /**
     * Valide l'appel pour une séance et enregistre les absences.
     */
    public function validerAppel(Request $request)
    {
        $request->validate([
            'groupe_id' => 'required|exists:groupes,id',
            'science_id' => 'nullable|exists:sciences,id',
            'module_id' => 'nullable|exists:modules,id',
            'date_debut' => 'required|date',
            'duree_heures' => 'required|numeric|min:0.5',
        ]);

        $formateur = Auth::user();

        // Vérifier que le formateur est bien assigné à ce groupe (sécurité)
        if (!$formateur->groupes()->where('groupes.id', $request->groupe_id)->exists()) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à faire l\'appel pour ce groupe.');
        }

        // 1. Créer la séance validée
        $seance = Seance::create([
            'groupe_id' => $request->groupe_id,
            'formateur_id' => $formateur->id,
            'science_id' => $request->input('science_id'),
            'module_id' => $request->input('module_id'),
            'date_debut' => Carbon::parse($request->date_debut),
            'duree_heures' => $request->duree_heures,
            'est_validee' => true, // Validée directement
        ]);

        // 2. Récupérer le tableau des IDs des stagiaires ABSENTS
        // (Ceux qui n'ont pas été cochés "présent" dans le formulaire)
        // Le formulaire envoie la liste des présents, on en déduit les absents.
        $tousStagiairesIds = Groupe::find($request->groupe_id)->stagiaires()->pluck('id')->toArray();
        $presentsIds = $request->input('presents', []); // Tableau des IDs cochés présents

        // Les absents sont ceux qui appartiennent au groupe mais ne sont pas dans la liste des présents
        $absentsIds = array_diff($tousStagiairesIds, $presentsIds);

        // Enregistrer les absences
        foreach ($absentsIds as $stagiaireId) {
            Absence::create([
                'stagiaire_id' => $stagiaireId,
                'seance_id' => $seance->id,
            ]);
        }

        return redirect()->route('formateur.dashboard', ['groupe_id' => $request->groupe_id])
            ->with('success', 'L\'appel a été enregistré avec succès. ' . count($absentsIds) . ' absence(s) notée(s).');
    }
}
