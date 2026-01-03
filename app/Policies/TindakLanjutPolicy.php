<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TindakLanjut;
use App\Enums\StatusTindakLanjut;

/**
 * Tindak Lanjut Policy
 * 
 * Authorization logic untuk operasi CRUD dan approval tindak lanjut.
 */
class TindakLanjutPolicy
{
    /**
     * Determine if the user can view any tindak lanjut.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users (dengan scope berbeda)
    }

    /**
     * Determine if the user can view the tindak lanjut.
     */
    public function view(User $user, TindakLanjut $tindakLanjut): bool
    {
        // Wali Murid hanya bisa lihat tindak lanjut anak sendiri
        if ($user->hasRole('Wali Murid')) {
            return $tindakLanjut->siswa?->wali_murid_user_id === $user->id;
        }

        // Wali Kelas bisa lihat tindak lanjut siswa di kelas binaan
        if ($user->hasRole('Wali Kelas')) {
            return $tindakLanjut->siswa?->kelas_id === $user->kelasDiampu?->id;
        }

        // Kaprodi bisa lihat tindak lanjut siswa di jurusan binaan
        if ($user->hasRole('Kaprodi')) {
            return $tindakLanjut->siswa?->kelas?->jurusan_id === $user->jurusanDiampu?->id;
        }

        // Admin, Kepsek, Waka bisa lihat semua
        return $user->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah', 'Waka Kesiswaan', 'Waka Sarana']);
    }

    /**
     * Determine if the user can create tindak lanjut.
     * 
     * Hanya Operator Sekolah dan Kepala Sekolah yang boleh create manual.
     * (Auto-create via RulesEngine bypass policy)
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah']);
    }

    /**
     * Determine if the user can update the tindak lanjut.
     * 
     * Hanya Operator Sekolah dan Kepala Sekolah yang boleh update.
     */
    public function update(User $user, TindakLanjut $tindakLanjut): bool
    {
        return $user->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah']);
    }

    /**
     * Determine if the user can delete the tindak lanjut.
     * 
     * Hanya Operator Sekolah yang boleh delete.
     */
    public function delete(User $user, TindakLanjut $tindakLanjut): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can approve the tindak lanjut.
     * 
     * BUSINESS RULE:
     * - Kepala Sekolah bisa approve semua
     * - Waka Kesiswaan bisa approve Surat 2 dan 3
     * - Kaprodi bisa approve untuk siswa di jurusan binaan
     */
    public function approve(User $user, TindakLanjut $tindakLanjut): bool
    {
        // Hanya bisa approve jika status = Menunggu Persetujuan
        if ($tindakLanjut->status !== StatusTindakLanjut::MENUNGGU_PERSETUJUAN) {
            return false;
        }

        // Kepala Sekolah bisa approve semua
        if ($user->hasRole('Kepala Sekolah')) {
            return true;
        }

        // Waka Kesiswaan bisa approve (untuk sistem pembinaan)
        if ($user->hasRole('Waka Kesiswaan')) {
            return true;
        }

        // Kaprodi bisa approve untuk siswa di jurusan binaan
        if ($user->hasRole('Kaprodi')) {
            return $tindakLanjut->siswa?->kelas?->jurusan_id === $user->jurusanDiampu?->id;
        }

        return false;
    }

    /**
     * Determine if the user can reject the tindak lanjut.
     * 
     * Same logic as approve.
     */
    public function reject(User $user, TindakLanjut $tindakLanjut): bool
    {
        return $this->approve($user, $tindakLanjut);
    }

    /**
     * Determine if the user can complete/close the tindak lanjut.
     * 
     * Operator, Kepala Sekolah, dan Waka Kesiswaan bisa complete.
     */
    public function complete(User $user, TindakLanjut $tindakLanjut): bool
    {
        // Hanya bisa complete jika status bukan Selesai atau Ditolak
        if (in_array($tindakLanjut->status, [StatusTindakLanjut::SELESAI, StatusTindakLanjut::DITOLAK])) {
            return false;
        }

        return $user->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah', 'Waka Kesiswaan']);
    }
}
