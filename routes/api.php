<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AnalysisController;
use App\Http\Controllers\Api\V1\BatchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth.apikey'])->group(function () {
    // Single Analysis & Score Alias
    Route::post('/analyses', [AnalysisController::class, 'store'])->name('api.v1.analyses.store');
    Route::post('/score/wallet', [AnalysisController::class, 'store'])->name('api.v1.score.wallet');
    Route::get('/analyses/{id}', [AnalysisController::class, 'show'])->name('api.v1.analyses.show');
    Route::get('/analyses', [AnalysisController::class, 'index'])->name('api.v1.analyses.index');

    // Batch Analysis & Score Alias
    Route::post('/batches', [BatchController::class, 'store'])->name('api.v1.batches.store');
    Route::post('/score/batch', [BatchController::class, 'store'])->name('api.v1.score.batch');
    Route::get('/batches/{id}', [BatchController::class, 'show'])->name('api.v1.batches.show');
    Route::get('/batches/{id}/results', [BatchController::class, 'results'])->name('api.v1.batches.results');

    // Account & Credits & Analytics
    Route::get('/account/credits', [AccountController::class, 'credits'])->name('api.v1.account.credits');
    Route::get('/analytics/stats', [AccountController::class, 'credits'])->name('api.v1.analytics.stats');
});

// Also support direct /v1/ without /api prefix if routed via custom domain or reverse proxy
Route::middleware(['auth.apikey'])->group(function () {
    Route::post('/analyses', [AnalysisController::class, 'store']);
    Route::post('/score/wallet', [AnalysisController::class, 'store']);
    Route::get('/analyses/{id}', [AnalysisController::class, 'show']);
    Route::get('/analyses', [AnalysisController::class, 'index']);
    Route::post('/batches', [BatchController::class, 'store']);
    Route::post('/score/batch', [BatchController::class, 'store']);
    Route::get('/batches/{id}', [BatchController::class, 'show']);
    Route::get('/batches/{id}/results', [BatchController::class, 'results']);
    Route::get('/account/credits', [AccountController::class, 'credits']);
    Route::get('/analytics/stats', [AccountController::class, 'credits']);
});
