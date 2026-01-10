<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\User\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect user ke halaman login Google.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google setelah user login.
     * 
     * LOGIC:
     * 1. Ambil data user dari Google (email, google_id, name, avatar)
     * 2. Cari user di database berdasarkan email
     * 3. Jika email TIDAK terdaftar → Tolak (redirect ke login dengan error)
     * 4. Jika email terdaftar → Login user & simpan google_id jika belum ada
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            // Stateful mode - validates state parameter for CSRF protection
            // Requires database session driver for reliability
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            \Log::error('Google OAuth error', ['error' => $e->getMessage()]);
            return redirect()->route('login')
                ->withErrors(['google' => 'Gagal terhubung dengan Google. Silakan coba lagi.']);
        }

        $googleEmail = $googleUser->getEmail();
        $googleId = $googleUser->getId();

        // Cari user berdasarkan email
        $user = User::where('email', $googleEmail)->first();

        // Jika email TIDAK terdaftar di sistem
        if (!$user) {
            return redirect()->route('login')
                ->withErrors([
                    'google' => 'Email "' . $googleEmail . '" tidak terdaftar di sistem. Login dengan Google hanya untuk user yang sudah memiliki akun.'
                ]);
        }

        // Cek apakah akun aktif
        if (!$user->is_active) {
            return redirect()->route('login')
                ->withErrors([
                    'google' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.'
                ]);
        }

        // Simpan google_id jika belum ada (linking akun)
        if (!$user->google_id) {
            $user->update(['google_id' => $googleId]);
        }

        // Login user
        Auth::login($user, true); // true = remember me

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Regenerate session
        request()->session()->regenerate();

        // Redirect berdasarkan role (sama dengan LoginController)
        return $this->redirectBasedOnRole($user);
    }

    /**
     * Redirect user berdasarkan role mereka.
     * (Logic sama dengan LoginController)
     */
    private function redirectBasedOnRole(User $user): RedirectResponse
    {
        // Developer mode
        if (RoleService::isRealDeveloper($user)) {
            return redirect('/dashboard/developer');
        }

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
        }

        // Fallback
        return redirect('/');
    }
}
