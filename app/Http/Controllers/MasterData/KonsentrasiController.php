<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Konsentrasi;
use App\Models\Jurusan;
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

        $jurusanList = Jurusan::orderBy('nama_jurusan')->get();

        return view('konsentrasi.index', compact('konsentrasiList', 'jurusanList'));
    }

    /**
     * Show the form for creating a new konsentrasi
     */
    public function create()
    {
        $jurusanList = Jurusan::orderBy('nama_jurusan')->get();

        return view('konsentrasi.create', compact('jurusanList'));
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
     */
    public function edit(Konsentrasi $konsentrasi)
    {
        $jurusanList = Jurusan::orderBy('nama_jurusan')->get();

        return view('konsentrasi.edit', compact('konsentrasi', 'jurusanList'));
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
     * Remove the specified konsentrasi
     */
    public function destroy(Konsentrasi $konsentrasi)
    {
        // Check if konsentrasi has kelas
        if ($konsentrasi->kelas()->exists()) {
            return redirect()
                ->route('konsentrasi.index')
                ->with('error', 'Tidak dapat menghapus konsentrasi yang masih memiliki kelas.');
        }

        $konsentrasi->delete();

        return redirect()
            ->route('konsentrasi.index')
            ->with('success', 'Konsentrasi berhasil dihapus.');
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
