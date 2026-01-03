<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelanggaranFrequencyRule extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'pelanggaran_frequency_rules';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'jenis_pelanggaran_id',
        'frequency_min',
        'frequency_max',
        'poin',
        'sanksi_description',
        'trigger_surat',
        'pembina_roles',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'trigger_surat' => 'boolean',
        'pembina_roles' => 'array',
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi ke JenisPelanggaran.
     */
    public function jenisPelanggaran(): BelongsTo
    {
        return $this->belongsTo(JenisPelanggaran::class, 'jenis_pelanggaran_id');
    }

    // =====================================================================
    // ----------------------- HELPER METHODS -----------------------
    // =====================================================================

    /**
     * Cek apakah frekuensi match dengan rule ini.
     * 
     * LOGIC (Updated 2025-12-11):
     * 
     * Case 1: min=1, max=NULL → Trigger SETIAP kali (1, 2, 3, 4, ...)
     * Case 2: min=1, max=1 → Trigger SETIAP kali (1, 2, 3, 4, ...)
     * Case 3: min=3, max=3 → Trigger SETIAP KELIPATAN 3 (3, 6, 9, 12, ...)
     * Case 4: min=1, max=3 → Trigger SEKALI di frekuensi 3 (escalation)
     * Case 5: min=4, max=10 → Trigger SEKALI di frekuensi 10 (escalation)
     * 
     * Rationale:
     * - max=NULL: Trigger setiap kali (unlimited)
     * - min=max: Trigger setiap kelipatan (repeating)
     * - min≠max: Trigger sekali di max (escalation threshold)
     * 
     * @param int $frequency Current frequency count
     * @return bool True if rule matches this frequency
     */
    public function matchesFrequency(int $frequency): bool
    {
        // Case 1: max=NULL → Trigger setiap kali >= min
        if ($this->frequency_max === null) {
            return $frequency >= $this->frequency_min;
        }
        
        // Case 2: min=max → Trigger setiap kelipatan min
        if ($this->frequency_min === $this->frequency_max) {
            // Check if frequency is multiple of min
            return $frequency > 0 && ($frequency % $this->frequency_min) === 0;
        }
        
        // Case 3: min≠max → Trigger SEKALI di max (escalation)
        return $frequency === $this->frequency_max;
    }

    /**
     * Tentukan tipe surat berdasarkan pembina yang terlibat.
     * 
     * CATATAN: "Semua Guru & Staff" biasanya untuk pembinaan ditempat, tidak trigger surat formal.
     * 
     * @return string|null Tipe surat (Surat 1, Surat 2, Surat 3, Surat 4) atau null
     */
    public function getSuratType(): ?string
    {
        if (!$this->trigger_surat) {
            return null;
        }

        // Jika pembina adalah "Semua Guru & Staff", tidak trigger surat formal
        // (pembinaan ditempat oleh siapa saja yang melihat)
        if (in_array('Semua Guru & Staff', $this->pembina_roles)) {
            return null;
        }

        $pembinaCount = count($this->pembina_roles);

        // Surat 1: Wali Kelas (1 pembina)
        if ($pembinaCount === 1 && in_array('Wali Kelas', $this->pembina_roles)) {
            return 'Surat 1';
        }

        // Surat 2: Wali Kelas + Kaprodi (2 pembina)
        if ($pembinaCount === 2 &&
            in_array('Wali Kelas', $this->pembina_roles) &&
            in_array('Kaprodi', $this->pembina_roles)) {
            return 'Surat 2';
        }

        // Surat 3: Wali Kelas + Kaprodi + Waka (3 pembina)
        if ($pembinaCount === 3 &&
            in_array('Wali Kelas', $this->pembina_roles) &&
            in_array('Kaprodi', $this->pembina_roles) &&
            in_array('Waka Kesiswaan', $this->pembina_roles)) {
            return 'Surat 3';
        }

        // Surat 4: Semua pembina (4 pembina atau lebih)
        if ($pembinaCount >= 4) {
            return 'Surat 4';
        }

        return null;
    }
}
