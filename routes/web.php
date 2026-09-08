<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DocsController;
use App\Http\Controllers\Web\LandingController;
use Illuminate\Support\Facades\Route;

// Public Landing Page & API Docs
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/docs', [DocsController::class, 'index'])->name('docs');
Route::get('/openapi.yaml', function () {
    return response(file_get_contents(public_path('openapi.yaml')), 200, [
        'Content-Type' => 'text/yaml',
    ]);
});



// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// App & Scoring Dashboard (Protected: Admin Only)
Route::middleware(['admin'])->group(function () {
    Route::get('/app', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/analyze', [DashboardController::class, 'analyze'])->name('dashboard.analyze');
    Route::get('/report/{id}', [DashboardController::class, 'report'])->name('dashboard.report');
    Route::post('/topup', [DashboardController::class, 'topup'])->name('dashboard.topup');
});

// Admin Panel Routes (Protected by admin middleware)
Route::prefix('admin')->middleware(['admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/accounts', [AdminController::class, 'createAccount'])->name('admin.accounts.create');
    Route::post('/accounts/{id}/scoring-rules', [AdminController::class, 'updateScoringRules'])->name('admin.accounts.scoring-rules');
    Route::post('/api-keys/generate', [AdminController::class, 'generateApiKey'])->name('admin.api-keys.generate');
    Route::post('/api-keys/{id}/revoke', [AdminController::class, 'revokeApiKey'])->name('admin.api-keys.revoke');
    Route::post('/credits/adjust', [AdminController::class, 'adjustCredits'])->name('admin.credits.adjust');
    
    // Entities Management
    Route::get('/entities', [AdminController::class, 'entities'])->name('admin.entities');
    Route::post('/entities/add-address', [AdminController::class, 'addEntityAddress'])->name('admin.entities.add-address');
});

