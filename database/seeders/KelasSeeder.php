<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\Jurusan;

/**
 * Kelas Seeder
 * 
 * Seed data kelas SMK Negeri 1
 */
class KelasSeeder extends Seeder
{
    public function run(): void
    {
        // Mapping kelas: nama_kelas => kode_jurusan
        $kelasData = [
            ['nama_kelas' => 'X AKL 1', 'tingkat' => 'X', 'kode' => 'AKL'],
            ['nama_kelas' => 'XI AKL 1', 'tingkat' => 'XI', 'kode' => 'AKL'],
            ['nama_kelas' => 'XII AKL 1', 'tingkat' => 'XII', 'kode' => 'AKL'],
            ['nama_kelas' => 'X APHP 1', 'tingkat' => 'X', 'kode' => 'APHP'],
            ['nama_kelas' => 'XI APHP 1', 'tingkat' => 'XI', 'kode' => 'APHP'],
            ['nama_kelas' => 'X ATP 1', 'tingkat' => 'X', 'kode' => 'ATP'],
            ['nama_kelas' => 'XI ATP 1', 'tingkat' => 'XI', 'kode' => 'ATP'],
            ['nama_kelas' => 'XI ATP 2', 'tingkat' => 'XI', 'kode' => 'ATP'],
            ['nama_kelas' => 'X ATU 1', 'tingkat' => 'X', 'kode' => 'ATU'],
            ['nama_kelas' => 'X TEB 1', 'tingkat' => 'X', 'kode' => 'TEB'],
        ];

        foreach ($kelasData as $kelas) {
            $jurusan = Jurusan::where('kode_jurusan', $kelas['kode'])->first();
            
            if ($jurusan) {
                Kelas::updateOrCreate(
                    ['nama_kelas' => $kelas['nama_kelas']],
                    [
                        'nama_kelas' => $kelas['nama_kelas'],
                        'tingkat' => $kelas['tingkat'],
                        'jurusan_id' => $jurusan->id,
                    ]
                );
            }
        }

        $this->command->info('✓ Kelas seeded: ' . count($kelasData) . ' kelas');
    }
}
