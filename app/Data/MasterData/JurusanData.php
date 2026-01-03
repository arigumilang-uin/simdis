<?php

namespace App\Data\MasterData;

use Spatie\LaravelData\Data;

/**
 * Jurusan Data Transfer Object
 * 
 * Purpose: Transfer jurusan data between layers (Controller → Service → Repository)
 * Pattern: DTO (Data Transfer Object)
 */
class JurusanData extends Data
{
    public function __construct(
        public ?int $id,
        public string $nama_jurusan,
        public ?string $kode_jurusan,
        public ?int $kaprodi_user_id,
        
        // Additional flag for auto-creating kaprodi user
        public bool $create_kaprodi = false,
    ) {}
}
