<?php

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * List activity logs dengan filter dan tabs
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Deny access for Kepala Sekolah — audit & logs are for Operator/System maintenance only
        if ($user->hasRole('Kepala Sekolah')) {
            return redirect()->route('dashboard.kepsek')->with('error', 'Akses fitur Audit & Log dibatasi untuk Kepala Sekolah.');
        }

        // Determine active tab
        $tab = $request->get('tab', 'activity'); // activity, last-login, status

        // Tab: Last Login
        if ($tab === 'last-login') {
            return $this->lastLoginTab($request);
        }

        // Tab: Status Akun
        if ($tab === 'status') {
            return $this->statusTab($request);
        }

        // Default Tab: Activity Logs
        $query = Activity::query();

        // Filter by log name (cacat, approval, etc)
        if ($request->filled('type')) {
            $query->where('log_name', $request->type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('created_at', '>=', $request->dari_tanggal);
        }

        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('created_at', '<=', $request->sampai_tanggal);
        }

        // Search in description or user
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('causer', function($q2) use ($request) {
                      $q2->where('nama', 'like', '%' . $request->search . '%')
                         ->orWhere('username', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $logs = $query->with('causer')
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);

        $activityTypes = Activity::distinct('log_name')->pluck('log_name');

        // Return partial view if requested (only for activity tab)
        if (($request->ajax() || $request->has('render_partial')) && $tab === 'activity') {
            return view('kepala_sekolah.activity._table_logs', [
                'logs' => $logs,
            ]);
        }

        return view('kepala_sekolah.activity.index', [
            'logs' => $logs,
            'activityTypes' => $activityTypes,
        ]);
    }

    /**
     * Show detail log
     * 
     * FIXED: Changed parameter from Activity $activity to int $id
     * to match route definition audit/activity/{id}
     */
    public function show(int $id)
    {
        $user = auth()->user();
        if ($user->hasRole('Kepala Sekolah')) {
            return redirect()->route('dashboard.kepsek')->with('error', 'Akses fitur Audit & Log dibatasi untuk Kepala Sekolah.');
        }

        // Manually find the activity log
        $log = Activity::findOrFail($id);

        return view('kepala_sekolah.activity.show', [
            'log' => $log,
        ]);
    }

    /**
     * Export logs to CSV
     */
    public function exportCsv(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('Kepala Sekolah')) {
            return redirect()->route('dashboard.kepsek')->with('error', 'Akses fitur Audit & Log dibatasi untuk Kepala Sekolah.');
        }

        $query = Activity::query();

        // Apply same filters as index
        if ($request->filled('type')) {
            $query->where('log_name', $request->type);
        }
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('created_at', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('created_at', '<=', $request->sampai_tanggal);
        }

        $logs = $query->with('causer')->orderBy('created_at', 'desc')->get();

        $filename = 'audit_logs_' . now()->format('Ymd_His') . '.csv';

        $callback = function() use ($logs) {
            echo "\xFF\xFE"; // UTF-16LE BOM
            
            $headerRow = "Tanggal\tJenis\tDilakukan Oleh\tDeskripsi\tProperti\n";
            echo mb_convert_encoding($headerRow, 'UTF-16LE', 'UTF-8');
            
            foreach ($logs as $log) {
                $properties = json_encode($log->properties ?? []);
                $dataRow = (
                    (formatForExport($log->created_at) ?? '') . "\t" .
                    ($log->log_name ?? '') . "\t" .
                    ($log->causer->nama ?? 'System') . "\t" .
                    ($log->description ?? '') . "\t" .
                    (substr($properties, 0, 50) . '...') . "\n"
                );
                echo mb_convert_encoding($dataRow, 'UTF-16LE', 'UTF-8');
            }
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-16LE',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Tab: Last Login Users
     */
    private function lastLoginTab(Request $request)
    {
        $query = \App\Models\User::with('role');

        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Filter by date range (Last Login)
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('last_login_at', '>=', $request->dari_tanggal);
        }

        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('last_login_at', '<=', $request->sampai_tanggal);
        }

        // Search by name/username/email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('last_login_at', 'desc')
                       ->paginate(20)
                       ->withQueryString();

        $roles = \App\Models\Role::all();

        if ($request->ajax() || $request->has('render_partial')) {
            return view('kepala_sekolah.activity._table_last_login', [
                'users' => $users,
            ]);
        }

        return view('kepala_sekolah.activity.index', [
            'tab' => 'last-login',
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Tab: Status Akun
     */
    private function statusTab(Request $request)
    {
        $query = \App\Models\User::with('role');

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Search by name/username/email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('is_active', 'desc')
                       ->orderBy('nama')
                       ->paginate(20)
                       ->withQueryString();

        $roles = \App\Models\Role::all();

        if ($request->ajax() || $request->has('render_partial')) {
            return view('kepala_sekolah.activity._table_status', [
                'users' => $users,
            ]);
        }

        return view('kepala_sekolah.activity.index', [
            'tab' => 'status',
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}


