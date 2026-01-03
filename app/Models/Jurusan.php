<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Jurusan extends Model
{
    use HasFactory;

    /**
     * Beri tahu Laravel bahwa tabel 'jurusan' tidak punya kolom timestamps.
     */
    public $timestamps = false;

    /**
     * Nama tabelnya adalah 'jurusan', bukan 'jurusans'.
     * Laravel biasanya otomatis, tapi ini untuk memastikan.
     */
    protected $table = 'jurusan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kaprodi_user_id',
        'nama_jurusan',
        'kode_jurusan',
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU Jurusan DIMILIKI OLEH SATU Kaprodi (User).
     * (Foreign Key: kaprodi_user_id)
     */
    public function kaprodi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kaprodi_user_id');
    }

    /**
     * Relasi Wajib: SATU Jurusan MEMILIKI BANYAK Kelas.
     * (Foreign Key di tabel 'kelas': jurusan_id)
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'jurusan_id');
    }

    /**
     * Relasi: SATU Jurusan MEMILIKI BANYAK Konsentrasi.
     * (Foreign Key di tabel 'konsentrasi': jurusan_id)
     */
    public function konsentrasi(): HasMany
    {
        return $this->hasMany(Konsentrasi::class, 'jurusan_id');
    }

    /**
     * Relasi Lanjutan (Advanced): Mengambil semua siswa di jurusan ini
     * melalui tabel perantara 'kelas'.
     * * SATU Jurusan MEMILIKI BANYAK Siswa MELALUI Kelas.
     */
    public function siswa(): HasManyThrough
    {
        return $this->hasManyThrough(Siswa::class, Kelas::class);
    }
}