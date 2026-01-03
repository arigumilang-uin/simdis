<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * Beri tahu Laravel bahwa tabel 'roles' tidak punya kolom timestamps.
     * INI PENTING! Jika tidak, aplikasi akan error saat mencoba mengisi created_at.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_role',
    ];


    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU Role MEMILIKI BANYAK User.
     * Ini adalah kebalikan dari relasi di User.php
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Temukan Role berdasarkan nama_role.
     * Memudahkan pencarian role tanpa mengulang string kolom.
     *
     * @param string $name
     * @return self|null
     */
    public static function findByName(string $name): ?self
    {
        return self::where('nama_role', $name)->first();
    }
}