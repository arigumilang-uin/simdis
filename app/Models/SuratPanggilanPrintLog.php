<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPanggilanPrintLog extends Model
{
    /**
     * Table name
     */
    protected $table = 'surat_panggilan_print_log';

    /**
     * Disable timestamps (we use printed_at)
     */
    public $timestamps = false;

    /**
     * Fillable attributes
     */
    protected $fillable = [
        'surat_panggilan_id',
        'user_id',
        'printed_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'printed_at' => 'datetime',
    ];

    /**
     * Relationship: Print log belongs to a surat panggilan
     */
    public function suratPanggilan(): BelongsTo
    {
        return $this->belongsTo(SuratPanggilan::class, 'surat_panggilan_id');
    }

    /**
     * Relationship: Print log belongs to a user (who printed)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
