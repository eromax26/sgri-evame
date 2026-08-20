<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollaborateurController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PlatController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\SelectionController;
use App\Http\Controllers\AgentSecuriteController;
use App\Http\Controllers\SocieteController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\EtatRhController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\MouvStockController;
use App\Http\Controllers\TableauBordController;
use App\Http\Controllers\ProfilController;

Route::get('/', [LoginController::class, 'showLoginForm'])->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('mot-de-passe-oublie', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('mot-de-passe-oublie', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('reinitialiser-mot-de-passe/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reinitialiser-mot-de-passe', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware('auth')->group(function () {
    Route::get('profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::post('profil/mot-de-passe', [ProfilController::class, 'changerMotDePasse'])->name('profil.motDePasse');

    Route::get('selection', [SelectionController::class, 'index'])->name('selection.index');
    Route::post('selection', [SelectionController::class, 'valider'])->name('selection.valider');
    Route::get('mes-repas', [SelectionController::class, 'historique'])->name('selection.historique');
    Route::get('mes-tickets', [SelectionController::class, 'mesTickets'])->name('selection.tickets');
});

Route::middleware(['auth', 'role:Agent de securite'])->group(function () {
    Route::get('agent-securite/demandes', [AgentSecuriteController::class, 'demandesEnAttente'])->name('agent.demandes');
    Route::post('agent-securite/imprimer', [AgentSecuriteController::class, 'imprimer'])->name('agent.imprimer');
    Route::get('agent-securite/verifier', [AgentSecuriteController::class, 'verifierForm'])->name('agent.verifierForm');
    Route::post('agent-securite/confirmer', [AgentSecuriteController::class, 'confirmerRetrait'])->name('agent.confirmerRetrait');
    Route::get('agent-securite/journal', [AgentSecuriteController::class, 'journalPassages'])->name('agent.journal');
});

Route::middleware(['auth', 'role:Administrateur DSII'])->group(function () {
    Route::get('/admin/roles', function () {
        return 'Cette page est reservee a l\'Administrateur DSII.';
    })->name('admin.roles');

    Route::resource('collaborateurs', CollaborateurController::class);

    Route::resource('roles', RoleController::class);
    Route::post('collaborateurs/{collaborateur}/acces', [RoleController::class, 'attribuer'])->name('acces.attribuer');
    Route::delete('acces/{acces}', [RoleController::class, 'retirer'])->name('acces.retirer');
    Route::get('collaborateurs/{collaborateur}/roles', [RoleController::class, 'attribuerForm'])->name('roles.attribuerForm');

    Route::resource('societes', SocieteController::class);
    Route::resource('departements', DepartementController::class);
});

Route::middleware(['auth', 'role:Ressources Humaines'])->group(function () {
    Route::get('etat-rh', [EtatRhController::class, 'index'])->name('etat-rh.index');
    Route::post('etat-rh/verrouiller', [EtatRhController::class, 'verrouiller'])->name('etat-rh.verrouiller');
    Route::get('etat-rh/export', [EtatRhController::class, 'export'])->name('etat-rh.export');
    Route::get('etat-rh/historique', [EtatRhController::class, 'historique'])->name('etat-rh.historique');
});

Route::middleware(['auth', 'role:Responsable cantine'])->group(function () {
    Route::resource('plats', PlatController::class);

    Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
    Route::get('menus/create', [MenuController::class, 'create'])->name('menus.create');
    Route::post('menus', [MenuController::class, 'store'])->name('menus.store');
    Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
    Route::post('menus/{menu}/publier', [MenuController::class, 'publier'])->name('menus.publier');
    Route::delete('menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');
    Route::put('ligne-menus/{ligneMenu}', [MenuController::class, 'updateLigne'])->name('ligne-menus.update');

    Route::get('articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('articles/create', [ArticleController::class, 'create'])->name('articles.create');
    Route::post('articles', [ArticleController::class, 'store'])->name('articles.store');
    Route::get('articles/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::get('previsions', [MenuController::class, 'previsions'])->name('menus.previsions');

    Route::get('mouv-stocks', [MouvStockController::class, 'index'])->name('mouv-stocks.index');
    Route::get('mouv-stocks/create', [MouvStockController::class, 'create'])->name('mouv-stocks.create');
    Route::post('mouv-stocks', [MouvStockController::class, 'store'])->name('mouv-stocks.store');
});

Route::middleware(['auth', 'role:Direction Generale,Administrateur DSII,Responsable cantine,Ressources Humaines'])->group(function () {
    Route::get('tableau-bord', [TableauBordController::class, 'index'])->name('tableau-bord.index');
});
