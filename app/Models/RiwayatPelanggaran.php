<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class RiwayatPelanggaran extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Get attributes to log for activity tracking
     */
    protected function getLogAttributes(): array
    {
        return ['siswa_id', 'jenis_pelanggaran_id', 'guru_pencatat_user_id', 'tanggal_kejadian'];
    }

    /**
     * Get custom activity description
     */
    protected function getActivityDescription(string $eventName): string
    {
        $userName = auth()->user()?->nama ?? 'System';
        $siswaName = $this->siswa?->nama_siswa ?? 'Siswa';
        
        return match($eventName) {
            'created' => "{$userName} mencatat pelanggaran untuk {$siswaName}",
            'updated' => "{$userName} mengubah riwayat pelanggaran {$siswaName}",
            'deleted' => "{$userName} menghapus riwayat pelanggaran {$siswaName}",
            default => "{$userName} melakukan {$eventName} pada Riwayat Pelanggaran {$siswaName}",
        };
    }

    /**
     * Nama tabelnya adalah 'riwayat_pelanggaran'.
     */
    protected $table = 'riwayat_pelanggaran';

    /**
     * PENTING: Beri tahu Laravel bahwa tabel ini TIDAK menggunakan
     * timestamps 'created_at' dan 'updated_at' bawaan.
     * Kita akan menganggap 'tanggal_kejadian' sebagai timestamp utamanya.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'siswa_id',
        'jenis_pelanggaran_id',
        'guru_pencatat_user_id',
        'tanggal_kejadian',
        'keterangan',
        'bukti_foto_path',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_kejadian' => 'datetime',
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU RiwayatPelanggaran DIMILIKI OLEH SATU Siswa.
     * (Foreign Key: siswa_id)
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Relasi Wajib: SATU RiwayatPelanggaran MEREKAM SATU JenisPelanggaran.
     * (Foreign Key: jenis_pelanggaran_id)
     */
    public function jenisPelanggaran(): BelongsTo
    {
        return $this->belongsTo(JenisPelanggaran::class, 'jenis_pelanggaran_id');
    }

    /**
     * Relasi Wajib: SATU RiwayatPelanggaran DICATAT OLEH SATU User (Guru).
     * (Foreign Key: guru_pencatat_user_id)
     */
    public function guruPencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_pencatat_user_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Filter riwayat pelanggaran by tanggal kejadian mulai.
     */
    public function scopeFromDate($query, $date)
    {
        if ($date) {
            $query->whereDate('tanggal_kejadian', '>=', $date);
        }
        return $query;
    }

    /**
     * Scope: Filter riwayat pelanggaran by tanggal kejadian akhir.
     */
    public function scopeToDate($query, $date)
    {
        if ($date) {
            $query->whereDate('tanggal_kejadian', '<=', $date);
        }
        return $query;
    }

    /**
     * Scope: Filter riwayat pelanggaran by jenis pelanggaran.
     */
    public function scopeByJenisPelanggaran($query, $jenisPelanggaranId)
    {
        if ($jenisPelanggaranId) {
            $query->where('jenis_pelanggaran_id', $jenisPelanggaranId);
        }
        return $query;
    }

    /**
     * Scope: Filter riwayat pelanggaran by guru pencatat.
     */
    public function scopeByGuruPencatat($query, $guruId)
    {
        if ($guruId) {
            $query->where('guru_pencatat_user_id', $guruId);
        }
        return $query;
    }

    /**
     * Scope: Filter riwayat pelanggaran by siswa.
     */
    public function scopeBySiswa($query, $siswaId)
    {
        if ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }
        return $query;
    }

    /**
     * Scope: Filter riwayat pelanggaran untuk siswa dalam kelas tertentu.
     */
    public function scopeInKelas($query, $kelasId)
    {
        if ($kelasId) {
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }
        return $query;
    }

    /**
     * Scope: Filter riwayat pelanggaran untuk siswa dalam jurusan tertentu.
     */
    public function scopeInJurusan($query, $jurusanId)
    {
        if ($jurusanId) {
            $query->whereHas('siswa.kelas', function ($q) use ($jurusanId) {
                $q->where('jurusan_id', $jurusanId);
            });
        }
        return $query;
    }
}