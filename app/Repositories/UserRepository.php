<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Data\User\UserData;
use Illuminate\Database\Eloquent\Collection;

/**
 * User Repository Implementation
 * 
 * Handles all data access operations for User entity.
 * Implements UserRepositoryInterface and extends BaseRepository.
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a user by username.
     *
     * @param string $username
     * @return UserData|null
     */
    public function findByUsername(string $username): ?UserData
    {
        $user = $this->model
            ->where('username', $username)
            ->with('role')
            ->first();

        return $user ? UserData::from($user) : null;
    }

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return UserData|null
     */
    public function findByEmail(string $email): ?UserData
    {
        $user = $this->model
            ->where('email', $email)
            ->with('role')
            ->first();

        return $user ? UserData::from($user) : null;
    }

    /**
     * Find users by role ID.
     *
     * @param int $roleId
     * @return Collection
     */
    public function findByRole(int $roleId): Collection
    {
        return $this->model
            ->where('role_id', $roleId)
            ->where('is_active', true)
            ->with('role')
            ->orderBy('nama')
            ->get();
    }

    /**
     * Find users by role name.
     *
     * @param string $roleName
     * @return Collection
     */
    public function findByRoleName(string $roleName): Collection
    {
        return $this->model
            ->whereHas('role', function ($query) use ($roleName) {
                $query->where('nama_role', $roleName);
            })
            ->where('is_active', true)
            ->with('role')
            ->orderBy('nama')
            ->get();
    }

    /**
     * Get all active users.
     *
     * @return Collection
     */
    public function getActiveUsers(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->with('role')
            ->orderBy('nama')
            ->get();
    }

    /**
     * Get all Wali Murid (parents/guardians) users.
     *
     * @return Collection
     */
    public function getWaliMurid(): Collection
    {
        return $this->findByRoleName('Wali Murid');
    }

    /**
     * Get all teacher users (Guru, Wali Kelas, Kaprodi, etc.).
     *
     * @return Collection
     */
    public function getTeachers(): Collection
    {
        $teacherRoles = ['Guru', 'Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Waka Kurikulum', 'Waka Sarana', 'Operator Sekolah'];

        return $this->model
            ->whereHas('role', function ($query) use ($teacherRoles) {
                $query->whereIn('nama_role', $teacherRoles);
            })
            ->where('is_active', true)
            ->with('role')
            ->orderBy('nama')
            ->get();
    }

    /**
     * Check if username already exists.
     *
     * @param string $username
     * @param int|null $excludeUserId
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool
    {
        $query = $this->model->where('username', $username);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->exists();
    }

    /**
     * Check if email already exists.
     *
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        $query = $this->model->where('email', $email);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->exists();
    }

    /**
     * Get paginated users with filters.
     * 
     * NOTE: Method ini BUKAN override BaseRepository::paginate()
     * Ini adalah custom method dengan filter support.
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedUsers(int $perPage = 20, array $filters = [])
    {
        $query = $this->model->newQuery()->with('role');

        // Apply filters
        if (isset($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama', 'like', "%{$filters['search']}%")
                  ->orWhere('username', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('nama')->paginate($perPage);
    }

    /**
     * Update user password.
     *
     * @param int $userId
     * @param string $newPassword
     * @return mixed
     */
    public function updatePassword(int $userId, string $newPassword)
    {
        return $this->model->findOrFail($userId)->update([
            'password' => bcrypt($newPassword),
            'password_changed_at' => now(),
        ]);
    }

    /**
     * Toggle user active status.
     *
     * @param int $userId
     * @return mixed
     */
    public function toggleActivation(int $userId)
    {
        $user = $this->model->findOrFail($userId);
        
        return $user->update([
            'is_active' => !$user->is_active,
        ]);
    }
}
