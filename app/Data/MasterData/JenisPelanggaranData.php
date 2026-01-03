<?php

namespace App\Data\MasterData;

use Spatie\LaravelData\Data;

/**
 * JenisPelanggaran Data Transfer Object
 * 
 * Purpose: Transfer jenis pelanggaran data between layers
 * Pattern: DTO (Data Transfer Object)
 */
class JenisPelanggaranData extends Data
{
    public function __construct(
        public ?int $id,
        public string $nama_pelanggaran,
        public int $kategori_id,
        public ?int $poin,
        public ?string $filter_category,
        public ?string $keywords,
        public ?bool $is_active,
    ) {}
}
