<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Mata Pelajaran
 * 
 * Daftar mata pelajaran yang diajarkan di sekolah.
 * Sekarang terikat ke kurikulum tertentu.
 */
class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'kurikulum_id',
        'nama_mapel',
        'kode_mapel',
        'kelompok',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Kelompok constants
    const KELOMPOK_A = 'A'; // Umum
    const KELOMPOK_B = 'B'; // Kejuruan
    const KELOMPOK_C = 'C'; // Pilihan/Muatan Lokal

    // =====================================================================
    // ----------------------- RELATIONSHIPS -----------------------
    // =====================================================================

    /**
     * Kurikulum yang memiliki mata pelajaran ini
     */
    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    /**
     * Mata pelajaran memiliki banyak jadwal mengajar
     */
    public function jadwalMengajar(): HasMany
    {
        return $this->hasMany(JadwalMengajar::class, 'mata_pelajaran_id');
    }

    /**
     * Guru-guru yang bisa mengajar mata pelajaran ini (many-to-many)
     */
    public function guruPengampu(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guru_mata_pelajaran', 'mata_pelajaran_id', 'user_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Guru utama untuk mata pelajaran ini
     */
    public function guruUtama(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->guruPengampu()->wherePivot('is_primary', true);
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Only active subjects
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by kurikulum
     */
    public function scopeForKurikulum($query, int $kurikulumId)
    {
        return $query->where('kurikulum_id', $kurikulumId);
    }

    /**
     * Scope: Filter by kelompok
     */
    public function scopeForKelompok($query, string $kelompok)
    {
        return $query->where('kelompok', $kelompok);
    }

    /**
     * Scope: Search by name or code
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (!$keyword) return $query;
        
        return $query->where(function($q) use ($keyword) {
            $q->where('nama_mapel', 'like', "%{$keyword}%")
              ->orWhere('kode_mapel', 'like', "%{$keyword}%");
        });
    }

    // =====================================================================
    // ----------------------- STATIC METHODS -----------------------
    // =====================================================================

    /**
     * Get mapel for a specific kurikulum (for dropdowns)
     */
    public static function getForKurikulum(int $kurikulumId): \Illuminate\Database\Eloquent\Collection
    {
        return self::forKurikulum($kurikulumId)
            ->active()
            ->orderBy('nama_mapel')
            ->get();
    }

    /**
     * Get mapel for a kelas (by determining kurikulum from tingkat)
     * 
     * @param int $kelasId
     * @param int $periodeId
     */
    public static function getForKelas(int $kelasId, int $periodeId): \Illuminate\Database\Eloquent\Collection
    {
        $kelas = Kelas::find($kelasId);
        if (!$kelas) return collect();
        
        $kurikulumId = TingkatKurikulum::getKurikulumIdFor($periodeId, $kelas->tingkat);
        if (!$kurikulumId) return collect();
        
        return self::getForKurikulum($kurikulumId);
    }

    // =====================================================================
    // ----------------------- HELPER METHODS -----------------------
    // =====================================================================

    /**
     * Get display name with code
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->kode_mapel) {
            return "{$this->kode_mapel} - {$this->nama_mapel}";
        }
        return $this->nama_mapel;
    }

    /**
     * Get kelompok label
     */
    public function getKelompokLabelAttribute(): string
    {
        return match($this->kelompok) {
            self::KELOMPOK_A => 'Umum',
            self::KELOMPOK_B => 'Kejuruan',
            self::KELOMPOK_C => 'Pilihan',
            default => '-',
        };
    }
}
