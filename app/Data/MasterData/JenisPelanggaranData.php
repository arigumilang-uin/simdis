<?php

namespace App\Data\MasterData;

use Spatie\LaravelData\Data;

/**
 * Jenis Pelanggaran Data Transfer Object
 * 
 * Purpose: Transfer data for JenisPelanggaran creation/update
 */
class JenisPelanggaranData extends Data
{
    public function __construct(
        public string $nama_pelanggaran,
        public int $kategori_id,
        public ?string $filter_category,
        public ?string $keywords,
    ) {}
}
