<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TindakLanjut\TindakLanjutController;

/*
|--------------------------------------------------------------------------
| Tindak Lanjut Routes
|--------------------------------------------------------------------------
|
| Routes untuk manajemen Tindak Lanjut (Follow-up Actions).
| Includes approval workflow routes.
|
*/

Route::middleware(['auth', 'profile.completed'])->group(function () {
    
    // ===================================================================
    // STATIC ROUTES (HARUS SEBELUM RESOURCE!)
    // ===================================================================
    // Ini penting karena jika resource didefinisikan duluan,
    // route /pending-approval akan ditangkap sebagai /tindak-lanjut/{id}
    
    Route::prefix('tindak-lanjut')->name('tindak-lanjut.')->group(function () {
        // List pending approval (for approvers)
        Route::get('/pending-approval', [TindakLanjutController::class, 'pendingApproval'])
            ->name('pending-approval')
            ->middleware('role:Kepala Sekolah,Waka Kesiswaan,Kaprodi');

        // My approvals (yang saya setujui/tolak)
        Route::get('/my-approvals', [TindakLanjutController::class, 'myApprovals'])
            ->name('my-approvals');

        // Statistics
        Route::get('/statistics', [TindakLanjutController::class, 'statistics'])
            ->name('statistics');
    });

    // ===================================================================
    // TINDAK LANJUT CRUD ROUTES (RESOURCE)
    // ===================================================================
    
    Route::resource('tindak-lanjut', TindakLanjutController::class)
        ->names([
            'index' => 'tindak-lanjut.index',
            'create' => 'tindak-lanjut.create',
            'store' => 'tindak-lanjut.store',
            'show' => 'tindak-lanjut.show',
            'edit' => 'tindak-lanjut.edit',
            'update' => 'tindak-lanjut.update',
            'destroy' => 'tindak-lanjut.destroy',
        ]);

    // ===================================================================
    // APPROVAL WORKFLOW ROUTES (WITH ID PARAMETER)
    // ===================================================================
    
    Route::prefix('tindak-lanjut/{id}')->name('tindak-lanjut.')->group(function () {
        // Approve tindak lanjut (authorization done in controller)
        Route::post('/approve', [TindakLanjutController::class, 'approve'])
            ->name('approve');

        // Reject tindak lanjut (authorization done in controller)
        Route::post('/reject', [TindakLanjutController::class, 'reject'])
            ->name('reject');

        // Complete/close tindak lanjut (authorization done in controller)
        Route::post('/complete', [TindakLanjutController::class, 'complete'])
            ->name('complete');
    });

    // ===================================================================
    // SURAT PANGGILAN MANAGEMENT ROUTES
    // ===================================================================
    
    Route::prefix('tindak-lanjut/{id}')->name('tindak-lanjut.')->group(function () {
        // Preview surat (modal/page)
        Route::get('/preview-surat', [TindakLanjutController::class, 'previewSurat'])
            ->name('preview-surat');
        
        // Edit surat content
        Route::get('/edit-surat', [TindakLanjutController::class, 'editSurat'])
            ->name('edit-surat');
        
        // Update surat content
        Route::put('/update-surat', [TindakLanjutController::class, 'updateSurat'])
            ->name('update-surat');
        
        // Cetak surat (Download PDF + Log print activity)
        Route::get('/cetak-surat', [TindakLanjutController::class, 'cetakSurat'])
            ->name('cetak-surat');
        
        // Mulai Tangani (Change status: Disetujui -> Sedang Ditangani)
        Route::put('/mulai-tangani', [TindakLanjutController::class, 'mulaiTangani'])
            ->name('mulai-tangani');
        
        // Selesaikan Kasus (Change status: Ditangani -> Selesai)
        Route::put('/selesaikan', [TindakLanjutController::class, 'selesaikan'])
            ->name('selesaikan');
    });

    // ===================================================================
    // LEGACY ROUTE ALIASES (Backward Compatibility)
    // ===================================================================
    // Migrated from legacy.php - maps old "kasus.*" routes to clean controllers
    // Can be removed once all views are updated to use "tindak-lanjut.*" routes
    
    Route::prefix('kasus')->name('kasus.')->group(function () {
        Route::get('/{tindakLanjut}/edit', [TindakLanjutController::class, 'edit'])
            ->name('edit');
        
        Route::put('/{tindakLanjut}', [TindakLanjutController::class, 'update'])
            ->name('update');
        
        Route::get('/{tindakLanjut}', [TindakLanjutController::class, 'show'])
            ->name('show');
        
        Route::get('/{id}/cetak', [TindakLanjutController::class, 'cetakSurat'])
            ->name('cetak');
    });
});
