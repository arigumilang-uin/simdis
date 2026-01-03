<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class TindakLanjut extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Get attributes to log for activity tracking
     */
    protected function getLogAttributes(): array
    {
        return ['siswa_id', 'status', 'pemicu', 'tanggal_tindak_lanjut'];
    }

    /**
     * Get custom activity description
     */
    protected function getActivityDescription(string $eventName): string
    {
        $userName = auth()->user()?->nama ?? 'System';
        $siswaName = $this->siswa?->nama_siswa ?? 'Siswa';
        
        return match($eventName) {
            'created' => "{$userName} membuat tindak lanjut untuk {$siswaName}",
            'updated' => "{$userName} mengubah tindak lanjut {$siswaName} (Status: {$this->status->value})",
            'deleted' => "{$userName} menghapus tindak lanjut {$siswaName}",
            default => parent::getActivityDescription($eventName),
        };
    }

    /**
     * Nama tabelnya adalah 'tindak_lanjut'.
     */
    protected $table = 'tindak_lanjut';

    /**
     * Kita memiliki timestamps 'created_at' dan 'updated_at' di tabel ini.
     * (Default, tidak perlu ditulis)
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'siswa_id',
        'pemicu',
        'sanksi_deskripsi',
        'pembina_roles',
        'denda_deskripsi',
        'status',
        'tanggal_tindak_lanjut',
        'penyetuju_user_id',
        // Tracking fields
        'ditangani_oleh_user_id',
        'ditangani_at',
        'diselesaikan_oleh_user_id',
        'diselesaikan_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_tindak_lanjut' => 'date',
        'status' => \App\Enums\StatusTindakLanjut::class,
        'pembina_roles' => 'array',
        'ditangani_at' => 'datetime',
        'diselesaikan_at' => 'datetime',
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU Kasus TindakLanjut DIMILIKI OLEH SATU Siswa.
     * (Foreign Key: siswa_id)
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Relasi Opsional: SATU Kasus TindakLanjut DISETUJUI OLEH SATU User.
     * (Foreign Key: penyetuju_user_id)
     */
    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penyetuju_user_id');
    }

    /**
     * Relasi Opsional: SATU Kasus TindakLanjut MEMILIKI SATU SuratPanggilan.
     * (Foreign Key di tabel 'surat_panggilan': tindak_lanjut_id)
     */
    public function suratPanggilan(): HasOne
    {
        return $this->hasOne(SuratPanggilan::class, 'tindak_lanjut_id');
    }

    /**
     * Relasi: User yang menangani kasus ini.
     */
    public function ditanganiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh_user_id');
    }

    /**
     * Relasi: User yang menyelesaikan kasus ini.
     */
    public function diselesaikanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diselesaikan_oleh_user_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Filter kasus yang sedang menunggu persetujuan Kepala Sekolah.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'Menunggu Persetujuan');
    }

    /**
     * Scope: Filter kasus yang sudah disetujui.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'Disetujui');
    }

    /**
     * Scope: Filter kasus yang sedang ditangani.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'Ditangani');
    }

    /**
     * Scope: Filter kasus yang sudah selesai.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'Selesai');
    }

    /**
     * Scope: Filter kasus berdasarkan status tertentu.
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope: Filter kasus untuk siswa tertentu.
     */
    public function scopeBySiswa($query, $siswaId)
    {
        if ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }
        return $query;
    }

    /**
     * Scope: Filter kasus untuk siswa dalam kelas tertentu.
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
     * Scope: Filter kasus untuk siswa dalam jurusan tertentu.
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

    /**
     * Scope: Filter kasus aktif (belum selesai).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani']);
    }

    /**
     * Scope: Filter kasus untuk pembina tertentu.
     * 
     * Parameter $role harus salah satu dari:
     * - 'Wali Kelas'
     * - 'Kaprodi'
     * - 'Waka Kesiswaan' / 'Waka Sarana'
     * - 'Kepala Sekolah'
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $role Role pembina (e.g., 'Wali Kelas')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForPembina($query, string $role)
    {
        return $query->whereJsonContains('pembina_roles', $role);
    }
}