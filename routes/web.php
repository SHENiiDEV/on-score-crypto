<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Merchant\MerchantController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DocsController;
use App\Http\Controllers\Web\LandingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/docs', [DocsController::class, 'index'])->name('docs');
Route::get('/openapi.yaml', function () {
    return response(file_get_contents(public_path('openapi.yaml')), 200, [
        'Content-Type' => 'text/yaml',
    ]);
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Internal scoring workbench (On-Score staff)
|--------------------------------------------------------------------------
*/
Route::middleware(['admin'])->group(function () {
    Route::get('/app', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/analyze', [DashboardController::class, 'analyze'])->name('dashboard.analyze');
    Route::get('/report/{id}', [DashboardController::class, 'report'])->name('dashboard.report');
    Route::post('/topup', [DashboardController::class, 'topup'])->name('dashboard.topup');
});

/*
|--------------------------------------------------------------------------
| Admin console (On-Score staff)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Clients
    Route::get('/clients', [AdminController::class, 'clients'])->name('admin.clients');
    Route::post('/clients', [AdminController::class, 'createAccount'])->name('admin.clients.create');
    Route::get('/clients/{id}', [AdminController::class, 'clientShow'])->name('admin.clients.show');
    Route::post('/clients/{id}', [AdminController::class, 'updateAccount'])->name('admin.clients.update');
    Route::post('/clients/{id}/scoring-rules', [AdminController::class, 'updateScoringRules'])->name('admin.clients.scoring-rules');
    Route::post('/clients/{id}/impersonate', [AdminController::class, 'impersonate'])->name('admin.clients.impersonate');

    // Merchant portal users
    Route::post('/clients/{id}/users', [AdminController::class, 'createUser'])->name('admin.clients.users.create');
    Route::post('/clients/{id}/users/{userId}/reset-password', [AdminController::class, 'resetUserPassword'])->name('admin.clients.users.reset');
    Route::post('/clients/{id}/users/{userId}/toggle', [AdminController::class, 'toggleUser'])->name('admin.clients.users.toggle');

    // API keys
    Route::get('/api-keys', [AdminController::class, 'keys'])->name('admin.keys');
    Route::post('/api-keys/generate', [AdminController::class, 'generateApiKey'])->name('admin.api-keys.generate');
    Route::post('/api-keys/{id}/revoke', [AdminController::class, 'revokeApiKey'])->name('admin.api-keys.revoke');

    // Billing
    Route::get('/ledger', [AdminController::class, 'ledger'])->name('admin.ledger');
    Route::post('/credits/adjust', [AdminController::class, 'adjustCredits'])->name('admin.credits.adjust');

    // Intelligence
    Route::get('/analyses', [AdminController::class, 'analyses'])->name('admin.analyses');
    Route::get('/entities', [AdminController::class, 'entities'])->name('admin.entities');
    Route::post('/entities/add-address', [AdminController::class, 'addEntityAddress'])->name('admin.entities.add-address');

    // Legacy aliases kept for backwards compatibility
    Route::post('/accounts', [AdminController::class, 'createAccount'])->name('admin.accounts.create');
    Route::post('/accounts/{id}/scoring-rules', [AdminController::class, 'updateScoringRules'])->name('admin.accounts.scoring-rules');
});

/*
|--------------------------------------------------------------------------
| Merchant portal (B2B clients)
|--------------------------------------------------------------------------
*/
Route::prefix('merchant')->middleware(['merchant'])->group(function () {
    Route::get('/', [MerchantController::class, 'dashboard'])->name('merchant.dashboard');

    // API keys
    Route::get('/keys', [MerchantController::class, 'keys'])->name('merchant.keys');
    Route::post('/keys', [MerchantController::class, 'storeKey'])->name('merchant.keys.store');
    Route::post('/keys/{id}/revoke', [MerchantController::class, 'revokeKey'])->name('merchant.keys.revoke');
    Route::post('/keys/{id}/rename', [MerchantController::class, 'renameKey'])->name('merchant.keys.rename');

    // Usage & reports
    Route::get('/usage', [MerchantController::class, 'usage'])->name('merchant.usage');
    Route::post('/lookup', [MerchantController::class, 'analyze'])->name('merchant.lookup');
    Route::get('/reports/{id}', [MerchantController::class, 'report'])->name('merchant.report');

    // Billing
    Route::get('/billing', [MerchantController::class, 'billing'])->name('merchant.billing');

    // Scoring rules
    Route::get('/scoring-rules', [MerchantController::class, 'scoringRules'])->name('merchant.scoring-rules');
    Route::post('/scoring-rules', [MerchantController::class, 'updateScoringRules'])->name('merchant.scoring-rules.update');
    Route::post('/scoring-rules/reset', [MerchantController::class, 'resetScoringRules'])->name('merchant.scoring-rules.reset');

    // Settings
    Route::get('/settings', [MerchantController::class, 'settings'])->name('merchant.settings');
    Route::post('/settings/profile', [MerchantController::class, 'updateProfile'])->name('merchant.settings.profile');
    Route::post('/settings/password', [MerchantController::class, 'updatePassword'])->name('merchant.settings.password');
    Route::post('/settings/webhook', [MerchantController::class, 'updateWebhook'])->name('merchant.settings.webhook');
    Route::post('/settings/webhook/rotate', [MerchantController::class, 'rotateWebhookSecret'])->name('merchant.settings.webhook.rotate');
});

// Leave "view as client" mode
Route::post('/stop-impersonating', [AdminController::class, 'stopImpersonating'])
    ->middleware(['admin'])->name('admin.stop-impersonating');
