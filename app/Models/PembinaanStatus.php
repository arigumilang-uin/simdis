<?php

namespace App\Models;

use App\Enums\StatusPembinaan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pembinaan Status Model
 * 
 * Merekam status pembinaan internal siswa berdasarkan akumulasi poin.
 * Setiap record mewakili satu "sesi" pembinaan yang perlu dilakukan.
 */
class PembinaanStatus extends Model
{
    protected $table = 'pembinaan_status';

    protected $fillable = [
        'siswa_id',
        'pembinaan_rule_id',
        'total_poin_saat_trigger',
        'range_text',
        'keterangan_pembinaan',
        'pembina_roles',
        'status',
        'dibina_oleh_user_id',
        'dibina_at',
        'diselesaikan_oleh_user_id',
        'selesai_at',
        'catatan_pembinaan',
        'hasil_pembinaan',
    ];

    protected $casts = [
        'pembina_roles' => 'array',
        'status' => StatusPembinaan::class,
        'dibina_at' => 'datetime',
        'selesai_at' => 'datetime',
    ];

    // =====================================================================
    // RELATIONSHIPS
    // =====================================================================

    /**
     * Relasi ke Siswa.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Relasi ke PembinaanInternalRule.
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(PembinaanInternalRule::class, 'pembinaan_rule_id');
    }

    /**
     * Relasi ke User yang membina.
     */
    public function dibinaOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibina_oleh_user_id');
    }

    /**
     * Relasi ke User yang menyelesaikan.
     */
    public function diselesaikanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diselesaikan_oleh_user_id');
    }

    // =====================================================================
    // SCOPES
    // =====================================================================

    /**
     * Scope untuk status aktif (perlu pembinaan atau sedang dibina).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            StatusPembinaan::PERLU_PEMBINAAN,
            StatusPembinaan::SEDANG_DIBINA,
        ]);
    }

    /**
     * Scope untuk status selesai.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', StatusPembinaan::SELESAI);
    }

    /**
     * Scope untuk siswa tertentu.
     */
    public function scopeForSiswa($query, int $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    /**
     * Scope untuk rule tertentu.
     */
    public function scopeForRule($query, int $ruleId)
    {
        return $query->where('pembinaan_rule_id', $ruleId);
    }

    // =====================================================================
    // HELPER METHODS
    // =====================================================================

    /**
     * Cek apakah pembinaan ini aktif (belum selesai).
     */
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /**
     * Cek apakah pembinaan ini sudah selesai.
     */
    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }

    /**
     * Cek apakah user dengan role tertentu adalah pembina yang ditugaskan.
     */
    public function isPembinaForRole(string $role): bool
    {
        return in_array($role, $this->pembina_roles ?? []);
    }

    /**
     * Mulai proses pembinaan.
     */
    public function mulaiPembinaan(int $userId): bool
    {
        if ($this->status !== StatusPembinaan::PERLU_PEMBINAAN) {
            return false;
        }

        return $this->update([
            'status' => StatusPembinaan::SEDANG_DIBINA,
            'dibina_oleh_user_id' => $userId,
            'dibina_at' => now(),
        ]);
    }

    /**
     * Selesaikan pembinaan.
     */
    public function selesaikanPembinaan(int $userId, ?string $hasilPembinaan = null): bool
    {
        if ($this->status !== StatusPembinaan::SEDANG_DIBINA) {
            return false;
        }

        return $this->update([
            'status' => StatusPembinaan::SELESAI,
            'diselesaikan_oleh_user_id' => $userId,
            'selesai_at' => now(),
            'hasil_pembinaan' => $hasilPembinaan,
        ]);
    }

    // =====================================================================
    // STATIC METHODS
    // =====================================================================

    /**
     * Cek apakah siswa sudah punya record (aktif ATAU selesai) untuk rule tertentu.
     */
    public static function hasRecordForSiswaAndRule(int $siswaId, int $ruleId): bool
    {
        return self::forSiswa($siswaId)
            ->forRule($ruleId)
            ->exists();
    }

    /**
     * Get record aktif untuk siswa dan rule tertentu.
     */
    public static function getActiveForSiswaAndRule(int $siswaId, int $ruleId): ?self
    {
        return self::forSiswa($siswaId)
            ->forRule($ruleId)
            ->active()
            ->first();
    }

    /**
     * Get record terakhir (aktif atau selesai) untuk siswa dan rule tertentu.
     */
    public static function getLatestForSiswaAndRule(int $siswaId, int $ruleId): ?self
    {
        return self::forSiswa($siswaId)
            ->forRule($ruleId)
            ->latest()
            ->first();
    }

    /**
     * Buat record pembinaan baru jika belum ada yang aktif DAN belum ada yang selesai untuk rule ini.
     * 
     * LOGIC:
     * - Jika sudah ada record AKTIF → tidak buat (return null)
     * - Jika sudah ada record SELESAI untuk rule ini → tidak buat (return null)
     * - Hanya buat jika belum ada record sama sekali untuk rule ini
     */
    public static function createIfNotExists(
        int $siswaId,
        int $ruleId,
        int $totalPoin,
        string $rangeText,
        string $keterangan,
        array $pembinaRoles
    ): ?self {
        // Cek apakah sudah ada record (aktif ATAU selesai) untuk rule ini
        if (self::hasRecordForSiswaAndRule($siswaId, $ruleId)) {
            return null;
        }

        return self::create([
            'siswa_id' => $siswaId,
            'pembinaan_rule_id' => $ruleId,
            'total_poin_saat_trigger' => $totalPoin,
            'range_text' => $rangeText,
            'keterangan_pembinaan' => $keterangan,
            'pembina_roles' => $pembinaRoles,
            'status' => StatusPembinaan::PERLU_PEMBINAAN,
        ]);
    }
}
