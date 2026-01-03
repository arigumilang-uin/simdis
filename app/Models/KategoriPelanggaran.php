<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\KategoriPelanggaranEnum;

/**
 * Kategori Pelanggaran Model
 * 
 * CRITICAL REFERENCE DATA:
 * This table contains essential data for system operation.
 * Deletion of records may break the application!
 */
class KategoriPelanggaran extends Model
{
    use HasFactory;

    /**
     * Enable timestamps (for audit trail)
     */
    public $timestamps = true;

    /**
     * Nama tabelnya adalah 'kategori_pelanggaran'.
     */
    protected $table = 'kategori_pelanggaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_kategori',
        'tingkat_keseriusan',
    ];
    
    /**
     * CRITICAL: System-required kategori that CANNOT be deleted
     * 
     * These represent core reference data seeded via migration
     */
    protected const SYSTEM_REQUIRED = ['ringan', 'sedang', 'berat'];
    
    /**
     * Check if this kategori is system-required (cannot be deleted)
     */
    public function isSystemRequired(): bool
    {
        return in_array($this->tingkat_keseriusan, self::SYSTEM_REQUIRED);
    }
    
    /**
     * Get enum instance for this kategori
     */
    public function getEnum(): ?KategoriPelanggaranEnum
    {
        return KategoriPelanggaranEnum::tryFrom($this->tingkat_keseriusan);
    }
    
    /**
     * Get color for UI display
     */
    public function getColorAttribute(): string
    {
        return $this->getEnum()?->color() ?? 'secondary';
    }
    
    /**
     * Get icon for UI display
     */
    public function getIconAttribute(): string
    {
        return $this->getEnum()?->icon() ?? 'fa-circle';
    }

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU Kategori MEMILIKI BANYAK JenisPelanggaran.
     * (Foreign Key di tabel 'jenis_pelanggaran': kategori_id)
     */
    public function jenisPelanggaran(): HasMany
    {
        return $this->hasMany(JenisPelanggaran::class, 'kategori_id');
    }
    
    // =====================================================================
    // ------------------ MODEL EVENTS (SAFETY) ------------------
    // =====================================================================
    
    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();
        
        // SAFETY: Prevent deletion of system-required kategori
        static::deleting(function ($kategori) {
            if ($kategori->isSystemRequired()) {
                throw new \LogicException(
                    "Cannot delete system-required kategori: {$kategori->nama_kategori}. " .
                    "This is critical reference data needed for system operation."
                );
            }
            
            // Prevent deletion if has related jenis pelanggaran
            if ($kategori->jenisPelanggaran()->exists()) {
                throw new \LogicException(
                    "Cannot delete kategori: {$kategori->nama_kategori}. " .
                    "It has {$kategori->jenisPelanggaran()->count()} related jenis pelanggaran."
                );
            }
        });
    }
}