<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\Pole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $admin = Auth::user();

        if (! $admin || ! $admin->isAdmin()) {
            abort(403, 'Acces non autorise.');
        }

        $absences = Absence::with([
            'stagiaire.groupe.pole',
            'seance.formateur',
            'seance.module.science',
            'seance.science',
            'justification',
        ])
            ->latest()
            ->take(80)
            ->get();

        $formateurs = User::where('role', 'formateur')
            ->with(['groupes', 'modules.science'])
            ->orderBy('name')
            ->get();

        $gestionnaires = User::where('role', 'gestionnaire')
            ->with('pole')
            ->orderBy('name')
            ->get();

        return view('admin.dashboard', [
            'absences' => $absences,
            'formateurs' => $formateurs,
            'gestionnaires' => $gestionnaires,
            'groupes' => Groupe::with('pole')->orderBy('nom')->get(),
            'modules' => Module::with('science')->orderBy('nom')->get(),
            'poles' => Pole::orderBy('nom')->get(),
        ]);
    }

    public function assignerFormateur(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'formateur_id' => 'required|exists:users,id',
            'groupe_ids' => 'nullable|array',
            'groupe_ids.*' => 'exists:groupes,id',
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'exists:modules,id',
        ]);

        $formateur = User::where('role', 'formateur')->findOrFail($data['formateur_id']);
        $formateur->groupes()->sync($data['groupe_ids'] ?? []);
        $formateur->modules()->sync($data['module_ids'] ?? []);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Assignations du formateur mises a jour.');
    }

    public function assignerGestionnaire(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'gestionnaire_id' => 'required|exists:users,id',
            'pole_id' => 'nullable|exists:poles,id',
        ]);

        $gestionnaire = User::where('role', 'gestionnaire')->findOrFail($data['gestionnaire_id']);
        $gestionnaire->update(['pole_id' => $data['pole_id'] ?? null]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Pole du gestionnaire mis a jour.');
    }

    private function authorizeAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Acces non autorise.');
        }
    }
}
