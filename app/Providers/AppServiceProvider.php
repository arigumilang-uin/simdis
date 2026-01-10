<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        
        // Configure Rate Limiting
        $this->configureRateLimiting();
    }
    
    /**
     * Configure rate limiting for the application.
     * 
     * PROTECTION LEVELS:
     * 1. login - Strict limit untuk mencegah brute force
     * 2. oauth - Limit untuk OAuth attempts
     * 3. global - General limit untuk semua request
     */
    protected function configureRateLimiting(): void
    {
        // LOGIN RATE LIMIT: 5 attempts per minute per IP+Username
        // Kombinasi IP + username untuk:
        // - Izinkan banyak user di WiFi sama login bersamaan
        // - Tetap blokir brute force pada 1 akun tertentu
        RateLimiter::for('login', function (Request $request) {
            // Key: IP + username yang dicoba login
            $key = $request->ip() . '|' . strtolower($request->input('username', ''));
            
            return Limit::perMinute(
                (int) env('RATE_LIMIT_LOGIN', 5)
            )->by($key)->response(function () {
                return redirect()->route('login')
                    ->withErrors(['throttle' => 'Terlalu banyak percobaan login. Silakan tunggu 1 menit.']);
            });
        });
        
        // OAUTH RATE LIMIT: 10 attempts per minute per IP
        // Melindungi dari OAuth abuse
        RateLimiter::for('oauth', function (Request $request) {
            return Limit::perMinute(
                (int) env('RATE_LIMIT_OAUTH', 10)
            )->by($request->ip())->response(function () {
                return redirect()->route('login')
                    ->withErrors(['throttle' => 'Terlalu banyak percobaan. Silakan tunggu 1 menit.']);
            });
        });
        
        // GLOBAL RATE LIMIT: 60 requests per minute per IP
        // Melindungi dari DDoS/bot spam
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(
                (int) env('RATE_LIMIT_GLOBAL', 60)
            )->by($request->ip());
        });
        
        // API RATE LIMIT: 30 requests per minute per user/IP
        // Untuk endpoint API/AJAX
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(
                (int) env('RATE_LIMIT_API', 30)
            )->by($request->user()?->id ?: $request->ip());
        });
    }
}
