<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Siswa extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Get attributes to log for activity tracking
     */
    protected function getLogAttributes(): array
    {
        return ['nama_siswa', 'nisn', 'kelas_id', 'wali_murid_user_id'];
    }

    /**
     * Get custom activity description
     */
    protected function getActivityDescription(string $eventName): string
    {
        $userName = auth()->user()?->nama ?? 'System';
        
        return match($eventName) {
            'created' => "{$userName} menambahkan siswa {$this->nama_siswa}",
            'updated' => "{$userName} mengubah data siswa {$this->nama_siswa}",
            'deleted' => "{$userName} menghapus siswa {$this->nama_siswa}",
            default => "{$userName} melakukan {$eventName} pada data siswa {$this->nama_siswa}",
        };
    }

    /**
     * Nama tabelnya adalah 'siswa', bukan 'siswas'.
     */
    protected $table = 'siswa';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kelas_id',
        'wali_murid_user_id',
        'nisn',
        'nama_siswa',
        'nomor_hp_wali_murid',
        'alasan_keluar',
        'keterangan_keluar',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'kelas_id' => 'integer',
        'wali_murid_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Kita memiliki timestamps 'created_at' dan 'updated_at' di tabel ini.
     * Jadi, $timestamps = true (ini default, tidak perlu ditulis).
     */

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU Siswa DIMILIKI OLEH SATU Kelas.
     * (Foreign Key: kelas_id)
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi Opsional: SATU Siswa DIMILIKI OLEH SATU User (Wali Murid).
     * (Foreign Key: wali_murid_user_id)
     */
    public function waliMurid(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_murid_user_id');
    }

    /**
     * Relasi Wajib: SATU Siswa MEMILIKI BANYAK Riwayat Pelanggaran.
     * (Foreign Key di tabel 'riwayat_pelanggaran': siswa_id)
     */
    public function riwayatPelanggaran(): HasMany
    {
        // Kita bisa urutkan langsung dari yang terbaru
        return $this->hasMany(RiwayatPelanggaran::class, 'siswa_id')->latest('tanggal_kejadian');
    }

    /**
     * Relasi Wajib: SATU Siswa MEMILIKI BANYAK Kasus Tindak Lanjut.
     * (Foreign Key di tabel 'tindak_lanjut': siswa_id)
     */
    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(TindakLanjut::class, 'siswa_id')->latest();
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Filter siswa berdasarkan kelas.
     */
    public function scopeInKelas($query, $kelasId)
    {
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }
        return $query;
    }

    /**
     * Scope: Filter siswa berdasarkan jurusan (via kelas).
     */
    public function scopeInJurusan($query, $jurusanId)
    {
        if ($jurusanId) {
            $query->whereHas('kelas', function ($q) use ($jurusanId) {
                $q->where('jurusan_id', $jurusanId);
            });
        }
        return $query;
    }

    /**
     * Scope: Filter siswa berdasarkan wali murid.
     */
    public function scopeByWaliMurid($query, $waliMuridId)
    {
        if ($waliMuridId) {
            $query->where('wali_murid_user_id', $waliMuridId);
        }
        return $query;
    }

    /**
     * Scope: Cari siswa berdasarkan nama atau NISN.
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_siswa', 'like', "%{$keyword}%")
                  ->orWhere('nisn', 'like', "%{$keyword}%");
            });
        }
        return $query;
    }

    /**
     * Scope: Filter siswa yang memiliki riwayat pelanggaran.
     */
    public function scopeWithViolations($query)
    {
        return $query->whereHas('riwayatPelanggaran');
    }

    /**
     * Scope: Filter siswa yang memiliki kasus tindak lanjut aktif.
     */
    public function scopeWithActiveCases($query)
    {
        return $query->whereHas('tindakLanjut', function ($q) {
            $q->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani']);
        });
    }
    
    // =====================================================================
    // ------------------ PERFORMANCE OPTIMIZATIONS ------------------
    // =====================================================================
    
    /**
     * Accessor: Get total poin pelanggaran for this siswa.
     * 
     * USAGE IN QUERY (Eager Load):
     * Siswa::withSum(['riwayatPelanggaran' => function($q) {
     *     $q->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
     *       ->select(DB::raw('COALESCE(SUM(jenis_pelanggaran.poin), 0)'));
     * }], 'poin')
     * 
     * This avoids N+1 by calculating points in single query with JOIN
     * 
     * @return int
     */
    public function getTotalPoinAttribute(): int
    {
        // If already loaded via withSum/eager loading, use that
        if (isset($this->attributes['total_poin'])) {
            return (int) $this->attributes['total_poin'];
        }
        
        // Fallback: Calculate on-demand (lazy loading - avoid in loops!)
        return (int) $this->riwayatPelanggaran()
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->sum('jenis_pelanggaran.poin');
    }
}