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
    Route::post('/admin/formateurs/assigner', [AdminController::class, 'assignerFormateur'])->name('admin.formateurs.assigner');
    Route::post('/admin/gestionnaires/assigner', [AdminController::class, 'assignerGestionnaire'])->name('admin.gestionnaires.assigner');

    Route::get('/formateur/dashboard', [FormateurController::class, 'index'])->name('formateur.dashboard');
    Route::post('/formateur/valider', [FormateurController::class, 'validerAppel'])->name('formateur.valider');
});

// 3. Espace Gestionnaire (Protégé par auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/gestionnaire/dashboard', [GestionnaireController::class, 'index'])->name('gestionnaire.dashboard');
    Route::post('/gestionnaire/absences/{absence}/justifier', [GestionnaireController::class, 'ajouterJustificatif'])->name('gestionnaire.justifier');
    Route::post('/gestionnaire/justifications/{justification}/valider', [GestionnaireController::class, 'validerJustificatif'])->name('gestionnaire.justification.valider');
    Route::post('/gestionnaire/import', [GestionnaireController::class, 'importerExcel'])->name('gestionnaire.import');
});
