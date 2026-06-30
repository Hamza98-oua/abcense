<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\GestionnaireController;

// 1. Simulation d'authentification
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// 2. Espace Formateur (Protégé par auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/formateurs/assigner', [AdminController::class, 'showAssignerFormateur'])->name('admin.formateurs.assigner.form');
    Route::post('/admin/formateurs/assigner', [AdminController::class, 'assignerFormateur'])->name('admin.formateurs.assigner');
    Route::get('/admin/gestionnaires/assigner', [AdminController::class, 'showAssignerGestionnaire'])->name('admin.gestionnaires.assigner.form');
    Route::post('/admin/gestionnaires/assigner', [AdminController::class, 'assignerGestionnaire'])->name('admin.gestionnaires.assigner');

    Route::get('/formateur/dashboard', [FormateurController::class, 'index'])->name('formateur.dashboard');
    Route::post('/formateur/valider', [FormateurController::class, 'validerAppel'])->name('formateur.valider');
    Route::get('/api/groupes/{groupeId}/modules', [FormateurController::class, 'getModulesByGroupe']);
    Route::get('/api/groupes/{groupeId}/stagiaires', [FormateurController::class, 'getStagiairesByGroupe']);
});

// 3. Espace Gestionnaire (Protégé par auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/gestionnaire/dashboard', [GestionnaireController::class, 'index'])->name('gestionnaire.dashboard');
    Route::get('/gestionnaire/stagiaires/{stagiaire}', [GestionnaireController::class, 'show'])->name('gestionnaire.stagiaires.show');
    Route::post('/gestionnaire/absences/{absence}/justifier', [GestionnaireController::class, 'ajouterJustificatif'])->name('gestionnaire.justifier');
    Route::post('/gestionnaire/justifications/{justification}/valider', [GestionnaireController::class, 'validerJustificatif'])->name('gestionnaire.justification.valider');
    Route::post('/gestionnaire/absences/{absence}/autoriser', [GestionnaireController::class, 'autoriserSeanceSuivante'])->name('gestionnaire.absences.autoriser');
    Route::post('/gestionnaire/import', [GestionnaireController::class, 'importerExcel'])->name('gestionnaire.import');
});
