<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RiwayatPelanggaran;
use Carbon\Carbon;

/**
 * Riwayat Pelanggaran Policy
 * 
 * Authorization logic untuk operasi CRUD riwayat pelanggaran.
 * Includes ownership dan time limit checks untuk edit/delete.
 */
class RiwayatPelanggaranPolicy
{
    /**
     * Determine if the user can view any riwayat.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users (dengan scope berbeda)
    }

    /**
     * Determine if the user can view the riwayat.
     */
    public function view(User $user, RiwayatPelanggaran $riwayat): bool
    {
        // Wali Murid hanya bisa lihat pelanggaran anak sendiri
        if ($user->hasRole('Wali Murid')) {
            return $riwayat->siswa?->wali_murid_user_id === $user->id;
        }

        // Wali Kelas bisa lihat pelanggaran siswa di kelas binaan
        if ($user->hasRole('Wali Kelas')) {
            return $riwayat->siswa?->kelas_id === $user->kelasDiampu?->id;
        }

        // Kaprodi bisa lihat pelanggaran siswa di jurusan binaan
        if ($user->hasRole('Kaprodi')) {
            return $riwayat->siswa?->kelas?->jurusan_id === $user->jurusanDiampu?->id;
        }

        // Admin, Kepsek, Waka bisa lihat semua
        return $user->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah', 'Waka Kesiswaan', 'Waka Sarana']);
    }

    /**
     * Determine if the user can create riwayat (catat pelanggaran).
     * 
     * Semua teacher roles boleh catat pelanggaran.
     */
    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    /**
     * Determine if the user can update the riwayat.
     * 
     * RULES:
     * - Operator Sekolah: bisa edit semua tanpa batasan
     * - Role lain: hanya bisa edit yang mereka catat sendiri (max 3 hari)
     */
    public function update(User $user, RiwayatPelanggaran $riwayat): bool
    {
        // Operator Sekolah bisa edit semua tanpa batasan
        if ($user->hasRole('Operator Sekolah')) {
            return true;
        }

        // Role lain: harus pencatat sendiri
        if ($riwayat->guru_pencatat_user_id !== $user->id) {
            return false;
        }

        // Batasi kemampuan edit sampai 3 hari sejak pencatatan
        if ($riwayat->created_at) {
            $created = Carbon::parse($riwayat->created_at);
            if (Carbon::now()->greaterThan($created->copy()->addDays(3))) {
                return false; // Lebih dari 3 hari
            }
        }

        return true;
    }

    /**
     * Determine if the user can delete the riwayat.
     * 
     * Same rules as update.
     */
    public function delete(User $user, RiwayatPelanggaran $riwayat): bool
    {
        return $this->update($user, $riwayat);
    }
}
