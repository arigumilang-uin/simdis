<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\User;

class DeveloperDisableCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'developer:disable {--fallback=Operator Sekolah} {--delete-users}';

    /**
     * The console command description.
     */
    protected $description = 'Safely disable Developer role by reassigning users to fallback role and optionally removing Developer role.';

    public function handle()
    {
        $fallbackName = $this->option('fallback');
        $deleteUsers = $this->option('delete-users');

        $role = Role::findByName('Developer');
        if (! $role) {
            $this->info('Developer role not found; nothing to disable.');
            return 0;
        }

        $users = User::where('role_id', $role->id)->get();
        if ($users->isEmpty()) {
            $this->info('No users assigned to Developer role.');
        } else {
            $this->info('Found ' . $users->count() . ' user(s) with Developer role.');
            if ($deleteUsers) {
                foreach ($users as $u) {
                    $this->line(" - Deleting user {$u->email}");
                    $u->delete();
                }
            } else {
                $fallback = Role::findByName($fallbackName);
                if (! $fallback) {
                    $this->error("Fallback role '{$fallbackName}' not found. Create it first or choose another fallback.");
                    return 1;
                }
                foreach ($users as $u) {
                    $this->line(" - Reassigning {$u->email} -> {$fallbackName}");
                    $u->role_id = $fallback->id;
                    $u->save();
                }
            }
        }

        // After reassign/delete, delete role
        $this->info('Removing Developer role from roles table.');
        $role->delete();

        $this->info('Developer role disabled and removed.');
        return 0;
    }
}
