<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Siswa;

/**
 * Siswa Policy
 * 
 * Authorization logic untuk operasi CRUD siswa.
 * Defines who can view, create, update, and delete siswa records.
 */
class SiswaPolicy
{
    /**
     * Determine if the user can view any siswa.
     * 
     * Semua authenticated user bisa melihat daftar siswa
     * (dengan scope berbeda berdasarkan role - handled di repository/service).
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users
    }

    /**
     * Determine if the user can view the siswa.
     */
    public function view(User $user, Siswa $siswa): bool
    {
        // Wali Murid hanya bisa lihat anak sendiri
        if ($user->hasRole('Wali Murid')) {
            return $siswa->wali_murid_user_id === $user->id;
        }

        // Wali Kelas bisa lihat siswa di kelas binaan
        if ($user->hasRole('Wali Kelas')) {
            return $siswa->kelas_id === $user->kelasDiampu?->id;
        }

        // Kaprodi bisa lihat siswa di jurusan binaan
        if ($user->hasRole('Kaprodi')) {
            return $siswa->kelas?->jurusan_id === $user->jurusanDiampu?->id;
        }

        // Admin, Kepsek, Waka bisa lihat semua
        return $user->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah', 'Waka Kesiswaan', 'Waka Kurikulum', 'Waka Sarana']);
    }

    /**
     * Determine if the user can create siswa.
     * 
     * Hanya Operator Sekolah yang boleh create siswa.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can update the siswa.
     * 
     * - Operator Sekolah: bisa update semua field
     * - Wali Kelas: hanya bisa update nomor HP wali murid (di kelas binaan)
     */
    public function update(User $user, Siswa $siswa): bool
    {
        // Operator Sekolah bisa update semua
        if ($user->hasRole('Operator Sekolah')) {
            return true;
        }

        // Wali Kelas bisa update siswa di kelas binaan (limited fields)
        if ($user->hasRole('Wali Kelas')) {
            return $siswa->kelas_id === $user->kelasDiampu?->id;
        }

        return false;
    }

    /**
     * Determine if the user can delete the siswa.
     * 
     * Hanya Operator Sekolah yang boleh delete siswa.
     */
    public function delete(User $user, Siswa $siswa): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can bulk import siswa.
     */
    public function bulkImport(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }
    
    /**
     * Determine if the user can bulk delete siswa.
     * 
     * Hanya Operator Sekolah yang boleh bulk delete siswa.
     */
    public function bulkDelete(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if user can restore deleted siswa.
     */
    public function restore(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if user can permanently delete siswa.
     */
    public function forceDelete(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if user can bulk transfer siswa (kenaikan kelas).
     * 
     * Hanya Operator Sekolah yang boleh memindahkan siswa antar kelas.
     */
    public function bulkTransfer(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }
}
