<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Report\ApprovalController;
use App\Http\Controllers\Report\SiswaPerluPembinaanController;

/*
|--------------------------------------------------------------------------
| Report Routes
|--------------------------------------------------------------------------
|
| Routes untuk laporan dan analytics.
| Access based on role permissions.
|
*/

Route::middleware(['auth', 'profile.completed'])->group(function () {
    
    // ===================================================================
    // GENERAL REPORTS
    // ===================================================================
    
    Route::prefix('reports')->name('reports.')->group(function () {
        // Main reports dashboard
        Route::get('/', [ReportController::class, 'index'])
            ->name('index');

        // Specific reports
        Route::get('/pelanggaran', [ReportController::class, 'pelanggaranReport'])
            ->name('pelanggaran');

        Route::get('/tindak-lanjut', [ReportController::class, 'tindakLanjutReport'])
            ->name('tindak-lanjut');

        Route::get('/siswa', [ReportController::class, 'siswaReport'])
            ->name('siswa');

        Route::get('/kelas', [ReportController::class, 'kelasReport'])
            ->name('kelas');

        Route::get('/jurusan', [ReportController::class, 'jurusanReport'])
            ->name('jurusan');

        // Export reports
        Route::get('/pelanggaran/export', [ReportController::class, 'exportPelanggaran'])
            ->name('pelanggaran.export');

        Route::get('/tindak-lanjut/export', [ReportController::class, 'exportTindakLanjut'])
            ->name('tindak-lanjut.export');
    });

    // ===================================================================
    // APPROVAL REPORTS (Kepala Sekolah, Waka, Kaprodi)
    // ===================================================================
    
    Route::prefix('approval')->name('approval.')->group(function () {
        // List pending approvals
        Route::get('/', [ApprovalController::class, 'index'])
            ->name('index')
            ->middleware('role:Kepala Sekolah,Waka Kesiswaan,Kaprodi');

        // Show detail kasus untuk approval
        Route::get('/{tindakLanjut}', [ApprovalController::class, 'show'])
            ->name('show')
            ->middleware('role:Kepala Sekolah,Waka Kesiswaan,Kaprodi');

        // Approval history
        Route::get('/history', [ApprovalController::class, 'history'])
            ->name('history')
            ->middleware('role:Kepala Sekolah,Waka Kesiswaan,Kaprodi');

        // Statistics
        Route::get('/statistics', [ApprovalController::class, 'statistics'])
            ->name('statistics')
            ->middleware('role:Kepala Sekolah,Waka Kesiswaan');
    });

    // ===================================================================
    // SISWA PERLU PEMBINAAN REPORT
    // ===================================================================
    
    Route::prefix('pembinaan')->name('pembinaan.')->middleware('role:Kepala Sekolah,Waka Kesiswaan,Kaprodi,Wali Kelas')->group(function () {
        // List siswa perlu pembinaan
        Route::get('/', [SiswaPerluPembinaanController::class, 'index'])
            ->name('index');

        // Detail siswa pembinaan
        Route::get('/{siswa}', [SiswaPerluPembinaanController::class, 'show'])
            ->name('show');

        // Export
        Route::get('/export/excel', [SiswaPerluPembinaanController::class, 'exportExcel'])
            ->name('export.excel');

        Route::get('/export/pdf', [SiswaPerluPembinaanController::class, 'exportPdf'])
            ->name('export.pdf');

        // Generate surat panggilan massal
        Route::post('/generate-surat', [SiswaPerluPembinaanController::class, 'generateSuratMassal'])
            ->name('generate-surat')
            ->middleware('role:Operator Sekolah,Waka Kesiswaan');
    });
});
