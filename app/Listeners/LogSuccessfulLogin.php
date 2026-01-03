<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        try {
            $user = $event->user;
            activity('auth')
                ->performedBy($user)
                ->withProperties(['ip' => request()->ip() ?? 'cli'])
                ->log('User logged in');
        } catch (\Throwable $e) {
            // don't break application if logging fails
        }
    }
}
