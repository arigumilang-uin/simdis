<?php

namespace Database\Seeders;

use App\Models\Kurikulum;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk data master kurikulum
 */
class KurikulumSeeder extends Seeder
{
    public function run(): void
    {
        $kurikulums = [
            [
                'kode' => 'K13',
                'nama' => 'Kurikulum 2013',
                'deskripsi' => 'Kurikulum nasional yang berlaku sejak tahun 2013. Menekankan pada pendekatan saintifik dan penilaian autentik.',
                'tahun_berlaku' => 2013,
                'is_active' => true,
            ],
            [
                'kode' => 'MERDEKA',
                'nama' => 'Kurikulum Merdeka',
                'deskripsi' => 'Kurikulum yang memberikan keleluasaan kepada sekolah dan guru untuk mengembangkan potensi siswa. Fokus pada pengembangan karakter dan kompetensi.',
                'tahun_berlaku' => 2022,
                'is_active' => true,
            ],
        ];

        foreach ($kurikulums as $data) {
            Kurikulum::updateOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }

        $this->command->info('✓ Kurikulum seeded: ' . count($kurikulums) . ' records');
    }
}
