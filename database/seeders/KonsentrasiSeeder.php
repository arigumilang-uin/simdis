<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Konsentrasi;
use App\Models\Jurusan;

/**
 * Konsentrasi Seeder
 * 
 * Seed data konsentrasi keahlian SMK Negeri 1
 * Konsentrasi adalah turunan dari Jurusan (Program Keahlian)
 * Biasanya siswa masuk konsentrasi di kelas XI
 */
class KonsentrasiSeeder extends Seeder
{
    public function run(): void
    {
        // Mapping: jurusan_kode => [konsentrasi list]
        $konsentrasiData = [
            'JA' => [
                ['nama'=> 'Konsentrasi Jurusan A', 'kode'=> 'KJA'],
                ['nama'=> 'Konsentrasi Jurusan A 11', 'kode'=> 'KJA11'],
                ['nama'=> 'Konsentrasi Jurusan A 12', 'kode'=> 'KJA12'],
            ],
            'JB' => [
                ['nama'=> 'Konsentrasi Jurusan B', 'kode'=> 'KJB'],
                ['nama'=> 'Konsentrasi Jurusan B 11', 'kode'=> 'KJB11'],
                ['nama'=> 'Konsentrasi Jurusan B 12', 'kode'=> 'KJB12'],
            ],
            'JC' => [
                ['nama'=> 'Konsentrasi Jurusan C', 'kode'=> 'KJC'],
                ['nama'=> 'Konsentrasi Jurusan C 11', 'kode'=> 'KJC11'],
                ['nama'=> 'Konsentrasi Jurusan C 12', 'kode'=> 'KJC12'],
            ],
            'JD' => [
                ['nama'=> 'Konsentrasi Jurusan D', 'kode'=> 'KJD'],
                ['nama'=> 'Konsentrasi Jurusan D 11', 'kode'=> 'KJD11'],
                ['nama'=> 'Konsentrasi Jurusan D 12', 'kode'=> 'KJD12'],
            ],
            'JE' => [
                ['nama'=> 'Konsentrasi Jurusan E', 'kode'=> 'KJE'],
                ['nama'=> 'Konsentrasi Jurusan E 11', 'kode'=> 'KJE11'],
                ['nama'=> 'Konsentrasi Jurusan E 12', 'kode'=> 'KJE12'],
            ],
        ];

        $count = 0;
        foreach ($konsentrasiData as $kodeJurusan => $konsentrasiList) {
            $jurusan = Jurusan::where('kode_jurusan', $kodeJurusan)->first();
            
            if (!$jurusan) {
                $this->command->warn("  ⚠ Jurusan {$kodeJurusan} tidak ditemukan, skip konsentrasi");
                continue;
            }

            foreach ($konsentrasiList as $kons) {
                Konsentrasi::updateOrCreate(
                    [
                        'jurusan_id' => $jurusan->id,
                        'kode_konsentrasi' => $kons['kode'],
                    ],
                    [
                        'jurusan_id' => $jurusan->id,
                        'nama_konsentrasi' => $kons['nama'],
                        'kode_konsentrasi' => $kons['kode'],
                        'is_active' => true,
                        'deskripsi' => null,
                    ]
                );
                $count++;
            }
        }

        $this->command->info('✓ Konsentrasi seeded: ' . $count . ' konsentrasi');
    }
}
