<?php

namespace App\Http\Controllers\Pelanggaran;

use App\Http\Controllers\Controller;
use App\Services\Pelanggaran\PelanggaranService;
use App\Data\Pelanggaran\RiwayatPelanggaranData;
use App\Data\Pelanggaran\RiwayatPelanggaranFilterData;
use App\Http\Requests\Pelanggaran\CatatPelanggaranRequest;
use App\Http\Requests\Pelanggaran\UpdatePelanggaranRequest;
use App\Http\Requests\Pelanggaran\FilterRiwayatRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Riwayat Pelanggaran Controller - Clean Architecture Pattern
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
 * - TIDAK BOLEH ada query database complex
 * - TIDAK BOLEH ada manipulasi data
 * - Target: < 20 baris per method
 * - TIDAK BOLEH inject RulesEngine (hanya Service!)
 */
class RiwayatPelanggaranController extends Controller
{
    /**
     * Inject PelanggaranService via constructor.
     * 
     * CRITICAL: Inject SERVICE, bukan RulesEngine!
     * Controller tidak perlu tahu tentang internal business logic.
     *
     * @param PelanggaranService $pelanggaranService
     */
    public function __construct(
        private PelanggaranService $pelanggaranService
    ) {}

    /**
     * Tampilkan daftar riwayat pelanggaran dengan filter.
     * 
     * ROLE-BASED ACCESS:
     * - Kepala Sekolah, Operator, Waka Kesiswaan: Lihat SEMUA riwayat
     * - Wali Kelas: Lihat riwayat siswa di kelasnya saja
     * - Kaprodi: Lihat riwayat siswa di jurusannya saja
     * 
     * ALUR:
     * 1. Validasi filter (via FilterRiwayatRequest)
     * 2. Apply role-based scope
     * 3. Convert ke RiwayatPelanggaranFilterData (DTO)
     * 4. Panggil service untuk data dan master data
     * 5. Return view
     */
    public function index(FilterRiwayatRequest $request): View
    {
        $user = auth()->user();
        $filterData = $request->getFilterData();

        // ROLE-BASED SCOPE: Apply filter berdasarkan role
        if ($user->hasRole('Wali Kelas')) {
            // Wali Kelas: hanya siswa di kelasnya
            $kelas = \App\Models\Kelas::where('wali_kelas_user_id', $user->id)->first();
            if ($kelas) {
                $filterData['kelas_id'] = $kelas->id;
            } else {
                // Jika tidak ada kelas, set filter impossible
                $filterData['kelas_id'] = -1;
            }
        } elseif ($user->hasRole('Kaprodi')) {
            // Kaprodi: hanya siswa di jurusannya
            $jurusan = \App\Models\Jurusan::where('kaprodi_user_id', $user->id)->first();
            if ($jurusan) {
                $filterData['jurusan_id'] = $jurusan->id;
            } else {
                // Jika tidak ada jurusan, set filter impossible
                $filterData['jurusan_id'] = -1;
            }
        }
        // Kepala Sekolah, Operator, Waka Kesiswaan: tidak ada filter tambahan (lihat semua)

        // Convert validated request data ke DTO
        $filters = RiwayatPelanggaranFilterData::from($filterData);

        // Panggil service untuk get filtered riwayat
        $riwayat = $this->pelanggaranService->getFilteredRiwayat($filters);

        // Return partial view if AJAX request
        if ($request->ajax() || $request->has('render_partial')) {
            return view('riwayat._table', compact('riwayat'));
        }

        // Panggil service untuk master data dropdown filter
        $allJurusan = $this->pelanggaranService->getAllJurusanForFilter();
        $allKelas = $this->pelanggaranService->getAllKelasForFilter();
        $allPelanggaran = $this->pelanggaranService->getActiveJenisPelanggaran();

        return view('riwayat.index', compact('riwayat', 'allJurusan', 'allKelas', 'allPelanggaran'));
    }

    /**
     * Tampilkan form create pelanggaran.
     * 
     * ALUR:
     * 1. Panggil service untuk master data (siswa, pelanggaran, kelas, jurusan)
     * 2. Apply role-based filter untuk siswa
     * 3. Return view
     */
    public function create(): View
    {
        $user = auth()->user();
        
        // Get data dengan role-based filter
        $daftarSiswa = $this->pelanggaranService->getAllSiswaForCreate($user->id);
        $daftarPelanggaran = $this->pelanggaranService->getActiveJenisPelanggaran();
        $jurusan = $this->pelanggaranService->getAllJurusanForFilter();
        $kelas = $this->pelanggaranService->getAllKelasForFilter();
        
        return view('riwayat.create', compact('daftarSiswa', 'daftarPelanggaran', 'jurusan', 'kelas'));
    }

    /**
     * Simpan pelanggaran baru.
     * 
     * ALUR:
     * 1. Validasi (via CatatPelanggaranRequest)
     * 2. Handle file upload jika ada
     * 3. Convert ke RiwayatPelanggaranData (DTO)
     * 4. Panggil service->catatPelanggaran()
     * 5. Redirect dengan success message
     */
    public function store(CatatPelanggaranRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // Handle file upload
        $buktiFotoPath = null;
        if ($request->hasFile('bukti_foto')) {
            $buktiFotoPath = $request->file('bukti_foto')
                ->store('bukti_pelanggaran', 'public');
        }

        // Get combined datetime once
        $combinedDateTime = $request->getCombinedDateTime();

        // Counter for success message
        $totalRecorded = 0;

        $savedResults = [];

        try {
            // Loop through each selected siswa
            foreach ($request->siswa_id as $siswaId) {
                $siswaObj = \App\Models\Siswa::find($siswaId);

                // Loop through each selected jenis pelanggaran
                foreach ($request->jenis_pelanggaran_id as $jenisPelanggaranId) {
                    $jenisObj = \App\Models\JenisPelanggaran::find($jenisPelanggaranId);

                    // Create DTO for this combination
                    $riwayatData = RiwayatPelanggaranData::from([
                        'id' => null,
                        'siswa_id' => $siswaId,
                        'jenis_pelanggaran_id' => $jenisPelanggaranId,
                        'guru_pencatat_user_id' => $request->guru_pencatat_user_id,
                        'tanggal_kejadian' => $combinedDateTime,
                        'keterangan' => $request->keterangan,
                        'bukti_foto_path' => $buktiFotoPath,
                    ]);

                    // Call service to record this violation
                    // Service will: save data + call RulesEngine + create tindak lanjut if needed
                    $this->pelanggaranService->catatPelanggaran($riwayatData);

                    $totalRecorded++;
                    
                    // Collect saved data for response
                    if ($siswaObj && $jenisObj) {
                        $savedResults[] = [
                            'siswa_nama' => $siswaObj->nama_siswa,
                            'pelanggaran_nama' => $jenisObj->nama_pelanggaran,
                            'poin' => $jenisObj->poin
                        ];
                    }
                }
            }

            // Success - redirect with success message
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Berhasil mencatat {$totalRecorded} pelanggaran.",
                    'total' => $totalRecorded,
                    'data' => $savedResults
                ]);
            }

            return redirect()
                ->route('riwayat.create')
                ->with('success', "Berhasil mencatat {$totalRecorded} pelanggaran.");
                
        } catch (\App\Exceptions\BusinessValidationException $e) {
            // Business validation error - show user friendly message
            \Log::error('Gagal mencatat pelanggaran (Business)', [
                'error' => $e->getMessage(),
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
                
        } catch (\Exception $e) {
            // Unexpected error - log and show generic message
            \Log::error('Gagal mencatat pelanggaran (Unexpected)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat mencatat pelanggaran. Silakan coba lagi atau hubungi administrator.'
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan saat mencatat pelanggaran. Silakan coba lagi atau hubungi administrator.']);
        }
    }

    /**
     * Tampilkan form edit pelanggaran.
     * 
     * ALUR:
     * 1. Panggil service untuk get riwayat dengan relationships
     * 2. Panggil service untuk master data
     * 3. Return view
     * 
     * NOTE: Parameter name can be 'id' or 'riwayat' depending on route
     * Laravel will inject the correct value based on route parameter name
     */
    public function edit(int $riwayat): View
    {
        // Panggil service untuk get riwayat dengan relationships
        $riwayatData = $this->pelanggaranService->getRiwayatForEdit($riwayat);
        $jenisPelanggaran = $this->pelanggaranService->getActiveJenisPelanggaran();

        return view('riwayat.edit', [
            'riwayat' => $riwayatData,
            'jenisPelanggaran' => $jenisPelanggaran,
        ]);
    }

    /**
     * Update pelanggaran.
     * 
     * ALUR:
     * 1. Validasi + Authorization (via UpdatePelanggaranRequest)
     * 2. Panggil service untuk get existing record
     * 3. Handle file upload jika ada
     * 4. Convert ke RiwayatPelanggaranData (DTO)
     * 5. Panggil service->updatePelanggaran()
     * 6. Redirect dengan success message
     * 
     * NOTE: Parameter name can be 'id' or 'riwayat' depending on route
     */
    public function update(UpdatePelanggaranRequest $request, int $riwayat): RedirectResponse
    {
        // Panggil service untuk get existing record
        $existingRiwayat = $this->pelanggaranService->getRiwayatById($riwayat);
        $oldBuktiFotoPath = $existingRiwayat->bukti_foto_path;

        // Handle file upload
        $buktiFotoPath = null;
        if ($request->hasFile('bukti_foto')) {
            $buktiFotoPath = $request->file('bukti_foto')
                ->store('bukti_pelanggaran', 'public');
        }

        // Convert validated request ke DTO
        $riwayatData = RiwayatPelanggaranData::from([
            'id' => $riwayat,
            'siswa_id' => $existingRiwayat->siswa_id,
            'jenis_pelanggaran_id' => $request->jenis_pelanggaran_id,
            'guru_pencatat_user_id' => $existingRiwayat->guru_pencatat_user_id,
            'tanggal_kejadian' => $request->getCombinedDateTime(),
            'keterangan' => $request->keterangan,
            'bukti_foto_path' => $buktiFotoPath ?? $oldBuktiFotoPath,
        ]);

        // Panggil service
        // Service akan: update data + reconcile tindak lanjut (poin/frekuensi berubah)
        $this->pelanggaranService->updatePelanggaran(
            $riwayat,
            $riwayatData,
            $buktiFotoPath ? $oldBuktiFotoPath : null
        );

        return redirect()
            ->route('riwayat.index')
            ->with('success', 'Riwayat pelanggaran berhasil diperbarui.');
    }

    /**
     * Hapus pelanggaran.
     * 
     * ALUR:
     * 1. Panggil service untuk get riwayat
     * 2. Authorization manual (karena tidak ada FormRequest untuk delete)
     * 3. Panggil service->deletePelanggaran()
     * 4. Service akan reconcile tindak lanjut
     * 5. Redirect dengan success message
     * 
     * NOTE: Parameter name can be 'id' or 'riwayat' depending on route
     */
    public function destroy(int $riwayat): RedirectResponse
    {
        $riwayatData = $this->pelanggaranService->getRiwayatById($riwayat);

        // Authorization via Policy
        $this->authorize('delete', $riwayatData);

        try {
            // Panggil service
            // Service akan validate active case, hapus record, dan reconcile
            $this->pelanggaranService->deletePelanggaran(
                $riwayat,
                $riwayatData->siswa_id,
                $riwayatData->bukti_foto_path
            );
            
            return redirect()
                ->route('riwayat.index')
                ->with('success', 'Riwayat pelanggaran berhasil dihapus.');

        } catch (\App\Exceptions\BusinessValidationException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilkan riwayat yang dicatat oleh user saat ini.
     * 
     * REUSABILITY: Method ini REUSES logic dari index()
     * - Menggunakan service method yang SAMA: getFilteredRiwayat()
     * - Perbedaan: Inject guru_pencatat_user_id untuk non-operator
     * - Operator: Lihat SEMUA riwayat (sama seperti index)
     * - Non-Operator: Lihat HANYA riwayat yang mereka catat
     * 
     * DRY PRINCIPLE: Don't Repeat Yourself
     * Tidak ada duplikasi logic, hanya perbedaan filter scope.
     */
    public function myIndex(FilterRiwayatRequest $request): View
    {
        $user = auth()->user();

        // Build filter dengan scope berdasarkan role
        $filterData = $request->getFilterData();
        
        // LOGIC REUSE: Inject user_id filter untuk non-operator
        // Operator tidak perlu filter ini (lihat semua)
        if (!$user->hasRole('Operator Sekolah')) {
            $filterData['guru_pencatat_user_id'] = $user->id;
        }

        // REUSE: Panggil service method yang SAMA seperti index()
        $filters = RiwayatPelanggaranFilterData::from($filterData);
        $riwayat = $this->pelanggaranService->getFilteredRiwayat($filters);

        return view('riwayat.my_index', compact('riwayat'));
    }

    /**
     * AJAX search endpoint for Siswa Dropdown.
     */
    public function ajaxSearchSiswa(\Illuminate\Http\Request $request) 
    {
        $query = $request->input('q');
        $data = $this->pelanggaranService->searchSiswa($query);
        return response()->json($data);
    }

    /**
     * AJAX search endpoint for Pelanggaran Dropdown.
     */
    public function ajaxSearchPelanggaran(\Illuminate\Http\Request $request) 
    {
        $query = $request->input('q');
        $data = $this->pelanggaranService->searchPelanggaran($query);
        return response()->json($data);
    }

    /**
     * Bulk delete selected riwayat pelanggaran.
     */
    public function bulkDelete(\Illuminate\Http\Request $request): RedirectResponse
    {
        // Parse ids from comma-separated string to array (same pattern as SiswaBulkController)
        $idsRaw = $request->input('ids');
        $riwayatIds = is_array($idsRaw) ? $idsRaw : array_filter(explode(',', $idsRaw ?? ''));
        $riwayatIds = array_map('intval', $riwayatIds);

        if (empty($riwayatIds)) {
            return back()->with('error', 'Pilih minimal 1 data untuk dihapus.');
        }

        try {
            $successCount = 0;
            $failedCount = 0;
            
            foreach ($riwayatIds as $riwayatId) {
                try {
                    $riwayatData = $this->pelanggaranService->getRiwayatById($riwayatId);
                    
                    if (!$riwayatData) {
                        $failedCount++;
                        continue;
                    }
                    
                    // Authorization via Policy
                    $this->authorize('delete', $riwayatData);
                    
                    // Delete via service
                    $this->pelanggaranService->deletePelanggaran(
                        $riwayatId,
                        $riwayatData->siswa_id,
                        $riwayatData->bukti_foto_path ?? null
                    );
                    $successCount++;
                } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                    $failedCount++;
                    \Log::warning('Bulk delete authorization failed', ['id' => $riwayatId]);
                } catch (\Exception $e) {
                    $failedCount++;
                    \Log::warning('Failed to delete riwayat', ['id' => $riwayatId, 'error' => $e->getMessage()]);
                }
            }
            
            $message = "Berhasil menghapus {$successCount} data pelanggaran.";
            if ($failedCount > 0) {
                $message .= " {$failedCount} data gagal dihapus (tidak ada izin atau error).";
            }
            
            return redirect()
                ->route('riwayat.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Bulk delete riwayat error', ['error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
