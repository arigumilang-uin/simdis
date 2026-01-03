<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * ProfileCompletionController
 * 
 * Controller untuk menangani proses lengkapi profil saat login pertama kali.
 * User yang dibuat oleh Operator/Sistem akan diminta untuk:
 * 1. Memeriksa dan mengubah username (opsional)
 * 2. Memeriksa dan mengubah email (wajib jika kosong)
 * 3. Mengubah password default (wajib)
 * 
 * Setelah lengkap, profile_completed_at akan diset dan user bisa mengakses sistem.
 */
class ProfileCompletionController extends Controller
{
    /**
     * Tampilkan halaman lengkapi profil.
     */
    public function show()
    {
        $user = Auth::user();
        
        // Jika sudah lengkap, redirect ke dashboard
        if ($user->hasCompletedProfile()) {
            return redirect()->route('dashboard')
                ->with('info', 'Profil Anda sudah lengkap.');
        }

        return view('auth.complete-profile', [
            'user' => $user,
            'needsPasswordChange' => !$user->hasChangedPassword(),
            'needsEmailUpdate' => empty($user->email),
            'isWaliMurid' => $user->hasRole('Wali Murid'),
        ]);
    }

    /**
     * Proses simpan data profil lengkap.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isWaliMurid = $user->hasRole('Wali Murid');

        // Validasi - tanpa nama (auto-generated oleh Operator)
        $rules = [
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'email' => ['required', 'email', 'max:100', 'unique:users,email,' . $user->id],
        ];

        // Phone hanya untuk non-Wali Murid
        if (!$isWaliMurid) {
            $rules['phone'] = ['nullable', 'string', 'max:20'];
        }

        // Password wajib diubah jika belum pernah diubah
        if (!$user->hasChangedPassword()) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password'] = ['required', 'confirmed', 'min:6'];
        }

        $validated = $request->validate($rules, [
            'username.unique' => 'Username sudah digunakan oleh user lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'current_password.required' => 'Password lama wajib diisi.',
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        // Update data user
        $updateData = [
            'email' => $validated['email'],
            'profile_completed_at' => now(),
        ];

        // Update phone hanya untuk non-Wali Murid
        if (!$isWaliMurid && isset($validated['phone'])) {
            $updateData['phone'] = $validated['phone'];
        }

        // Update username jika diisi dan berubah
        if (!empty($validated['username']) && $validated['username'] !== $user->username) {
            $updateData['username'] = $validated['username'];
            $updateData['username_changed_at'] = now();
        }

        // Update password jika diubah
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
            $updateData['password_changed_at'] = now();
        }

        $user->update($updateData);

        return redirect()->route('dashboard')
            ->with('success', 'Selamat datang! Profil Anda berhasil dilengkapi. Silakan gunakan username dan password baru untuk login berikutnya.');
    }

    /**
     * Skip profile completion (untuk development/testing).
     * Hanya aktif jika APP_ENV=local.
     */
    public function skip()
    {
        if (app()->environment('local')) {
            $user = Auth::user();
            $user->update(['profile_completed_at' => now()]);
            
            return redirect()->route('dashboard')
                ->with('info', '[DEV] Profile completion skipped.');
        }

        abort(403, 'Fitur ini hanya tersedia di environment development.');
    }
}
