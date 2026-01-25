<?php

namespace App\Services\Siswa;

use App\Models\User;
use App\Models\Role;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Exceptions\BusinessValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Siswa Wali Service
 * 
 * RESPONSIBILITY: Handle wali murid management for siswa
 * - Find or create wali by phone
 * - Generate wali credentials
 * - Link/unlink siswa to wali
 * 
 * CLEAN ARCHITECTURE: Single Responsibility Principle
 * Split from SiswaService for better maintainability.
 * 
 * @package App\Services\Siswa
 */
class SiswaWaliService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Find existing wali or create new wali based on phone number.
     * 
     * LOGIC:
     * - Pencarian akun existing: Berdasarkan NOMOR HP (untuk handle sibling/kakak-adik)
     * - Nama akun: "Wali dari {nama_siswa_pertama}" 
     * - Username: phone number
     * - Password: Random generated
     *
     * @param string $nomorHp
     * @param string $namaSiswa
     * @param string|null $nisn
     * @return array{user_id: int, username: string, password: string, is_new: bool}
     * @throws BusinessValidationException
     */
    public function findOrCreateWaliByPhone(string $nomorHp, string $namaSiswa, ?string $nisn = null): array
    {
        // Normalize phone number
        $phoneClean = preg_replace('/\D+/', '', $nomorHp);
        
        if (empty($phoneClean)) {
            throw new BusinessValidationException('Nomor HP wali tidak valid');
        }

        // Try to find existing wali by phone
        $existingWali = User::where('phone', $phoneClean)
            ->whereHas('role', function ($q) {
                $q->where('nama_role', 'Wali Murid');
            })
            ->first();

        if ($existingWali) {
            return [
                'user_id' => $existingWali->id,
                'username' => $existingWali->username,
                'password' => null, // Not returned for existing
                'is_new' => false,
            ];
        }

        // Create new wali
        return $this->createWaliMurid($phoneClean, $namaSiswa, $nisn);
    }

    /**
     * Create new wali murid account.
     *
     * @param string $phone
     * @param string $namaSiswa
     * @param string|null $nisn
     * @return array
     */
    private function createWaliMurid(string $phone, string $namaSiswa, ?string $nisn = null): array
    {
        $waliMuridRole = Role::where('nama_role', 'Wali Murid')->first();
        
        if (!$waliMuridRole) {
            throw new BusinessValidationException('Role Wali Murid tidak ditemukan di database');
        }

        // Generate username based on NISN or phone
        $username = $nisn ? "wali_{$nisn}" : "wali_{$phone}";
        
        // Ensure unique username
        $counter = 1;
        $baseUsername = $username;
        while (User::where('username', $username)->exists()) {
            $username = "{$baseUsername}_{$counter}";
            $counter++;
        }

        // Use phone number as password (easier for distribution)
        $password = $phone;

        // Generate nama
        $nama = "Wali dari {$namaSiswa}";

        $wali = User::create([
            'username' => $username,
            'nama' => $nama,
            'email' => null,
            'phone' => $phone,
            'password' => Hash::make($password),
            'role_id' => $waliMuridRole->id,
            'is_active' => true,
        ]);

        return [
            'user_id' => $wali->id,
            'username' => $username,
            'password' => $password, // Plain text for first-time display
            'is_new' => true,
        ];
    }

    /**
     * Get available wali murid for selection (not linked to any siswa).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableWaliMurid()
    {
        return User::whereHas('role', function ($q) {
                $q->where('nama_role', 'Wali Murid');
            })
            ->where('is_active', true)
            ->orderBy('username')
            ->get();
    }

    /**
     * Link siswa to wali murid.
     *
     * @param int $siswaId
     * @param int $waliId
     * @return bool
     */
    public function linkSiswaToWali(int $siswaId, int $waliId): bool
    {
        return \App\Models\Siswa::where('id', $siswaId)
            ->update(['wali_murid_id' => $waliId]);
    }

    /**
     * Unlink siswa from wali murid.
     *
     * @param int $siswaId
     * @return bool
     */
    public function unlinkSiswaFromWali(int $siswaId): bool
    {
        return \App\Models\Siswa::where('id', $siswaId)
            ->update(['wali_murid_id' => null]);
    }

    /**
     * Check if phone number is already used by a wali murid.
     *
     * @param string $phone
     * @param int|null $excludeUserId
     * @return bool
     */
    public function isPhoneUsedByWali(string $phone, ?int $excludeUserId = null): bool
    {
        $phoneClean = preg_replace('/\D+/', '', $phone);
        
        $query = User::where('phone', $phoneClean)
            ->whereHas('role', function ($q) {
                $q->where('nama_role', 'Wali Murid');
            });

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->exists();
    }
}
