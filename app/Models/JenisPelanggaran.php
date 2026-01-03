<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPelanggaran extends Model
{
    use HasFactory;

    /**
     * Beri tahu Laravel bahwa tabel ini tidak punya timestamps.
     */
    public $timestamps = false;

    /**
     * Nama tabelnya adalah 'jenis_pelanggaran'.
     */
    protected $table = 'jenis_pelanggaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kategori_id',
        'nama_pelanggaran',
        'poin',
        'has_frequency_rules',
        'is_active',
        'filter_category',
        'keywords',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'has_frequency_rules' => 'boolean',
        'is_active' => 'boolean',
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU JenisPelanggaran DIMILIKI OLEH SATU KategoriPelanggaran.
     * (Foreign Key: kategori_id)
     */
    public function kategoriPelanggaran(): BelongsTo
    {
        return $this->belongsTo(KategoriPelanggaran::class, 'kategori_id');
    }

    /**
     * Relasi Wajib: SATU JenisPelanggaran TELAH TERCATAT BANYAK KALI di RiwayatPelanggaran.
     * (Foreign Key di tabel 'riwayat_pelanggaran': jenis_pelanggaran_id)
     */
    public function riwayatPelanggaran(): HasMany
    {
        return $this->hasMany(RiwayatPelanggaran::class, 'jenis_pelanggaran_id');
    }

    /**
     * Relasi Opsional: SATU JenisPelanggaran MEMILIKI BANYAK FrequencyRules.
     * (Foreign Key di tabel 'pelanggaran_frequency_rules': jenis_pelanggaran_id)
     */
    public function frequencyRules(): HasMany
    {
        return $this->hasMany(PelanggaranFrequencyRule::class, 'jenis_pelanggaran_id')
                    ->orderBy('display_order');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Filter berdasarkan kategori filter (atribut, absensi, kerapian, ibadah, berat).
     */
    public function scopeByFilterCategory($query, $category)
    {
        if ($category) {
            $query->where('filter_category', $category);
        }
        return $query;
    }

    /**
     * Scope: Pencarian fuzzy berdasarkan keyword/alias atau nama pelanggaran.
     * Menggunakan logika pencarian yang fleksibel:
     * - Cek kesamaan dengan nama pelanggaran (LIKE)
     * - Cek kesamaan dengan keywords yang tersimpan
     */
    public function scopeSearchByKeyword($query, $keyword)
    {
        if (!$keyword) {
            return $query;
        }

        $keyword = strtolower(trim($keyword));

        return $query->where(function($q) use ($keyword) {
            // Pencarian pada nama pelanggaran
            $q->whereRaw("LOWER(nama_pelanggaran) LIKE ?", ["%{$keyword}%"])
              // Pencarian pada keywords (yang dipisahkan dengan |)
              ->orWhereRaw("LOWER(keywords) LIKE ?", ["%{$keyword}%"]);
        });
    }

    // =====================================================================
    // ----------------------- HELPER METHODS -----------------------
    // =====================================================================

    /**
     * Kembalikan daftar keywords sebagai array.
     * Keywords disimpan dalam format: "keyword1|keyword2|keyword3"
     */
    public function getKeywordsArray(): array
    {
        if (!$this->keywords) {
            return [];
        }
        return array_filter(array_map('trim', explode('|', $this->keywords)));
    }

    /**
     * Set keywords dari array, akan disimpan dengan pemisah |
     */
    public function setKeywordsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['keywords'] = implode('|', array_filter($value));
        } else {
            $this->attributes['keywords'] = $value;
        }
    }

    /**
     * Helper: Cek apakah pelanggaran ini menggunakan frequency rules.
     */
    public function usesFrequencyRules(): bool
    {
        return $this->has_frequency_rules === true;
    }

    /**
     * Get display poin for UI - BACKWARD COMPATIBLE
     * 
     * LOGIC:
     * - If has frequency rules → Show "Berdasarkan Frekuensi"
     * - If no frequency rules → Show actual poin from database
     * 
     * This ensures old pelanggaran (without rules) still show correct poin
     */
    public function getDisplayPoin(): string
    {
        if ($this->usesFrequencyRules()) {
            return 'Berdasarkan Frekuensi';
        }
        
        return (string)($this->poin ?? 0);
    }

    /**
     * Get numeric poin for calculations - BACKWARD COMPATIBLE
     * 
     * LOGIC:
     * - If has frequency rules → Returns 0 (poin determined by rules at runtime)
     * - If no frequency rules → Returns actual poin
     * 
     * This is used when recording violations to know base poin
     */
    public function getNumericPoin(): int
    {
        if ($this->usesFrequencyRules()) {
            // Frequency-based: poin will be calculated based on rules
            return 0;
        }
        
        // Legacy: use poin from database
        return $this->poin ?? 0;
    }

    /**
     * Check if this jenis pelanggaran is valid for recording
     * 
     * Valid if:
     * - Has frequency rules AND is active, OR
     * - No frequency rules but has poin > 0
     */
    public function isRecordable(): bool
    {
        if ($this->usesFrequencyRules()) {
            // Must be active and have at least one rule
            return $this->is_active && $this->frequencyRules()->exists();
        }
        
        // Legacy: must have poin
        return $this->poin > 0;
    }
}