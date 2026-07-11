<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\BoutiqueCategoryController;
use App\Http\Controllers\Auth\ClientAuthController;
use App\Http\Controllers\Auth\FrsAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\FournisseurController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\ProduitController as AdminProduitController;
use App\Http\Controllers\Admin\CommandeController as AdminCommandeController;
use App\Http\Controllers\Fournisseur\DashboardController as FrsDashboardController;
use App\Http\Controllers\Fournisseur\ProduitController as FrsProduitController;
use App\Http\Controllers\Fournisseur\ClientController as FrsClientController;
use App\Http\Controllers\Fournisseur\CommandeController as FrsCommandeController;
use App\Http\Controllers\Fournisseur\TokenController as FrsTokenController;
use App\Http\Controllers\Fournisseur\ProfileController as FrsProfileController;
use App\Http\Controllers\Fournisseur\PrevendeurController as FrsPrevendeurController;
use App\Http\Controllers\Fournisseur\VisitPlanningController as FrsVisitPlanningController;
use App\Http\Controllers\StoreController;

Route::get('/', [StoreController::class, 'index']);
Route::get('/boutiques', [StoreController::class, 'boutiques']);
Route::get('/boutiques/{id}', [StoreController::class, 'boutique']);
Route::get('/produits', [StoreController::class, 'produits']);
Route::get('/produits/{id}', [StoreController::class, 'produit']);

Route::get('/login', [ClientAuthController::class, 'showLogin']);
Route::post('/login', [ClientAuthController::class, 'login']);
Route::get('/register', [ClientAuthController::class, 'showRegister']);
Route::post('/register', [ClientAuthController::class, 'register']);
Route::post('/register/verify-email', [ClientAuthController::class, 'verifyEmail']);
Route::post('/register/resend-email-code', [ClientAuthController::class, 'resendEmailCode']);
Route::post('/register/restart', [ClientAuthController::class, 'restartRegister']);
Route::post('/logout', [ClientAuthController::class, 'logout']);

Route::get('/panier', [StoreController::class, 'panier']);
Route::post('/panier/add', [StoreController::class, 'panierAdd']);
Route::post('/panier/update', [StoreController::class, 'panierUpdate']);
Route::post('/panier/remove', [StoreController::class, 'panierRemove']);
Route::post('/panier/clear', [StoreController::class, 'panierClear']);

Route::get('/checkout', [StoreController::class, 'checkout']);
Route::post('/checkout', [StoreController::class, 'checkoutStore']);

Route::get('/profil', [StoreController::class, 'profil']);
Route::get('/mes-commandes', [StoreController::class, 'mesCommandes']);
Route::get('/mes-commandes/{id}', [StoreController::class, 'commandeShow']);

Route::get('/admin/login', function () {
    return view('auth.admin-login');
});

Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout']);

Route::get('/fournisseur/login', function () {
    return view('auth.fournisseur-login');
});

Route::post('/fournisseur/login', [FrsAuthController::class, 'login']);
Route::post('/fournisseur/logout', [FrsAuthController::class, 'logout']);

Route::prefix('admin')->middleware('auth.admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::post('/boutique-categories', [BoutiqueCategoryController::class, 'store']);
    Route::put('/boutique-categories/{id}', [BoutiqueCategoryController::class, 'update']);
    Route::delete('/boutique-categories/{id}', [BoutiqueCategoryController::class, 'destroy']);

    Route::get('/fournisseurs', [FournisseurController::class, 'index']);
    Route::get('/fournisseurs/create', [FournisseurController::class, 'create']);
    Route::post('/fournisseurs', [FournisseurController::class, 'store']);
    Route::get('/fournisseurs/{id}/edit', [FournisseurController::class, 'edit']);
    Route::put('/fournisseurs/{id}', [FournisseurController::class, 'update']);
    Route::delete('/fournisseurs/{id}', [FournisseurController::class, 'destroy']);
    Route::post('/fournisseurs/{id}/toggle-actif', [FournisseurController::class, 'toggleActif']);
    Route::post('/fournisseurs/{id}/regenerer-token', [FournisseurController::class, 'regenererToken']);

    Route::get('/wilayas/{idWilaya}/communes', [FournisseurController::class, 'communes']);

    Route::get('/clients', [AdminClientController::class, 'index']);
    Route::get('/clients/{id}', [AdminClientController::class, 'show']);
    Route::delete('/clients/{id}', [AdminClientController::class, 'destroy']);
    Route::get('/produits', [AdminProduitController::class, 'index']);
    Route::get('/commandes', [AdminCommandeController::class, 'index']);

    Route::get('/api-keys', [ApiKeyController::class, 'index']);
    Route::post('/api-keys', [ApiKeyController::class, 'store']);
    Route::post('/api-keys/{id}/toggle', [ApiKeyController::class, 'toggle']);
    Route::delete('/api-keys/{id}', [ApiKeyController::class, 'destroy']);

    Route::get('/api-docs', function () {
        return view('admin.api-docs', ['title' => 'API Doc']);
    });

    Route::get('/parametres', function () {
        return view('admin.parametres', ['title' => 'Paramètres']);
    });

    Route::get('/profil', function () {
        return view('admin.profil', ['title' => 'Profil']);
    });
});

Route::prefix('fournisseur')->middleware('auth.fournisseur')->group(function () {
    Route::get('/dashboard', [FrsDashboardController::class, 'index']);

    Route::get('/produits', [FrsProduitController::class, 'index']);
    Route::get('/produits/create', [FrsProduitController::class, 'create']);
    Route::post('/produits', [FrsProduitController::class, 'store']);
    Route::get('/produits/{id}', [FrsProduitController::class, 'show']);
    Route::get('/produits/{id}/edit', [FrsProduitController::class, 'edit']);
    Route::put('/produits/{id}', [FrsProduitController::class, 'update']);
    Route::delete('/produits/{id}', [FrsProduitController::class, 'destroy']);
    Route::post('/produits/{id}/toggle-actif', [FrsProduitController::class, 'toggleActif']);

    Route::get('/clients', [FrsClientController::class, 'index']);
    Route::get('/clients/{id}', [FrsClientController::class, 'show']);
    Route::put('/clients/{id}/prevendeur', [FrsClientController::class, 'updatePrevendeur']);
    Route::put('/clients/{id}/planning', [FrsClientController::class, 'updatePlanning']);

    Route::get('/commandes', [FrsCommandeController::class, 'index']);
    Route::get('/commandes/{id}', [FrsCommandeController::class, 'show']);
    Route::put('/commandes/{id}/statut', [FrsCommandeController::class, 'updateStatut']);

    Route::get('/prevendeurs', [FrsPrevendeurController::class, 'index']);
    Route::post('/prevendeurs', [FrsPrevendeurController::class, 'store']);
    Route::put('/prevendeurs/{id}', [FrsPrevendeurController::class, 'update']);
    Route::post('/prevendeurs/{id}/toggle', [FrsPrevendeurController::class, 'toggle']);

    Route::get('/token', [FrsTokenController::class, 'index']);

    Route::get('/visites/planning', [FrsVisitPlanningController::class, 'index']);
    Route::get('/visites/planning/tournees/{id}', [FrsVisitPlanningController::class, 'show']);
    Route::post('/visites/planning', [FrsVisitPlanningController::class, 'store']);
    Route::put('/visites/planning/{id}', [FrsVisitPlanningController::class, 'update']);
    Route::post('/visites/planning/{id}/toggle', [FrsVisitPlanningController::class, 'toggle']);
    Route::post('/visites/planning/tournees/{id}/status', [FrsVisitPlanningController::class, 'updateTourStatus']);
    Route::post('/visites/planning/regenerate', [FrsVisitPlanningController::class, 'regenerate']);

    Route::get('/profil', [FrsProfileController::class, 'edit']);
    Route::put('/profil', [FrsProfileController::class, 'update']);
    Route::put('/profil/password', [FrsProfileController::class, 'updatePassword']);

    Route::get('/wilayas/{idWilaya}/communes', [FrsProfileController::class, 'communes']);
});
