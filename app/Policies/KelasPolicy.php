<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Kelas;

/**
 * Kelas Policy
 * 
 * Authorization logic untuk operasi CRUD Kelas.
 * Defines who can view, create, update, and delete kelas records.
 * 
 * @package App\Policies
 */
class KelasPolicy
{
    /**
     * Determine if the user can view any kelas.
     * 
     * All authenticated users can view kelas list
     * (needed for filters, dropdowns, etc).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the kelas.
     */
    public function view(User $user, Kelas $kelas): bool
    {
        // Wali Kelas can only view their own kelas
        if ($user->hasRole('Wali Kelas')) {
            return $kelas->id === $user->kelasDiampu?->id;
        }

        // Kaprodi can view kelas in their jurusan
        if ($user->hasRole('Kaprodi')) {
            return $kelas->jurusan_id === $user->jurusanDiampu?->id;
        }

        // Admin roles can view all
        return $user->hasAnyRole([
            'Operator Sekolah', 
            'Kepala Sekolah', 
            'Waka Kesiswaan', 
            'Waka Kurikulum',
            'Waka Sarana'
        ]);
    }

    /**
     * Determine if the user can create kelas.
     * 
     * Only Operator Sekolah can create kelas.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can update the kelas.
     * 
     * Only Operator Sekolah can update kelas.
     */
    public function update(User $user, Kelas $kelas): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can delete the kelas.
     * 
     * Only Operator Sekolah can delete kelas.
     * Kelas with siswa cannot be deleted (handled in service).
     */
    public function delete(User $user, Kelas $kelas): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can assign Wali Kelas to kelas.
     */
    public function assignWaliKelas(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if user can view kelas monitoring (statistics).
     */
    public function viewMonitoring(User $user): bool
    {
        return $user->hasAnyRole([
            'Operator Sekolah',
            'Kepala Sekolah',
            'Waka Kesiswaan',
            'Waka Kurikulum',
            'Waka Sarana',
            'Kaprodi',
            'Wali Kelas'
        ]);
    }

    /**
     * Determine if user can view siswa in kelas.
     */
    public function viewSiswa(User $user, Kelas $kelas): bool
    {
        // Wali Kelas can view siswa in their kelas
        if ($user->hasRole('Wali Kelas')) {
            return $kelas->id === $user->kelasDiampu?->id;
        }

        // Kaprodi can view siswa in kelas of their jurusan
        if ($user->hasRole('Kaprodi')) {
            return $kelas->jurusan_id === $user->jurusanDiampu?->id;
        }

        return $user->hasAnyRole([
            'Operator Sekolah',
            'Kepala Sekolah',
            'Waka Kesiswaan',
            'Waka Kurikulum',
            'Waka Sarana'
        ]);
    }
}
