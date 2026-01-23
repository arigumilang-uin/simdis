<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Services\Siswa\SiswaService;
use App\Services\Siswa\SiswaWaliService;
use App\Data\Siswa\SiswaData;
use App\Data\Siswa\SiswaFilterData;
use App\Http\Requests\Siswa\CreateSiswaRequest;
use App\Http\Requests\Siswa\UpdateSiswaRequest;
use App\Http\Requests\Siswa\FilterSiswaRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Siswa Controller - Core CRUD Operations
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Validasi (via FormRequest)
 * - Convert ke DTO
 * - Panggil Service
 * - Return Response
 * 
 * ATURAN:
 * - TIDAK BOLEH ada business logic
 * - TIDAK BOLEH ada query database
 * - TIDAK BOLEH ada manipulasi data
 * - Target: < 20 baris per method
 * 
 * DELEGATIONS:
 * - Bulk operations → SiswaBulkController
 * - Archive/Restore → SiswaArchiveController
 * - Transfer → SiswaTransferController
 * 
 * @package App\Http\Controllers\MasterData
 */
class SiswaController extends Controller
{
    public function __construct(
        private SiswaService $siswaService,
        private SiswaWaliService $waliService
    ) {}

    // =====================================================================
    // CORE CRUD OPERATIONS
    // =====================================================================

    /**
     * Tampilkan daftar siswa dengan filter.
     */
    public function index(FilterSiswaRequest $request): View
    {
        $filters = SiswaFilterData::from($request->validated());
        $user = auth()->user();
        $role = $user->effectiveRoleName();
        
        // Apply role-based auto-filters
        if ($role === 'Kaprodi' && $user->jurusanDiampu) {
            $filters->jurusan_id = $user->jurusanDiampu->id;
        }
        if ($role === 'Wali Kelas' && $user->kelasDiampu) {
            $filters->kelas_id = $user->kelasDiampu->id;
        }

        $siswa = $this->siswaService->getFilteredSiswa($filters);
        
        // Return partial view if requested
        if ($request->ajax() || $request->has('render_partial')) {
            return view('siswa._table', compact('siswa'));
        }

        // Get filter options based on role
        $allJurusan = [];
        $allKonsentrasi = [];
        $allKelas = [];
        
        if ($role === 'Kaprodi' && $user->jurusanDiampu) {
            // Kaprodi: filter hanya konsentrasi dan kelas di jurusannya
            $allKonsentrasi = $this->siswaService->getKonsentrasiByJurusan($user->jurusanDiampu->id);
            $allKelas = $this->siswaService->getKelasByJurusan($user->jurusanDiampu->id);
        } elseif ($role !== 'Wali Kelas') {
            // Operator/Waka: semua filter
            $allJurusan = $this->siswaService->getAllJurusanForFilter();
            $allKonsentrasi = $this->siswaService->getAllKonsentrasiForFilter();
            $allKelas = $this->siswaService->getAllKelasForFilter();
        }
        // Wali Kelas: no filters needed (already auto-filtered)

        return view('siswa.index', compact('siswa', 'allJurusan', 'allKonsentrasi', 'allKelas', 'filters'))->with('userRole', $role);
    }

    /**
     * Tampilkan form create siswa.
     */
    public function create(): View
    {
        $kelas = $this->siswaService->getAllKelas();
        $waliMuridOptions = $this->waliService->getAvailableWaliMurid();
        
        return view('siswa.create', compact('kelas', 'waliMuridOptions'));
    }

    /**
     * Check if NISN is available (AJAX).
     */
    public function checkNisn(Request $request): JsonResponse
    {
        $nisn = $request->input('nisn');
        
        if (empty($nisn)) {
            return response()->json(['available' => false, 'message' => 'NISN harus diisi']);
        }

        if (!preg_match('/^\d{10}$/', $nisn)) {
            return response()->json(['available' => false, 'message' => 'NISN harus 10 digit angka']);
        }

        $existingSiswa = $this->siswaService->findByNisn($nisn);
        $exists = $existingSiswa !== null;

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'NISN sudah terdaftar' : 'NISN tersedia',
            'owner' => $exists ? $existingSiswa->nama_siswa : null,
        ]);
    }

    /**
     * Check Wali HP availability (AJAX).
     */
    public function checkWaliHp(Request $request): JsonResponse
    {
        $phone = $request->input('phone');
        $phoneClean = preg_replace('/\D+/', '', $phone);

        if (empty($phoneClean)) {
            return response()->json(['available' => true, 'message' => 'Nomor HP kosong']);
        }

        // Logic check existing via Service (manual query here for specifics)
        $existingWali = \App\Models\User::where('phone', $phoneClean)
            ->whereHas('role', fn($q) => $q->where('nama_role', 'Wali Murid'))
            ->select('id', 'nama', 'username', 'email') // Select needed fields
            ->first();

        if ($existingWali) {
            return response()->json([
                'status' => 'found',
                'available' => false,
                'message' => 'Nomor HP sudah terdaftar.',
                'wali' => $existingWali
            ]);
        }

        return response()->json([
            'status' => 'available',
            'available' => true,
            'message' => 'Nomor HP tersedia untuk akun baru.'
        ]);
    }

    /**
     * Simpan siswa baru.
     */
    public function store(CreateSiswaRequest $request): RedirectResponse
    {
        $siswaData = SiswaData::from($request->validated());
        
        $result = $this->siswaService->createSiswa(
            $siswaData,
            $request->boolean('create_wali')
        );

        // Flash wali credentials if created
        if (!empty($result['wali_credentials']) && $result['wali_credentials']['is_new']) {
            session()->flash('wali_credentials', [$result['wali_credentials']]);
        }

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail siswa.
     */
    public function show(int $id): View
    {
        $data = $this->siswaService->getSiswaDetail($id);
        
        return view('siswa.show', [
            'siswa' => $data['siswa'],
            'statistik' => $data['statistik'],
            'totalPoin' => $data['statistik']['total_poin'] ?? 0,
            'pembinaanAktif' => $data['statistik']['pembinaan_aktif'] ?? false,
        ]);
    }

    /**
     * Tampilkan form edit siswa.
     */
    public function edit(int $id): View
    {
        $siswa = $this->siswaService->getSiswaForEdit($id);
        $kelas = $this->siswaService->getAllKelas();
        $waliMurid = $this->waliService->getAvailableWaliMurid();
        
        return view('siswa.edit', compact('siswa', 'kelas', 'waliMurid'));
    }

    /**
     * Update siswa.
     */
    public function update(UpdateSiswaRequest $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        $isWaliKelas = $user->hasRole('Wali Kelas');

        // Get existing siswa for partial update
        $existingSiswa = $this->siswaService->findSiswa($id);
        
        // Build DTO based on role
        $siswaData = $isWaliKelas
            ? SiswaData::from([
                'id' => $id,
                'kelas_id' => $existingSiswa->kelas_id,
                'wali_murid_user_id' => $existingSiswa->wali_murid_id,
                'nisn' => $existingSiswa->nisn,
                'nama_siswa' => $existingSiswa->nama_siswa,
                'nomor_hp_wali_murid' => $request->input('nomor_hp_wali_murid'),
            ])
            : SiswaData::from($request->validated());

        $this->siswaService->updateSiswa(
            $id,
            $siswaData,
            $isWaliKelas,
            $request->boolean('create_wali')
        );

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Siswa berhasil diperbarui.');
    }

    /**
     * Hapus siswa (soft delete).
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'alasan_keluar' => 'nullable|in:Alumni,Dikeluarkan,Pindah Sekolah,Lainnya',
            'keterangan_keluar' => 'nullable|string|max:500',
        ]);

        try {
            $this->siswaService->deleteSiswa(
                $id,
                $validated['alasan_keluar'] ?? null,
                $validated['keterangan_keluar'] ?? null
            );

            return redirect()
                ->route('siswa.index')
                ->with('success', 'Siswa berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus siswa: ' . $e->getMessage());
        }
    }

    // =====================================================================
    // BACKWARD COMPATIBILITY REDIRECTS
    // These methods now delegate to sub-controllers for legacy routes
    // =====================================================================

    /**
     * @deprecated Use SiswaBulkController::create()
     */
    public function bulkCreate(): View
    {
        return app(SiswaBulkController::class)->create();
    }

    /**
     * @deprecated Use SiswaBulkController::store()
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        return app(SiswaBulkController::class)->store($request);
    }

    /**
     * @deprecated Use SiswaBulkController::deleteByKelas()
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        return app(SiswaBulkController::class)->deleteByKelas($request);
    }

    /**
     * @deprecated Use SiswaArchiveController::index()
     */
    public function showDeleted(Request $request): View
    {
        return app(SiswaArchiveController::class)->index($request);
    }

    /**
     * @deprecated Use SiswaArchiveController::restore()
     */
    public function restore(int $id): RedirectResponse
    {
        return app(SiswaArchiveController::class)->restore($id);
    }

    /**
     * @deprecated Use SiswaArchiveController::forceDestroy()
     */
    public function forceDestroy(int $id): RedirectResponse
    {
        return app(SiswaArchiveController::class)->forceDestroy($id);
    }

    /**
     * @deprecated Use SiswaTransferController::index()
     */
    public function transferForm(Request $request): View
    {
        return app(SiswaTransferController::class)->index($request);
    }

    /**
     * @deprecated Use SiswaTransferController::transfer()
     */
    public function bulkTransfer(Request $request): RedirectResponse
    {
        return app(SiswaTransferController::class)->transfer($request);
    }
}
