<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\User;

class DeveloperEnableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage: php artisan developer:enable {--email=} {--password=} 
     */
    protected $signature = 'developer:enable {--email=developer@local} {--password=password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create/assign the Developer role to a developer account (default developer@local)';

    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');

        $role = Role::firstOrCreate(['nama_role' => 'Developer']);

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->info("User {$email} not found — creating new user with username 'developer'.");
            $user = User::create([
                'role_id' => $role->id,
                'nama' => 'Developer',
                'username' => explode('@', $email)[0],
                'email' => $email,
                'password' => bcrypt($password),
            ]);
            $this->info("Created user {$email} and assigned Developer role.");
            return 0;
        }

        // If user has another role, warn and override (use safe check)
        $currentRoleName = $user->role?->nama_role ?? '(tidak ada)';
        if ($currentRoleName !== 'Developer') {
            $this->warn("User {$email} currently has role '{$currentRoleName}' — assigning Developer role now.");
        }

        $user->role_id = $role->id;
        $user->save();

        $this->info("User {$email} is now assigned role Developer.");
        return 0;
    }
}
