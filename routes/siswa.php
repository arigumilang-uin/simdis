<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterData\SiswaController;

/*
|--------------------------------------------------------------------------
| Siswa Routes
|--------------------------------------------------------------------------
|
| Routes untuk manajemen Siswa (Master Data).
| Semua routes memerlukan authentication dan authorization via Policy.
|
*/

Route::middleware(['auth', 'profile.completed'])->group(function () {
    
    // ===================================================================
    // IMPORTANT: Specific routes MUST be defined BEFORE resource routes
    // to prevent Laravel from matching them as resource parameters
    // ===================================================================
    
    // Additional Siswa Routes (BEFORE resource routes)
    Route::prefix('siswa')->name('siswa.')->group(function () {
        // Bulk Create
        Route::get('/bulk-create', [SiswaController::class, 'bulkCreate'])
            ->name('bulk-create')
            ->middleware('can:create,App\Models\Siswa');

        Route::post('/bulk-store', [SiswaController::class, 'bulkStore'])
            ->name('bulk-store')
            ->middleware('can:create,App\Models\Siswa');

        // ===================================================================
        // KENAIKAN KELAS / PINDAH KELAS (Transfer)
        // ===================================================================
        Route::get('/transfer', [SiswaController::class, 'transferForm'])
            ->name('transfer')
            ->middleware('can:bulkTransfer,App\Models\Siswa');

        Route::get('/transfer/siswa', [SiswaController::class, 'getTransferSiswa'])
            ->name('transfer.siswa')
            ->middleware('can:bulkTransfer,App\Models\Siswa');

        Route::post('/bulk-transfer', [SiswaController::class, 'bulkTransfer'])
            ->name('bulk-transfer')
            ->middleware('can:bulkTransfer,App\Models\Siswa');

        // Export/Import
        Route::get('/export', [SiswaController::class, 'export'])
            ->name('export')
            ->middleware('can:bulkImport,App\Models\Siswa');

        Route::get('/import', [SiswaController::class, 'importForm'])
            ->name('import.form')
            ->middleware('can:bulkImport,App\Models\Siswa');

        Route::post('/import', [SiswaController::class, 'import'])
            ->name('import')
            ->middleware('can:bulkImport,App\Models\Siswa');

        // Bulk Operations
        Route::post('/bulk-delete', [SiswaController::class, 'bulkDelete'])
            ->name('bulk-delete')
            ->middleware('can:bulkDelete,App\Models\Siswa');

        Route::post('/bulk-delete-selection', [SiswaController::class, 'bulkDeleteSelection'])
            ->name('bulk-delete-selection')
            ->middleware('can:bulkDelete,App\Models\Siswa');

        // Restore Operations
        Route::get('/deleted', [SiswaController::class, 'showDeleted'])
            ->name('deleted')
            ->middleware('can:restore,App\Models\Siswa');
        
        Route::post('/{id}/restore', [SiswaController::class, 'restore'])
            ->name('restore')
            ->middleware('can:restore,App\Models\Siswa');

            // Permanent Delete Operations
        Route::delete('/{id}/force-delete', [SiswaController::class, 'forceDestroy'])
            ->name('force-delete')
            ->middleware('can:forceDelete,App\Models\Siswa');

        Route::post('/bulk-force-delete', [SiswaController::class, 'bulkForceDelete'])
            ->name('bulk-force-delete')
            ->middleware('can:forceDelete,App\Models\Siswa');

        // Statistics/Reports
        Route::get('/statistics', [SiswaController::class, 'statistics'])
            ->name('statistics');
    });

    // Siswa Resource Routes (AFTER specific routes)
    Route::resource('siswa', SiswaController::class)
        ->names([
            'index' => 'siswa.index',
            'create' => 'siswa.create',
            'store' => 'siswa.store',
            'show' => 'siswa.show',
            'edit' => 'siswa.edit',
            'update' => 'siswa.update',
            'destroy' => 'siswa.destroy',
        ]);
});
