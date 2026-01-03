<?php

namespace App\Data\Siswa;

use Spatie\LaravelData\Data;

/**
 * Siswa Data Transfer Object
 * 
 * Represents student data with type-safe properties.
 * Column names are mapped from the real database schema (siswa table).
 */
class SiswaData extends Data
{
    public function __construct(
        public ?int $id,
        public int $kelas_id,
        public ?int $wali_murid_user_id,
        public string $nisn,
        public string $nama_siswa,
        public ?string $nomor_hp_wali_murid,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
        
        // Relationships (optional, for responses)
        public ?object $kelas = null,
        public ?object $waliMurid = null,
    ) {}

    /**
     * Validation rules for creating/updating siswa.
     *
     * @return array<string, array<string>>
     */
    public static function rules(): array
    {
        return [
            'nisn' => ['required', 'string', 'max:20'],
            'nama_siswa' => ['required', 'string', 'max:255'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'wali_murid_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'nomor_hp_wali_murid' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Get rules for creating a new siswa (with unique NISN check).
     *
     * @return array<string, array<string>>
     */
    public static function createRules(): array
    {
        $rules = self::rules();
        $rules['nisn'][] = 'unique:siswa,nisn';
        return $rules;
    }

    /**
     * Get rules for updating an existing siswa.
     *
     * @param int $siswaId
     * @return array<string, array<string>>
     */
    public static function updateRules(int $siswaId): array
    {
        $rules = self::rules();
        $rules['nisn'][] = "unique:siswa,nisn,{$siswaId}";
        return $rules;
    }
}
