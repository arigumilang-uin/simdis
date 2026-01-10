<?php

namespace App\Services\MasterData;

use App\Data\MasterData\JurusanData;
use App\Models\Jurusan;
use App\Models\User;
use App\Models\Role;
use App\Repositories\JurusanRepository;
use App\Services\User\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Jurusan Service
 * 
 * Purpose: Handle ALL business logic for Jurusan management
 * Pattern: Service Layer
 * Responsibility: Business Logic ONLY (delegates data access to Repository)
 * 
 * CRITICAL: ALL logic from original JurusanController preserved EXACTLY
 */
class JurusanService
{
    public function __construct(
        private JurusanRepository $jurusanRepository,
        private UserService $userService
    ) {}
    
    /**
     * Get all jurusan for index view
     */
    public function getAllJurusan()
    {
        return $this->jurusanRepository->getAllWithCounts();
    }
    
    /**
     * Get jurusan for show view
     */
    public function getJurusan(int $id): ?Jurusan
    {
        return $this->jurusanRepository->getWithRelationships($id);
    }
    
    /**
     * Create new jurusan with optional auto-create kaprodi
     * 
     * EXACT LOGIC from JurusanController::store() (lines 32-91)
     * 
     * @param JurusanData $data
     * @return Jurusan
     */
    public function createJurusan(JurusanData $data): Jurusan
    {
        // STEP 1: Generate kode_jurusan if empty
        $kodeJurusan = $data->kode_jurusan;
        
        if (empty($kodeJurusan)) {
            $baseKode = $this->generateKode($data->nama_jurusan);
            $kodeJurusan = $this->jurusanRepository->generateUniqueKode($baseKode);
        }
        
        // STEP 2: Create jurusan
        $jurusan = $this->jurusanRepository->create([
            'nama_jurusan' => $data->nama_jurusan,
            'kode_jurusan' => $kodeJurusan,
            'kaprodi_user_id' => $data->kaprodi_user_id,
        ]);
        
        // Auto-create kaprodi DIHAPUS - kaprodi harus dibuat manual via halaman User
        
        return $jurusan;
    }
    
    /**
     * Update jurusan with kode propagation and kaprodi sync
     * 
     * EXACT LOGIC from JurusanController::update() (lines 104-225)
     * 
     * @param Jurusan $jurusan
     * @param JurusanData $data
     * @return Jurusan
     */
    public function updateJurusan(Jurusan $jurusan, JurusanData $data): Jurusan
    {
        // STEP 1: Generate kode if empty (lines 113-123)
        $kodeJurusan = $data->kode_jurusan;
        
        if (empty($kodeJurusan)) {
            $baseKode = $this->generateKode($data->nama_jurusan);
            $kodeJurusan = $this->jurusanRepository->generateUniqueKode($baseKode, $jurusan->id);
        }
        
        // STEP 2: Execute update in transaction (lines 125-222)
        DB::transaction(function () use ($jurusan, $data, $kodeJurusan) {
            $oldKode = $jurusan->kode_jurusan;
            
            // Update jurusan (lines 127-128)
            $this->jurusanRepository->update($jurusan->id, [
                'nama_jurusan' => $data->nama_jurusan,
                'kode_jurusan' => $kodeJurusan,
                'kaprodi_user_id' => $data->kaprodi_user_id,
            ]);
            
            // Refresh model to get updated data
            $jurusan->refresh();
            
            $newKode = $jurusan->kode_jurusan;
            
            // STEP 3: Propagate kode changes to kelas
            if ($newKode !== $oldKode) {
                $this->propagateKodeChangeToKelas($jurusan, $newKode);
            }
            
            // STEP 4: Update Kaprodi name if exists (nama only, not username)
            if ($jurusan->kaprodi_user_id) {
                $this->updateKaprodiUser($jurusan);
            }
            // Auto-create kaprodi DIHAPUS - kaprodi harus dibuat manual via halaman User
        });
        
        return $jurusan->fresh();
    }
    
    /**
     * Delete jurusan with validation and cleanup
     * 
     * EXACT LOGIC from JurusanController::destroy() (lines 227-266)
     * 
     * @param Jurusan $jurusan
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteJurusan(Jurusan $jurusan): array
    {
        try {
            // STEP 1: Validate - prevent deletion if has kelas/siswa (lines 231-237)
            $counts = $this->jurusanRepository->getCounts($jurusan);
            
            if ($counts['kelas'] > 0 || $counts['siswa'] > 0) {
                return [
                    'success' => false,
                    'message' => "Tidak dapat menghapus jurusan yang memiliki kelas ({$counts['kelas']}) atau siswa ({$counts['siswa']})."
                ];
            }
            
            // STEP 2: Store kaprodi_user_id for cleanup (line 240)
            $kaprodiUserId = $jurusan->kaprodi_user_id;
            
            // STEP 3: Delete jurusan (line 243)
            $this->jurusanRepository->delete($jurusan);
            
            // STEP 4: Optional kaprodi user cleanup (lines 246-254)
            if ($kaprodiUserId) {
                $this->cleanupKaprodiUser($kaprodiUserId);
            }
            
            return [
                'success' => true,
                'message' => 'Jurusan berhasil dihapus.'
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error deleting jurusan: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal menghapus jurusan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get jurusan for monitoring views (Kepala Sekolah)
     */
    public function getAllForMonitoring()
    {
        return $this->jurusanRepository->getAllForMonitoring();
    }
    
    /**
     * Get jurusan for monitoring show view
     */
    public function getForMonitoringShow(int $id): ?Jurusan
    {
        return $this->jurusanRepository->getForMonitoringShow($id);
    }
    
    // ========================================================================
    // PRIVATE HELPER METHODS (Business Logic Extracted from Controller)
    // ========================================================================
    
    /**
     * Generate kode from nama_jurusan
     * 
     * EXACT LOGIC from JurusanController::generateKode() (lines 271-284)
     * 
     * @param string $nama
     * @return string
     */
    private function generateKode(string $nama): string
    {
        $words = preg_split('/\s+/', trim($nama));
        $letters = '';
        
        foreach ($words as $w) {
            if ($w === '') continue;
            $letters .= strtoupper(mb_substr($w, 0, 1));
            if (mb_strlen($letters) >= 3) break;
        }
        
        if ($letters === '') {
            $letters = 'JRS';
        }
        
        return $letters;
    }
    
    // createKaprodiUser DIHAPUS - kaprodi harus dibuat manual via halaman User
    
    /**
     * Update Kaprodi user when jurusan changes
     * 
     * UPDATED 2026-01-07: Username TIDAK diubah otomatis lagi
     * Hanya nama yang diupdate sesuai nama jurusan
     * 
     * @param Jurusan $jurusan
     * @return void
     */
    private function updateKaprodiUser(Jurusan $jurusan): void
    {
        $kaprodi = User::find($jurusan->kaprodi_user_id);
        
        if (!$kaprodi) {
            return;
        }
        
        // Hanya update nama, TIDAK username
        $newNama = 'Kaprodi ' . $jurusan->nama_jurusan;
        
        if ($kaprodi->nama !== $newNama) {
            $kaprodi->nama = $newNama;
            $kaprodi->save();
        }
    }
    
    /**
     * Propagate kode changes to all kelas and their wali kelas
     * 
     * EXACT LOGIC from JurusanController::update() (lines 133-164)
     * 
     * @param Jurusan $jurusan
     * @param string $newKode
     * @return void
     */
    private function propagateKodeChangeToKelas(Jurusan $jurusan, string $newKode): void
    {
        // STEP 1: Get kelas grouped by tingkat (line 133)
        $kelasByTingkat = $this->jurusanRepository->getKelasGroupedByTingkat($jurusan);
        
        // STEP 2: Iterate and update (lines 134-163)
        foreach ($kelasByTingkat as $tingkat => $kelasGroup) {
            $seq = 0;
            
            foreach ($kelasGroup as $kelas) {
                $seq++;
                
                // Update kelas nama (lines 138-139)
                $kelas->nama_kelas = trim($kelas->tingkat . ' ' . $newKode . ' ' . $seq);
                $kelas->save();
                
                // Update wali kelas if exists (lines 142-162)
                if ($kelas->wali_kelas_user_id) {
                    $this->updateWaliKelasUser($kelas, $jurusan, $newKode, $seq);
                }
            }
        }
    }
    
    /**
     * Update wali kelas user when kelas nama changes
     * 
     * UPDATED 2026-01-07: Username TIDAK diubah otomatis lagi
     * Hanya nama yang diupdate sesuai nama kelas
     * 
     * @param $kelas
     * @param Jurusan $jurusan
     * @param string $newKode
     * @param int $seq
     * @return void
     */
    private function updateWaliKelasUser($kelas, Jurusan $jurusan, string $newKode, int $seq): void
    {
        $wali = User::find($kelas->wali_kelas_user_id);
        
        if (!$wali) {
            return;
        }
        
        // Hanya update nama, TIDAK username
        $newNama = 'Wali Kelas ' . $kelas->nama_kelas;
        
        if ($wali->nama !== $newNama) {
            $wali->nama = $newNama;
            $wali->save();
        }
    }
    
    /**
     * Cleanup kaprodi user if not used by other jurusan
     * 
     * EXACT LOGIC from JurusanController::destroy() (lines 247-254)
     * 
     * @param int $kaprodiUserId
     * @return void
     */
    private function cleanupKaprodiUser(int $kaprodiUserId): void
    {
        $user = User::find($kaprodiUserId);
        
        if (!$user) {
            return;
        }
        
        // Check if this user is still kaprodi of any other jurusan
        $stillKaprodi = Jurusan::where('kaprodi_user_id', $kaprodiUserId)->exists();
        
        if (!$stillKaprodi) {
            $user->delete();
        }
    }
}
