<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

/**
 * Role Seeder
 * 
 * Seed basic roles untuk sistem kedisiplinan SMK
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Kepala Sekolah',
            'Waka Kesiswaan',
            'Waka Sarana',
            'Kaprodi',
            'Wali Kelas',
            'Guru',
            'Wali Murid',
            'Operator Sekolah',
            'Developer',
        ];

        foreach ($roles as $roleName) {
            Role::updateOrCreate(
                ['nama_role' => $roleName],
                ['nama_role' => $roleName]
            );
        }

        $this->command->info('✓ Roles seeded: ' . count($roles) . ' roles');
    }
}