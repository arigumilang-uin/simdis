<?php

namespace App\Services\User;

use Illuminate\Support\Facades\Session;
use App\Models\User;

/**
 * RoleService
 *
 * Service ini memusatkan logika terkait role dan impersonation (developer override).
 * Tujuan: menghindari duplikasi logika di banyak file, mempermudah pengujian,
 * dan menjaga agar akses role selalu dilakukan dengan cara yang konsisten.
 */
class RoleService
{
    /**
     * Ambil nilai override impersonation Developer dari session.
     * Jika tidak ada, kembalikan null.
     *
     * @return string|null
     */
    public static function getOverride(): ?string
    {
        return Session::get('developer_role_override');
    }

    /**
     * Cek apakah user yang diberikan merupakan akun Developer "nyata" (di DB)
     * dan hanya dianggap Developer jika environment bukan production.
     *
     * @param User|null $user
     * @return bool
     */
    public static function isRealDeveloper(?User $user = null): bool
    {
        if (! $user) {
            $user = auth()->user();
        }

        // Gunakan optional chaining dan pengecekan environment
        return $user !== null && ($user->role?->nama_role ?? null) === 'Developer' && ! app()->environment('production');
    }

    /**
     * Mengembalikan nama role yang efektif untuk user.
     * Jika user adalah Developer nyata dan ada override di session,
     * kembalikan nilai override tersebut.
     * Jika tidak, kembalikan nama role dari relasi atau null.
     *
     * @param User|null $user
     * @return string|null
     */
    public static function effectiveRoleName(?User $user = null): ?string
    {
        if (! $user) {
            $user = auth()->user();
        }

        if (! $user) return null;

        $override = self::getOverride();

        // Jika akun Developer nyata dan ada override, gunakan override
        if (($user->role?->nama_role ?? null) === 'Developer' && $override) {
            return $override;
        }

        return $user->role?->nama_role;
    }

    /**
     * Cek apakah user (atau user saat ini jika null) memiliki salah satu role yang diberikan.
     * Parameter $roles menerima string atau array.
     *
     * @param string|array $roles
     * @param User|null $user
     * @return bool
     */
    public static function hasRole($roles, ?User $user = null): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (! $user) {
            $user = auth()->user();
        }
        if (! $user) return false;

        $effective = self::effectiveRoleName($user);
        if (! $effective) return false;

        return in_array($effective, $roles, true);
    }
}

