<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\User\RoleService;

class LoginController extends Controller
{
    /**
     * Controller untuk autentikasi (login/logout).
     * Komentar dan pengalihan disesuaikan berdasarkan role efektif pengguna.
     */
    /**
     * 1. Menampilkan halaman formulir login.
     * (Menangani: GET / )
     */
    public function showLoginForm(): View
    {
        // 'auth.login' adalah file view yang akan kita buat
        // di resources/views/auth/login.blade.php
        return view('auth.login');
    }

    /**
     * 2. Memproses upaya login.
     * (Menangani: POST / )
     * 
     * User bisa login dengan:
     * - Username + Password
     * - Email + Password
     * - NIP + Password
     * - NUPTK + Password
     * - Nomor HP + Password
     */
    public function login(Request $request): RedirectResponse
    {
        // --- Validasi Input ---
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cek apakah user mencentang "Ingat Saya"
        $remember = $request->has('remember');

        $loginField = trim($request->username);
        $password = $request->password;

        // --- Cari user berdasarkan berbagai identifier ---
        // Prioritas: username > email > nip > ni_pppk > nuptk > phone
        $user = \App\Models\User::where('username', $loginField)
            ->orWhere('email', $loginField)
            ->orWhere('nip', $loginField)
            ->orWhere('ni_pppk', $loginField)
            ->orWhere('nuptk', $loginField)
            ->orWhere('phone', $loginField)
            ->first();

        // --- Coba Login ---
        $attempted = false;
        
        if ($user) {
            // Verifikasi password manual
            if (\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                // Login user secara manual
                Auth::login($user, $remember);
                $attempted = true;
            }
        }

        if ($attempted) {
            // --- BERHASIL LOGIN ---
            
            // 1. Regenerasi session untuk keamanan
            $request->session()->regenerate();

            // 2. Ambil data user yang login
            $user = Auth::user();
            
            // 3. CEK APAKAH AKUN AKTIF
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
                ])->onlyInput('username');
            }
            
            // 4. Update last login timestamp
            $user->update(['last_login_at' => now()]);

            // 5. LOGIKA PENGALIHAN (REDIRECT) BERDASARKAN PERAN
            // Gunakan helper hasRole/hasAnyRole untuk keputusan
            if (!$user->role) {
                Auth::logout();
                return redirect('/')->withErrors(['username' => 'Role tidak valid.']);
            }

            // Jika user adalah role Developer yang sesungguhnya, arahkan ke dashboard Developer khusus
            if (RoleService::isRealDeveloper($user)) {
                return redirect()->intended('/dashboard/developer');
            }

            if ($user->hasAnyRole(['Waka Kesiswaan', 'Operator Sekolah', 'Waka Kurikulum'])) {
                return redirect()->intended('/dashboard/admin');
            } elseif ($user->hasRole('Kepala Sekolah')) {
                return redirect()->intended('/dashboard/kepsek');
            } elseif ($user->hasRole('Kaprodi')) {
                return redirect()->intended('/dashboard/kaprodi');
            } elseif ($user->hasRole('Wali Kelas')) {
                return redirect()->intended('/dashboard/walikelas');
            } elseif ($user->hasRole('Waka Sarana')) {
                return redirect()->intended('/dashboard/waka-sarana');
            } elseif ($user->hasRole('Guru')) {
                return redirect()->intended('/pelanggaran/catat');
            } elseif ($user->hasRole('Wali Murid')) {
                return redirect()->intended('/dashboard/wali_murid');
            } else {
                Auth::logout();
                return redirect('/')->withErrors(['username' => 'Role tidak valid.']);
            }

        }

        // --- GAGAL LOGIN ---
        return back()->withErrors([
            'username' => 'Login gagal. Periksa kembali username dan password Anda.',
        ])->onlyInput('username');
    }

    /**
     * 3. Memproses logout.
     * (Menangani: POST /logout )
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Kembali ke halaman login
        return redirect('/');
    }
}
