<?php

namespace App\Services\MasterData;

use App\Data\MasterData\JenisPelanggaranData;
use App\Models\JenisPelanggaran;
use App\Repositories\JenisPelanggaranRepository;

/**
 * JenisPelanggaran Service
 * 
 * Purpose: Handle ALL business logic for JenisPelanggaran management
 * Pattern: Service Layer
 * Responsibility: Business Logic ONLY (delegates data access to Repository)
 * 
 * CRITICAL: ALL logic from original JenisPelanggaranController preserved EXACTLY
 */
class JenisPelanggaranService
{
    public function __construct(
        private JenisPelanggaranRepository $jenisPelanggaranRepository
    ) {}
    
    /**
     * Get paginated jenis pelanggaran with optional search
     * 
     * EXACT LOGIC from JenisPelanggaranController::index() (lines 23-34)
     */
    public function getPaginated(?string $searchTerm = null, int $perPage = 10)
    {
        return $this->jenisPelanggaranRepository->getPaginatedWithSearch($searchTerm, $perPage);
    }
    
    /**
     * Get data for create form
     */
    public function getDataForCreate(): array
    {
        return [
            'kategori' => $this->jenisPelanggaranRepository->getAllKategori(),
        ];
    }
    
    /**
     * Get data for edit form
     */
    public function getDataForEdit(int $id): array
    {
        $jenisPelanggaran = $this->jenisPelanggaranRepository->find($id);
        
        return [
            'jenisPelanggaran' => $jenisPelanggaran,
            'kategori' => $this->jenisPelanggaranRepository->getAllKategori(),
        ];
    }
    
    /**
     * Create new jenis pelanggaran with default values
     * 
     * EXACT LOGIC from JenisPelanggaranController::store() (lines 49-70)
     * 
     * @param JenisPelanggaranData $data
     * @return JenisPelanggaran
     */
    public function createJenisPelanggaran(JenisPelanggaranData $data): JenisPelanggaran
    {
        // STEP 1: Prepare data with defaults (lines 59-63)
        $createData = [
            'nama_pelanggaran' => $data->nama_pelanggaran,
            'kategori_id' => $data->kategori_id,
            'filter_category' => $data->filter_category,
            'keywords' => $data->keywords,
            // Default values (lines 62-63)
            'poin' => 0, // Poin akan diatur di frequency rules
            'has_frequency_rules' => false, // Belum ada rules
            'is_active' => false, // Nonaktif sampai ada rules
        ];
        
        // STEP 2: Create (line 65)
        $jenisPelanggaran = $this->jenisPelanggaranRepository->create($createData);
        
        return $jenisPelanggaran;
    }
    
    /**
     * Update existing jenis pelanggaran
     * 
     * EXACT LOGIC from JenisPelanggaranController::update() (lines 85-104)
     * 
     * @param int $id
     * @param JenisPelanggaranData $data
     * @return JenisPelanggaran
     */
    public function updateJenisPelanggaran(int $id, JenisPelanggaranData $data): JenisPelanggaran
    {
        // STEP 1: Find existing (line 95)
        $jenisPelanggaran = $this->jenisPelanggaranRepository->find($id);
        
        // STEP 2: Prepare data (line 97)
        $updateData = [
            'nama_pelanggaran' => $data->nama_pelanggaran,
            'kategori_id' => $data->kategori_id,
            'filter_category' => $data->filter_category,
            'keywords' => $data->keywords,
        ];
        
        // STEP 3: Update (line 99)
        $this->jenisPelanggaranRepository->update($jenisPelanggaran, $updateData);
        
        return $jenisPelanggaran->fresh();
    }
    
    /**
     * Delete jenis pelanggaran with protection
     * 
     * EXACT LOGIC from JenisPelanggaranController::destroy() (lines 110-121)
     * 
     * @param int $id
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteJenisPelanggaran(int $id): array
    {
        // STEP 1: Find (line 112)
        $jenisPelanggaran = $this->jenisPelanggaranRepository->find($id);
        
        // STEP 2: Check if has ACTIVE (non-deleted) riwayat records
        if ($this->jenisPelanggaranRepository->hasRiwayatRecords($jenisPelanggaran)) {
            return [
                'success' => false,
                'message' => 'Gagal hapus! Pelanggaran ini masih memiliki riwayat aktif. Silakan hapus/archive riwayat tersebut terlebih dahulu.'
            ];
        }
        
        // STEP 2.5: Permanent delete all soft-deleted riwayat (archive cleanup)
        // This is necessary because DB foreign key constraint doesn't respect soft deletes
        $jenisPelanggaran->riwayatPelanggaran()
            ->onlyTrashed()
            ->forceDelete();  // Permanent delete archived records
        
        
        // STEP 3: Delete with foreign key protection (line 119)
        try {
            $this->jenisPelanggaranRepository->delete($jenisPelanggaran->id);
            
            return [
                'success' => true,
                'message' => 'Jenis pelanggaran berhasil dihapus.'
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            // Catch foreign key constraint violations
            if ($e->getCode() === '23000') {
                return [
                    'success' => false,
                    'message' => 'Gagal hapus! Pelanggaran ini sudah tercatat di riwayat siswa. (Hanya boleh diedit)'
                ];
            }
            
            // Re-throw other exceptions
            throw $e;
        }
    }
}
