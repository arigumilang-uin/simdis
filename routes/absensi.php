<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Absensi\AbsensiController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\JadwalMengajarController;
use App\Http\Controllers\Admin\PeriodeSemesterController;
use App\Http\Controllers\Admin\KurikulumController;
use App\Http\Controllers\Admin\TemplateJamController;

/*
|--------------------------------------------------------------------------
| Absensi Routes
|--------------------------------------------------------------------------
|
| Routes for attendance (absensi), kurikulum, mata pelajaran, 
| template jam, and jadwal mengajar.
|
*/

Route::middleware(['auth', 'profile.completed'])->group(function () {

    // ===================================================================
    // ABSENSI ROUTES (For Guru/Wali Kelas to take attendance)
    // ===================================================================
    
    Route::prefix('absensi')->name('absensi.')->group(function () {
        // Dashboard - Semua jadwal per hari
        Route::get('/', [AbsensiController::class, 'index'])->name('index');
        
        // Grid view absensi (siswa x pertemuan)
        Route::get('/{jadwalId}/grid', [AbsensiController::class, 'grid'])->name('grid');
        
        // AJAX: Update single absensi
        Route::post('/update-single', [AbsensiController::class, 'updateSingle'])->name('updateSingle');
        
        // AJAX: Batch update semua siswa
        Route::post('/batch-update', [AbsensiController::class, 'batchUpdate'])->name('batchUpdate');
        
        // Form absensi (legacy - redirect to grid)
        Route::get('/{jadwalId}/create', [AbsensiController::class, 'create'])->name('create');
        
        // Simpan absensi batch (legacy)
        Route::post('/store', [AbsensiController::class, 'store'])->name('store');
        
        // Lihat detail absensi
        Route::get('/{jadwalId}/show', [AbsensiController::class, 'show'])->name('show');
        
        // Laporan rekap
        Route::get('/report', [AbsensiController::class, 'report'])->name('report');
    });

    // ===================================================================
    // ADMIN ROUTES - AKADEMIK
    // ===================================================================
    
    Route::prefix('admin')->name('admin.')->middleware('role:Operator Sekolah,Waka Kurikulum,Developer')->group(function () {
        
        // --- Kurikulum ---
        Route::prefix('kurikulum')->name('kurikulum.')->group(function () {
            Route::get('/', [KurikulumController::class, 'index'])->name('index');
            Route::get('/trash', [KurikulumController::class, 'trash'])->name('trash');
            Route::post('/bulk-restore', [KurikulumController::class, 'bulkRestore'])->name('bulk-restore');
            Route::delete('/bulk-force-delete', [KurikulumController::class, 'bulkForceDelete'])->name('bulk-force-delete');
            Route::get('/create', [KurikulumController::class, 'create'])->name('create');
            Route::post('/', [KurikulumController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [KurikulumController::class, 'edit'])->name('edit');
            Route::put('/{id}', [KurikulumController::class, 'update'])->name('update');
            Route::delete('/{id}', [KurikulumController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/restore', [KurikulumController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [KurikulumController::class, 'forceDelete'])->name('forceDelete');
        });

        // --- Periode Semester ---
        Route::prefix('periode-semester')->name('periode-semester.')->group(function () {
            Route::get('/', [PeriodeSemesterController::class, 'index'])->name('index');
            Route::get('/trash', [PeriodeSemesterController::class, 'trash'])->name('trash');
            Route::post('/bulk-restore', [PeriodeSemesterController::class, 'bulkRestore'])->name('bulk-restore');
            Route::delete('/bulk-force-delete', [PeriodeSemesterController::class, 'bulkForceDelete'])->name('bulk-force-delete');
            Route::get('/create', [PeriodeSemesterController::class, 'create'])->name('create');
            Route::post('/', [PeriodeSemesterController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [PeriodeSemesterController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PeriodeSemesterController::class, 'update'])->name('update');
            Route::delete('/{id}', [PeriodeSemesterController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/restore', [PeriodeSemesterController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [PeriodeSemesterController::class, 'forceDelete'])->name('forceDelete');
            Route::post('/{id}/set-active', [PeriodeSemesterController::class, 'setActive'])->name('setActive');
            Route::post('/{id}/generate-pertemuan', [PeriodeSemesterController::class, 'generatePertemuan'])->name('generatePertemuan');
            // Tingkat Kurikulum configuration
            Route::get('/{id}/tingkat-kurikulum', [PeriodeSemesterController::class, 'tingkatKurikulum'])->name('tingkatKurikulum');
            Route::post('/{id}/tingkat-kurikulum', [PeriodeSemesterController::class, 'saveTingkatKurikulum'])->name('saveTingkatKurikulum');
        });

        // --- Mata Pelajaran ---
        Route::prefix('mata-pelajaran')->name('mata-pelajaran.')->group(function () {
            Route::get('/', [MataPelajaranController::class, 'index'])->name('index');
            Route::get('/create', [MataPelajaranController::class, 'create'])->name('create');
            Route::post('/', [MataPelajaranController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [MataPelajaranController::class, 'edit'])->name('edit');
            Route::put('/{id}', [MataPelajaranController::class, 'update'])->name('update');
            Route::delete('/{id}', [MataPelajaranController::class, 'destroy'])->name('destroy');
            // API: Get mapel by kurikulum
            Route::get('/by-kurikulum/{kurikulumId}', [MataPelajaranController::class, 'getByKurikulum'])->name('byKurikulum');
            // API: Get guru by mapel
            Route::get('/{mapelId}/guru', [MataPelajaranController::class, 'getGuruByMapel'])->name('guruByMapel');
        });

        // --- Template Jam (replaces Jam Pelajaran) ---
        Route::prefix('template-jam')->name('template-jam.')->group(function () {
            Route::get('/', [TemplateJamController::class, 'index'])->name('index');
            Route::post('/', [TemplateJamController::class, 'store'])->name('store');
            Route::post('/generate', [TemplateJamController::class, 'generate'])->name('generate');
            Route::post('/add-row', [TemplateJamController::class, 'addRow'])->name('addRow');
            Route::delete('/bulk-destroy', [TemplateJamController::class, 'bulkDestroy'])->name('bulkDestroy');
            Route::put('/{id}', [TemplateJamController::class, 'update'])->name('update');
            Route::patch('/{id}/update-field', [TemplateJamController::class, 'updateField'])->name('updateField');
            Route::delete('/{id}', [TemplateJamController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/reorder', [TemplateJamController::class, 'reorder'])->name('reorder');
            Route::post('/copy', [TemplateJamController::class, 'copy'])->name('copy');
        });

        // --- Jadwal Mengajar ---
        Route::prefix('jadwal-mengajar')->name('jadwal-mengajar.')->group(function () {
            Route::get('/', [JadwalMengajarController::class, 'index'])->name('index');
            Route::get('/matrix', [JadwalMengajarController::class, 'matrix'])->name('matrix');
            Route::post('/update-cell', [JadwalMengajarController::class, 'updateCell'])->name('updateCell');
            Route::delete('/{id}', [JadwalMengajarController::class, 'destroy'])->name('destroy');
            // PDF Export
            Route::get('/pdf/download', [\App\Http\Controllers\Admin\JadwalPdfController::class, 'generate'])->name('pdf.download');
            Route::get('/pdf/preview', [\App\Http\Controllers\Admin\JadwalPdfController::class, 'preview'])->name('pdf.preview');
            // API endpoints
            Route::get('/api/mapel-for-kelas', [JadwalMengajarController::class, 'getMapelForKelas'])->name('api.mapelForKelas');
            Route::get('/api/template-jam', [JadwalMengajarController::class, 'getTemplateJam'])->name('api.templateJam');
        });
    });
});
