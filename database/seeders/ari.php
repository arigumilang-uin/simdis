<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * User Seeder
 * 
 * Seed data user SMK Negeri 1
 */
class ari extends Seeder
{
    public function run(): void
    {
        $roleOperator = Role::where('nama_role', 'Operator Sekolah')->first();
        $roleGuru = Role::where('nama_role', 'Guru')->first();

        $defaultPassword = Hash::make('password123');
        $now = Carbon::now();
        $createdCount = 0;
        User::updateOrCreate(
            ['username' => 'Ari Gumilang'],
            [
                'nama' => 'Operator Sekolah',
                'username' => 'Ari Gumilang',
                'email' => 'arigumilang271@gmail.com',
                'password' => $defaultPassword,
                'role_id' => $roleOperator?->id,
                'is_active' => true,
                'profile_completed_at' => $now,
            ]
        );
        $createdCount++;

        $guruData = [
            'pak mtk',
            'buk mtk',
            'pak PABP',
            'buk PABP',
            'pak PPKn',
            'buk PPKn',
            'pak BINDO',
            'buk BINDO',
            'pak MTK',
            'buk MTK',
            'pak SEJ',
            'buk SEJ',
            'pak BING',
            'buk BING',
            'pak SENBUD',
            'buk SENBUD',
            'pak PJOK',
            'buk PJOK',
            'pak INF',
            'buk INF',
            'pak IPAS',
            'buk IPAS',
            'pak BDER',
            'buk BDER',
            'pak BK',
            'buk BK',
            'pak DDPPLG',
            'buk DDPPLG',
            'pak SISKOM',
            'buk SISKOM',
            'pak KJD',
            'buk KJD',
            'pak PROGDAS',
            'buk PROGDAS',
            'pak DGD',
            'buk DGD',
            'pak SIMDIG',
            'buk SIMDIG',
            'pak K3LH',
            'buk K3LH',
            'pak DDIT',
            'buk DDIT',
            'pak PROGDAS',
            'buk PROGDAS',
            'pak DGD',
            'buk DGD',
            'pak SIMDIG',
            'buk SIMDIG',
            'pak K3LH',
            'buk K3LH',
            'pak DDIT',
            'buk DDIT',
            'pak PWPB',
            'buk PWPB',
            'pak PBO',
            'buk PBO',
            'pak BASDAT',
            'buk BASDAT',
            'pak PPL',
            'buk PPL',
            'pak PKK',
            'buk PKK',
            'pak KPL',
            'buk KPL',
            'pak WEB',
            'buk WEB',
            'pak MOB',
            'buk MOB',
            'pak BD',
            'buk BD',
            'pak IOT',
            'buk IOT',
            'pak CLOUD',
            'buk CLOUD',
            'pak CS',
            'buk CS',
            'pak PKK',
            'buk PKK',
            'pak KPL',
            'buk KPL',
            'pak WEB',
            'buk WEB',
            'pak MOB',
            'buk MOB',
            'pak BD',
            'buk BD',
            'pak IOT',
            'buk IOT',
            'pak CLOUD',
            'buk CLOUD',
            'pak CS',
            'buk CS',
            'pak P5',
            'buk P5',
            'pak BJP',
            'buk BJP',
            'pak BMD',
            'buk BMD',
            'pak AI',
            'buk AI',
            'pak DKV',
            'buk DKV',
            'pak ANI',
            'buk ANI',
            'pak DIGMAR',
            'buk DIGMAR',
            'pak TFA',
            'buk TFA',
            'WKJA',
            'WKJA11',
            'WKJA12',
            'WKJB',
            'WKJB11',
            'WKJB12',
            'WKJC',
            'WKJC11',
            'WKJC12',
            'WKJD',
            'WKJD11',
            'WKJD12',
            'WKJE',
            'WKJE11',
            'WKJE12',
        ];

        $guruCounter = 1;
        foreach ($guruData as $guru) {
            User::updateOrCreate(
                ['username' => $guru],
                [
                    'nama' => 'Guru',
                    'username' => $guru,
                    'email' => 'guru' . $guruCounter . '@smkn1.sch.id',
                    'password' => $defaultPassword,
                    'role_id' => $roleGuru?->id,
                    'is_active' => true,
                    'profile_completed_at' => $now,
                ]
            );
            $guruCounter++;
            $createdCount++;
        }


        $this->command->info('✓ Users seeded: ' . $createdCount . ' users');
        $this->command->info('  - Default password: password123');
    }
}
