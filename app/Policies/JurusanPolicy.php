<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Jurusan;

/**
 * Jurusan Policy
 * 
 * Authorization logic untuk operasi CRUD Jurusan (Program Studi).
 * Defines who can view, create, update, and delete jurusan records.
 * 
 * @package App\Policies
 */
class JurusanPolicy
{
    /**
     * Determine if the user can view any jurusan.
     * 
     * All authenticated users can view jurusan list
     * (needed for filters, dropdowns, etc).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the jurusan.
     */
    public function view(User $user, Jurusan $jurusan): bool
    {
        // Kaprodi can only view their own jurusan
        if ($user->hasRole('Kaprodi')) {
            return $jurusan->id === $user->jurusanDiampu?->id;
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
     * Determine if the user can create jurusan.
     * 
     * Only Operator Sekolah can create jurusan.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can update the jurusan.
     * 
     * Only Operator Sekolah can update jurusan.
     */
    public function update(User $user, Jurusan $jurusan): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can delete the jurusan.
     * 
     * Only Operator Sekolah can delete jurusan.
     * Jurusan with kelas/siswa cannot be deleted (handled in service).
     */
    public function delete(User $user, Jurusan $jurusan): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can assign Kaprodi to jurusan.
     */
    public function assignKaprodi(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if user can manage konsentrasi in jurusan.
     */
    public function manageKonsentrasi(User $user, Jurusan $jurusan): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if user can view jurusan monitoring (statistics).
     */
    public function viewMonitoring(User $user): bool
    {
        return $user->hasAnyRole([
            'Operator Sekolah',
            'Kepala Sekolah',
            'Waka Kesiswaan',
            'Waka Kurikulum',
            'Waka Sarana',
            'Kaprodi'
        ]);
    }
}
