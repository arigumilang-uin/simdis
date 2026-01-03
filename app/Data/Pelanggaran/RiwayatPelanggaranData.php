<?php

namespace App\Data\Pelanggaran;

use Spatie\LaravelData\Data;

/**
 * Riwayat Pelanggaran Data Transfer Object
 * 
 * Represents violation history record data with type-safe properties.
 * Column names are mapped from the real database schema (riwayat_pelanggaran table).
 */
class RiwayatPelanggaranData extends Data
{
    public function __construct(
        public ?int $id,
        public int $siswa_id,
        public int $jenis_pelanggaran_id,
        public int $guru_pencatat_user_id,
        public string $tanggal_kejadian,
        public ?string $keterangan = null,
        public ?string $bukti_foto_path = null,
        public ?string $deleted_at = null,
        
        // Relationships (optional, for responses)
        public ?object $siswa = null,
        public ?object $jenisPelanggaran = null,
        public ?object $guruPencatat = null,
    ) {}

    /**
     * Validation rules for creating/updating riwayat pelanggaran.
     *
     * @return array<string, array<string>>
     */
    public static function rules(): array
    {
        return [
            'siswa_id' => ['required', 'integer', 'exists:siswa,id'],
            'jenis_pelanggaran_id' => ['required', 'integer', 'exists:jenis_pelanggaran,id'],
            'guru_pencatat_user_id' => ['required', 'integer', 'exists:users,id'],
            'tanggal_kejadian' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'bukti_foto_path' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get rules for file upload (bukti foto).
     *
     * @return array<string, array<string>>
     */
    public static function fileUploadRules(): array
    {
        return [
            'bukti_foto' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png'],
        ];
    }
}
