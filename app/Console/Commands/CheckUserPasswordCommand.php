<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CheckUserPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:check-password {username} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check a user password against stored hash for debugging';

    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');

        $user = User::where('username', $username)->orWhere('email', $username)->first();
        if (! $user) {
            $this->error("User not found: {$username}");
            return 2;
        }

        $hash = $user->getAuthPassword();
        $this->info("Found user: {$user->username} (id={$user->id})");
        $this->line("Stored hash: {$hash}");

        if (Hash::check($password, $hash)) {
            $this->info('Password MATCH (Hash::check returned true).');
            return 0;
        }

        $this->error('Password DOES NOT MATCH.');
        return 1;
    }
}
