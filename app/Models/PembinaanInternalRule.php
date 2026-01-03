<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembinaanInternalRule extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'pembinaan_internal_rules';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'poin_min',
        'poin_max',
        'pembina_roles',
        'keterangan',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'pembina_roles' => 'array',
    ];

    // =====================================================================
    // ----------------------- HELPER METHODS -----------------------
    // =====================================================================

    /**
     * Cek apakah total poin masuk dalam range rule ini.
     */
    public function matchesPoin(int $totalPoin): bool
    {
        if ($this->poin_max === null) {
            // Open-ended: poin >= poin_min
            return $totalPoin >= $this->poin_min;
        }

        // Range: poin_min <= poin <= poin_max
        return $totalPoin >= $this->poin_min && $totalPoin <= $this->poin_max;
    }

    /**
     * Get formatted range text untuk display.
     */
    public function getRangeText(): string
    {
        if ($this->poin_max === null) {
            return "{$this->poin_min}+ poin";
        }

        return "{$this->poin_min}-{$this->poin_max} poin";
    }
}
