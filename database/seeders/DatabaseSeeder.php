<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Database Seeder
 * 
 * Main seeder untuk SMK Negeri 1 Sistem Kedisiplinan
 * 
 * URUTAN PENTING:
 * 1. RoleSeeder - Roles harus ada dulu
 * 2. JurusanSeeder - Jurusan untuk referensi kelas & kaprodi
 * 3. KelasSeeder - Kelas untuk referensi wali kelas
 * 4. UserSeeder - Users dengan assignment ke jurusan/kelas
 * 
 * NANTI DITAMBAHKAN:
 * 5. KategoriPelanggaranSeeder
 * 6. JenisPelanggaranSeeder
 * 7. FrequencyRulesSeeder
 * 8. PembinaanInternalSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🌱 SEEDING DATABASE SMK NEGERI 1...');
        $this->command->info('================================');
        
        // Core data
        $this->call([
            RoleSeeder::class,
            JurusanSeeder::class,
            KelasSeeder::class,
            UserSeeder::class,
        ]);
        
        // Pelanggaran & Rules (akan ditambahkan nanti)
        // $this->call([
        //     KategoriPelanggaranSeeder::class,
        //     JenisPelanggaranSeeder::class,
        //     FrequencyRulesSeeder::class,
        //     PembinaanInternalSeeder::class,
        // ]);
        
        $this->command->info('');
        $this->command->info('✅ DATABASE SEEDING COMPLETE!');
        $this->command->info('================================');
    }
}