<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterData\SiswaController;
use App\Http\Controllers\TindakLanjut\TindakLanjutController;

/*
|--------------------------------------------------------------------------
| Legacy Route Adapter
|--------------------------------------------------------------------------
|
| This file provides backward compatibility routes for legacy Blade views.
| Maps old route names (used in views) to new Clean Architecture controllers.
|
| IMPORTANT: Do NOT modify views - fix routing here instead!
|
| Pattern:
|   Old Route Name → New Controller Method
|
*/

Route::middleware(['auth', 'profile.completed'])->group(function () {
    
    // ===================================================================
    // LEGACY: SISWA BULK OPERATIONS (Placeholder - Not Implemented Yet)
    // ===================================================================
    
    /**
     * Views call these routes but methods don't exist in SiswaController yet
     * Providing redirects as temporary solution
     */
    
    Route::get('/siswa/bulk/create', function () {
        return redirect()->route('siswa.create')
            ->with('info', 'Bulk create feature coming soon. Use single create for now.');
    })->name('siswa.bulk.create')->middleware('role:Operator Sekolah');

    Route::post('/siswa/bulk/store', function () {
        return redirect()->route('siswa.index')
            ->with('error', 'Bulk store not implemented yet.');
    })->name('siswa.bulk.store')->middleware('role:Operator Sekolah');

    // ===================================================================
    // LEGACY: KASUS ROUTES (Old name for Tindak Lanjut)
    // ===================================================================
    
    /**
     * Legacy route: kasus.edit
     * Views expect: route('kasus.edit', $id)
     * Maps to: TindakLanjutController@edit
     * 
     * Note: "kasus" was old terminology, now it's "tindak-lanjut"
     */
    Route::get('/kasus/{tindakLanjut}/edit', [\App\Http\Controllers\Pelanggaran\TindakLanjutController::class, 'edit'])
        ->name('kasus.edit');

    /**
     * Legacy route: kasus.update
     * Views expect: route('kasus.update', $id)
     * Maps to: TindakLanjutController@update
     */
    Route::put('/kasus/{tindakLanjut}', [\App\Http\Controllers\Pelanggaran\TindakLanjutController::class, 'update'])
        ->name('kasus.update');

    /**
     * Legacy route: kasus.show
     * Views expect: route('kasus.show', $id)
     * Maps to: TindakLanjutController@show
     */
    Route::get('/kasus/{tindakLanjut}', [\App\Http\Controllers\Pelanggaran\TindakLanjutController::class, 'show'])
        ->name('kasus.show');

    /**
     * Legacy route: kasus.cetak
     * Views expect: route('kasus.cetak', $id)
     * Maps to: Tind akLanjutController@cetakSurat
     * 
     * Note: This generates surat panggilan PDF
     */
    Route::get('/kasus/{id}/cetak', [\App\Http\Controllers\Pelanggaran\TindakLanjutController::class, 'cetakSurat'])
        ->name('kasus.cetak');

    // ===================================================================
    // LEGACY: MY RIWAYAT (Personal Violation Records)
    // ===================================================================
    
    /**
     * my-riwayat routes were referenced in admin.php but not fully defined
     * These allow users to view/edit/delete their own violation records
     * 
     * FIXED: Changed {id} to {riwayat} to match view parameter passing
     * View passes: ['riwayat' => $r->id]
     */
    
    Route::get('/riwayat/my/edit/{riwayat}', [\App\Http\Controllers\Pelanggaran\RiwayatPelanggaranController::class, 'edit'])
        ->name('my-riwayat.edit');

    Route::delete('/riwayat/my/{riwayat}', [\App\Http\Controllers\Pelanggaran\RiwayatPelanggaranController::class, 'destroy'])
        ->name('my-riwayat.destroy');

    Route::post('/riwayat/my', [\App\Http\Controllers\Pelanggaran\RiwayatPelanggaranController::class, 'store'])
        ->name('my-riwayat.store');

    Route::get('/riwayat/my/create', [\App\Http\Controllers\Pelanggaran\RiwayatPelanggaranController::class, 'create'])
        ->name('my-riwayat.create');

    // ===================================================================
    // LEGACY: AUDIT ROUTES (System Audit/Logging - if exists)
    // ===================================================================
    
    /**
     * Note: If these views exist but controllers don't,
     * redirect to appropriate admin pages or placeholder
     */
    
    Route::get('/audit/siswa/{id}', function ($id) {
        return redirect()->route('siswa.show', $id)
            ->with('info', 'Audit feature merged into student details.');
    })->name('audit.siswa.show');

    Route::delete('/audit/siswa/{id}', function ($id) {
        return redirect()->route('siswa.destroy', $id);
    })->name('audit.siswa.destroy');

    Route::get('/audit/siswa/summary', function () {
        return redirect()->route('dashboard')
            ->with('info', 'Audit summary feature merged into dashboard.');
    })->name('audit.siswa.summary');

    // ===================================================================
    // LEGACY: MY RIWAYAT UPDATE (Missing PUT/PATCH route)
    // ===================================================================
    
    Route::match(['PUT', 'PATCH'], '/riwayat/my/{riwayat}', [\App\Http\Controllers\Pelanggaran\RiwayatPelanggaranController::class, 'update'])
        ->name('my-riwayat.update');

    // ===================================================================
    // LEGACY: LAPORAN (REPORTS) ROUTES
    // ===================================================================
    
    /**
     * Laporan routes - old naming for reports
     * Redirect to new report routes or placeholder
     */
    
    Route::get('/laporan', function () {
        return redirect()->route('kepala-sekolah.reports.index');
    })->name('laporan.index');

    Route::get('/laporan/cetak', function () {
        return redirect()->route('dashboard')
            ->with('info', 'Fitur cetak laporan sedang dalam pengembangan.');
    })->name('laporan.cetak');

    Route::get('/laporan/pelanggaran', function () {
        return redirect()->route('kepala-sekolah.reports.pelanggaran');
    })->name('laporan.pelanggaran');

    Route::get('/laporan/tindak-lanjut', function () {
        return redirect()->route('kepala-sekolah.reports.tindak-lanjut');
    })->name('laporan.tindak-lanjut');

    Route::get('/laporan/siswa', function () {
        return redirect()->route('siswa.index');
    })->name('laporan.siswa');

    // ===================================================================
    // LEGACY: ADDITIONAL AUDIT ROUTES
    // ===================================================================
    
    Route::get('/audit/pelanggaran/{id}', function ($id) {
        return redirect()->route('riwayat.show', $id);
    })->name('audit.pelanggaran.show');

    Route::get('/audit/tindak-lanjut/{id}', function ($id) {
        return redirect()->route('tindak-lanjut.show', $id);
    })->name('audit.tindak-lanjut.show');

    Route::get('/audit/users/{id}', function ($id) {
        return redirect()->route('users.show', $id);
    })->name('audit.users.show');

    // Additional audit.siswa routes
    Route::get('/audit/siswa', function () {
        return redirect()->route('siswa.index');
    })->name('audit.siswa.index');

    Route::get('/audit/siswa/preview', function () {
        return redirect()->route('siswa.index')
            ->with('info', 'Preview feature merged into main view.');
    })->name('audit.siswa.preview');

    Route::get('/audit/siswa/create', function () {
        return redirect()->route('siswa.create');
    })->name('audit.siswa.create');

    Route::post('/audit/siswa', function () {
        return redirect()->route('siswa.store');
    })->name('audit.siswa.store');

    Route::get('/audit/siswa/{id}/edit', function ($id) {
        return redirect()->route('siswa.edit', $id);
    })->name('audit.siswa.edit');

    Route::put('/audit/siswa/{id}', function ($id) {
        return redirect()->route('siswa.update', $id);
    })->name('audit.siswa.update');

    /**
     * LEGACY: Profile completion routes
     * 
     * REMOVED - Replaced by ProfileCompletionController in routes/web.php
     * Routes: profile.complete.show, profile.complete.store, profile.complete.skip
     */

    // ===================================================================
    // LEGACY: DATA ROUTES (Old Master Data Naming)
    // ===================================================================
    
    Route::get('/data/siswa', function () {
        return redirect()->route('siswa.index');
    })->name('data.siswa.index');

    Route::get('/data/kelas', function () {
        return redirect()->route('kelas.index');
    })->name('data.kelas.index');

    Route::get('/data/jurusan', function () {
        return redirect()->route('jurusan.index');
    })->name('data.jurusan.index');

    Route::get('/data/pelanggaran', function () {
        return redirect()->route('jenis-pelanggaran.index');
    })->name('data.pelanggaran.index');

    // ===================================================================
    // LEGACY: PLACEHOLDER ROUTES (Feature Under Development/Maintenance)
    // ===================================================================
    
    /**
     * Generic placeholder for any other missing routes
     * Returns user to dashboard with info message
     * Better than 500 error!
     */
    
    // Example placeholders - uncomment and customize as needed:
    
    /*
    Route::get('/placeholder-feature', function () {
        return redirect()->route('dashboard')
            ->with('info', 'This feature is currently under maintenance.');
    })->name('placeholder.route');
    */

    
    // ===================================================================
    // LEGACY: AUDIT ACTIVITY EXPORT
    // ===================================================================
    
    /**
     * Audit activity export to CSV
     * Placeholder until export feature is fully implemented
     */
    Route::get('/audit/activity/export-csv', function () {
        return redirect()->route('audit.activity.index')
            ->with('info', 'Export CSV feature coming soon. Contact IT for manual export.');
    })->name('audit.activity.export-csv')
      ->middleware('role:Operator Sekolah,Kepala Sekolah');

    // ===================================================================
    // LEGACY: ACCOUNT ROUTES (Already handled in routes/user.php)
    // ===================================================================
    
    /**
     * Note: account.edit, account.update, account.password already defined
     * in routes/user.php as backward compatibility aliases for profile.* routes.
     * 
     * If you see errors about duplicate route names, check routes/user.php first.
     */
});

/*
|--------------------------------------------------------------------------
| Legacy Route Adapter - Documentation
|--------------------------------------------------------------------------
|
| WHY THIS FILE EXISTS:
| - Legacy Blade views use old route names from before Clean Architecture
| - Rather than modify 70+ view files, we create aliases here
| - Maps old route names → new controller methods
|
| HOW TO USE:
| 1. Run: php artisan audit:views --suggestions
| 2. Find broken routes in output
| 3. Add route alias here pointing to correct controller
| 4. Re-run audit to verify
|
| PATTERNS FOR ADDING ROUTES:
|
| Pattern 1 - Direct Controller Mapping:
|   Route::get('/old-path/{id}', [NewController::class, 'method'])
|       ->name('old.route.name');
|
| Pattern 2 - Redirect (Temporary):
|   Route::get('/old-path', function () {
|       return redirect()->route('new.route')
|           ->with('info', 'Feature moved/coming soon');
|   })->name('old.route.name');
|
| Pattern 3 - Placeholder (Maintenance):
|   Route::get('/feature', function () {
|       return redirect()->route('dashboard')
|           ->with('warning', 'Feature under maintenance');
|   })->name('feature.index');
|
| MAINTENANCE:
| - Run audit tool after any route refactoring
| - Add new legacy mappings as needed
| - Remove legacy routes when views are updated
| - Document WHY each legacy route exists
|
| GOAL:
| - php artisan audit:views should show: "No broken routes found"
|
*/
