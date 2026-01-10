<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterData\SiswaController;
use App\Http\Controllers\MasterData\SiswaBulkController;
use App\Http\Controllers\MasterData\SiswaArchiveController;
use App\Http\Controllers\MasterData\SiswaTransferController;

/*
|--------------------------------------------------------------------------
| Siswa Routes
|--------------------------------------------------------------------------
|
| Routes untuk manajemen Siswa (Master Data).
| Semua routes memerlukan authentication dan authorization via Policy.
|
| STRUCTURE:
| - Core CRUD → SiswaController
| - Bulk Operations → SiswaBulkController
| - Archive/Restore → SiswaArchiveController
| - Transfer/Kenaikan → SiswaTransferController
|
*/

Route::middleware(['auth', 'profile.completed'])->group(function () {
    
    // ===================================================================
    // IMPORTANT: Specific routes MUST be defined BEFORE resource routes
    // to prevent Laravel from matching them as resource parameters
    // ===================================================================
    
    Route::prefix('siswa')->name('siswa.')->group(function () {
        
        // =============================================================
        // BULK OPERATIONS (SiswaBulkController)
        // =============================================================
        Route::get('/bulk-create', [SiswaBulkController::class, 'create'])
            ->name('bulk-create')
            ->middleware('can:create,App\Models\Siswa');
        
        Route::post('/bulk-store', [SiswaBulkController::class, 'store'])
            ->name('bulk-store')
            ->middleware('can:create,App\Models\Siswa');

        Route::post('/bulk-delete', [SiswaBulkController::class, 'deleteByKelas'])
            ->name('bulk-delete')
            ->middleware('can:bulkDelete,App\Models\Siswa');

        Route::post('/bulk-delete-selection', [SiswaBulkController::class, 'deleteSelected'])
            ->name('bulk-delete-selection')
            ->middleware('can:bulkDelete,App\Models\Siswa');

        // =============================================================
        // TRANSFER / KENAIKAN KELAS (SiswaTransferController)
        // =============================================================
        Route::get('/transfer', [SiswaTransferController::class, 'index'])
            ->name('transfer')
            ->middleware('can:bulkTransfer,App\Models\Siswa');

        Route::get('/transfer/siswa', [SiswaTransferController::class, 'getSiswaByKelas'])
            ->name('transfer.siswa')
            ->middleware('can:bulkTransfer,App\Models\Siswa');

        Route::post('/bulk-transfer', [SiswaTransferController::class, 'transfer'])
            ->name('bulk-transfer')
            ->middleware('can:bulkTransfer,App\Models\Siswa');

        // =============================================================
        // ARCHIVE / DELETED MANAGEMENT (SiswaArchiveController)
        // =============================================================
        Route::get('/deleted', [SiswaArchiveController::class, 'index'])
            ->name('deleted')
            ->middleware('can:restore,App\Models\Siswa');
        
        Route::post('/{id}/restore', [SiswaArchiveController::class, 'restore'])
            ->name('restore')
            ->middleware('can:restore,App\Models\Siswa');

        Route::delete('/{id}/force-delete', [SiswaArchiveController::class, 'forceDestroy'])
            ->name('force-delete')
            ->middleware('can:forceDelete,App\Models\Siswa');

        Route::post('/bulk-force-delete', [SiswaArchiveController::class, 'bulkForceDestroy'])
            ->name('bulk-force-delete')
            ->middleware('can:forceDelete,App\Models\Siswa');

        Route::post('/bulk-restore', [SiswaArchiveController::class, 'bulkRestore'])
            ->name('bulk-restore')
            ->middleware('can:restore,App\Models\Siswa');

        // =============================================================
        // AJAX CHECKS (SiswaController)
        // =============================================================
        Route::get('/check-nisn', [SiswaController::class, 'checkNisn'])->name('check-nisn');
        Route::get('/check-wali-hp', [SiswaController::class, 'checkWaliHp'])->name('check-wali-hp');
    });

    // =================================================================
    // CORE CRUD - Resource Routes (SiswaController)
    // =================================================================
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
