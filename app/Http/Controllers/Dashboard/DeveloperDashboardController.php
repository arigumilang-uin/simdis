<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\User\RoleService;

class DeveloperDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Developer dashboard only for Developer role
        if (! $user->hasRole('Developer')) {
            abort(403, 'Developer dashboard only accessible by Developer role.');
        }
        
        // Show warning if in production (but still allow access for testing)
        $isProduction = app()->environment('production');

        return view('dashboards.developer', [
            'isProduction' => $isProduction,
            'user' => $user,
        ]);
    }
}

