<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MataPelajaran;
use App\Models\Kurikulum;
use App\Models\User;

/**
 * Mata Pelajaran Seeder
 * 
 * Membuat mata pelajaran untuk kurikulum MERDEKA
 * dan menghubungkan dengan guru pengampu berdasarkan username.
 */
class MapelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get kurikulum MERDEKA
        $kurikulum = Kurikulum::where('kode', 'MERDEKA')->first();
        
        if (!$kurikulum) {
            $this->command->error('Kurikulum MERDEKA tidak ditemukan! Jalankan KurikulumSeeder terlebih dahulu.');
            return;
        }

        // Mapping: kode_mapel => [nama_mapel, kelompok, guru_usernames[]]
        $mapelData = [
            // === KELOMPOK A - UMUM ===
            'PABP' => [
                'nama' => 'Pendidikan Agama dan Budi Pekerti',
                'kelompok' => 'A',
                'guru' => ['pak PABP', 'buk PABP'],
            ],
            'PPKn' => [
                'nama' => 'Pendidikan Pancasila dan Kewarganegaraan',
                'kelompok' => 'A',
                'guru' => ['pak PPKn', 'buk PPKn'],
            ],
            'BINDO' => [
                'nama' => 'Bahasa Indonesia',
                'kelompok' => 'A',
                'guru' => ['pak BINDO', 'buk BINDO'],
            ],
            'MTK' => [
                'nama' => 'Matematika',
                'kelompok' => 'A',
                'guru' => ['pak mtk', 'buk mtk', 'pak MTK', 'buk MTK'],
            ],
            'SEJ' => [
                'nama' => 'Sejarah Indonesia',
                'kelompok' => 'A',
                'guru' => ['pak SEJ', 'buk SEJ'],
            ],
            'BING' => [
                'nama' => 'Bahasa Inggris',
                'kelompok' => 'A',
                'guru' => ['pak BING', 'buk BING'],
            ],
            'SENBUD' => [
                'nama' => 'Seni Budaya',
                'kelompok' => 'A',
                'guru' => ['pak SENBUD', 'buk SENBUD'],
            ],
            'PJOK' => [
                'nama' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',
                'kelompok' => 'A',
                'guru' => ['pak PJOK', 'buk PJOK'],
            ],

            // === KELOMPOK A - UMUM (Lanjutan) ===
            'INF' => [
                'nama' => 'Informatika',
                'kelompok' => 'A',
                'guru' => ['pak INF', 'buk INF'],
            ],
            'IPAS' => [
                'nama' => 'Ilmu Pengetahuan Alam dan Sosial',
                'kelompok' => 'A',
                'guru' => ['pak IPAS', 'buk IPAS'],
            ],

            // === KELOMPOK C - PILIHAN/MUATAN LOKAL ===
            'BDER' => [
                'nama' => 'Bahasa Daerah',
                'kelompok' => 'C',
                'guru' => ['pak BDER', 'buk BDER'],
            ],
            'BK' => [
                'nama' => 'Bimbingan Konseling',
                'kelompok' => 'C',
                'guru' => ['pak BK', 'buk BK'],
            ],
            'BJP' => [
                'nama' => 'Bahasa Jepang',
                'kelompok' => 'C',
                'guru' => ['pak BJP', 'buk BJP'],
            ],
            'BMD' => [
                'nama' => 'Bahasa Mandarin',
                'kelompok' => 'C',
                'guru' => ['pak BMD', 'buk BMD'],
            ],
            'P5' => [
                'nama' => 'Projek Penguatan Profil Pelajar Pancasila',
                'kelompok' => 'C',
                'guru' => ['pak P5', 'buk P5'],
            ],

            // === KELOMPOK B - KEJURUAN (Dasar) ===
            'DDPPLG' => [
                'nama' => 'Dasar-dasar Pengembangan Perangkat Lunak dan GIM',
                'kelompok' => 'B',
                'guru' => ['pak DDPPLG', 'buk DDPPLG'],
            ],
            'SISKOM' => [
                'nama' => 'Sistem Komputer',
                'kelompok' => 'B',
                'guru' => ['pak SISKOM', 'buk SISKOM'],
            ],
            'KJD' => [
                'nama' => 'Komputer dan Jaringan Dasar',
                'kelompok' => 'B',
                'guru' => ['pak KJD', 'buk KJD'],
            ],
            'PROGDAS' => [
                'nama' => 'Pemrograman Dasar',
                'kelompok' => 'B',
                'guru' => ['pak PROGDAS', 'buk PROGDAS'],
            ],
            'DGD' => [
                'nama' => 'Desain Grafis Dasar',
                'kelompok' => 'B',
                'guru' => ['pak DGD', 'buk DGD'],
            ],
            'SIMDIG' => [
                'nama' => 'Simulasi dan Komunikasi Digital',
                'kelompok' => 'B',
                'guru' => ['pak SIMDIG', 'buk SIMDIG'],
            ],
            'K3LH' => [
                'nama' => 'Keselamatan, Kesehatan Kerja dan Lingkungan Hidup',
                'kelompok' => 'B',
                'guru' => ['pak K3LH', 'buk K3LH'],
            ],
            'DDIT' => [
                'nama' => 'Dasar-dasar Teknik Jaringan Komputer dan Informatika',
                'kelompok' => 'B',
                'guru' => ['pak DDIT', 'buk DDIT'],
            ],

            // === KELOMPOK B - KEJURUAN (Produktif) ===
            'PWPB' => [
                'nama' => 'Pemrograman Web dan Perangkat Bergerak',
                'kelompok' => 'B',
                'guru' => ['pak PWPB', 'buk PWPB'],
            ],
            'PBO' => [
                'nama' => 'Pemrograman Berorientasi Objek',
                'kelompok' => 'B',
                'guru' => ['pak PBO', 'buk PBO'],
            ],
            'BASDAT' => [
                'nama' => 'Basis Data',
                'kelompok' => 'B',
                'guru' => ['pak BASDAT', 'buk BASDAT'],
            ],
            'PPL' => [
                'nama' => 'Pemodelan Perangkat Lunak',
                'kelompok' => 'B',
                'guru' => ['pak PPL', 'buk PPL'],
            ],
            'PKK' => [
                'nama' => 'Produk Kreatif dan Kewirausahaan',
                'kelompok' => 'B',
                'guru' => ['pak PKK', 'buk PKK'],
            ],
            'KPL' => [
                'nama' => 'Kualitas Perangkat Lunak (Software Testing)',
                'kelompok' => 'B',
                'guru' => ['pak KPL', 'buk KPL'],
            ],
            'WEB' => [
                'nama' => 'Pemrograman Web',
                'kelompok' => 'B',
                'guru' => ['pak WEB', 'buk WEB'],
            ],
            'MOB' => [
                'nama' => 'Pemrograman Perangkat Bergerak',
                'kelompok' => 'B',
                'guru' => ['pak MOB', 'buk MOB'],
            ],
            'BD' => [
                'nama' => 'Big Data',
                'kelompok' => 'B',
                'guru' => ['pak BD', 'buk BD'],
            ],
            'IOT' => [
                'nama' => 'Internet of Things',
                'kelompok' => 'B',
                'guru' => ['pak IOT', 'buk IOT'],
            ],
            'CLOUD' => [
                'nama' => 'Komputasi Awan (Cloud Computing)',
                'kelompok' => 'B',
                'guru' => ['pak CLOUD', 'buk CLOUD'],
            ],
            'CS' => [
                'nama' => 'Keamanan Siber (Cyber Security)',
                'kelompok' => 'B',
                'guru' => ['pak CS', 'buk CS'],
            ],
            'AI' => [
                'nama' => 'Kecerdasan Buatan (Artificial Intelligence)',
                'kelompok' => 'B',
                'guru' => ['pak AI', 'buk AI'],
            ],
            'DKV' => [
                'nama' => 'Desain Komunikasi Visual',
                'kelompok' => 'B',
                'guru' => ['pak DKV', 'buk DKV'],
            ],
            'ANI' => [
                'nama' => 'Animasi 2D dan 3D',
                'kelompok' => 'B',
                'guru' => ['pak ANI', 'buk ANI'],
            ],
            'DIGMAR' => [
                'nama' => 'Pemasaran Digital (Digital Marketing)',
                'kelompok' => 'B',
                'guru' => ['pak DIGMAR', 'buk DIGMAR'],
            ],
            'TFA' => [
                'nama' => 'Teaching Factory',
                'kelompok' => 'B',
                'guru' => ['pak TFA', 'buk TFA'],
            ],
        ];

        $createdCount = 0;
        $guruAttachedCount = 0;

        foreach ($mapelData as $kode => $data) {
            // Create or update mata pelajaran
            $mapel = MataPelajaran::updateOrCreate(
                [
                    'kurikulum_id' => $kurikulum->id,
                    'kode_mapel' => $kode,
                ],
                [
                    'nama_mapel' => $data['nama'],
                    'kelompok' => $data['kelompok'],
                    'is_active' => true,
                ]
            );
            $createdCount++;

            // Attach guru pengampu
            $guruIds = [];
            $isPrimarySet = false;
            
            foreach ($data['guru'] as $guruUsername) {
                $guru = User::where('username', $guruUsername)->first();
                if ($guru) {
                    // First guru found becomes primary
                    $guruIds[$guru->id] = ['is_primary' => !$isPrimarySet];
                    if (!$isPrimarySet) $isPrimarySet = true;
                    $guruAttachedCount++;
                }
            }

            if (!empty($guruIds)) {
                $mapel->guruPengampu()->sync($guruIds);
            }
        }

        $this->command->info('✓ Mata Pelajaran seeded:');
        $this->command->info("  - {$createdCount} mata pelajaran created/updated");
        $this->command->info("  - {$guruAttachedCount} guru attached");
        $this->command->info("  - Kurikulum: {$kurikulum->nama}");
    }
}
