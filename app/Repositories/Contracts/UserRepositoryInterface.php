<?php

namespace App\Repositories\Contracts;

use App\Data\User\UserData;
use Illuminate\Database\Eloquent\Collection;

/**
 * User Repository Interface
 * 
 * Defines methods for accessing and managing user data.
 * Extends base repository with domain-specific operations.
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a user by username.
     *
     * @param string $username
     * @return UserData|null
     */
    public function findByUsername(string $username): ?UserData;

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return UserData|null
     */
    public function findByEmail(string $email): ?UserData;

    /**
     * Find users by role ID.
     *
     * @param int $roleId
     * @return Collection
     */
    public function findByRole(int $roleId): Collection;

    /**
     * Find users by role name.
     *
     * @param string $roleName
     * @return Collection
     */
    public function findByRoleName(string $roleName): Collection;

    /**
     * Get all active users.
     *
     * @return Collection
     */
    public function getActiveUsers(): Collection;

    /**
     * Get all Wali Murid (parents/guardians) users.
     *
     * @return Collection
     */
    public function getWaliMurid(): Collection;

    /**
     * Get all teacher users (Guru, Wali Kelas, etc.).
     *
     * @return Collection
     */
    public function getTeachers(): Collection;

    /**
     * Check if username already exists.
     *
     * @param string $username
     * @param int|null $excludeUserId
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool;

    /**
     * Check if email already exists.
     *
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool;
}
