<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;

/**
 * User Seeder
 * 
 * Seed data user SMK Negeri 1
 * 
 * FORMAT:
 * - nama = Jabatan/Role (contoh: "Kepala Sekolah", "Kaprodi ATP")
 * - username = Nama Orangnya (contoh: "Salmiah, S.Pd.MM")
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // GET ROLES
        // =====================================================================
        $roleOperator = Role::where('nama_role', 'Operator Sekolah')->first();


        $defaultPassword = Hash::make('password123');
        $createdCount = 0;


        // =====================================================================
        // 2. OPERATOR
        // =====================================================================
        User::updateOrCreate(
            ['username' => 'Muhd. Bima Satryo. F, S.Kom'],
            [
                'nama' => 'Operator',
                'username' => 'Muhd. Bima Satryo. F, S.Kom',
                'email' => 'operator@smkn1.sch.id',
                'password' => $defaultPassword,
                'role_id' => $roleOperator?->id,
                'is_active' => true,
            ]
        );
        $createdCount++;

     

        $this->command->info('✓ Users seeded: ' . $createdCount . ' users');
        $this->command->info('  - Default password: password123');
    }
}
