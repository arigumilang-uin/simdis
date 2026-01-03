<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Utility\DeveloperController;
use App\Http\Controllers\Utility\FileController;

/*
|--------------------------------------------------------------------------
| Developer & Utility Routes
|--------------------------------------------------------------------------
|
| Routes untuk developer tools dan utilities.
| ONLY accessible in non-production environments.
|
*/

Route::middleware(['auth', 'profile.completed'])->group(function () {
    
    // ===================================================================
    // DEVELOPER TOOLS (Non-Production Only)
    // ===================================================================
    
    Route::prefix('developer')->name('developer.')->group(function () {
        // Impersonate role (untuk testing)
        Route::get('/impersonate/{role}', [DeveloperController::class, 'impersonate'])
            ->name('impersonate')
            ->where('role', '.*'); // Allow role names with spaces

        // Clear impersonation
        Route::get('/clear-impersonation', [DeveloperController::class, 'clear'])
            ->name('impersonate.clear'); // Match view: developer.impersonate.clear

        // Debug status
        Route::get('/status', [DeveloperController::class, 'status'])
            ->name('status');
    });

    // ===================================================================
    // FILE UTILITIES
    // ===================================================================
    
    Route::prefix('files')->name('files.')->group(function () {
        // Serve uploaded files (if not using public storage link)
        Route::get('/{path}', [FileController::class, 'serve'])
            ->name('serve')
            ->where('path', '.*');
    });

    // ===================================================================
    // BUKTI/EVIDENCE FILE SERVING
    // ===================================================================
    
    Route::get('/bukti/{path}', function ($path) {
        // Serve evidence files from storage
        $filePath = storage_path('app/public/' . $path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }
        
        return response()->file($filePath);
    })->name('bukti.show')->where('path', '.*');
});
