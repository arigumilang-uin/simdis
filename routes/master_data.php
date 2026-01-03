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
        ->middleware('role:Operator Sekolah');

    // ===================================================================
    // KONSENTRASI ROUTES (Konsentrasi Keahlian)
    // ===================================================================
    
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
        ->middleware('role:Operator Sekolah');
    
    // API: Get konsentrasi by jurusan (for dynamic dropdown in Kelas form)
    Route::get('/api/konsentrasi-by-jurusan', [KonsentrasiController::class, 'getByJurusan'])
        ->name('api.konsentrasi.by-jurusan');

    // ===================================================================
    // KELAS ROUTES
    // ===================================================================
    
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
        ->middleware('role:Operator Sekolah');
});
