<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Observers\SiswaObserver;
use App\Observers\UserNameSyncObserver;
use App\Observers\JurusanObserver;
use App\Observers\KelasObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Siswa::observe(SiswaObserver::class);
        
        // Auto-sync user names based on role and assignments
        User::observe(UserNameSyncObserver::class);
        Jurusan::observe(JurusanObserver::class);
        Kelas::observe(KelasObserver::class);
    }
}
