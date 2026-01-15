<?php

namespace App\Data\User;

use Spatie\LaravelData\Data;

/**
 * User Data Transfer Object
 * 
 * Represents user/account data with type-safe properties.
 * Column names are mapped from the real database schema (users table).
 */
class UserData extends Data
{
    public function __construct(
        public ?int $id,
        public int $role_id,
        public ?string $nama = null, // NULLABLE: nama auto-generated, tidak dari form
        public string $username,
        public ?string $email = null, // NULLABLE: email optional
        public ?string $phone = null,
        public ?string $nip = null,
        public ?string $ni_pppk = null,
        public ?string $nuptk = null,
        public ?string $password = null,
        public bool $is_active = true,
        public ?string $email_verified_at = null,
        public ?string $profile_completed_at = null,
        public ?string $username_changed_at = null,
        public ?string $password_changed_at = null,
        public ?string $last_login_at = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        
        // Relationships (optional, for responses)
        public ?object $role = null,
        public ?object $kelasDiampu = null,
        public ?object $jurusanDiampu = null,

        // Input assignments (optional, for create/update)
        public ?int $kelas_id = null,
        public ?int $jurusan_id = null,
        public ?array $siswa_ids = null,
    ) {}

    /**
     * Validation rules for creating/updating users.
     *
     * @return array<string, array<string>>
     */
    public static function rules(): array
    {
        return [
            'nama' => ['nullable', 'string', 'max:255'], // NULLABLE: auto-generated
            'username' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:50'],
            'ni_pppk' => ['nullable', 'string', 'max:50'],
            'nuptk' => ['nullable', 'string', 'max:50'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get rules for creating a new user.
     *
     * @return array<string, array<string>>
     */
    public static function createRules(): array
    {
        $rules = self::rules();
        $rules['username'][] = 'unique:users,username';
        $rules['email'][] = 'unique:users,email';
        $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        return $rules;
    }

    /**
     * Get rules for updating an existing user.
     *
     * @param int $userId
     * @return array<string, array<string>>
     */
    public static function updateRules(int $userId): array
    {
        $rules = self::rules();
        $rules['username'][] = "unique:users,username,{$userId}";
        $rules['email'][] = "unique:users,email,{$userId}";
        $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        return $rules;
    }

    /**
     * Get rules for Wali Murid registration (simplified).
     *
     * @return array<string, array<string>>
     */
    public static function waliMuridRules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }
}
