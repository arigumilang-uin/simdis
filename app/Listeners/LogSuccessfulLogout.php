<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        try {
            $user = $event->user;
            activity('auth')
                ->performedBy($user)
                ->withProperties(['ip' => request()->ip() ?? 'cli'])
                ->log('User logged out');
        } catch (\Throwable $e) {
            // silent
        }
    }
}
