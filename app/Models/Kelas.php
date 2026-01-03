<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    /**
     * Beri tahu Laravel bahwa tabel 'kelas' tidak punya kolom timestamps.
     */
    public $timestamps = false;

    /**
     * Nama tabelnya adalah 'kelas', bukan 'kelas'.
     * (Di Laravel 11, ini mungkin tidak perlu, tapi ini praktik yang aman).
     */
    protected $table = 'kelas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'jurusan_id',
        'konsentrasi_id',
        'wali_kelas_user_id',
        'nama_kelas',
        'tingkat',
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU Kelas DIMILIKI OLEH SATU Jurusan.
     * (Foreign Key: jurusan_id)
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    /**
     * Relasi Opsional: SATU Kelas DIMILIKI OLEH SATU Konsentrasi.
     * (Foreign Key: konsentrasi_id)
     * Nullable: Kelas X umumnya belum masuk konsentrasi.
     */
    public function konsentrasi(): BelongsTo
    {
        return $this->belongsTo(Konsentrasi::class, 'konsentrasi_id');
    }

    /**
     * Relasi Wajib: SATU Kelas DIMILIKI OLEH SATU Wali Kelas (User).
     * (Foreign Key: wali_kelas_user_id)
     */
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_user_id');
    }

    /**
     * Relasi Wajib: SATU Kelas MEMILIKI BANYAK Siswa.
     * (Foreign Key di tabel 'siswa': kelas_id)
     */
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }
}