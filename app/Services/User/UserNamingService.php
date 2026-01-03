<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\Role;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Str;

/**
 * Service untuk generate nama, username, dan password otomatis berdasarkan role dan konfigurasi.
 */
class UserNamingService
{
    /**
     * Generate nama otomatis berdasarkan role dan konfigurasi.
     * 
     * @param User $user User yang akan di-generate namanya
     * @return string Nama yang di-generate
     */
    public static function generateNama(User $user): string
    {
        $roleName = $user->role?->nama_role ?? '';

        switch ($roleName) {
            case 'Kepala Sekolah':
                return 'Kepala Sekolah';

            case 'Waka Kesiswaan':
                return 'Waka Kesiswaan';

            case 'Kaprodi':
                $jurusan = $user->jurusanDiampu;
                if ($jurusan) {
                    return 'Kaprodi ' . $jurusan->nama_jurusan;
                }
                return 'Kaprodi';

            case 'Wali Kelas':
                $kelas = $user->kelasDiampu;
                if ($kelas) {
                    return 'Wali Kelas ' . $kelas->nama_kelas;
                }
                return 'Wali Kelas';

            case 'Wali Murid':
                // Nama = "Wali dari {nama_siswa_pertama}"
                $anakWali = $user->anakWali()->orderBy('id')->first();
                if ($anakWali) {
                    return 'Wali dari ' . $anakWali->nama_siswa;
                }
                return 'Wali Murid';

            case 'Guru':
                return 'Guru';

            default:
                return $user->nama ?? 'User';
        }
    }

    /**
     * Generate username otomatis berdasarkan role dan konfigurasi.
     * 
     * @param User $user User yang akan di-generate usernamenya
     * @return string Username yang di-generate
     */
    public static function generateUsername(User $user): string
    {
        $roleName = $user->role?->nama_role ?? '';

        switch ($roleName) {
            case 'Kepala Sekolah':
                return self::ensureUniqueUsername('kepalasekolah', $user->id ?? null);

            case 'Waka Kesiswaan':
                return self::ensureUniqueUsername('wakakesiswaan', $user->id ?? null);

            case 'Kaprodi':
                $jurusan = $user->jurusanDiampu;
                if ($jurusan) {
                    $kodeJurusan = preg_replace('/[^a-z0-9]+/i', '', $jurusan->kode_jurusan ?? $jurusan->nama_jurusan);
                    $kodeJurusan = Str::lower($kodeJurusan);
                    $baseUsername = 'kaprodi.' . $kodeJurusan;
                    return self::ensureUniqueUsername($baseUsername, $user->id ?? null);
                }
                return self::ensureUniqueUsername('kaprodi', $user->id ?? null);

            case 'Wali Kelas':
                $kelas = $user->kelasDiampu;
                if ($kelas) {
                    // Format: walikelas.{tingkat}.{kode}{nomor}
                    $namaKelas = $kelas->nama_kelas;
                    $parts = explode(' ', $namaKelas);
                    $tingkat = Str::lower($parts[0] ?? 'x');
                    
                    // Ambil kode jurusan dari kelas
                    $jurusan = $kelas->jurusan;
                    $kode = preg_replace('/[^a-z0-9]+/i', '', $jurusan->kode_jurusan ?? $jurusan->nama_jurusan ?? '');
                    $kode = Str::lower($kode);
                    
                    // Ambil nomor kelas (angka terakhir)
                    $nomor = '';
                    if (count($parts) > 1) {
                        $lastPart = end($parts);
                        if (is_numeric($lastPart)) {
                            $nomor = $lastPart;
                        }
                    }
                    
                    $baseUsername = "walikelas.{$tingkat}.{$kode}{$nomor}";
                    return self::ensureUniqueUsername($baseUsername, $user->id ?? null);
                }
                return self::ensureUniqueUsername('walikelas', $user->id ?? null);

            case 'Wali Murid':
                // Username = "wali.{nisn_siswa_pertama}"
                $anakWali = $user->anakWali()->orderBy('id')->first();
                if ($anakWali) {
                    $nisn = preg_replace('/\D+/', '', (string) $anakWali->nisn);
                    if ($nisn === '') {
                        $nisn = Str::slug($anakWali->nama_siswa);
                    }
                    $baseUsername = 'wali.' . $nisn;
                    return self::ensureUniqueUsername($baseUsername, $user->id ?? null);
                }
                return self::ensureUniqueUsername('walimurid', $user->id ?? null);

            case 'Guru':
                return self::ensureUniqueUsername('guru', $user->id ?? null);

            default:
                return self::ensureUniqueUsername('user', $user->id ?? null);
        }
    }

    /**
     * Generate password otomatis berdasarkan role dan konfigurasi.
     * 
     * @param User $user User yang akan di-generate passwordnya
     * @return string Password yang di-generate (plain text, belum di-hash)
     */
    public static function generatePassword(User $user): string
    {
        $roleName = $user->role?->nama_role ?? '';

        switch ($roleName) {
            case 'Kepala Sekolah':
                return 'smkn1.kepalasekolah';

            case 'Waka Kesiswaan':
                return 'smkn1.wakakesiswaan';

            case 'Kaprodi':
                $jurusan = $user->jurusanDiampu;
                if ($jurusan) {
                    $kodeJurusan = preg_replace('/[^a-z0-9]+/i', '', $jurusan->kode_jurusan ?? $jurusan->nama_jurusan);
                    $kodeJurusan = Str::lower($kodeJurusan);
                    return 'smkn1.kaprodi.' . $kodeJurusan;
                }
                return 'smkn1.kaprodi';

            case 'Wali Kelas':
                $kelas = $user->kelasDiampu;
                if ($kelas) {
                    $namaKelas = $kelas->nama_kelas;
                    $parts = explode(' ', $namaKelas);
                    $tingkat = Str::lower($parts[0] ?? 'x');
                    
                    $jurusan = $kelas->jurusan;
                    $kode = preg_replace('/[^a-z0-9]+/i', '', $jurusan->kode_jurusan ?? $jurusan->nama_jurusan ?? '');
                    $kode = Str::lower($kode);
                    
                    $nomor = '';
                    if (count($parts) > 1) {
                        $lastPart = end($parts);
                        if (is_numeric($lastPart)) {
                            $nomor = $lastPart;
                        }
                    }
                    
                    return 'smkn1.walikelas.' . $tingkat . $kode . $nomor;
                }
                return 'smkn1.walikelas';

            case 'Wali Murid':
                // Password = "smkn1.walimurid.{nomor_hp}" (lebih mudah diingat)
                $anakWali = $user->anakWali()->orderBy('id')->first();
                if ($anakWali && $anakWali->nomor_hp_wali_murid) {
                    $phoneClean = preg_replace('/\D+/', '', $anakWali->nomor_hp_wali_murid);
                    if ($phoneClean !== '') {
                        return 'smkn1.walimurid.' . $phoneClean;
                    }
                }
                // Fallback to phone from user if available
                if ($user->phone) {
                    return 'smkn1.walimurid.' . $user->phone;
                }
                return 'smkn1.walimurid';

            case 'Guru':
                return 'smkn1.guru';

            default:
                return 'smkn1.user';
        }
    }

    /**
     * Pastikan username unik dengan menambahkan angka jika perlu.
     */
    private static function ensureUniqueUsername(string $baseUsername, ?int $excludeUserId = null): string
    {
        $username = $baseUsername;
        $i = 1;
        
        while (User::where('username', $username)
            ->when($excludeUserId, fn($q) => $q->where('id', '!=', $excludeUserId))
            ->exists()) {
            $i++;
            $username = $baseUsername . $i;
        }
        
        return $username;
    }
}



