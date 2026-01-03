<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CleanupDeveloperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Options:
     *  --unassign    : Unassign Developer role from users (set role_id = NULL)
     *  --delete-users: Delete users that have Developer role (use with caution)
     *  --remove-role : Remove the Developer role entry from roles table
     */
    protected $signature = 'developer:cleanup {--unassign} {--delete-users} {--remove-role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up Developer role and users before production (unassign/delete role users, optionally remove role record).';

    public function handle()
    {
        $role = Role::findByName('Developer');
        if (! $role) {
            $this->info('Role Developer tidak ditemukan. Tidak ada yang perlu dibersihkan.');
            return 0;
        }

        $users = User::where('role_id', $role->id)->get();
        $count = $users->count();

        if ($count === 0) {
            $this->info('Tidak ada user dengan role Developer.');
        } else {
            $this->info("Menemukan {$count} user dengan role Developer.");

            if ($this->option('delete-users')) {
                $this->warn('Menghapus user Developer (permanent). Pastikan backup jika diperlukan.');
                foreach ($users as $u) {
                    $this->line(" - Menghapus: {$u->email}");
                    $u->delete();
                }
            } elseif ($this->option('unassign')) {
                    // Instead of setting role_id = NULL (which may break code assuming a role exists),
                    // reassign developer users to a safe fallback role (configurable via DEV_FALLBACK_ROLE env).
                    $fallbackName = env('DEV_FALLBACK_ROLE', 'Operator Sekolah');
                    $fallbackRole = Role::findByName($fallbackName);
                    if (! $fallbackRole) {
                        $this->error("Fallback role '{$fallbackName}' tidak ditemukan. Buat role ini terlebih dahulu atau set DEV_FALLBACK_ROLE di .env.");
                        return 1;
                    }

                    $this->info("Meng-assign ulang user Developer ke role fallback: {$fallbackName}");
                    foreach ($users as $u) {
                        $this->line(" - Reassign: {$u->email} -> {$fallbackName}");
                        $u->role_id = $fallbackRole->id;
                        $u->save();
                    }
            } else {
                $this->warn('Tidak ada aksi yang dipilih. Gunakan --unassign atau --delete-users.');
                return 1;
            }
        }

        if ($this->option('remove-role')) {
            // Pastikan tidak ada user tersisa
            $remaining = User::where('role_id', $role->id)->count();
            if ($remaining > 0) {
                $this->error("Masih ada {$remaining} user dengan role Developer. Hapus/unassign mereka terlebih dahulu.");
                return 1;
            }

            $this->info('Menghapus role Developer dari tabel roles.');
            $role->delete();
        }

        $this->info('Selesai.');
        return 0;
    }
}
