<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Database Seeder
 * 
 * Main seeder untuk SMK Negeri 1 Sistem Kedisiplinan & Absensi
 * 
 * URUTAN PENTING:
 * 1. RoleSeeder - Roles harus ada dulu
 * 2. JurusanSeeder - Jurusan untuk referensi konsentrasi & kaprodi
 * 3. KonsentrasiSeeder - Konsentrasi untuk referensi kelas XI/XII
 * 4. KelasSeeder - Kelas untuk referensi wali kelas
 * 5. UserSeeder - Users dengan assignment ke jurusan/kelas
 * 6. KurikulumSeeder - Master kurikulum
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🌱 SEEDING DATABASE SMK NEGERI 1...');
        $this->command->info('================================');
        
        // Core data - urutan penting!
        $this->call([
            RoleSeeder::class,
            JurusanSeeder::class,
            KonsentrasiSeeder::class,  // NEW: Sebelum KelasSeeder
            KelasSeeder::class,
            ari::class,
            KurikulumSeeder::class,
            MapelSeeder::class,  // Mata pelajaran + guru pengampu
            JadwalMengajarSeeder::class
        ]);
        
        $this->command->info('');
        $this->command->info('✅ DATABASE SEEDING COMPLETE!');
        $this->command->info('================================');
    }
}