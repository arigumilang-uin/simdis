<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Konsentrasi;

/**
 * Kelas Seeder
 * 
 * Seed data kelas SMK Negeri 1
 * 
 * Catatan:
 * - Kelas X: biasanya belum masuk konsentrasi (konsentrasi_id = null)
 * - Kelas XI & XII: sudah masuk konsentrasi
 */
class KelasSeeder extends Seeder
{
    public function run(): void
    {
        // Kelas dengan format: [nama_kelas, tingkat, kode_jurusan, kode_konsentrasi (nullable)]
        $kelasData = [
            // === JA ===
            ['nama' => 'X KJA', 'tingkat' => 'X', 'jurusan' => 'JA', 'konsentrasi' => 'KJA'],
            ['nama' => 'XI KJA11', 'tingkat' => 'XI', 'jurusan' => 'JA', 'konsentrasi' => 'KJA11'],
            ['nama' => 'XII KJA12', 'tingkat' => 'XII', 'jurusan' => 'JA', 'konsentrasi' => 'KJA12'],

            // === JB ===
            ['nama' => 'X KJB', 'tingkat' => 'X', 'jurusan' => 'JB', 'konsentrasi' => 'KJB'],
            ['nama' => 'XI KJB11', 'tingkat' => 'XI', 'jurusan' => 'JB', 'konsentrasi' => 'KJB11'],
            ['nama' => 'XII KJB12', 'tingkat' => 'XII', 'jurusan' => 'JB', 'konsentrasi' => 'KJB12'],

            // === JC ===
            ['nama' => 'X KJC', 'tingkat' => 'X', 'jurusan' => 'JC', 'konsentrasi' => 'KJC'],
            ['nama' => 'XI KJC11', 'tingkat' => 'XI', 'jurusan' => 'JC', 'konsentrasi' => 'KJC11'],
            ['nama' => 'XII KJC12', 'tingkat' => 'XII', 'jurusan' => 'JC', 'konsentrasi' => 'KJC12'],

            // === JD ===
            ['nama' => 'X KJD', 'tingkat' => 'X', 'jurusan' => 'JD', 'konsentrasi' => 'KJD'],
            ['nama' => 'XI KJD11', 'tingkat' => 'XI', 'jurusan' => 'JD', 'konsentrasi' => 'KJD11'],
            ['nama' => 'XII KJD12', 'tingkat' => 'XII', 'jurusan' => 'JD', 'konsentrasi' => 'KJD12'],

            // === JE ===
            ['nama' => 'X KJE', 'tingkat' => 'X', 'jurusan' => 'JE', 'konsentrasi' => 'KJE'],
            ['nama' => 'XI KJE11', 'tingkat' => 'XI', 'jurusan' => 'JE', 'konsentrasi' => 'KJE11'],
            ['nama' => 'XII KJE12', 'tingkat' => 'XII', 'jurusan' => 'JE', 'konsentrasi' => 'KJE12'],
        ];

        $count = 0;
        foreach ($kelasData as $kelas) {
            $jurusan = Jurusan::where('kode_jurusan', $kelas['jurusan'])->first();
            
            if (!$jurusan) {
                $this->command->warn("  ⚠ Jurusan {$kelas['jurusan']} tidak ditemukan, skip kelas {$kelas['nama']}");
                continue;
            }

            // Cari konsentrasi jika ada
            $konsentrasiId = null;
            if ($kelas['konsentrasi']) {
                $konsentrasi = Konsentrasi::where('kode_konsentrasi', $kelas['konsentrasi'])
                    ->where('jurusan_id', $jurusan->id)
                    ->first();
                    
                if ($konsentrasi) {
                    $konsentrasiId = $konsentrasi->id;
                } else {
                    $this->command->warn("  ⚠ Konsentrasi {$kelas['konsentrasi']} tidak ditemukan untuk jurusan {$kelas['jurusan']}");
                }
            }

            Kelas::updateOrCreate(
                ['nama_kelas' => $kelas['nama']],
                [
                    'nama_kelas' => $kelas['nama'],
                    'tingkat' => $kelas['tingkat'],
                    'jurusan_id' => $jurusan->id,
                    'konsentrasi_id' => $konsentrasiId,
                    // wali_kelas_user_id will be set later by UserSeeder
                ]
            );
            $count++;
        }

        $this->command->info('✓ Kelas seeded: ' . $count . ' kelas');
    }
}
