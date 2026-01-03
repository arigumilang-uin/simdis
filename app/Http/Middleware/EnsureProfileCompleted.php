<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureProfileCompleted
{
    /**
     * Middleware untuk memaksa user melengkapi profil minimal pada login pertama.
     *
     * Aturan:
     * - Hanya dijalankan untuk user yang sudah autentikasi.
     * - Jika profile_completed_at masih null, redirect ke halaman lengkapi profil.
     * - Kecualikan rute tertentu agar tidak looping:
     *   - Halaman lengkapi profil (GET/POST)
     *   - Logout
     *   - Developer impersonation status (untuk debugging)
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Jika sudah lengkap, lanjutkan saja
        if ($user->hasCompletedProfile()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        // Rute yang dikecualikan dari pengecekan
        $excludedRoutes = [
            'profile.complete.show',
            'profile.complete.store',
            'logout',
            'developer.status',
            // Rute verifikasi email sebaiknya tetap bisa diakses meskipun profil belum ditandai lengkap
            'verification.notice',
            'verification.verify',
            'verification.send',
        ];

        if ($routeName && in_array($routeName, $excludedRoutes, true)) {
            return $next($request);
        }

        // Hanya paksa redirect untuk rute web biasa, biarkan API/console berjalan normal
        return redirect()->route('profile.complete.show');
    }
}


