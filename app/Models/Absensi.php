<?php

namespace App\Models;

use App\Enums\StatusAbsensi;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Absensi
 * 
 * Catatan kehadiran siswa per sesi/per hari.
 * 
 * INTEGRASI PELANGGARAN:
 * - Status ALFA akan otomatis mencatat ke riwayat_pelanggaran
 * - riwayat_pelanggaran_id menyimpan referensi ke pelanggaran yang dibuat
 */
class Absensi extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'absensi';

    protected $fillable = [
        'siswa_id',
        'jadwal_mengajar_id',
        'pertemuan_id',
        'tanggal',
        'status',
        'keterangan',
        'pencatat_user_id',
        'riwayat_pelanggaran_id',
        'absen_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'status' => StatusAbsensi::class,
        'absen_at' => 'datetime',
    ];

    /**
     * Get attributes to log for activity tracking
     */
    protected function getLogAttributes(): array
    {
        return ['siswa_id', 'status', 'tanggal'];
    }

    /**
     * Get custom activity description
     */
    protected function getActivityDescription(string $eventName): string
    {
        $userName = auth()->user()?->nama ?? 'System';
        $siswaName = $this->siswa?->nama_siswa ?? 'Siswa';
        
        return match($eventName) {
            'created' => "{$userName} mencatat absensi {$this->status->value} untuk {$siswaName}",
            'updated' => "{$userName} mengubah status absensi {$siswaName}",
            'deleted' => "{$userName} menghapus absensi {$siswaName}",
            default => "{$userName} melakukan {$eventName} pada Absensi {$siswaName}",
        };
    }

    // =====================================================================
    // ----------------------- RELATIONSHIPS -----------------------
    // =====================================================================

    /**
     * Siswa yang diabsen
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Jadwal mengajar (optional)
     */
    public function jadwalMengajar(): BelongsTo
    {
        return $this->belongsTo(JadwalMengajar::class, 'jadwal_mengajar_id');
    }

    /**
     * Guru/Staff yang mencatat
     */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pencatat_user_id');
    }

    /**
     * Pertemuan spesifik (tanggal real)
     */
    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id');
    }

    /**
     * Pelanggaran yang terkait (jika status = Alfa)
     */
    public function riwayatPelanggaran(): BelongsTo
    {
        return $this->belongsTo(RiwayatPelanggaran::class, 'riwayat_pelanggaran_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Filter by tanggal
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('tanggal', $date);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    /**
     * Scope: Today's attendance
     */
    public function scopeToday($query)
    {
        return $query->onDate(today());
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, StatusAbsensi $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Only Alfa (for pelanggaran tracking)
     */
    public function scopeAlfa($query)
    {
        return $query->withStatus(StatusAbsensi::Alfa);
    }

    /**
     * Scope: Filter by siswa
     */
    public function scopeForSiswa($query, int $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    /**
     * Scope: Filter by jadwal
     */
    public function scopeForJadwal($query, int $jadwalId)
    {
        return $query->where('jadwal_mengajar_id', $jadwalId);
    }

    /**
     * Scope: Filter by pertemuan
     */
    public function scopeForPertemuan($query, int $pertemuanId)
    {
        return $query->where('pertemuan_id', $pertemuanId);
    }

    /**
     * Scope: Filter by kelas (via siswa)
     */
    public function scopeForKelas($query, int $kelasId)
    {
        return $query->whereHas('siswa', function($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        });
    }

    // =====================================================================
    // ----------------------- HELPER METHODS -----------------------
    // =====================================================================

    /**
     * Check if this attendance is Alfa and linked to pelanggaran
     */
    public function isAlfa(): bool
    {
        return $this->status === StatusAbsensi::Alfa;
    }

    /**
     * Check if pelanggaran sudah tercatat
     */
    public function hasPelanggaran(): bool
    {
        return $this->riwayat_pelanggaran_id !== null;
    }

    /**
     * Link to pelanggaran
     */
    public function linkToPelanggaran(int $pelanggaranId): void
    {
        $this->update(['riwayat_pelanggaran_id' => $pelanggaranId]);
    }
}
