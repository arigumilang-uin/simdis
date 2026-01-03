<?php

namespace App\Services\User;

use App\Data\User\UserData;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\User\UserNamingService;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * User Service - The Orchestrator
 * 
 * Tanggung jawab:
 * - User management (CRUD)
 * - Role assignment
 * - Password management
 * - Account activation/deactivation
 * 
 * CRITICAL: Service ini TIDAK BOLEH menerima Request object.
 * Semua input harus berupa DTO atau primitive types.
 */
class UserService
{
    /**
     * UserService constructor.
     *
     * @param UserRepositoryInterface $userRepo
     */
    public function __construct(
        private UserRepositoryInterface $userRepo
    ) {}

    /**
     * Create user baru.
     * 
     * ALUR:
     * 1. Validate role_id exists
     * 2. Create temporary user object for naming
     * 3. Auto-generate nama, username if not provided
     * 4. Hash password
     * 5. Create user via repository
     * 6. Handle role-specific assignments (kelas, jurusan, siswa)
     * 7. Return created user data
     *
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function createUser(array $data)
    {
        DB::beginTransaction();

        try {
            // Validate role exists
            $role = Role::findOrFail($data['role_id']);

            // Create temporary user object for auto-naming
            $tempUser = new \App\Models\User();
            $tempUser->role_id = $data['role_id'];
            $tempUser->role = $role;
            
            // Handle role-specific pre-assignments for naming
            if ($role->nama_role === 'Wali Kelas' && isset($data['kelas_id'])) {
                $tempUser->kelas_diampu_id = $data['kelas_id'];
                $tempUser->load('kelasDiampu');
            }
            
            if ($role->nama_role === 'Kaprodi' && isset($data['jurusan_id'])) {
                $tempUser->jurusan_diampu_id = $data['jurusan_id'];
                $tempUser->load('jurusanDiampu');
            }

            // Auto-generate nama and username using naming service
            $nama = UserNamingService::generateNama($tempUser);
            $username = UserNamingService::generateUsername($tempUser);

            // Siapkan data untuk disimpan
            $userData = [
                'role_id' => $data['role_id'],
                'nama' => $nama,
                'username' => $username,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'nip' => $data['nip'] ?? null,
                'nuptk' => $data['nuptk'] ?? null,
                'password' => Hash::make($data['password']),
                'is_active' => true,
            ];

            // Create via repository
            $createdUser = $this->userRepo->create($userData);

            // Handle role-specific assignments
            if ($role->nama_role === 'Wali Kelas' && isset($data['kelas_id'])) {
                \App\Models\Kelas::where('id', $data['kelas_id'])
                    ->update(['wali_kelas_user_id' => $createdUser->id]);
            }

            if ($role->nama_role === 'Kaprodi' && isset($data['jurusan_id'])) {
                \App\Models\Jurusan::where('id', $data['jurusan_id'])
                    ->update(['kaprodi_user_id' => $createdUser->id]);
            }

            if ($role->nama_role === 'Wali Murid' && isset($data['siswa_ids']) && is_array($data['siswa_ids'])) {
                \App\Models\Siswa::whereIn('id', $data['siswa_ids'])
                    ->update(['wali_murid_user_id' => $createdUser->id]);
                    
                // Re-generate nama using first siswa
                if (count($data['siswa_ids']) > 0) {
                    $createdUser->refresh();
                    $newNama = UserNamingService::generateNama($createdUser);
                    $newUsername = UserNamingService::generateUsername($createdUser);
                    $this->userRepo->update($createdUser->id, [
                        'nama' => $newNama,
                        'username' => $newUsername,
                    ]);
                }
            }

            DB::commit();

            return $createdUser;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update user.
     * 
     * ALUR:
     * 1. Validate role_id exists (if changed)
     * 2. Update user data via repository
     * 3. Update password if provided
     * 4. Return updated user data
     *
     * @param int $userId
     * @param UserData $data
     * @return UserData
     * @throws \Exception
     */
    public function updateUser(int $userId, UserData $data): UserData
    {
        DB::beginTransaction();

        try {
            // Validate role exists if provided
            if ($data->role_id) {
                Role::findOrFail($data->role_id);
            }

            // Siapkan data untuk update
            $updateData = [
                'nama' => $data->nama,
                'email' => $data->email,
                'phone' => $data->phone,
                'nip' => $data->nip,
                'nuptk' => $data->nuptk,
                'is_active' => $data->is_active,
            ];

            // Update role jika ada
            if ($data->role_id) {
                $updateData['role_id'] = $data->role_id;
            }

            // Update username jika ada dan berubah
            if ($data->username) {
                $updateData['username'] = $data->username;
                $updateData['username_changed_at'] = now();
            }

            // CRITICAL FIX: Update password jika ada
            if ($data->password) {
                $updateData['password'] = Hash::make($data->password);
                $updateData['password_changed_at'] = now();
            }

            // Update via repository
            $updatedUser = $this->userRepo->update($userId, $updateData);

            DB::commit();

            return UserData::from($updatedUser);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete user.
     *
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId): bool
    {
        return $this->userRepo->delete($userId);
    }

    /**
     * Reset user password.
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword(int $userId, string $newPassword): bool
    {
        return $this->userRepo->updatePassword($userId, $newPassword);
    }

    /**
     * Change user password (dengan verify old password).
     *
     * @param int $userId
     * @param string $oldPassword
     * @param string $newPassword
     * @return bool
     * @throws \Exception
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool
    {
        $user = $this->userRepo->find($userId);

        // Verify old password
        if (!Hash::check($oldPassword, $user->password)) {
            throw new \Exception('Password lama tidak sesuai');
        }

        return $this->userRepo->updatePassword($userId, $newPassword);
    }

    /**
     * Toggle user activation status.
     *
     * @param int $userId
     * @return bool
     */
    public function toggleActivation(int $userId): bool
    {
        return $this->userRepo->toggleActivation($userId);
    }

    /**
     * Link siswa to user (for Wali Murid/Developer roles).
     * 
     * LOGIC:
     * 1. Unlink all siswa currently linked to this user
     * 2. Link selected siswa to this user
     * 
     * @param int $userId
     * @param array $siswaIds Array of siswa IDs to link
     * @return void
     */
    public function linkSiswa(int $userId, array $siswaIds): void
    {
        DB::beginTransaction();
        
        try {
            // Step 1: Unlink all siswa previously linked to this user
            \App\Models\Siswa::where('wali_murid_user_id', $userId)
                ->update(['wali_murid_user_id' => null]);
            
            // Step 2: Link selected siswa to this user
            if (!empty($siswaIds)) {
                \App\Models\Siswa::whereIn('id', $siswaIds)
                    ->update(['wali_murid_user_id' => $userId]);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Assign user to kelas (for Wali Kelas/Developer roles).
     * 
     * LOGIC:
     * - Unassign current wali kelas from target kelas (if any)
     * - Assign this user as wali kelas
     * 
     * @param int $userId
     * @param int $kelasId
     * @return void
     */
    public function assignKelas(int $userId, int $kelasId): void
    {
        DB::beginTransaction();
        
        try {
            // Update kelas to set this user as wali kelas
            \App\Models\Kelas::where('id', $kelasId)
                ->update(['wali_kelas_user_id' => $userId]);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Assign user to jurusan (for Kaprodi/Developer roles).
     * 
     * LOGIC:
     * - Unassign current kaprodi from target jurusan (if any)
     * - Assign this user as kaprodi
     * 
     * @param int $userId
     * @param int $jurusanId
     * @return void
     */
    public function assignJurusan(int $userId, int $jurusanId): void
    {
        DB::beginTransaction();
        
        try {
            // Update jurusan to set this user as kaprodi
            \App\Models\Jurusan::where('id', $jurusanId)
                ->update(['kaprodi_user_id' => $userId]);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get paginated users with filters.
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedUsers(int $perPage = 20, array $filters = [])
    {
        return $this->userRepo->getPaginatedUsers($perPage, $filters);
    }

    /**
     * Get user by ID.
     *
     * @param int $userId
     * @return mixed
     */
    public function getUser(int $userId)
    {
        return $this->userRepo->find($userId);
    }

    /**
     * Get users by role name.
     *
     * @param string $roleName
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersByRole(string $roleName)
    {
        return $this->userRepo->findByRoleName($roleName);
    }

    /**
     * Get all active users.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveUsers()
    {
        return $this->userRepo->getActiveUsers();
    }

    /**
     * Get all teachers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTeachers()
    {
        return $this->userRepo->getTeachers();
    }

    /**
     * Get all roles for dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRoles()
    {
        return Role::all();
    }

    /**
     * Get all siswa for user linking (Wali Murid, Wali Kelas, Kaprodi).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSiswa()
    {
        return \App\Models\Siswa::with('kelas')->orderBy('nama_siswa')->get();
    }

    /**
     * Get siswa IDs connected to a specific user.
     * Used for Wali Murid (siswa.wali_murid_user_id), 
     * Wali Kelas (kelas.wali_kelas_user_id), 
     * Kaprodi (jurusan.kaprodi_user_id).
     *
     * @param int $userId
     * @return array
     */
    public function getConnectedSiswaIds(int $userId): array
    {
        $user = $this->userRepo->find($userId);
        $connectedIds = [];

        if (!$user) {
            return $connectedIds;
        }

        // Check if user is Wali Murid
        $siswaAsWaliMurid = \App\Models\Siswa::where('wali_murid_user_id', $userId)
            ->pluck('id')
            ->toArray();
        $connectedIds = array_merge($connectedIds, $siswaAsWaliMurid);

        // Check if user is Wali Kelas
        $kelasAsWaliKelas = \App\Models\Kelas::where('wali_kelas_user_id', $userId)->first();
        if ($kelasAsWaliKelas) {
            $siswaInKelas = \App\Models\Siswa::where('kelas_id', $kelasAsWaliKelas->id)
                ->pluck('id')
                ->toArray();
            $connectedIds = array_merge($connectedIds, $siswaInKelas);
        }

        // Check if user is Kaprodi
        $jurusanAsKaprodi = \App\Models\Jurusan::where('kaprodi_user_id', $userId)->first();
        if ($jurusanAsKaprodi) {
            $kelasInJurusan = \App\Models\Kelas::where('jurusan_id', $jurusanAsKaprodi->id)
                ->pluck('id')
                ->toArray();
            $siswaInJurusan = \App\Models\Siswa::whereIn('kelas_id', $kelasInJurusan)
                ->pluck('id')
                ->toArray();
            $connectedIds = array_merge($connectedIds, $siswaInJurusan);
        }

        return array_unique($connectedIds);
    }

    /**
     * Get all kelas for dropdown (sorted).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllKelas()
    {
        return \App\Models\Kelas::orderBy('nama_kelas')->get();
    }

    /**
     * Get all jurusan for dropdown (sorted).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllJurusan()
    {
        return \App\Models\Jurusan::orderBy('nama_jurusan')->get();
    }

    /**
     * Check if username exists.
     *
     * @param string $username
     * @param int|null $excludeUserId
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool
    {
        return $this->userRepo->usernameExists($username, $excludeUserId);
    }

    /**
     * Check if email exists.
     *
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        return $this->userRepo->emailExists($email, $excludeUserId);
    }

    /**
     * Bulk activate users.
     *
     * @param array $ids
     * @return void
     */
    public function bulkActivate(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $id) {
                // Gunakan toggleActivation jika ingin trigger events, atau create specific repo method
                // Untuk efisiensi bisa whereIn update via model, tapi clean arch via repo
                // Asumsi repo punya method update atau toggle
                
                // Disini kita loop update via repo update untuk aman
                $this->userRepo->update($id, ['is_active' => true]);
            }
        });
    }

    /**
     * Bulk deactivate users.
     *
     * @param array $ids
     * @return void
     */
    public function bulkDeactivate(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $this->userRepo->update($id, ['is_active' => false]);
            }
        });
    }

    /**
     * Bulk delete users.
     *
     * @param array $ids
     * @return void
     */
    public function bulkDelete(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $this->deleteUser($id);
            }
        });
    }
}
