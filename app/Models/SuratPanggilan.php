<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratPanggilan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nama tabelnya adalah 'surat_panggilan'.
     */
    protected $table = 'surat_panggilan';

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
        'tindak_lanjut_id',
        'nomor_surat',
        'lampiran',           // NEW
        'hal',                // NEW
        'tipe_surat',
        'pembina_data',
        'pembina_roles',  // Field untuk template tanda tangan
        'tanggal_surat',
        'tanggal_pertemuan',
        'waktu_pertemuan',
        'tempat_pertemuan',   // NEW
        'keperluan',
        'file_path_pdf',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_pertemuan' => 'date',
        'pembina_data' => 'array',
        'pembina_roles' => 'array',  // Cast JSON ke array
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU SuratPanggilan DIMILIKI OLEH SATU TindakLanjut.
     * (Foreign Key: tindak_lanjut_id)
     */
    public function tindakLanjut(): BelongsTo
    {
        return $this->belongsTo(TindakLanjut::class, 'tindak_lanjut_id');
    }

    /**
     * Relasi: SATU SuratPanggilan MEMILIKI BANYAK PrintLog.
     */
    public function printLogs()
    {
        return $this->hasMany(SuratPanggilanPrintLog::class, 'surat_panggilan_id')
                    ->orderBy('printed_at', 'desc');
    }

    /**
     * Get last print info
     */
    public function getLastPrintedAttribute()
    {
        return $this->printLogs()->first();
    }

    /**
     * Get total print count
     */
    public function getPrintCountAttribute()
    {
        return $this->printLogs()->count();
    }
}