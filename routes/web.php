<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes (Core)
|--------------------------------------------------------------------------
|
| Core application routes (Dashboard, Auth, Public pages).
| Domain-specific routes are split into separate files:
| - routes/siswa.php
| - routes/pelanggaran.php
| - routes/tindak_lanjut.php
| - routes/user.php
|
*/

// ===================================================================
// AUTHENTICATION ROUTES (Guest)
// ===================================================================

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/', [LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/', [LoginController::class, 'login'])
        ->name('login.post');
});

// ===================================================================
// AUTHENTICATED ROUTES (Profile Completion - Excluded from middleware)
// ===================================================================

Route::middleware(['auth'])->group(function () {
    
    // Logout (always accessible)
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    // ===================================================================
    // PROFILE COMPLETION ROUTES (First Login)
    // Rute ini dikecualikan dari middleware profile.completed
    // ===================================================================
    
    Route::get('/profile/complete', [\App\Http\Controllers\Auth\ProfileCompletionController::class, 'show'])
        ->name('profile.complete.show');

    Route::post('/profile/complete', [\App\Http\Controllers\Auth\ProfileCompletionController::class, 'store'])
        ->name('profile.complete.store');

    Route::get('/profile/complete/skip', [\App\Http\Controllers\Auth\ProfileCompletionController::class, 'skip'])
        ->name('profile.complete.skip');
});

// ===================================================================
// AUTHENTICATED ROUTES (Protected by profile.completed)
// ===================================================================

Route::middleware(['auth', 'profile.completed'])->group(function () {
    
    // ===================================================================
    // DASHBOARD ROUTES (Role-based with Controllers)
    // ===================================================================
    
    // Main dashboard (auto-redirect to role-specific dashboard)
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        // Role-based dashboard redirect
        if ($user->hasAnyRole(['Waka Kesiswaan', 'Operator Sekolah'])) {
            return redirect('/dashboard/admin');
        } elseif ($user->hasRole('Kepala Sekolah')) {
            return redirect('/dashboard/kepsek');
        } elseif ($user->hasRole('Kaprodi')) {
            return redirect('/dashboard/kaprodi');
        } elseif ($user->hasRole('Wali Kelas')) {
            return redirect('/dashboard/walikelas');
        } elseif ($user->hasRole('Waka Sarana')) {
            return redirect('/dashboard/waka-sarana');
        } elseif ($user->hasRole('Guru')) {
            return redirect('/pelanggaran/catat');
        } elseif ($user->hasRole('Wali Murid')) {
            return redirect('/dashboard/wali_murid');
        } elseif ($user->hasRole('Developer')) {
            return redirect('/dashboard/developer');
        }
        
        // Fallback: redirect to admin dashboard
        return redirect('/dashboard/admin');
    })->name('dashboard');

    // Role-specific dashboards with REAL controllers & statistics
    Route::get('/dashboard/admin', [\App\Http\Controllers\Dashboard\AdminDashboardController::class, 'index'])
        ->name('dashboard.admin');

    Route::get('/dashboard/kepsek', [\App\Http\Controllers\Dashboard\KepsekDashboardController::class, 'index'])
        ->name('dashboard.kepsek');

    Route::get('/dashboard/kaprodi', [\App\Http\Controllers\Dashboard\KaprodiDashboardController::class, 'index'])
        ->name('dashboard.kaprodi');

    Route::get('/dashboard/walikelas', [\App\Http\Controllers\Dashboard\WaliKelasDashboardController::class, 'index'])
        ->name('dashboard.walikelas');

    Route::get('/dashboard/waka-sarana', [\App\Http\Controllers\Dashboard\WakaSaranaDashboardController::class, 'index'])
        ->name('dashboard.waka-sarana');

    Route::get('/dashboard/wali_murid', [\App\Http\Controllers\Dashboard\WaliMuridDashboardController::class, 'index'])
        ->name('dashboard.wali_murid');

    Route::get('/dashboard/developer', [\App\Http\Controllers\Dashboard\DeveloperDashboardController::class, 'index'])
        ->name('dashboard.developer');

    // ===================================================================
    // QUICK ACCESS / SHORTCUTS
    // ===================================================================
    
    Route::prefix('quick')->name('quick.')->group(function () {
        Route::get('/catat-pelanggaran', function () {
            return redirect()->route('riwayat.create');
        })->name('catat-pelanggaran');

        Route::get('/daftar-siswa', function () {
            return redirect()->route('siswa.index');
        })->name('daftar-siswa');

        Route::get('/pending-approval', function () {
            return redirect()->route('tindak-lanjut.pending-approval');
        })->name('pending-approval');
    });


    // ===================================================================
    // PELANGGARAN SHORTCUTS
    // ===================================================================
    
    // Shortcut untuk catat pelanggaran (untuk Guru)
    Route::get('/pelanggaran/catat', function () {
        return redirect()->route('riwayat.create');
    })->name('pelanggaran.catat');

    // ===================================================================
    // REPORTS & ANALYTICS
    // ===================================================================
    
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/overview', function () {
            return view('reports.overview');
        })->name('overview');

        Route::get('/pelanggaran-by-kelas', function () {
            return view('reports.pelanggaran-kelas');
        })->name('pelanggaran-kelas');

        Route::get('/pelanggaran-by-jurusan', function () {
            return view('reports.pelanggaran-jurusan');
        })->name('pelanggaran-jurusan');
    });

    // ===================================================================
    // KEPALA SEKOLAH REPORTS (Consolidated - Role Protected)
    // ===================================================================
    Route::prefix('kepala-sekolah/reports')
        ->name('kepala-sekolah.reports.')
        ->middleware('role:Kepala Sekolah,Waka Kesiswaan')
        ->group(function () {
            // Preview report (form submit uses POST) - Uses ReportController for consistent form handling
            Route::post('/preview', [\App\Http\Controllers\Report\ReportController::class, 'preview'])
                ->name('preview');
            
            // Export CSV
            Route::get('/export-csv', [\App\Http\Controllers\Report\ReportController::class, 'exportCsv'])
                ->name('export-csv');
            
            // Export PDF
            Route::get('/export-pdf', [\App\Http\Controllers\Report\ReportController::class, 'exportPdf'])
                ->name('export-pdf');
        });

    // ===================================================================
    // SETTINGS
    // ===================================================================
    
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/general', function () {
            return view('settings.general');
        })->name('general')->middleware('role:Operator Sekolah');

        Route::get('/sekolah', function () {
            return view('settings.sekolah');
        })->name('sekolah')->middleware('role:Operator Sekolah');
    });
});

// ===================================================================
// FALLBACK ROUTE
// ===================================================================

Route::fallback(function () {
    abort(404, 'Halaman tidak ditemukan');
});

