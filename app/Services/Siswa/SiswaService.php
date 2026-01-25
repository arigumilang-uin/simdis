<?php

namespace App\Services\Siswa;

use App\Data\Siswa\SiswaData;
use App\Data\Siswa\SiswaFilterData;
use App\Repositories\Contracts\SiswaRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Exceptions\BusinessValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Siswa Service - Core CRUD Operations
 * 
 * RESPONSIBILITY: Core siswa CRUD and query operations ONLY
 * 
 * DELEGATIONS TO SUB-SERVICES:
 * - Bulk operations → SiswaBulkService
 * - Archive/Restore → SiswaArchiveService
 * - Transfer/Kenaikan → SiswaTransferService
 * - Wali management → SiswaWaliService
 * 
 * CLEAN ARCHITECTURE: Single Responsibility Principle
 * This service now focuses ONLY on core CRUD.
 * 
 * @package App\Services\Siswa
 */
class SiswaService
{
    public function __construct(
        private SiswaRepositoryInterface $siswaRepository,
        private UserRepositoryInterface $userRepository,
        private SiswaWaliService $waliService,
        private \App\Services\Pelanggaran\PelanggaranService $pelanggaranService
    ) {}

    // =====================================================================
    // CORE CRUD OPERATIONS
    // =====================================================================

    /**
     * Create new siswa.
     *
     * @param SiswaData $siswaData
     * @param bool $createWali
     * @return array ['siswa' => Siswa, 'wali_credentials' => array|null]
     */
    public function createSiswa(SiswaData $siswaData, bool $createWali = false): array
    {
        DB::beginTransaction();
        
        try {
            $waliCredentials = null;
            $waliMuridUserId = $siswaData->wali_murid_user_id;

            if ($createWali && !$waliMuridUserId) {
                $waliCredentials = $this->waliService->findOrCreateWaliByPhone(
                    $siswaData->nomor_hp_wali_murid,
                    $siswaData->nama_siswa,
                    $siswaData->nisn
                );
                
                $waliMuridUserId = $waliCredentials['user_id'];
            }

            $siswaArray = [
                'kelas_id' => $siswaData->kelas_id,
                'wali_murid_user_id' => $waliMuridUserId,
                'nisn' => $siswaData->nisn,
                'nama_siswa' => $siswaData->nama_siswa,
                'nomor_hp_wali_murid' => $siswaData->nomor_hp_wali_murid,
            ];

            $createdSiswa = $this->siswaRepository->create($siswaArray);

            DB::commit();

            return [
                'siswa' => $createdSiswa,
                'wali_credentials' => $waliCredentials,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw new BusinessValidationException('Gagal membuat siswa: ' . $e->getMessage());
        }
    }

    /**
     * Update siswa.
     *
     * @param int $siswaId
     * @param SiswaData $siswaData
     * @param bool $isWaliKelas
     * @return \App\Models\Siswa
     */
    public function updateSiswa(int $siswaId, SiswaData $siswaData, bool $isWaliKelas = false, bool $createWali = false): \App\Models\Siswa
    {
        $currentSiswa = $this->findSiswa($siswaId);
        
        // Determine initial Wali ID (from manual selection or existing)
        $waliIdToLink = $isWaliKelas ? $currentSiswa->wali_murid_user_id : $siswaData->wali_murid_user_id;
        $namaSiswa = $isWaliKelas ? $currentSiswa->nama_siswa : $siswaData->nama_siswa;
        $nisn = $isWaliKelas ? $currentSiswa->nisn : $siswaData->nisn;

        // Auto-sync Wali based on Phone Number
        if (!empty($siswaData->nomor_hp_wali_murid)) {
            $phoneClean = preg_replace('/\D+/', '', $siswaData->nomor_hp_wali_murid);
            
            if (!empty($phoneClean)) {
                // 1. Check if ANY Wali Murid account owns this phone number
                $existingWali = \App\Models\User::where('phone', $phoneClean)
                    ->whereHas('role', fn($q) => $q->where('nama_role', 'Wali Murid'))
                    ->first();

                if ($existingWali) {
                    // Match found: Always link to this existing account
                    $waliIdToLink = $existingWali->id;
                } elseif ($createWali) {
                    // No match + Checkbox ticked: Create NEW account
                    $result = $this->waliService->findOrCreateWaliByPhone($phoneClean, $namaSiswa, $nisn);
                    $waliIdToLink = $result['user_id'];
                    
                    if ($result['is_new']) {
                        session()->flash('wali_credentials', [$result]);
                    }
                }
            }
        }

        $updateData = $isWaliKelas
            ? [
                'nomor_hp_wali_murid' => $siswaData->nomor_hp_wali_murid,
                'wali_murid_user_id' => $waliIdToLink // Wali Kelas can update link logic implicitly
              ]
            : [
                'kelas_id' => $siswaData->kelas_id,
                'wali_murid_user_id' => $waliIdToLink,
                'nisn' => $siswaData->nisn,
                'nama_siswa' => $siswaData->nama_siswa,
                'nomor_hp_wali_murid' => $siswaData->nomor_hp_wali_murid,
            ];

        return $this->siswaRepository->update($siswaId, $updateData);
    }

    /**
     * Delete siswa (soft delete).
     *
     * @param int $siswaId
     * @param string|null $alasanKeluar
     * @param string|null $keteranganKeluar
     * @return bool
     */
    public function deleteSiswa(int $siswaId, ?string $alasanKeluar = null, ?string $keteranganKeluar = null): bool
    {
        $siswa = $this->siswaRepository->find($siswaId);
        
        if (!$siswa) {
            throw new BusinessValidationException('Siswa tidak ditemukan');
        }

        // Set alasan keluar
        if ($alasanKeluar) {
            $siswa->alasan_keluar = $alasanKeluar;
            $siswa->keterangan_keluar = $keteranganKeluar;
            $siswa->save();
        }

        // Soft delete related data handled by Observer
        // \App\Models\RiwayatPelanggaran::where('siswa_id', $siswaId)->delete();
        // \App\Models\TindakLanjut::where('siswa_id', $siswaId)->delete();

        return $this->siswaRepository->delete($siswaId);
    }

    // =====================================================================
    // QUERY OPERATIONS
    // =====================================================================

    /**
     * Find siswa by ID.
     */
    public function findSiswa(int $siswaId): ?\App\Models\Siswa
    {
        return $this->siswaRepository->find($siswaId);
    }

    /**
     * Find siswa by NISN.
     */
    public function findByNisn(string $nisn)
    {
        return $this->siswaRepository->findByNisn($nisn);
    }

    /**
     * Get filtered siswa with pagination.
     */
    public function getFilteredSiswa(SiswaFilterData $filters): LengthAwarePaginator
    {
        return $this->siswaRepository->filterAndPaginate($filters);
    }

    /**
     * Get IDs of filtered siswa (for bulk operations).
     */
    public function getSiswaIdsByFilter(SiswaFilterData $filters): array
    {
        return $this->siswaRepository->getIdsByFilter($filters);
    }

    /**
     * Get siswa by kelas.
     */
    public function getSiswaByKelas(int $kelasId)
    {
        return $this->siswaRepository->getByKelas($kelasId);
    }

    /**
     * Get siswa by jurusan.
     */
    public function getSiswaByJurusan(int $jurusanId)
    {
        return $this->siswaRepository->getByJurusan($jurusanId);
    }

    /**
     * Get siswa by wali murid.
     */
    public function getSiswaByWaliMurid(int $waliMuridId)
    {
        return $this->siswaRepository->getByWaliMurid($waliMuridId);
    }

    /**
     * Get siswa detail with statistics.
     */
    public function getSiswaDetail(int $siswaId): array
    {
        $siswa = $this->siswaRepository->findWithRelations($siswaId, [
            'kelas.jurusan',
            'waliMurid',
            'riwayatPelanggaran.jenisPelanggaran.kategoriPelanggaran',
            'tindakLanjut.suratPanggilan',
        ]);

        if (!$siswa) {
            throw new BusinessValidationException('Siswa tidak ditemukan');
        }

        // Calculate statistics
        $statistik = $this->pelanggaranService->getStatistikSiswa($siswaId);

        return [
            'siswa' => $siswa,
            'statistik' => $statistik,
        ];
    }

    /**
     * Get siswa for edit form.
     */
    public function getSiswaForEdit(int $siswaId): \App\Models\Siswa
    {
        return $this->siswaRepository->findWithRelations($siswaId, ['kelas', 'waliMurid']);
    }

    // =====================================================================
    // MASTER DATA HELPERS
    // =====================================================================

    /**
     * Get all jurusan for filter dropdown.
     */
    public function getAllJurusanForFilter()
    {
        return DB::table('jurusan')
            ->select('id', 'nama_jurusan', 'kode_jurusan')
            ->orderBy('nama_jurusan')
            ->get();
    }

    /**
     * Get all konsentrasi for filter dropdown.
     */
    public function getAllKonsentrasiForFilter()
    {
        return DB::table('konsentrasi')
            ->select('id', 'nama_konsentrasi')
            ->orderBy('nama_konsentrasi')
            ->get();
    }

    /**
     * Get all kelas for filter dropdown.
     */
    public function getAllKelasForFilter()
    {
        return DB::table('kelas')
            ->select('id', 'nama_kelas')
            ->orderBy('nama_kelas')
            ->get();
    }

    /**
     * Get konsentrasi by jurusan ID for Kaprodi filter.
     */
    public function getKonsentrasiByJurusan(int $jurusanId)
    {
        return DB::table('konsentrasi')
            ->select('id', 'nama_konsentrasi')
            ->where('jurusan_id', $jurusanId)
            ->orderBy('nama_konsentrasi')
            ->get();
    }

    /**
     * Get kelas by jurusan ID for Kaprodi filter.
     */
    public function getKelasByJurusan(int $jurusanId)
    {
        return DB::table('kelas')
            ->select('id', 'nama_kelas')
            ->where(function($query) use ($jurusanId) {
                $query->where('jurusan_id', $jurusanId)
                      ->orWhere('konsentrasi_id', $jurusanId);
            })
            ->orderBy('nama_kelas')
            ->get();
    }

    /**
     * Get all kelas with details.
     */
    public function getAllKelas()
    {
        return \App\Models\Kelas::with('jurusan')
            ->withCount('siswa')
            ->orderBy('nama_kelas')
            ->get();
    }

    /**
     * Get available wali murid for selection.
     */
    public function getAvailableWaliMurid()
    {
        return $this->waliService->getAvailableWaliMurid();
    }

    // =====================================================================
    // DELEGATION TO SUB-SERVICES (Backward Compatibility)
    // =====================================================================

    /**
     * @deprecated Use SiswaWaliService instead
     */
    public function findOrCreateWaliByPhone(string $nomorHp, string $namaSiswa, ?string $nisn = null): array
    {
        return $this->waliService->findOrCreateWaliByPhone($nomorHp, $namaSiswa, $nisn);
    }

    /**
     * @deprecated Use SiswaBulkService instead
     */
    public function bulkCreateSiswa(array $rows, int $kelasId, bool $createWaliAll = false): array
    {
        return app(SiswaBulkService::class)->bulkCreateSiswa($rows, $kelasId, $createWaliAll);
    }

    /**
     * @deprecated Use SiswaBulkService instead
     */
    public function bulkDeleteByKelas(int $kelasId, array $options = []): array
    {
        return app(SiswaBulkService::class)->bulkDeleteByKelas($kelasId, $options);
    }

    /**
     * @deprecated Use SiswaBulkService instead
     */
    public function processBulkCreate(string $dataType, mixed $data, int $kelasId, bool $createWaliAll = false): array
    {
        return app(SiswaBulkService::class)->processBulkCreate($dataType, $data, $kelasId, $createWaliAll);
    }

    /**
     * @deprecated Use SiswaArchiveService instead
     */
    public function getDeletedSiswa(array $filters = [])
    {
        return app(SiswaArchiveService::class)->getDeletedSiswa($filters);
    }

    /**
     * @deprecated Use SiswaArchiveService instead
     */
    public function restoreSiswa(int $siswaId): \App\Models\Siswa
    {
        return app(SiswaArchiveService::class)->restoreSiswa($siswaId);
    }

    /**
     * @deprecated Use SiswaArchiveService instead
     */
    public function permanentDeleteSiswa(int $siswaId): bool
    {
        return app(SiswaArchiveService::class)->permanentDeleteSiswa($siswaId);
    }

    /**
     * @deprecated Use SiswaTransferService instead
     */
    public function bulkTransferSiswa(array $siswaIds, int $targetKelasId): array
    {
        return app(SiswaTransferService::class)->bulkTransferSiswa($siswaIds, $targetKelasId);
    }

    /**
     * @deprecated Use SiswaTransferService instead
     */
    public function getSiswaForTransfer(?int $kelasId = null)
    {
        return app(SiswaTransferService::class)->getSiswaForTransfer($kelasId);
    }
}