<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\User\RoleService;

class CheckRole
{
    /**
     * Middleware pengecekan role.
     *
     * Tanggung jawab:
     * - Memastikan user terautentikasi
     * - Mengizinkan Developer nyata (non-prod) untuk menjadi super-user
     * - Saat Developer melakukan impersonation, pemeriksaan role mengikuti override
     */
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  (Daftar role yang dibolehkan, dipisah koma)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Jika user belum punya role terkait, tolak akses
        if (!$user->role) {
            abort(403, 'AKSES DITOLAK: Role user belum terdefinisi.');
        }

        // Developer special handling: consult RoleService for centralized logic
        if (RoleService::isRealDeveloper($user)) {
            $override = RoleService::getOverride();
            if (! $override) {
                // Real Developer in non-production and NOT impersonating: allow bypass
                return $next($request);
            }
            // Real Developer but currently impersonating: fallthrough to normal checks
        }

        // Gunakan centralized RoleService untuk mengecek role efektif
        if (RoleService::hasRole($roles, $user)) {
            return $next($request);
        }

        abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}   
