<?php

namespace App\Services\TindakLanjut;

use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;

/**
 * Service untuk mengelola Surat Panggilan Orang Tua
 * 
 * Tanggung jawab:
 * - Build pembina data dari database berdasarkan roles
 * - Generate nomor surat
 * - Set default jadwal pertemuan
 * 
 * TIDAK bertanggung jawab untuk:
 * - Rules evaluation (itu tanggung jawab PelanggaranRulesEngine)
 * - PDF generation (itu tanggung jawab TindakLanjutController)
 */
class SuratPanggilanService
{
    /**
     * Build pembina data dari database berdasarkan roles yang dipilih.
     * 
     * @param array $pembinaRoles Array of role names (e.g., ['Wali Kelas', 'Kaprodi'])
     * @param Siswa $siswa Siswa yang terkait (untuk ambil wali kelas dan kaprodi)
     * @return array Array of pembina data: [['jabatan' => '...', 'nama' => '...', 'nip' => '...']]
     */
    public function buildPembinaData(array $pembinaRoles, Siswa $siswa): array
    {
        $pembinaData = [];

        foreach ($pembinaRoles as $role) {
            $pembina = $this->getPembinaByRole($role, $siswa);
            
            if ($pembina) {
                // Use User model helper methods for identifier priority: NIP > NI PPPK > NUPTK
                $pembinaData[] = [
                    'jabatan' => $role,
                    'username' => $pembina->username,  // Username untuk ditampilkan
                    'nama' => $pembina->nama,          // Nama (untuk backward compat)
                    'nip' => $pembina->getIdentifierNumber(),  // Nomor identitas
                    'nip_label' => $pembina->getIdentifierLabel() . '.', // Label: NIP. atau NI PPPK. atau NUPTK.
                ];
            }
        }

        return $pembinaData;
    }

    /**
     * Get pembina user berdasarkan role dan siswa.
     * 
     * @param string $role Role name
     * @param Siswa $siswa Siswa yang terkait
     * @return User|null
     */
    private function getPembinaByRole(string $role, Siswa $siswa): ?User
    {
        // Load relasi yang diperlukan jika belum di-load
        $siswa->loadMissing(['kelas.waliKelas', 'kelas.jurusan.kaprodi']);

        switch ($role) {
            case 'Wali Kelas':
                // Null safety: check kelas exists first
                if (!$siswa->kelas) {
                    \Log::warning("Siswa {$siswa->id} tidak memiliki kelas, tidak bisa ambil Wali Kelas");
                    return null;
                }
                return $siswa->kelas->waliKelas ?? null;

            case 'Kaprodi':
                // Null safety: check kelas and jurusan exist
                if (!$siswa->kelas || !$siswa->kelas->jurusan) {
                    \Log::warning("Siswa {$siswa->id} tidak memiliki kelas/jurusan, tidak bisa ambil Kaprodi");
                    return null;
                }
                return $siswa->kelas->jurusan->kaprodi ?? null;

            case 'Waka Kesiswaan':
                return User::whereHas('role', function ($q) {
                    $q->where('nama_role', 'Waka Kesiswaan');
                })->first();

            case 'Waka Sarana':
                return User::whereHas('role', function ($q) {
                    $q->where('nama_role', 'Waka Sarana');
                })->first();

            case 'Kepala Sekolah':
                return User::whereHas('role', function ($q) {
                    $q->where('nama_role', 'Kepala Sekolah');
                })->first();

            default:
                return null;
        }
    }

    /**
     * Generate nomor surat dengan format: DRAFT/[random]/421.5-SMKN 1 LD/[tahun]
     * 
     * @return string
     */
    public function generateNomorSurat(): string
    {
        $randomNumber = rand(100, 999);
        $year = Carbon::now()->year;
        
        return "DRAFT/{$randomNumber}/421.5-SMKN 1 LD/{$year}";
    }

    /**
     * Set default jadwal pertemuan (3 hari dari sekarang, jam 09:00)
     * 
     * @return array ['tanggal_pertemuan' => Carbon, 'waktu_pertemuan' => string]
     */
    public function setDefaultMeetingSchedule(): array
    {
        return [
            'tanggal_pertemuan' => Carbon::now()->addDays(3),
            'waktu_pertemuan' => '09:00',
        ];
    }
}

