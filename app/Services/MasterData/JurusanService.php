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
        // STEP 1: Generate kode_jurusan if empty (lines 40-50)
        $kodeJurusan = $data->kode_jurusan;
        
        if (empty($kodeJurusan)) {
            $baseKode = $this->generateKode($data->nama_jurusan);
            $kodeJurusan = $this->jurusanRepository->generateUniqueKode($baseKode);
        }
        
        // STEP 2: Create jurusan (line 53)
        $jurusan = $this->jurusanRepository->create([
            'nama_jurusan' => $data->nama_jurusan,
            'kode_jurusan' => $kodeJurusan,
            'kaprodi_user_id' => $data->kaprodi_user_id,
        ]);
        
        // STEP 3: Auto-create Kaprodi if requested (lines 56-88)
        if ($data->create_kaprodi) {
            $this->createKaprodiUser($jurusan);
        }
        
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
            $this->jurusanRepository->update($jurusan, [
                'nama_jurusan' => $data->nama_jurusan,
                'kode_jurusan' => $kodeJurusan,
                'kaprodi_user_id' => $data->kaprodi_user_id,
            ]);
            
            // Refresh model to get updated data
            $jurusan->refresh();
            
            $newKode = $jurusan->kode_jurusan;
            
            // STEP 3: Propagate kode changes to kelas (lines 132-165)
            if ($newKode !== $oldKode) {
                $this->propagateKodeChangeToKelas($jurusan, $newKode);
            }
            
            // STEP 4: Update Kaprodi user if exists (lines 168-188)
            if ($jurusan->kaprodi_user_id) {
                $this->updateKaprodiUser($jurusan);
            } else {
                // STEP 5: Create Kaprodi if requested during update (lines 191-220)
                if ($data->create_kaprodi) {
                    $this->createKaprodiUser($jurusan, true); // true = from update
                }
            }
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
    
    /**
     * Create Kaprodi user for jurusan
     * 
     * EXACT LOGIC from JurusanController (lines 56-88 for store, 192-219 for update)
     * 
     * @param Jurusan $jurusan
     * @param bool $fromUpdate
     * @return User
     */
    private function createKaprodiUser(Jurusan $jurusan, bool $fromUpdate = false): User
    {
        // STEP 1: Generate clean kode (lines 57-62, 193-196)
        $kode = $jurusan->kode_jurusan ?? $this->generateKode($jurusan->nama_jurusan);
        $cleanKode = preg_replace('/[^a-z0-9]+/i', '', (string) $kode);
        $cleanKode = Str::lower($cleanKode);
        
        if ($cleanKode === '') {
            $cleanKode = Str::lower($this->generateKode($jurusan->nama_jurusan));
        }
        
        // STEP 2: Generate unique username (lines 63-69, 198-204)
        $baseUsername = 'kaprodi.' . $cleanKode;
        $username = $baseUsername;
        $i = 1;
        
        while (User::where('username', $username)->exists()) {
            $i++;
            $username = $baseUsername . $i;
        }
        
        // STEP 3: Generate password
        // STANDARDIZED: Always use predictable format (consistent for all conditions)
        $password = 'smkn1.kaprodi.' . $cleanKode;
        
        // STEP 4: Find Kaprodi role (lines 73, 208)
        $role = Role::findByName('Kaprodi');
        
        // STEP 5: Create user (lines 74-80, 209-215)
        $user = User::create([
            'role_id' => $role?->id,
            'nama' => 'Kaprodi ' . $jurusan->nama_jurusan,
            'username' => $username,
            'email' => $username . '@no-reply.local',
            'password' => $password,
        ]);
        
        // STEP 6: Link user to jurusan (lines 83-84, 217-218)
        $jurusan->kaprodi_user_id = $user->id;
        $jurusan->save();
        
        // STEP 7: Flash credentials for operator (lines 87, 219)
        session()->flash('kaprodi_created', [
            'username' => $username,
            'password' => $password
        ]);
        
        return $user;
    }
    
    /**
     * Update Kaprodi user when jurusan changes
     * 
     * EXACT LOGIC from JurusanController::update() (lines 168-188)
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
        
        // STEP 1: Generate clean kode (lines 171-176)
        $rawKode = $jurusan->kode_jurusan ?? $this->generateKode($jurusan->nama_jurusan);
        $cleanKode = preg_replace('/[^a-z0-9]+/i', '', (string) $rawKode);
        $cleanKode = Str::lower($cleanKode);
        
        if ($cleanKode === '') {
            $cleanKode = Str::lower($this->generateKode($jurusan->nama_jurusan));
        }
        
        // STEP 2: Generate new unique username (lines 177-183)
        $desiredBase = 'kaprodi.' . $cleanKode;
        $newUsername = $desiredBase;
        $i = 1;
        
        while (User::where('username', $newUsername)->where('id', '!=', $kaprodi->id)->exists()) {
            $i++;
            $newUsername = $desiredBase . $i;
        }
        
        // STEP 3: Update username & display name (lines 185-187)
        $kaprodi->username = $newUsername;
        $kaprodi->nama = 'Kaprodi ' . $jurusan->nama_jurusan;
        $kaprodi->save();
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
     * EXACT LOGIC from JurusanController::update() (lines 143-161)
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
        
        // STEP 1: Generate clean kode (lines 145-150)
        $tingkatShort = Str::lower($kelas->tingkat);
        $kodeSafe = preg_replace('/[^a-z0-9]+/i', '', (string) $newKode);
        $kodeSafe = Str::lower($kodeSafe);
        
        if ($kodeSafe === '') {
            $kodeSafe = Str::lower($this->generateKode($jurusan->nama_jurusan));
        }
        
        // STEP 2: Generate new unique username (lines 151-157)
        $baseWaliUsername = "walikelas.{$tingkatShort}.{$kodeSafe}{$seq}";
        $newWaliUsername = $baseWaliUsername;
        $j = 1;
        
        while (User::where('username', $newWaliUsername)->where('id', '!=', $wali->id)->exists()) {
            $j++;
            $newWaliUsername = $baseWaliUsername . $j;
        }
        
        // STEP 3: Update wali user (lines 158-160)
        $wali->username = $newWaliUsername;
        $wali->nama = 'Wali Kelas ' . $kelas->nama_kelas;
        $wali->save();
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
