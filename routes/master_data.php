<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterData\JurusanController;
use App\Http\Controllers\MasterData\KelasController;
use App\Http\Controllers\MasterData\KonsentrasiController;

/*
|--------------------------------------------------------------------------
| Master Data Routes
|--------------------------------------------------------------------------
|
| Routes untuk manajemen master data (Jurusan, Konsentrasi, Kelas).
| Restricted to Operator Sekolah only.
|
*/

Route::middleware(['auth', 'profile.completed'])->group(function () {
    
    // ===================================================================
    // JURUSAN ROUTES
    // ===================================================================
    
    // Archive routes MUST be before resource routes (to avoid wildcard conflict)
    Route::prefix('jurusan')->name('jurusan.')->middleware('role:Operator Sekolah,Waka Kurikulum')->group(function () {
        Route::get('/trash', [JurusanController::class, 'trash'])->name('trash');
        Route::post('/bulk-restore', [JurusanController::class, 'bulkRestore'])->name('bulk-restore');
        Route::delete('/bulk-force-delete', [JurusanController::class, 'bulkForceDelete'])->name('bulk-force-delete');
        Route::post('/{id}/restore', [JurusanController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [JurusanController::class, 'forceDelete'])->name('forceDelete');
    });
    
    Route::resource('jurusan', JurusanController::class)
        ->names([
            'index' => 'jurusan.index',
            'create' => 'jurusan.create',
            'store' => 'jurusan.store',
            'show' => 'jurusan.show',
            'edit' => 'jurusan.edit',
            'update' => 'jurusan.update',
            'destroy' => 'jurusan.destroy',
        ])
        ->middleware('role:Operator Sekolah,Waka Kurikulum');

    // ===================================================================
    // KONSENTRASI ROUTES (Konsentrasi Keahlian)
    // ===================================================================
    
    // Archive routes MUST be before resource routes
    Route::prefix('konsentrasi')->name('konsentrasi.')->middleware('role:Operator Sekolah,Waka Kurikulum')->group(function () {
        Route::get('/trash', [KonsentrasiController::class, 'trash'])->name('trash');
        Route::post('/bulk-restore', [KonsentrasiController::class, 'bulkRestore'])->name('bulk-restore');
        Route::delete('/bulk-force-delete', [KonsentrasiController::class, 'bulkForceDelete'])->name('bulk-force-delete');
        Route::post('/{id}/restore', [KonsentrasiController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [KonsentrasiController::class, 'forceDelete'])->name('forceDelete');
    });
    
    Route::resource('konsentrasi', KonsentrasiController::class)
        ->names([
            'index' => 'konsentrasi.index',
            'create' => 'konsentrasi.create',
            'store' => 'konsentrasi.store',
            'show' => 'konsentrasi.show',
            'edit' => 'konsentrasi.edit',
            'update' => 'konsentrasi.update',
            'destroy' => 'konsentrasi.destroy',
        ])
        ->middleware('role:Operator Sekolah,Waka Kurikulum');
    
    // API: Get konsentrasi by jurusan (for dynamic dropdown in Kelas form)
    Route::get('/api/konsentrasi-by-jurusan', [KonsentrasiController::class, 'getByJurusan'])
        ->name('api.konsentrasi.by-jurusan');

    // ===================================================================
    // KELAS ROUTES
    // ===================================================================
    
    // Archive routes MUST be before resource routes
    Route::prefix('kelas')->name('kelas.')->middleware('role:Operator Sekolah,Waka Kurikulum')->group(function () {
        Route::get('/trash', [KelasController::class, 'trash'])->name('trash');
        Route::post('/bulk-restore', [KelasController::class, 'bulkRestore'])->name('bulk-restore');
        Route::delete('/bulk-force-delete', [KelasController::class, 'bulkForceDelete'])->name('bulk-force-delete');
        Route::post('/{id}/restore', [KelasController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [KelasController::class, 'forceDelete'])->name('forceDelete');
    });
    
    Route::resource('kelas', KelasController::class)
        ->parameters(['kelas' => 'kelas']) // Force parameter name to be 'kelas' not 'kela'
        ->names([
            'index' => 'kelas.index',
            'create' => 'kelas.create',
            'store' => 'kelas.store',
            'show' => 'kelas.show',
            'edit' => 'kelas.edit',
            'update' => 'kelas.update',
            'destroy' => 'kelas.destroy',
        ])
        ->middleware('role:Operator Sekolah,Waka Kurikulum');
});
