<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Models\Kurikulum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Mata Pelajaran Controller (Admin)
 * 
 * CRUD untuk master data mata pelajaran.
 * Filter by kurikulum dan kelompok untuk organisasi yang lebih baik.
 */
class MataPelajaranController extends Controller
{
    /**
     * Kelompok options
     */
    private array $kelompokOptions = [
        'A' => 'A - Umum',
        'B' => 'B - Kejuruan',
        'C' => 'C - Muatan Lokal',
    ];

    /**
     * Display list of mata pelajaran
     */
    public function index(Request $request): View
    {
        // Get all kurikulums for filter
        $kurikulums = Kurikulum::active()->orderBy('nama')->get();

        // Get selected filters
        $kurikulumId = $request->input('kurikulum_id');
        $kelompok = $request->input('kelompok');
        $search = $request->input('search');

        // Default to first kurikulum if not selected
        $selectedKurikulum = null;
        if ($kurikulumId) {
            $selectedKurikulum = Kurikulum::find($kurikulumId);
        }
        if (!$selectedKurikulum && $kurikulums->isNotEmpty()) {
            $selectedKurikulum = $kurikulums->first();
            $kurikulumId = $selectedKurikulum->id;
        }

        // Default to kelompok A if not selected
        if (!$kelompok) {
            $kelompok = 'A';
        }

        // Query mata pelajaran
        $mataPelajaran = collect();
        $kelompokCounts = [];
        if ($selectedKurikulum) {
            $query = MataPelajaran::with(['kurikulum', 'guruPengampu'])
                ->forKurikulum($selectedKurikulum->id)
                ->forKelompok($kelompok)
                ->search($search);
            
            $mataPelajaran = $query->orderBy('nama_mapel')->get();
            
            // Count mapels per kelompok (single query to avoid N+1)
            $kelompokCounts = MataPelajaran::forKurikulum($selectedKurikulum->id)
                ->selectRaw('kelompok, COUNT(*) as count')
                ->groupBy('kelompok')
                ->pluck('count', 'kelompok')
                ->toArray();
        }

        return view('admin.mata-pelajaran.index', [
            'mataPelajaran' => $mataPelajaran,
            'kurikulums' => $kurikulums,
            'selectedKurikulum' => $selectedKurikulum,
            'kurikulumId' => $kurikulumId,
            'kelompok' => $kelompok,
            'kelompokOptions' => $this->kelompokOptions,
            'kelompokCounts' => $kelompokCounts,
            'search' => $search,
        ]);
    }

    /**
     * Show create form - receives kurikulum_id and kelompok from query
     */
    public function create(Request $request): View
    {
        $kurikulumId = $request->input('kurikulum_id');
        $kelompok = $request->input('kelompok', 'A');
        
        $kurikulum = null;
        if ($kurikulumId) {
            $kurikulum = Kurikulum::find($kurikulumId);
        }
        
        $guruList = $this->getGuruList();
        
        return view('admin.mata-pelajaran.create', [
            'kurikulum' => $kurikulum,
            'kurikulumId' => $kurikulumId,
            'kelompok' => $kelompok,
            'kelompokOptions' => $this->kelompokOptions,
            'guruList' => $guruList,
        ]);
    }

    /**
     * Store new mata pelajaran
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'nama_mapel' => 'required|string|max:100',
            'kode_mapel' => 'nullable|string|max:20',
            'kelompok' => 'required|in:A,B,C',
            'deskripsi' => 'nullable|string|max:500',
            'guru_ids' => 'nullable|array',
            'guru_ids.*' => 'exists:users,id',
            'guru_utama_id' => 'nullable|exists:users,id',
        ]);

        $validated['is_active'] = true;

        $mataPelajaran = MataPelajaran::create($validated);

        // Attach guru pengampu
        if (!empty($request->guru_ids)) {
            $syncData = [];
            foreach ($request->guru_ids as $guruId) {
                $syncData[$guruId] = [
                    'is_primary' => $guruId == $request->guru_utama_id,
                ];
            }
            $mataPelajaran->guruPengampu()->sync($syncData);
        }

        return redirect()
            ->route('admin.mata-pelajaran.index', [
                'kurikulum_id' => $validated['kurikulum_id'],
                'kelompok' => $validated['kelompok'],
            ])
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id): View
    {
        $mataPelajaran = MataPelajaran::with(['kurikulum', 'guruPengampu'])->findOrFail($id);
        $guruList = $this->getGuruList();
        
        // Get current guru IDs
        $selectedGuruIds = $mataPelajaran->guruPengampu->pluck('id')->toArray();
        $guruUtamaId = $mataPelajaran->guruPengampu->where('pivot.is_primary', true)->first()?->id;
        
        return view('admin.mata-pelajaran.edit', [
            'mataPelajaran' => $mataPelajaran,
            'kelompokOptions' => $this->kelompokOptions,
            'guruList' => $guruList,
            'selectedGuruIds' => $selectedGuruIds,
            'guruUtamaId' => $guruUtamaId,
        ]);
    }

    /**
     * Update mata pelajaran
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $mataPelajaran = MataPelajaran::findOrFail($id);

        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:100',
            'kode_mapel' => 'nullable|string|max:20',
            'kelompok' => 'required|in:A,B,C',
            'deskripsi' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'guru_ids' => 'nullable|array',
            'guru_ids.*' => 'exists:users,id',
            'guru_utama_id' => 'nullable|exists:users,id',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $mataPelajaran->update($validated);

        // Sync guru pengampu
        $guruIds = $request->guru_ids ?? [];
        $syncData = [];
        foreach ($guruIds as $guruId) {
            $syncData[$guruId] = [
                'is_primary' => $guruId == $request->guru_utama_id,
            ];
        }
        $mataPelajaran->guruPengampu()->sync($syncData);

        return redirect()
            ->route('admin.mata-pelajaran.index', [
                'kurikulum_id' => $mataPelajaran->kurikulum_id,
                'kelompok' => $mataPelajaran->kelompok,
            ])
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    /**
     * Delete mata pelajaran
     */
    public function destroy(int $id): RedirectResponse
    {
        $mataPelajaran = MataPelajaran::findOrFail($id);
        $kurikulumId = $mataPelajaran->kurikulum_id;
        $kelompok = $mataPelajaran->kelompok;
        
        // Check if used in jadwal
        if ($mataPelajaran->jadwalMengajar()->exists()) {
            return back()->with('error', 'Mata pelajaran tidak dapat dihapus karena masih digunakan di jadwal.');
        }

        // Detach all guru first
        $mataPelajaran->guruPengampu()->detach();
        
        $mataPelajaran->delete();

        return redirect()
            ->route('admin.mata-pelajaran.index', [
                'kurikulum_id' => $kurikulumId,
                'kelompok' => $kelompok,
            ])
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    /**
     * API: Get mapel for a specific kurikulum (untuk AJAX dropdown)
     */
    public function getByKurikulum(int $kurikulumId)
    {
        $mapels = MataPelajaran::forKurikulum($kurikulumId)
            ->active()
            ->orderBy('nama_mapel')
            ->get(['id', 'nama_mapel', 'kode_mapel']);

        return response()->json($mapels);
    }

    /**
     * API: Get guru for a specific mapel (untuk filter di jadwal)
     */
    public function getGuruByMapel(int $mapelId)
    {
        $mapel = MataPelajaran::with('guruPengampu')->find($mapelId);
        
        if (!$mapel) {
            return response()->json([]);
        }

        $guru = $mapel->guruPengampu->map(function($g) {
            return [
                'id' => $g->id,
                'nama' => $g->username,
                'username' => $g->username,
                'is_primary' => $g->pivot->is_primary,
            ];
        });

        return response()->json($guru);
    }

    /**
     * Get list of guru for dropdown
     */
    private function getGuruList()
    {
        return User::whereHas('role', function($q) {
                $q->whereIn('nama_role', ['Guru', 'Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Waka Kurikulum', 'Waka Sarana', 'Kepala Sekolah']);
            })
            ->where('is_active', true)
            ->orderBy('username')
            ->get(['id', 'nama', 'username']);
    }
}
