<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Kurikulum Controller
 * 
 * CRUD untuk master data kurikulum
 */
class KurikulumController extends Controller
{
    /**
     * Display list of kurikulum
     */
    public function index(): View
    {
        $kurikulums = Kurikulum::withCount(['mataPelajaran' => function($q) {
            $q->where('is_active', true);
        }])
        ->orderBy('tahun_berlaku', 'desc')
        ->get();

        return view('admin.kurikulum.index', [
            'kurikulums' => $kurikulums,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        return view('admin.kurikulum.create');
    }

    /**
     * Store new kurikulum
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:kurikulum,kode',
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'tahun_berlaku' => 'nullable|integer|min:2000|max:2100',
        ]);

        $validated['is_active'] = true;

        Kurikulum::create($validated);

        return redirect()
            ->route('admin.kurikulum.index')
            ->with('success', 'Kurikulum berhasil ditambahkan.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id): View
    {
        $kurikulum = Kurikulum::findOrFail($id);

        return view('admin.kurikulum.edit', [
            'kurikulum' => $kurikulum,
        ]);
    }

    /**
     * Update kurikulum
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $kurikulum = Kurikulum::findOrFail($id);

        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:kurikulum,kode,' . $id,
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'tahun_berlaku' => 'nullable|integer|min:2000|max:2100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $kurikulum->update($validated);

        return redirect()
            ->route('admin.kurikulum.index')
            ->with('success', 'Kurikulum berhasil diperbarui.');
    }

    /**
     * Soft delete kurikulum (archive)
     */
    public function destroy(int $id): RedirectResponse
    {
        $kurikulum = Kurikulum::findOrFail($id);
        
        $kurikulum->delete(); // Soft delete
        
        // Also soft delete related mata pelajaran
        $kurikulum->mataPelajaran()->delete();

        return redirect()
            ->route('admin.kurikulum.index')
            ->with('success', 'Kurikulum berhasil diarsipkan beserta mata pelajaran di dalamnya.');
    }

    /**
     * Display archived kurikulum
     */
    public function trash(): View
    {
        $kurikulums = Kurikulum::onlyTrashed()
            ->withCount(['mataPelajaran' => function($q) {
                $q->withTrashed();
            }])
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('admin.kurikulum.trash', [
            'kurikulums' => $kurikulums,
        ]);
    }

    /**
     * Restore soft deleted kurikulum
     */
    public function restore(int $id): RedirectResponse
    {
        $kurikulum = Kurikulum::onlyTrashed()->findOrFail($id);
        
        // Restore kurikulum
        $kurikulum->restore();
        
        // Also restore related mata pelajaran
        $kurikulum->mataPelajaran()->onlyTrashed()->restore();

        return redirect()
            ->route('admin.kurikulum.trash')
            ->with('success', 'Kurikulum berhasil dipulihkan beserta mata pelajaran di dalamnya.');
    }

    /**
     * Permanently delete kurikulum
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $kurikulum = Kurikulum::onlyTrashed()->findOrFail($id);
        
        // Check if has jadwal mengajar (even trashed)
        $hasJadwal = \App\Models\JadwalMengajar::withTrashed()
            ->whereIn('mata_pelajaran_id', $kurikulum->mataPelajaran()->withTrashed()->pluck('id'))
            ->exists();
            
        if ($hasJadwal) {
            return redirect()
                ->route('admin.kurikulum.trash')
                ->with('error', 'Tidak dapat menghapus permanen kurikulum yang memiliki data jadwal mengajar.');
        }
        
        // Force delete mata pelajaran first
        $kurikulum->mataPelajaran()->withTrashed()->forceDelete();
        
        // Force delete kurikulum
        $kurikulum->forceDelete();

        return redirect()
            ->route('admin.kurikulum.trash')
            ->with('success', 'Kurikulum berhasil dihapus secara permanen.');
    }

    /**
     * Bulk restore archived kurikulum.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|string']);
        $ids = explode(',', $request->input('ids'));

        $kurikulums = Kurikulum::onlyTrashed()->whereIn('id', $ids)->get();
        $restoredCount = 0;

        foreach ($kurikulums as $kurikulum) {
            $kurikulum->restore();
            // Also restore related mata pelajaran
            $kurikulum->mataPelajaran()->onlyTrashed()->restore();
            $restoredCount++;
        }

        return redirect()
            ->route('admin.kurikulum.trash')
            ->with('success', "{$restoredCount} kurikulum berhasil dipulihkan beserta mata pelajaran di dalamnya.");
    }

    /**
     * Bulk force delete archived kurikulum.
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|string']);
        $ids = explode(',', $request->input('ids'));

        $kurikulums = Kurikulum::onlyTrashed()->whereIn('id', $ids)->get();
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($kurikulums as $kurikulum) {
            // Check if has jadwal mengajar (even trashed)
            $hasJadwal = \App\Models\JadwalMengajar::withTrashed()
                ->whereIn('mata_pelajaran_id', $kurikulum->mataPelajaran()->withTrashed()->pluck('id'))
                ->exists();
                
            if ($hasJadwal) {
                $skippedCount++;
                continue;
            }
            
            // Force delete mata pelajaran first
            $kurikulum->mataPelajaran()->withTrashed()->forceDelete();
            
            // Force delete kurikulum
            $kurikulum->forceDelete();
            $deletedCount++;
        }

        $message = "{$deletedCount} kurikulum berhasil dihapus permanen.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} kurikulum dilewati karena memiliki data jadwal mengajar.";
        }

        return redirect()
            ->route('admin.kurikulum.trash')
            ->with($deletedCount > 0 ? 'success' : 'error', $message);
    }
}

