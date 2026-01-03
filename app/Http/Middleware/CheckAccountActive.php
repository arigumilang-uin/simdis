<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckAccountActive Middleware
 * 
 * Middleware untuk memastikan user yang login memiliki akun aktif.
 * Jika akun dinonaktifkan saat user masih login, user akan di-logout otomatis.
 * 
 * Use case:
 * - Operator menonaktifkan akun user yang sedang login
 * - User mencoba akses halaman lain
 * - Middleware ini akan logout user dan redirect ke login
 */
class CheckAccountActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login
        if (Auth::check()) {
            $user = Auth::user();
            
            // Cek apakah akun aktif
            if (!$user->is_active) {
                // Logout user
                Auth::logout();
                
                // Invalidate session
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redirect ke login dengan pesan error
                return redirect('/')
                    ->withErrors([
                        'username' => 'Akun Anda telah dinonaktifkan oleh administrator. Silakan hubungi administrator untuk informasi lebih lanjut.',
                    ]);
            }
        }
        
        return $next($request);
    }
}
