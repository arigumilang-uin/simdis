<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodeSemester;
use App\Services\Absensi\PertemuanService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Periode Semester Controller (Admin)
 * 
 * CRUD untuk konfigurasi periode semester (tanggal mulai/selesai).
 */
class PeriodeSemesterController extends Controller
{
    public function __construct(
        private PertemuanService $pertemuanService
    ) {}

    /**
     * Display list of periods
     */
    public function index(): View
    {
        $periodes = PeriodeSemester::orderByDesc('tanggal_mulai')->get();
        
        return view('admin.periode-semester.index', [
            'periodes' => $periodes,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        return view('admin.periode-semester.create');
    }

    /**
     * Store new period
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'semester' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:10|regex:/^\d{4}\/\d{4}$/',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ], [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2025/2026)',
        ]);

        // Auto-generate nama_periode from semester + tahun_ajaran
        $validated['nama_periode'] = $validated['semester'] . ' ' . $validated['tahun_ajaran'];

        // Check duplicate
        $exists = PeriodeSemester::where('semester', $validated['semester'])
            ->where('tahun_ajaran', $validated['tahun_ajaran'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Periode untuk semester dan tahun ajaran ini sudah ada.');
        }

        PeriodeSemester::create($validated);

        return redirect()
            ->route('admin.periode-semester.index')
            ->with('success', 'Periode semester berhasil ditambahkan.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id): View
    {
        $periode = PeriodeSemester::findOrFail($id);
        
        return view('admin.periode-semester.edit', [
            'periode' => $periode,
        ]);
    }

    /**
     * Update period
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $periode = PeriodeSemester::findOrFail($id);

        $validated = $request->validate([
            'semester' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:10|regex:/^\d{4}\/\d{4}$/',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ], [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2025/2026)',
        ]);

        // Auto-generate nama_periode from semester + tahun_ajaran
        $validated['nama_periode'] = $validated['semester'] . ' ' . $validated['tahun_ajaran'];

        // Check duplicate (exclude current)
        $exists = PeriodeSemester::where('semester', $validated['semester'])
            ->where('tahun_ajaran', $validated['tahun_ajaran'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Periode untuk semester dan tahun ajaran ini sudah ada.');
        }

        $periode->update($validated);

        return redirect()
            ->route('admin.periode-semester.index')
            ->with('success', 'Periode semester berhasil diperbarui.');
    }

    /**
     * Set period as active
     */
    public function setActive(int $id): RedirectResponse
    {
        $periode = PeriodeSemester::findOrFail($id);
        $periode->setAsActive();

        return redirect()
            ->route('admin.periode-semester.index')
            ->with('success', "Periode '{$periode->nama_periode}' berhasil diaktifkan.");
    }

    /**
     * Generate pertemuan for all jadwal in this period
     */
    public function generatePertemuan(int $id): RedirectResponse
    {
        $periode = PeriodeSemester::findOrFail($id);
        $generated = $this->pertemuanService->generateAllPertemuanForPeriode($periode);

        return redirect()
            ->route('admin.periode-semester.index')
            ->with('success', "{$generated} pertemuan berhasil di-generate untuk periode '{$periode->nama_periode}'.");
    }

    /**
     * Soft delete period (archive)
     */
    public function destroy(int $id): RedirectResponse
    {
        $periode = PeriodeSemester::findOrFail($id);

        // Can't archive active period
        if ($periode->is_active) {
            return back()->with('error', 'Tidak dapat mengarsipkan periode yang sedang aktif.');
        }

        // Soft delete cascade: template_jam, jadwal_mengajar, pertemuan
        $periode->templateJam()->delete();
        $periode->jadwalMengajar()->delete();
        
        // Delete pertemuan via jadwal
        \App\Models\Pertemuan::whereIn('jadwal_mengajar_id', 
            $periode->jadwalMengajar()->withTrashed()->pluck('id')
        )->delete();

        $periode->delete();

        return redirect()
            ->route('admin.periode-semester.index')
            ->with('success', 'Periode semester berhasil diarsipkan beserta template jam dan jadwal.');
    }

    /**
     * Display archived periods
     */
    public function trash(): View
    {
        $periodes = PeriodeSemester::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('admin.periode-semester.trash', [
            'periodes' => $periodes,
        ]);
    }

    /**
     * Restore soft deleted period
     */
    public function restore(int $id): RedirectResponse
    {
        $periode = PeriodeSemester::onlyTrashed()->findOrFail($id);
        
        // Restore cascade: template_jam, jadwal_mengajar, pertemuan
        $periode->restore();
        $periode->templateJam()->onlyTrashed()->restore();
        $periode->jadwalMengajar()->onlyTrashed()->restore();
        
        // Restore pertemuan via jadwal
        \App\Models\Pertemuan::withTrashed()
            ->whereIn('jadwal_mengajar_id', $periode->jadwalMengajar()->pluck('id'))
            ->restore();

        return redirect()
            ->route('admin.periode-semester.trash')
            ->with('success', 'Periode semester berhasil dipulihkan beserta template jam dan jadwal.');
    }

    /**
     * Permanently delete period
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $periode = PeriodeSemester::onlyTrashed()->findOrFail($id);
        
        // Check if has absensi (even on trashed jadwal)
        $hasAbsensi = \App\Models\Absensi::whereIn('jadwal_mengajar_id', 
            $periode->jadwalMengajar()->withTrashed()->pluck('id')
        )->exists();
            
        if ($hasAbsensi) {
            return redirect()
                ->route('admin.periode-semester.trash')
                ->with('error', 'Tidak dapat menghapus permanen periode yang memiliki data absensi.');
        }
        
        // Force delete pertemuan
        \App\Models\Pertemuan::withTrashed()
            ->whereIn('jadwal_mengajar_id', $periode->jadwalMengajar()->withTrashed()->pluck('id'))
            ->forceDelete();
        
        // Force delete jadwal
        $periode->jadwalMengajar()->withTrashed()->forceDelete();
        
        // Force delete template_jam
        $periode->templateJam()->withTrashed()->forceDelete();
        
        // Force delete periode
        $periode->forceDelete();

        return redirect()
            ->route('admin.periode-semester.trash')
            ->with('success', 'Periode semester berhasil dihapus secara permanen.');
    }

    /**
     * Bulk restore archived periodes.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|string']);
        $ids = explode(',', $request->input('ids'));

        $periodes = PeriodeSemester::onlyTrashed()->whereIn('id', $ids)->get();
        $restoredCount = 0;

        foreach ($periodes as $periode) {
            // Restore cascade: template_jam, jadwal_mengajar
            $periode->restore();
            $periode->templateJam()->onlyTrashed()->restore();
            $periode->jadwalMengajar()->onlyTrashed()->restore();
            
            // Restore pertemuan via jadwal
            \App\Models\Pertemuan::withTrashed()
                ->whereIn('jadwal_mengajar_id', $periode->jadwalMengajar()->pluck('id'))
                ->restore();
                
            $restoredCount++;
        }

        return redirect()
            ->route('admin.periode-semester.trash')
            ->with('success', "{$restoredCount} periode semester berhasil dipulihkan.");
    }

    /**
     * Bulk force delete archived periodes.
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|string']);
        $ids = explode(',', $request->input('ids'));

        $periodes = PeriodeSemester::onlyTrashed()->whereIn('id', $ids)->get();
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($periodes as $periode) {
            // Check if has absensi (even on trashed jadwal)
            $hasAbsensi = \App\Models\Absensi::whereIn('jadwal_mengajar_id', 
                $periode->jadwalMengajar()->withTrashed()->pluck('id')
            )->exists();
                
            if ($hasAbsensi) {
                $skippedCount++;
                continue;
            }
            
            // Force delete pertemuan
            \App\Models\Pertemuan::withTrashed()
                ->whereIn('jadwal_mengajar_id', $periode->jadwalMengajar()->withTrashed()->pluck('id'))
                ->forceDelete();
            
            // Force delete jadwal
            $periode->jadwalMengajar()->withTrashed()->forceDelete();
            
            // Force delete template_jam
            $periode->templateJam()->withTrashed()->forceDelete();
            
            // Force delete periode
            $periode->forceDelete();
            $deletedCount++;
        }

        $message = "{$deletedCount} periode semester berhasil dihapus permanen.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} periode dilewati karena memiliki data absensi.";
        }

        return redirect()
            ->route('admin.periode-semester.trash')
            ->with($deletedCount > 0 ? 'success' : 'error', $message);
    }

    /**
     * Show tingkat kurikulum configuration for a period
     */
    public function tingkatKurikulum(int $id): View
    {
        $periode = PeriodeSemester::with('tingkatKurikulum.kurikulum')->findOrFail($id);
        $kurikulums = \App\Models\Kurikulum::active()->orderBy('nama')->get();
        
        // Get current configuration for each tingkat
        $tingkatConfig = [];
        foreach (['X', 'XI', 'XII'] as $tingkat) {
            $config = $periode->tingkatKurikulum()->where('tingkat', $tingkat)->first();
            $tingkatConfig[$tingkat] = $config?->kurikulum_id;
        }

        return view('admin.periode-semester.tingkat-kurikulum', [
            'periode' => $periode,
            'kurikulums' => $kurikulums,
            'tingkatConfig' => $tingkatConfig,
        ]);
    }

    /**
     * Save tingkat kurikulum configuration
     */
    public function saveTingkatKurikulum(Request $request, int $id): RedirectResponse
    {
        $periode = PeriodeSemester::findOrFail($id);

        $validated = $request->validate([
            'tingkat' => 'required|array',
            'tingkat.X' => 'nullable|exists:kurikulum,id',
            'tingkat.XI' => 'nullable|exists:kurikulum,id',
            'tingkat.XII' => 'nullable|exists:kurikulum,id',
        ]);

        // Update or create configuration for each tingkat
        foreach (['X', 'XI', 'XII'] as $tingkat) {
            $kurikulumId = $validated['tingkat'][$tingkat] ?? null;
            
            if ($kurikulumId) {
                \App\Models\TingkatKurikulum::updateOrCreate(
                    [
                        'periode_semester_id' => $periode->id,
                        'tingkat' => $tingkat,
                    ],
                    [
                        'kurikulum_id' => $kurikulumId,
                    ]
                );
            } else {
                // Remove if no kurikulum selected
                \App\Models\TingkatKurikulum::where('periode_semester_id', $periode->id)
                    ->where('tingkat', $tingkat)
                    ->delete();
            }
        }

        return redirect()
            ->route('admin.periode-semester.index')
            ->with('success', "Konfigurasi kurikulum untuk periode '{$periode->nama_periode}' berhasil disimpan.");
    }
}
