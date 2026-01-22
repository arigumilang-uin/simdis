<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Konsentrasi;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Konsentrasi Controller
 * 
 * Mengelola CRUD untuk Konsentrasi Keahlian.
 * Konsentrasi adalah spesialisasi dalam suatu Jurusan (Program Keahlian).
 */
class KonsentrasiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of konsentrasi
     */
    public function index(Request $request)
    {
        $query = Konsentrasi::with('jurusan')
            ->withCount('kelas');

        // Filter by jurusan
        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_konsentrasi', 'like', "%{$search}%")
                  ->orWhere('kode_konsentrasi', 'like', "%{$search}%");
            });
        }

        $konsentrasiList = $query->orderBy('jurusan_id')
                                 ->orderBy('nama_konsentrasi')
                                 ->paginate(15)
                                 ->withQueryString();

        // Return partial view for AJAX requests
        if ($request->ajax() || $request->has('render_partial')) {
            return view('konsentrasi._table', compact('konsentrasiList'));
        }

        $jurusanList = Jurusan::orderBy('nama_jurusan')->get();

        return view('konsentrasi.index', compact('konsentrasiList', 'jurusanList'));
    }

    /**
     * Show the form for creating a new konsentrasi
     * DEPRECATED: Form is now inside slide-over drawer on index page
     */
    public function create()
    {
        return redirect()->route('konsentrasi.index');
    }

    /**
     * Store a newly created konsentrasi
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'nama_konsentrasi' => ['required', 'string', 'max:255'],
            'kode_konsentrasi' => ['nullable', 'string', 'max:20'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Konsentrasi::create($validated);

        return redirect()
            ->route('konsentrasi.index')
            ->with('success', 'Konsentrasi berhasil ditambahkan.');
    }

    /**
     * Display the specified konsentrasi
     */
    public function show(Konsentrasi $konsentrasi)
    {
        $konsentrasi->load(['jurusan', 'kelas.waliKelas', 'kelas.siswa']);

        return view('konsentrasi.show', compact('konsentrasi'));
    }

    /**
     * Show the form for editing the specified konsentrasi
     * DEPRECATED: Form is now inside slide-over drawer on index page
     */
    public function edit(Konsentrasi $konsentrasi)
    {
        return redirect()->route('konsentrasi.index')->with('edit_id', $konsentrasi->id);
    }

    /**
     * Update the specified konsentrasi
     */
    public function update(Request $request, Konsentrasi $konsentrasi)
    {
        $validated = $request->validate([
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'nama_konsentrasi' => ['required', 'string', 'max:255'],
            'kode_konsentrasi' => ['nullable', 'string', 'max:20'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $konsentrasi->update($validated);

        return redirect()
            ->route('konsentrasi.index')
            ->with('success', 'Konsentrasi berhasil diperbarui.');
    }

    /**
     * Remove the specified konsentrasi (soft delete)
     */
    public function destroy(Konsentrasi $konsentrasi)
    {
        $konsentrasi->delete();

        return redirect()
            ->route('konsentrasi.index')
            ->with('success', 'Konsentrasi berhasil diarsipkan.');
    }

    /**
     * Display archived konsentrasi
     */
    public function trash()
    {
        $konsentrasiList = Konsentrasi::onlyTrashed()
            ->with('jurusan')
            ->withCount(['kelas' => fn($q) => $q->withTrashed()])
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('konsentrasi.trash', compact('konsentrasiList'));
    }

    /**
     * Restore soft deleted konsentrasi
     */
    public function restore(int $id)
    {
        $konsentrasi = Konsentrasi::onlyTrashed()->findOrFail($id);
        $konsentrasi->restore();

        return redirect()
            ->route('konsentrasi.trash')
            ->with('success', "Konsentrasi '{$konsentrasi->nama_konsentrasi}' berhasil dipulihkan.");
    }

    /**
     * Permanently delete konsentrasi
     */
    public function forceDelete(int $id)
    {
        $konsentrasi = Konsentrasi::onlyTrashed()->findOrFail($id);
        $nama = $konsentrasi->nama_konsentrasi;
        
        // Check if has kelas
        $hasKelas = Kelas::withTrashed()
            ->where('konsentrasi_id', $konsentrasi->id)
            ->exists();
            
        if ($hasKelas) {
            return redirect()
                ->route('konsentrasi.trash')
                ->with('error', 'Tidak dapat menghapus permanen konsentrasi yang memiliki data kelas.');
        }
        
        $konsentrasi->forceDelete();

        return redirect()
            ->route('konsentrasi.trash')
            ->with('success', "Konsentrasi '{$nama}' berhasil dihapus secara permanen.");
    }

    /**
     * Bulk restore konsentrasi
     */
    public function bulkRestore(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        
        $count = 0;
        foreach ($request->ids as $id) {
            $konsentrasi = Konsentrasi::onlyTrashed()->find($id);
            if ($konsentrasi) {
                $konsentrasi->restore();
                $count++;
            }
        }
        
        return redirect()
            ->route('konsentrasi.trash')
            ->with('success', "{$count} konsentrasi berhasil dipulihkan.");
    }

    /**
     * Bulk force delete konsentrasi
     */
    public function bulkForceDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        
        $count = 0;
        $skipped = 0;
        
        foreach ($request->ids as $id) {
            $konsentrasi = Konsentrasi::onlyTrashed()->find($id);
            if ($konsentrasi) {
                $hasKelas = Kelas::withTrashed()
                    ->where('konsentrasi_id', $konsentrasi->id)
                    ->exists();
                    
                if ($hasKelas) {
                    $skipped++;
                    continue;
                }
                
                $konsentrasi->forceDelete();
                $count++;
            }
        }
        
        $message = "{$count} konsentrasi berhasil dihapus permanen.";
        if ($skipped > 0) {
            $message .= " {$skipped} konsentrasi dilewati karena masih memiliki data kelas.";
        }
        
        return redirect()
            ->route('konsentrasi.trash')
            ->with($count > 0 ? 'success' : 'warning', $message);
    }

    /**
     * API: Get konsentrasi by jurusan (for dynamic dropdown)
     */
    public function getByJurusan(Request $request)
    {
        $jurusanId = $request->jurusan_id;

        if (!$jurusanId) {
            return response()->json([]);
        }

        $konsentrasi = Konsentrasi::where('jurusan_id', $jurusanId)
            ->where('is_active', true)
            ->orderBy('nama_konsentrasi')
            ->get(['id', 'nama_konsentrasi', 'kode_konsentrasi']);

        return response()->json($konsentrasi);
    }
}
