<?php

namespace App\Data\MasterData;

use Spatie\LaravelData\Data;

/**
 * Kelas Data Transfer Object
 * 
 * Purpose: Transfer kelas data between layers (Controller → Service → Repository)
 * Pattern: DTO (Data Transfer Object)
 */
class KelasData extends Data
{
    public function __construct(
        public ?int $id,
        public string $tingkat,
        public int $jurusan_id,
        public ?int $konsentrasi_id,
        public ?int $wali_kelas_user_id,
        public ?string $nama_kelas, // Can be auto-generated or manual
        
        // Additional flag for auto-creating wali kelas user
        public bool $create_wali = false,
    ) {}
}
