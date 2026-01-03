<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pembinaan\PembinaanStatusController;

/*
|--------------------------------------------------------------------------
| Pembinaan Routes
|--------------------------------------------------------------------------
|
| Routes untuk manajemen Pembinaan Internal dengan Status Tracking.
|
*/

Route::middleware(['auth', 'profile.completed'])->group(function () {
    
    Route::prefix('pembinaan')->name('pembinaan.')->group(function () {
        
        // Index - List semua pembinaan dengan status
        Route::get('/', [PembinaanStatusController::class, 'index'])
            ->name('index');
        
        // Detail pembinaan
        Route::get('/{id}', [PembinaanStatusController::class, 'show'])
            ->name('show');
        
        // Mulai pembinaan
        Route::put('/{id}/mulai', [PembinaanStatusController::class, 'mulaiPembinaan'])
            ->name('mulai');
        
        // Selesaikan pembinaan
        Route::put('/{id}/selesaikan', [PembinaanStatusController::class, 'selesaikanPembinaan'])
            ->name('selesaikan');
        
        // Export CSV
        Route::get('/export/csv', [PembinaanStatusController::class, 'exportCsv'])
            ->name('export-csv');
    });
});
