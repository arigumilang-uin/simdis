<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Model Konsentrasi (Konsentrasi Keahlian)
 * 
 * Representasi Konsentrasi Keahlian dalam struktur Kurikulum Merdeka SMK.
 * Satu Jurusan (Program Keahlian) memiliki banyak Konsentrasi.
 * Satu Konsentrasi memiliki banyak Kelas (biasanya kelas XI dan XII).
 * 
 * Hierarki:
 * Jurusan (Program Keahlian) 
 *   └── Konsentrasi (Konsentrasi Keahlian)
 *         └── Kelas
 *               └── Siswa
 */
class Konsentrasi extends Model
{
    use HasFactory;

    /**
     * Tabel tidak menggunakan timestamps.
     */
    public $timestamps = false;

    /**
     * Nama tabel di database.
     */
    protected $table = 'konsentrasi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'jurusan_id',
        'nama_konsentrasi',
        'kode_konsentrasi',
        'is_active',
        'deskripsi',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi: Konsentrasi DIMILIKI OLEH satu Jurusan (Program Keahlian).
     * (Foreign Key: jurusan_id)
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    /**
     * Relasi: Konsentrasi MEMILIKI BANYAK Kelas.
     * (Foreign Key di tabel 'kelas': konsentrasi_id)
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'konsentrasi_id');
    }

    /**
     * Relasi: Konsentrasi MEMILIKI BANYAK Siswa MELALUI Kelas.
     */
    public function siswa(): HasManyThrough
    {
        return $this->hasManyThrough(Siswa::class, Kelas::class, 'konsentrasi_id', 'kelas_id');
    }

    // =====================================================================
    // ----------------- SCOPES ------------------
    // =====================================================================

    /**
     * Scope: Hanya konsentrasi yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Konsentrasi berdasarkan jurusan tertentu.
     */
    public function scopeByJurusan($query, int $jurusanId)
    {
        return $query->where('jurusan_id', $jurusanId);
    }

    // =====================================================================
    // ----------------- ACCESSORS ------------------
    // =====================================================================

    /**
     * Accessor: Nama lengkap dengan kode (jika ada).
     * Contoh: "Teknik Pembangkit Biomassa (TPB)"
     */
    public function getNamaLengkapAttribute(): string
    {
        if ($this->kode_konsentrasi) {
            return "{$this->nama_konsentrasi} ({$this->kode_konsentrasi})";
        }
        return $this->nama_konsentrasi;
    }

    /**
     * Accessor: Nama jurusan parent.
     */
    public function getNamaJurusanAttribute(): string
    {
        return $this->jurusan?->nama_jurusan ?? '-';
    }
}
