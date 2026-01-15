<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jurusan;

/**
 * Jurusan Seeder
 * 
 * Seed data jurusan SMK Negeri 1
 */
class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        $jurusan = [
            [
                'nama_jurusan' => 'Jurusan A',
                'kode_jurusan' => 'JA',                
            ],
            [
                'nama_jurusan' => 'Jurusan B',
                'kode_jurusan' => 'JB',
            ],
            [
                'nama_jurusan' => 'Jurusan C',
                'kode_jurusan' => 'JC',
            ],
            [
                'nama_jurusan' => 'Jurusan D',
                'kode_jurusan' => 'JD',
            ],
            [
                'nama_jurusan' => 'Jurusan E',
                'kode_jurusan' => 'JE',
            ],
        ];

        foreach ($jurusan as $j) {
            Jurusan::updateOrCreate(
                ['kode_jurusan' => $j['kode_jurusan']],
                $j
            );
        }

        $this->command->info('✓ Jurusan seeded: ' . count($jurusan) . ' jurusan');
    }
}
