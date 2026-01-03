<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Tampilkan form untuk melengkapi profil minimal pada login pertama.
     *
     * - Semua role wajib mengisi email yang valid.
     * - Semua role kecuali Wali Murid boleh (dan dianjurkan) mengisi kontak/nomor HP.
     * - Untuk Wali Murid, kontak utama diambil dari data siswa (nomor_hp_wali_murid)
     *   sehingga tidak diedit langsung di sini demi kredibilitas data.
     */
    public function showCompleteForm(): View
    {
        $user = Auth::user();

        // Untuk Wali Murid, ambil kontak dari salah satu siswa yang dibina (jika ada)
        $waliMuridContact = null;
        if ($user->isWaliMurid()) {
            $waliMuridContact = $user
                ->anakWali()
                ->whereNotNull('nomor_hp_wali_murid')
                ->value('nomor_hp_wali_murid');
        }

        return view('profile.complete', [
            'user' => $user,
            'isWaliMurid' => $user->isWaliMurid(),
            'waliMuridContact' => $waliMuridContact,
        ]);
    }

    /**
     * Simpan data profil minimal.
     */
    public function storeCompleteForm(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Validasi dasar: username, password, dan email wajib diisi
        $rules = [
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'required|string|min:8|confirmed',
            'email' => 'required|email:rfc,dns|unique:users,email,' . $user->id,
        ];

        // Untuk role selain Wali Murid, izinkan input nomor kontak/HP opsional.
        if (! $user->isWaliMurid()) {
            $rules['phone'] = 'nullable|string|max:30';
        }

        $validated = $request->validate($rules);

        // Track perubahan username
        $usernameChanged = $validated['username'] !== $user->username;
        if ($usernameChanged && !$user->hasChangedUsername()) {
            $user->username_changed_at = now();
        }
        $user->username = $validated['username'];

        // Track perubahan password
        if (!$user->hasChangedPassword()) {
            $user->password_changed_at = now();
        }
        $user->password = bcrypt($validated['password']);

        // Update email
        $emailChanged = $validated['email'] !== $user->email;
        $user->email = $validated['email'];

        // Hanya role non-Wali Murid yang boleh mengubah kontak akun.
        if (! $user->isWaliMurid() && array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'] ?: null;
        }

        if (! $user->hasCompletedProfile()) {
            $user->profile_completed_at = now();
        }

        // Jika email berubah, reset verifikasi dan kirim ulang email verifikasi (jika memungkinkan)
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                // Jangan gagalkan alur jika mail server belum dikonfigurasi
            }
        }

        // Setelah profil lengkap, arahkan ke dashboard sesuai role (fallback ke intended)
        return $this->redirectToRoleDashboard($user);
    }

    /**
     * Tampilkan form "Akun Saya" untuk mengubah email dan (bagi role tertentu) kontak.
     */
    public function editAccount(): View
    {
        $user = Auth::user();

        $waliMuridContact = null;
        if ($user->isWaliMurid()) {
            $waliMuridContact = $user
                ->anakWali()
                ->whereNotNull('nomor_hp_wali_murid')
                ->value('nomor_hp_wali_murid');
        }

        return view('profile.account', [
            'user' => $user,
            'isWaliMurid' => $user->isWaliMurid(),
            'waliMuridContact' => $waliMuridContact,
        ]);
    }

    /**
     * Update data akun (username, email dan kontak) milik user sendiri.
     */
    public function updateAccount(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $rules = [
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email:rfc,dns|unique:users,email,' . $user->id,
        ];

        if (! $user->isWaliMurid()) {
            $rules['phone'] = 'nullable|string|max:30';
        }

        $validated = $request->validate($rules);

        $usernameChanged = $validated['username'] !== $user->username;
        $emailChanged = $validated['email'] !== $user->email;

        // Track jika username diubah oleh user
        if ($usernameChanged && !$user->hasChangedUsername()) {
            $user->username_changed_at = now();
        }

        $user->username = $validated['username'];
        $user->email = $validated['email'];

        if (! $user->isWaliMurid() && array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'] ?: null;
        }

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                // Abaikan error kirim email jika konfigurasi mail belum siap
            }
        }

        $successMessage = 'Akun berhasil diperbarui.';
        if ($usernameChanged) {
            $successMessage .= ' Username Anda telah diubah.';
        }
        if ($emailChanged) {
            $successMessage .= ' Silakan cek email Anda untuk verifikasi.';
        }

        return redirect()
            ->route('account.edit')
            ->with('success', $successMessage);
    }

    /**
     * Tentukan redirect setelah profil lengkap berdasarkan role yang efektif.
     */
    protected function redirectToRoleDashboard($user): RedirectResponse
    {
        if ($user->isDeveloper()) {
            return redirect()->intended('/dashboard/developer');
        }

        if ($user->hasAnyRole(['Waka Kesiswaan', 'Operator Sekolah'])) {
            return redirect()->intended('/dashboard/admin');
        }

        if ($user->hasRole('Kepala Sekolah')) {
            return redirect()->intended('/dashboard/kepsek');
        }

        if ($user->hasRole('Kaprodi')) {
            return redirect()->intended('/dashboard/kaprodi');
        }

        if ($user->hasRole('Wali Kelas')) {
            return redirect()->intended('/dashboard/walikelas');
        }

        if ($user->hasRole('Waka Sarana')) {
            return redirect()->intended('/dashboard/waka-sarana');
        }

        if ($user->hasRole('Guru')) {
            return redirect()->intended('/pelanggaran/catat');
        }

        if ($user->hasRole('Wali Murid')) {
            return redirect()->intended('/dashboard/wali_murid');
        }

        // Jika role tidak dikenal, arahkan ke halaman login dengan pesan error.
        Auth::logout();

        return redirect('/')
            ->withErrors(['username' => 'Role tidak valid. Silakan hubungi operator sekolah.']);
    }
}




