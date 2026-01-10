<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Services\Siswa\SiswaTransferService;
use App\Services\Siswa\SiswaService;
use App\Exceptions\BusinessValidationException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Siswa Transfer Controller - Class Transfer/Kenaikan Kelas
 * 
 * RESPONSIBILITY: Handle kenaikan kelas / pindah kelas
 * - Display transfer form
 * - Process bulk transfer
 * - AJAX endpoints for siswa data
 * 
 * CLEAN ARCHITECTURE: Controller hanya sebagai kurir.
 * 
 * @package App\Http\Controllers\MasterData
 */
class SiswaTransferController extends Controller
{
    public function __construct(
        private SiswaTransferService $transferService,
        private SiswaService $siswaService
    ) {}

    /**
     * Tampilkan halaman transfer/pindah kelas.
     * 
     * OPTIMIZED: Initial load tanpa siswa, siswa di-load via AJAX.
     */
    public function index(Request $request): View
    {
        $allKelas = $this->transferService->getKelasForTransfer();
        
        return view('siswa.transfer', [
            'allKelas' => $allKelas,
            'selectedKelasId' => $request->input('kelas_id'),
        ]);
    }

    /**
     * API: Get siswa by kelas for transfer (AJAX).
     * 
     * Returns JSON with siswa list and kelas info for dynamic loading.
     */
    public function getSiswaByKelas(Request $request): JsonResponse
    {
        $kelasId = $request->input('kelas_id');
        
        if (!$kelasId) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas ID diperlukan',
            ]);
        }

        try {
            $siswa = $this->transferService->getSiswaForTransfer($kelasId);
            $kelas = \App\Models\Kelas::with(['jurusan', 'waliKelas'])->find($kelasId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'siswa' => $siswa,
                    'kelas' => $kelas,
                    'count' => $siswa->count(),
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data siswa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proses bulk transfer siswa ke kelas lain.
     */
    public function transfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswa,id',
            'target_kelas_id' => 'required|exists:kelas,id',
        ], [
            'siswa_ids.required' => 'Pilih minimal 1 siswa untuk dipindahkan.',
            'siswa_ids.min' => 'Pilih minimal 1 siswa untuk dipindahkan.',
            'target_kelas_id.required' => 'Pilih kelas tujuan.',
        ]);

        try {
            $result = $this->transferService->bulkTransferSiswa(
                $validated['siswa_ids'],
                $validated['target_kelas_id']
            );

            $targetKelas = \App\Models\Kelas::find($validated['target_kelas_id']);
            $message = "Berhasil memindahkan {$result['success_count']} siswa ke kelas {$targetKelas->nama_kelas}.";
            
            if ($result['failed_count'] > 0) {
                $message .= " {$result['failed_count']} siswa gagal dipindahkan.";
            }

            return redirect()
                ->route('siswa.transfer')
                ->with('success', $message);

        } catch (BusinessValidationException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Transfer Error', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Gagal memindahkan siswa: ' . $e->getMessage());
        }
    }
}
