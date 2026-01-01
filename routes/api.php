<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Document generation API routes
Route::middleware(['auth', 'throttle:60,1'])->prefix('documents')->name('api.documents.')->group(function () {
    // Document generation endpoints with specific rate limits
    Route::post('/preview', [DocumentController::class, 'preview'])
        ->middleware('throttle:20,1')
        ->name('preview');
    Route::post('/generate', [DocumentController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('generate');
    Route::get('/download/{session}/{document}', [DocumentController::class, 'download'])
        ->middleware('throttle:30,1')
        ->name('download');
    Route::get('/pdf-status/{session}/{document}', [DocumentController::class, 'pdfStatus'])
        ->name('pdf-status');
    
    // Configuration endpoints
    Route::get('/types', [DocumentController::class, 'types'])->name('types');
    Route::get('/countries', [DocumentController::class, 'countries'])->name('countries');
    Route::get('/tone-levels', [DocumentController::class, 'toneLevels'])->name('tone-levels');
    
    // Health check endpoint
    Route::get('/health', [DocumentController::class, 'health'])->name('health');
});

// Public health check (no auth required)
Route::get('/health', [DocumentController::class, 'health'])->name('health');