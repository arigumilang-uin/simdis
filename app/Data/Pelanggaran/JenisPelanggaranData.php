<?php

namespace App\Data\Pelanggaran;

use Spatie\LaravelData\Data;

/**
 * Jenis Pelanggaran Data Transfer Object
 * 
 * Represents violation type data with type-safe properties.
 * Column names are mapped from the real database schema (jenis_pelanggaran table).
 */
class JenisPelanggaranData extends Data
{
    public function __construct(
        public ?int $id,
        public int $kategori_id,
        public string $nama_pelanggaran,
        public int $poin = 0,
        public bool $has_frequency_rules = false,
        public bool $is_active = true,
        public ?string $filter_category = null,
        public ?string $keywords = null,
        
        // Relationships (optional, for responses)
        public ?object $kategoriPelanggaran = null,
        public ?array $frequencyRules = null,
    ) {}

    /**
     * Validation rules for creating/updating jenis pelanggaran.
     *
     * @return array<string, array<string>>
     */
    public static function rules(): array
    {
        return [
            'kategori_id' => ['required', 'integer', 'exists:kategori_pelanggaran,id'],
            'nama_pelanggaran' => ['required', 'string', 'max:255'],
            'poin' => ['required', 'integer', 'min:0', 'max:100'],
            'has_frequency_rules' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'filter_category' => ['nullable', 'string', 'in:atribut,absensi,kerapian,ibadah,berat'],
            'keywords' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get keywords as an array.
     *
     * @return array<string>
     */
    public function getKeywordsArray(): array
    {
        if (!$this->keywords) {
            return [];
        }
        return array_filter(array_map('trim', explode('|', $this->keywords)));
    }

    /**
     * Set keywords from an array.
     *
     * @param array<string> $keywords
     * @return void
     */
    public function setKeywordsFromArray(array $keywords): void
    {
        $this->keywords = implode('|', array_filter($keywords));
    }
}
