<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Role;
use App\Services\User\RoleService;

/**
 * Controller untuk tindakan khusus Developer seperti impersonation.
 *
 * Catatan: fungsionalitas impersonation hanya diaktifkan di lingkungan non-production.
 */
class DeveloperController extends Controller
{
    public function impersonate(Request $request, $roleName)
    {
        // Only allow when not production
        if (app()->environment('production')) {
            abort(403, 'Impersonation not allowed in production.');
        }

        $role = Role::findByName($roleName);
        if (! $role) {
            return redirect()->back()->withErrors(['role' => 'Role not found: ' . $roleName]);
        }

        $roleNameActual = $role->nama_role;
        Session::put('developer_role_override', $roleNameActual);

        // After switching impersonation, redirect to a safe dashboard/page that the
        // impersonated role is expected to have access to. This avoids returning
        // to a page the new role cannot access (which would cause an immediate 403).
        $route = null;
        switch ($roleNameActual) {
            case 'Operator Sekolah':
            case 'Waka Kesiswaan':
                $route = 'dashboard.admin';
                break;
            case 'Kepala Sekolah':
                $route = 'dashboard.kepsek';
                break;
            case 'Kaprodi':
                $route = 'dashboard.kaprodi';
                break;
            case 'Wali Kelas':
                $route = 'dashboard.walikelas';
                break;
            case 'Waka Sarana':
                $route = 'dashboard.waka-sarana';
                break;
            case 'Wali Murid':
                $route = 'dashboard.wali_murid';
                break;
            case 'Guru':
                $route = 'pelanggaran.create';
                break;
            default:
                $route = 'dashboard.admin';
        }

        if (\Illuminate\Support\Facades\Route::has($route ?? '')) {
            return redirect()->route($route)->with('success', "Impersonating role: {$roleNameActual}");
        }

        return redirect('/')->with('success', "Impersonating role: {$roleNameActual}");
    }

    public function clear()
    {
        if (app()->environment('production')) {
            abort(403, 'Impersonation not allowed in production.');
        }

        Session::forget('developer_role_override');
        // Redirect developer to admin dashboard (developer account normally maps here)
        if (\Illuminate\Support\Facades\Route::has('dashboard.admin')) {
            return redirect()->route('dashboard.admin')->with('success', 'Developer impersonation cleared.');
        }
        return redirect('/')->with('success', 'Developer impersonation cleared.');
    }

    /**
     * Debug status: return real role, effective role, and current override (JSON).
     * Authenticated only; safe for non-production debugging.
     */
    public function status(Request $request)
    {
        if (app()->environment('production')) {
            abort(403, 'Not available in production.');
        }

        $user = $request->user();
        return response()->json([
            'user_id' => $user->id,
            'email' => $user->email,
            'real_role' => $user->role?->nama_role,
            'effective_role' => $user->effectiveRoleName(),
            'override' => RoleService::getOverride(),
        ]);
    }
}



