<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Services\Siswa\SiswaService;
use App\Data\Siswa\SiswaData;
use App\Data\Siswa\SiswaFilterData;
use App\Http\Requests\Siswa\CreateSiswaRequest;
use App\Http\Requests\Siswa\UpdateSiswaRequest;
use App\Http\Requests\Siswa\FilterSiswaRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Siswa Controller - Clean Architecture Pattern
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
 */
class SiswaController extends Controller
{
    /**
     * Inject SiswaService via constructor.
     *
     * @param SiswaService $siswaService
     */
    public function __construct(
        private SiswaService $siswaService
    ) {}

    /**
     * Tampilkan daftar siswa dengan filter.
     * 
     * ALUR:
     * 1. Validasi filter (via FilterSiswaRequest)
     * 2. Convert ke SiswaFilterData (DTO)
     * 3. Apply role-based auto-filters (Kaprodi/Wali Kelas)
     * 4. Panggil service untuk data siswa
     * 5. Panggil service untuk master data filter
     * 6. Return view
     */
    public function index(FilterSiswaRequest $request): View
    {
        // Convert validated request data ke DTO
        $filterData = $request->getFilterData();
        
        // AUTO-FILTER berdasarkan role
        $user = auth()->user();
        $role = $user->effectiveRoleName() ?? $user->role?->nama_role;
        
        // Kaprodi: filter by assigned jurusan
        if ($role === 'Kaprodi' && !isset($filterData['jurusan_id'])) {
            $jurusanKaprodi = \App\Models\Jurusan::where('kaprodi_user_id', $user->id)->first();
            if ($jurusanKaprodi) {
                $filterData['jurusan_id'] = $jurusanKaprodi->id;
            }
        }
        
        // Wali Kelas: filter by assigned kelas
        if ($role === 'Wali Kelas' && !isset($filterData['kelas_id'])) {
            $kelasWali = \App\Models\Kelas::where('wali_kelas_user_id', $user->id)->first();
            if ($kelasWali) {
                $filterData['kelas_id'] = $kelasWali->id;
            }
        }
        
        // Convert to DTO with role-based filters applied
        $filters = SiswaFilterData::from($filterData);

        // Panggil service untuk get filtered siswa
        $siswa = $this->siswaService->getFilteredSiswa($filters);

        // Panggil service untuk master data dropdown filter
        $allKelas = $this->siswaService->getAllKelasForFilter();
        $allJurusan = $this->siswaService->getAllJurusanForFilter();

        // Jika request AJAX atau ada parameter render_partial, return hanya partial table
        if ($request->ajax() || $request->has('render_partial')) {
            return view('siswa._table', [
                'siswa' => $siswa,
            ]);
        }
        
        // Return view full page
        return view('siswa.index', [
            'siswa' => $siswa,
            'allJurusan' => $allJurusan,
            'allKelas' => $allKelas,
            'filters' => $filterData, // Kirim data filter ke view untuk repopulate form
        ]);
    }

    /**
     * Tampilkan form create siswa.
     */
    public function create(): View
    {
        $kelas = $this->siswaService->getAllKelas();
        $waliMurid = $this->siswaService->getAvailableWaliMurid();

        return view('siswa.create', compact('kelas', 'waliMurid'));
    }

    /**
     * Check if NISN is available immediately via AJAX.
     */
    public function checkNisn(\Illuminate\Http\Request $request)
    {
        $nisn = $request->query('nisn');
        
        if (!$nisn) {
            return response()->json(['valid' => false, 'message' => 'NISN kosong']);
        }
        
        // Cek format angka & panjang (HARUS 10 digit pas)
        if (!ctype_digit($nisn) || strlen($nisn) !== 10) {
            return response()->json([
                'valid' => false, 
                'message' => 'NISN harus tepat 10 digit angka'
            ]);
        }

        // Cek database
        $existing = \App\Models\Siswa::where('nisn', $nisn)->first();

        if ($existing) {
            return response()->json([
                'valid' => false,
                'message' => 'NISN sudah terdaftar',
                'owner' => $existing->nama_siswa
            ]);
        }

        return response()->json(['valid' => true, 'message' => 'NISN tersedia']);
    }

    /**
     * Check Wali HP availability via AJAX.
     */
    public function checkWaliHp(\Illuminate\Http\Request $request)
    {
        $hp = $request->query('hp');
        
        // Bersihkan nomor HP (hanya angka)
        $hpClean = preg_replace('/\D+/', '', $hp);

        if (!$hpClean) {
            return response()->json(['status' => 'invalid']);
        }

        // Cari user yang punya role 'Wali Murid' dan phone matching
        $wali = \App\Models\User::where('phone', $hpClean)
            ->whereHas('role', function($q) {
                $q->where('nama_role', 'Wali Murid');
            })
            ->first();

        if ($wali) {
            return response()->json([
                'status' => 'found',
                'wali' => [
                    'id' => $wali->id,
                    'nama' => $wali->nama,
                    'username' => $wali->username
                ]
            ]);
        }

        return response()->json(['status' => 'available']);
    }

    /**
     * Simpan siswa baru.
     * 
     * ALUR:
     * 1. Validasi (via CreateSiswaRequest)
     * 2. Convert ke SiswaData (DTO)
     * 3. Panggil service->createSiswa()
     * 4. Flash credentials jika wali dibuat
     * 5. Redirect dengan success message
     */
    public function store(CreateSiswaRequest $request): RedirectResponse
    {
        // Convert validated request ke DTO
        $siswaData = SiswaData::from($request->validated());

        // Panggil service dengan DTO + primitive boolean
        $result = $this->siswaService->createSiswa(
            $siswaData,
            $request->boolean('create_wali')
        );

        // Flash credentials ke session jika wali dibuat (untuk ditampilkan)
        if ($result['wali_credentials']) {
            session()->flash('wali_created', $result['wali_credentials']);
        }

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Data Siswa Berhasil Ditambahkan');
    }

    /**
     * Tampilkan detail siswa.
     * 
     * ALUR:
     * 1. Panggil service untuk get detail siswa (dengan eager loading)
     * 2. Service menghitung total poin (business logic)
     * 3. Return view dengan data
     */
    public function show(int $id): View
    {
        // Panggil service untuk get detail lengkap siswa
        $result = $this->siswaService->getSiswaDetail($id);

        return view('siswa.show', [
            'siswa' => $result['siswa'],
            'totalPoin' => $result['totalPoin'],
            'pembinaanRekomendasi' => $result['pembinaanRekomendasi'],
            'pembinaanAktif' => $result['pembinaanAktif'] ?? null,
            'pembinaanSelesai' => $result['pembinaanSelesai'] ?? null,
        ]);
    }

    /**
     * Tampilkan form edit siswa.
     * 
     * ALUR:
     * 1. Panggil service untuk get siswa
     * 2. Panggil service untuk master data (kelas, wali murid)
     * 3. Return view
     */
    public function edit(int $id): View
    {
        $siswa = $this->siswaService->getSiswaForEdit($id);
        $kelas = $this->siswaService->getAllKelas();
        $waliMurid = $this->siswaService->getAvailableWaliMurid();

        return view('siswa.edit', compact('siswa', 'kelas', 'waliMurid'));
    }

    /**
     * Update siswa.
     * 
     * ALUR:
     * 1. Validasi (via UpdateSiswaRequest - role-based rules)
     * 2. Convert ke SiswaData (DTO) - handle partial data for Wali Kelas
     * 3. Panggil service->updateSiswa() dengan flag isWaliKelas
     * 4. Redirect dengan success message
     */
    public function update(UpdateSiswaRequest $request, int $id): RedirectResponse
    {
        // For Wali Kelas: merge validated data with existing siswa data
        // Because Wali Kelas only validates nomor_hp_wali_murid
        if ($request->isWaliKelas()) {
            $existingSiswa = $this->siswaService->getSiswaForEdit($id);
            $mergedData = array_merge(
                $existingSiswa->toArray(),
                $request->validated()
            );
            $siswaData = SiswaData::from($mergedData);
        } else {
            // For Operator: use validated data directly
            $siswaData = SiswaData::from($request->validated());
        }

        // Panggil service dengan DTO + flag role
        $this->siswaService->updateSiswa(
            $id,
            $siswaData,
            $request->isWaliKelas()
        );

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Hapus siswa.
     * 
     * UPDATED: Sekarang menerima alasan_keluar dan keterangan_keluar dari form.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        // Validasi input alasan keluar
        $validated = $request->validate([
            'alasan_keluar' => 'required|in:Alumni,Dikeluarkan,Pindah Sekolah,Lainnya',
            'keterangan_keluar' => 'nullable|string|max:500',
        ]);

        // Ambil siswa
        $siswa = \App\Models\Siswa::findOrFail($id);
        
        // Set alasan & keterangan keluar sebelum soft delete
        $siswa->alasan_keluar = $validated['alasan_keluar'];
        $siswa->keterangan_keluar = $validated['keterangan_keluar'] ?? null;
        $siswa->save();

        // Soft delete via service
        $this->siswaService->deleteSiswa($id);

        return redirect()
            ->route('siswa.index')
            ->with('success', "Data Siswa Berhasil Dihapus dengan alasan: {$validated['alasan_keluar']}");
    }

    /**
     * Bulk delete siswa per kelas.
     * 
     * CLEAN ARCHITECTURE: Controller hanya sebagai kurir.
     * - Validasi input dasar
     * - Delegate ke service untuk delete + cleanup
     * - Return response
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        // Validasi input
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'alasan_keluar' => 'required|in:Alumni,Dikeluarkan,Pindah Sekolah,Lainnya',
            'keterangan_keluar' => 'nullable|string|max:500',
            'delete_orphaned_wali' => 'nullable|boolean',
            'confirm' => 'required|accepted',
        ]);

        try {
            // Delegate ALL processing to service
            $result = $this->siswaService->bulkDeleteByKelas(
                $validated['kelas_id'],
                [
                    'deleteOrphanedWali' => $request->boolean('delete_orphaned_wali'),
                    'alasanKeluar' => $validated['alasan_keluar'],
                    'keteranganKeluar' => $validated['keterangan_keluar'] ?? null,
                ]
            );

            // Check if any siswa were deleted
            if ($result['count'] === 0) {
                return back()->with('error', 'Tidak ada siswa aktif di kelas ini.');
            }

            // Build success message
            $message = "Berhasil menghapus {$result['count']} siswa dengan alasan: {$validated['alasan_keluar']}.";
            if ($result['orphaned_wali_deleted'] > 0) {
                $message .= " {$result['orphaned_wali_deleted']} akun wali murid yang tidak lagi memiliki siswa aktif juga telah dihapus.";
            }

            return redirect()
                ->route('siswa.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Bulk Delete Siswa Error', [
                'kelas_id' => $validated['kelas_id'],
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal menghapus siswa: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan form bulk create siswa.
     * 
     * ALUR:
     * 1. Panggil service untuk master data (kelas)
     * 2. Return view bulk_create
     */
    public function bulkCreate(): View
    {
        $kelas = $this->siswaService->getAllKelas();

        return view('siswa.bulk_create', compact('kelas'));
    }

    /**
     * Proses bulk create siswa dari CSV/Excel.
     * 
     * CLEAN ARCHITECTURE: Controller hanya sebagai kurir.
     * - Validasi input dasar (FormRequest style)
     * - Handle file upload
     * - Delegate ke service untuk parsing, validasi, dan create
     * - Return response
     */
    public function bulkStore(\Illuminate\Http\Request $request): RedirectResponse
    {
        // Validasi input dasar
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'bulk_file' => 'nullable|file|mimes:csv,txt|max:2048',
            'bulk_data' => 'nullable|string',
        ]);

        // Determine data type and prepare data for service
        $dataType = null;
        $data = null;
        
        if ($request->hasFile('bulk_file')) {
            $dataType = 'csv';
            $data = $request->file('bulk_file')->getRealPath();
        } elseif ($request->filled('bulk_data')) {
            $dataType = 'manual';
            $data = $request->input('bulk_data');
        } else {
            return redirect()
                ->back()
                ->with('error', 'Silakan upload file atau isi tabel manual.');
        }

        try {
            // Delegate ALL processing to service (parsing, validation, creation)
            $result = $this->siswaService->processBulkCreate(
                $dataType,
                $data,
                $request->input('kelas_id'),
                $request->boolean('create_wali_all')
            );

            // Build success message
            $message = $this->buildBulkCreateMessage($result, $request->boolean('create_wali_all'));

            return redirect()
                ->route('siswa.index')
                ->with('success', $message)
                ->with('bulk_errors', $result['errors'] ?? [])
                ->with('wali_credentials', $result['wali_credentials'] ?? []);
                
        } catch (\App\Exceptions\BusinessValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            \Log::error('Bulk create siswa error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal memproses bulk create: ' . $e->getMessage());
        }
    }

    /**
     * Build success message for bulk create result.
     * Helper method to keep controller clean.
     */
    private function buildBulkCreateMessage(array $result, bool $createWaliAll): string
    {
        $successCount = $result['success_count'];
        $skippedWaliCount = $result['skipped_wali_count'] ?? 0;
        $message = "Berhasil menambahkan {$successCount} siswa.";
        
        if ($createWaliAll) {
            $connectedCount = $successCount - $skippedWaliCount;
            if ($connectedCount > 0) {
                $newWaliCount = count($result['wali_credentials'] ?? []);
                $message .= " {$connectedCount} siswa terhubung ke akun wali murid ({$newWaliCount} akun baru dibuat).";
            }
            if ($skippedWaliCount > 0) {
                $message .= " {$skippedWaliCount} siswa tidak terhubung ke wali (nomor HP kosong).";
            }
        }
        
        if (!empty($result['errors'])) {
            $message .= " Beberapa baris dilewati karena error.";
        }
        
        return $message;
    }

     /**
     * Show deleted siswa page.
     */
    public function showDeleted(\Illuminate\Http\Request $request): View
    {
        $filters = [
            'alasan_keluar' => $request->input('alasan_keluar'),
            'kelas_id' => $request->input('kelas_id'),
            'search' => $request->input('search'),
        ];
        
        $deletedSiswa = $this->siswaService->getDeletedSiswa($filters);
        
        // Return partial view if requested
        if ($request->ajax() || $request->has('render_partial')) {
            return view('siswa._table_deleted', compact('deletedSiswa'));
        }

        $allKelas = $this->siswaService->getAllKelasForFilter();
        $alasanOptions = ['Alumni', 'Dikeluarkan', 'Pindah Sekolah', 'Lainnya'];
        
        return view('siswa.deleted', compact('deletedSiswa', 'allKelas', 'alasanOptions', 'filters'));
    }
    
    /**
     * Restore deleted siswa.
     */
    public function restore(int $id): RedirectResponse
    {
        try {
            $this->siswaService->restoreSiswa($id);
            
            return redirect()
                ->route('siswa.deleted')
                ->with('success', 'Siswa berhasil di-restore beserta semua data terkait.');
                
        } catch (\Exception $e) {
            \Log::error('Restore siswa error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal restore siswa: ' . $e->getMessage());
        }
    }
    
    /**
     * Permanent delete single siswa.
     */
    public function forceDestroy(int $id): RedirectResponse
    {
        try {
            request()->validate([
                'confirm_permanent' => 'required|accepted',
            ], [
                'confirm_permanent.accepted' => 'Anda harus confirm permanent delete.',
            ]);
            
            $this->siswaService->permanentDeleteSiswa($id);
            
            return redirect()
                ->route('siswa.deleted')
                ->with('success', 'Siswa berhasil dihapus PERMANENT dari database.');
                
        } catch (\Exception $e) {
            \Log::error('Permanent delete error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal permanent delete: ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk permanent delete.
     */
    public function bulkForceDelete(\Illuminate\Http\Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'siswa_ids' => 'required|array',
                'siswa_ids.*' => 'integer',
                'confirm_permanent' => 'required|accepted',
            ]);
            
            $count = $this->siswaService->bulkPermanentDelete($request->input('siswa_ids'));
            
            return redirect()
                ->route('siswa.deleted')
                ->with('success', "Berhasil PERMANENT DELETE {$count} siswa dari database.");
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal bulk permanent delete: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete selected siswa (Checkbox selection).
     */
    public function bulkDeleteSelection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => 'required|string',
            'alasan_keluar' => 'required|in:Alumni,Dikeluarkan,Pindah Sekolah,Lainnya',
            'keterangan_keluar' => 'nullable|string|max:500',
        ]);

        $ids = explode(',', $validated['ids']);
        $count = 0;

        foreach ($ids as $id) {
            $siswa = \App\Models\Siswa::find($id);
            if ($siswa) {
                $siswa->alasan_keluar = $validated['alasan_keluar'];
                $siswa->keterangan_keluar = $validated['keterangan_keluar'];
                $siswa->save();
                
                // Soft delete via service
                $this->siswaService->deleteSiswa($id);
                $count++;
            }
        }

        return redirect()
            ->route('siswa.index')
            ->with('success', "Berhasil menghapus {$count} siswa terpilih dengan alasan: {$validated['alasan_keluar']}.");
    }

    // ===================================================================
    // KENAIKAN KELAS / PINDAH KELAS FEATURE
    // ===================================================================

    /**
     * Tampilkan halaman transfer/pindah kelas.
     * 
     * Operator bisa memilih siswa dari satu kelas dan memindahkan
     * mereka ke kelas lain (kenaikan kelas, pindah konsentrasi, dll).
     * 
     * OPTIMIZED: Initial load tanpa siswa, siswa di-load via AJAX.
     */
    public function transferForm(Request $request): View
    {
        // Get all kelas for dropdowns (single optimized query)
        $allKelas = $this->siswaService->getAllKelas();

        return view('siswa.transfer', compact('allKelas'));
    }

    /**
     * API: Get siswa by kelas for transfer (AJAX).
     * 
     * Returns JSON with siswa list and kelas info for dynamic loading.
     */
    public function getTransferSiswa(Request $request)
    {
        $kelasId = $request->input('kelas_id');
        
        if (!$kelasId) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas ID diperlukan',
            ], 400);
        }

        // Get kelas info (single query with eager load)
        $kelas = \App\Models\Kelas::with('jurusan', 'waliKelas')
            ->find($kelasId);
        
        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan',
            ], 404);
        }

        // Get siswa with optimized query (total_poin calculated in subquery)
        $siswaList = $this->siswaService->getSiswaForTransfer((int) $kelasId);

        return response()->json([
            'success' => true,
            'kelas' => [
                'id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'jurusan' => $kelas->jurusan->nama_jurusan ?? '-',
                'wali_kelas' => $kelas->waliKelas->nama ?? '-',
                'jumlah_siswa' => $siswaList->count(),
            ],
            'siswa' => $siswaList->map(fn($s) => [
                'id' => $s->id,
                'nisn' => $s->nisn,
                'nama_siswa' => $s->nama_siswa,
                'nomor_hp_wali_murid' => $s->nomor_hp_wali_murid ?? '-',
                'total_poin' => (int) $s->total_poin,
            ])->values(),
        ]);
    }

    /**
     * Proses bulk transfer siswa ke kelas lain.
     * 
     * ALUR:
     * 1. Validasi input (siswa_ids, target_kelas_id)
     * 2. Panggil service->bulkTransferSiswa()
     * 3. Return dengan success/error message
     */
    public function bulkTransfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'integer|exists:siswa,id',
            'target_kelas_id' => 'required|integer|exists:kelas,id',
            'confirm_transfer' => 'required|accepted',
        ], [
            'siswa_ids.required' => 'Pilih minimal 1 siswa untuk dipindahkan.',
            'target_kelas_id.required' => 'Pilih kelas tujuan.',
            'confirm_transfer.accepted' => 'Anda harus mengkonfirmasi perpindahan kelas.',
        ]);

        try {
            $result = $this->siswaService->bulkTransferSiswa(
                $validated['siswa_ids'],
                $validated['target_kelas_id']
            );

            $message = "Berhasil memindahkan {$result['success_count']} siswa ke kelas {$result['target_kelas']}.";
            
            if ($result['failed_count'] > 0) {
                $message .= " {$result['failed_count']} siswa gagal dipindahkan (sudah di kelas tujuan atau tidak ditemukan).";
            }

            return redirect()
                ->route('siswa.transfer')
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Bulk transfer siswa error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal memindahkan siswa: ' . $e->getMessage())
                ->withInput();
        }
    }
}
