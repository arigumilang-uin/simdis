<?php

namespace App\Services\MasterData;

use App\Data\MasterData\KelasData;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use App\Models\Role;
use App\Repositories\KelasRepository;
use Illuminate\Support\Str;

/**
 * Kelas Service
 * 
 * Purpose: Handle ALL business logic for Kelas management
 * Pattern: Service Layer
 * Responsibility: Business Logic ONLY (delegates data access to Repository)
 * 
 * CRITICAL: ALL logic from original KelasController preserved EXACTLY
 */
class KelasService
{
    public function __construct(
        private KelasRepository $kelasRepository
    ) {}
    
    /**
     * Get all kelas for index view
     */
    public function getAllKelas()
    {
        return $this->kelasRepository->getAllWithRelationships();
    }
    
    /**
     * Get data for create form
     */
    public function getDataForCreate(): array
    {
        return [
            'jurusanList' => Jurusan::orderBy('nama_jurusan')->get(),
            'waliList' => $this->kelasRepository->getAvailableWaliKelas(),
        ];
    }
    
    /**
     * Get data for edit form
     */
    public function getDataForEdit(Kelas $kelas): array
    {
        return [
            'kelas' => $kelas,
            'jurusanList' => Jurusan::orderBy('nama_jurusan')->get(),
            'waliList' => $this->kelasRepository->getAvailableWaliKelas(),
        ];
    }
    
    /**
     * Get kelas for show view
     */
    public function getKelas(int $id): ?Kelas
    {
        return $this->kelasRepository->getWithRelationships($id);
    }
    
    /**
     * Create new kelas with auto-generated nama_kelas and optional wali user creation
     * 
     * @param KelasData $data
     * @return array ['kelas' => Kelas, 'nama_kelas' => string]
     */
    public function createKelas(KelasData $data): array
    {
        // STEP 1: Get jurusan
        $jurusan = $this->kelasRepository->getJurusan($data->jurusan_id);
        
        // STEP 2: Determine kode - prioritize konsentrasi if selected
        $kode = $this->determineJurusanKode($jurusan);
        
        if ($data->konsentrasi_id) {
            // Use konsentrasi code if available
            $konsentrasi = \App\Models\Konsentrasi::find($data->konsentrasi_id);
            if ($konsentrasi) {
                $kode = $this->determineKonsentrasiKode($konsentrasi);
            }
        }
        
        // STEP 3: Generate base nama_kelas
        $base = $data->tingkat . ' ' . $kode;
        
        // STEP 4: Find next sequential number
        $next = $this->findNextSequentialNumber($jurusan->id, $base, $data->konsentrasi_id);
        
        // STEP 5: Set auto-generated nama_kelas
        $namaKelas = $base . ' ' . $next;
        
        // STEP 6: Disconnect wali kelas from other class if already assigned
        if ($data->wali_kelas_user_id) {
            $this->disconnectWaliFromOtherKelas($data->wali_kelas_user_id);
        }
        
        // STEP 7: Create kelas
        $kelas = $this->kelasRepository->create([
            'tingkat' => $data->tingkat,
            'jurusan_id' => $data->jurusan_id,
            'konsentrasi_id' => $data->konsentrasi_id,
            'wali_kelas_user_id' => $data->wali_kelas_user_id,
            'nama_kelas' => $namaKelas,
        ]);
        
        // STEP 8: Auto-create Wali Kelas user if requested
        if ($data->create_wali) {
            $this->createWaliKelasUser($kelas, $jurusan, $kode, $next);
        }
        
        return [
            'kelas' => $kelas,
            'nama_kelas' => $namaKelas,
        ];
    }
    
    /**
     * Update kelas with auto-regeneration and wali user sync
     * 
     * ENHANCED from original KelasController::update() (lines 146-199)
     * IMPROVEMENT: Auto-regenerate nama_kelas if tingkat or jurusan_id changes
     * 
     * @param Kelas $kelas
     * @param KelasData $data
     * @return Kelas
     */
    public function updateKelas(Kelas $kelas, KelasData $data): Kelas
    {
        // STEP 1: Store old values for change detection (lines 156-158)
        $oldNama = $kelas->nama_kelas;
        $oldTingkat = $kelas->tingkat;
        $oldJurusanId = $kelas->jurusan_id;
        
        // ENHANCEMENT: Auto-regenerate nama_kelas if tingkat or jurusan changes
        $namaKelas = $data->nama_kelas;
        
        if ($data->tingkat !== $oldTingkat || $data->jurusan_id !== $oldJurusanId) {
            // Regenerate nama_kelas based on new tingkat/jurusan
            $jurusan = $this->kelasRepository->getJurusan($data->jurusan_id);
            $kode = $this->determineJurusanKode($jurusan);
            
            // Extract current number from old nama_kelas
            $currentNumber = 1;
            if (preg_match('/\s(\d+)$/', $kelas->nama_kelas, $m)) {
                $currentNumber = intval($m[1]);
            }
            
        // Generate new nama_kelas with same number but new tingkat/kode
            $namaKelas = $data->tingkat . ' ' . $kode . ' ' . $currentNumber;
        }
        
        // STEP 2: Disconnect wali kelas from other class if changing to a different wali
        if ($data->wali_kelas_user_id && $data->wali_kelas_user_id !== $kelas->wali_kelas_user_id) {
            $this->disconnectWaliFromOtherKelas($data->wali_kelas_user_id, $kelas->id);
        }
        
        // STEP 3: Update kelas
        $this->kelasRepository->update($kelas, [
            'nama_kelas' => $namaKelas, // Use auto-generated if tingkat/jurusan changed
            'tingkat' => $data->tingkat,
            'jurusan_id' => $data->jurusan_id,
            'konsentrasi_id' => $data->konsentrasi_id,
            'wali_kelas_user_id' => $data->wali_kelas_user_id,
        ]);
        
        // Refresh to get updated data
        $kelas->refresh();
        
        // STEP 3: Sync wali kelas user if relevant fields changed (lines 163-196)
        if (($kelas->nama_kelas !== $oldNama) || 
            ($kelas->tingkat !== $oldTingkat) || 
            ($kelas->jurusan_id !== $oldJurusanId)) {
            
            if ($kelas->wali_kelas_user_id) {
                $this->updateWaliKelasUser($kelas);
            }
        }
        
        return $kelas->fresh();
    }
    
    /**
     * Delete kelas
     * 
     * EXACT LOGIC from KelasController::destroy() (lines 201-205)
     * 
     * @param Kelas $kelas
     * @return void
     */
    public function deleteKelas(Kelas $kelas): void
    {
        $this->kelasRepository->delete($kelas);
    }
    
    /**
     * Get kelas for monitoring view
     */
    public function getAllForMonitoring()
    {
        return $this->kelasRepository->getAllForMonitoring();
    }
    
    // ========================================================================
    // PRIVATE HELPER METHODS (Business Logic Extracted from Controller)
    // ========================================================================
    
    /**
     * Disconnect a wali kelas from any other class they're currently assigned to
     * 
     * This ensures a wali kelas can only be assigned to one class at a time.
     * When reassigning, the old class will have wali_kelas_user_id set to null.
     * 
     * @param int $waliUserId The wali kelas user ID to disconnect
     * @param int|null $excludeKelasId Exclude this kelas from disconnection (used in update)
     * @return void
     */
    private function disconnectWaliFromOtherKelas(int $waliUserId, ?int $excludeKelasId = null): void
    {
        $query = Kelas::where('wali_kelas_user_id', $waliUserId);
        
        // If updating a specific kelas, exclude it from disconnection
        if ($excludeKelasId !== null) {
            $query->where('id', '!=', $excludeKelasId);
        }
        
        // Disconnect wali from all other classes
        $query->update(['wali_kelas_user_id' => null]);
    }
    
    /**
     * Generate kode from nama (abbreviation logic)
     * 
     * EXACT LOGIC from KelasController::generateKode() (lines 24-37)
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
     * Determine jurusan kode (prefer kode_jurusan, fallback to abbreviation)
     * 
     * EXACT LOGIC from KelasController::store() (lines 56-69)
     * 
     * @param Jurusan $jurusan
     * @return string
     */
    private function determineJurusanKode(Jurusan $jurusan): string
    {
        $kode = null;
        
        // Check if kode_jurusan exists and has value
        if (array_key_exists('kode_jurusan', $jurusan->getAttributes()) && $jurusan->kode_jurusan) {
            $kode = $jurusan->kode_jurusan;
        } else {
            // Fallback: build abbreviation from nama_jurusan (take first letters of words, up to 3 chars)
            $words = preg_split('/\s+/', trim($jurusan->nama_jurusan));
            $abbr = '';
            
            foreach ($words as $w) {
                if ($w === '') continue;
                $abbr .= mb_strtoupper(mb_substr($w, 0, 1));
                if (mb_strlen($abbr) >= 3) break;
            }
            
            $kode = $abbr ?: strtoupper(substr(preg_replace('/[^A-Z]/', '', $jurusan->nama_jurusan), 0, 3));
        }
        
        return $kode;
    }
    
    /**
     * Determine konsentrasi kode (prefer kode_konsentrasi, fallback to abbreviation)
     * 
     * @param \App\Models\Konsentrasi $konsentrasi
     * @return string
     */
    private function determineKonsentrasiKode(\App\Models\Konsentrasi $konsentrasi): string
    {
        $kode = null;
        
        // Check if kode_konsentrasi exists and has value
        if ($konsentrasi->kode_konsentrasi) {
            $kode = $konsentrasi->kode_konsentrasi;
        } else {
            // Fallback: build abbreviation from nama_konsentrasi (take first letters of words, up to 3 chars)
            $words = preg_split('/\s+/', trim($konsentrasi->nama_konsentrasi));
            $abbr = '';
            
            foreach ($words as $w) {
                if ($w === '') continue;
                $abbr .= mb_strtoupper(mb_substr($w, 0, 1));
                if (mb_strlen($abbr) >= 3) break;
            }
            
            $kode = $abbr ?: strtoupper(substr(preg_replace('/[^A-Z]/', '', $konsentrasi->nama_konsentrasi), 0, 3));
        }
        
        return $kode;
    }
    
    /**
     * Find next sequential number for kelas nama
     * 
     * @param int $jurusanId
     * @param string $base
     * @param int|null $konsentrasiId
     * @return int
     */
    private function findNextSequentialNumber(int $jurusanId, string $base, ?int $konsentrasiId = null): int
    {
        // Find existing kelas with same base and extract numeric suffixes
        $existing = $this->kelasRepository->getExistingKelasNames($jurusanId, $base, $konsentrasiId);
        
        $max = 0;
        foreach ($existing as $name) {
            if (preg_match('/\s+(\d+)$/', $name, $m)) {
                $num = intval($m[1]);
                if ($num > $max) $max = $num;
            }
        }
        
        $next = $max + 1;
        
        return $next;
    }
    
    /**
     * Create Wali Kelas user for kelas
     * 
     * EXACT LOGIC from KelasController::store() (lines 93-126)
     * 
     * @param Kelas $kelas
     * @param Jurusan $jurusan
     * @param string $kode
     * @param int $next Sequential number
     * @return User
     */
    private function createWaliKelasUser(Kelas $kelas, Jurusan $jurusan, string $kode, int $next): User
    {
        // STEP 1: Generate username components (lines 95-101)
        $tingkat = Str::lower($kelas->tingkat);
        $kodeSafe = preg_replace('/[^a-z0-9]+/i', '', (string) $kode);
        $kodeSafe = Str::lower($kodeSafe);
        
        if ($kodeSafe === '') {
            $kodeSafe = Str::lower($this->generateKode($jurusan->nama_jurusan));
        }
        
        $nomor = $next; // seq yang sudah dihitung
        
        // STEP 2: Generate unique username (lines 102-108)
        $baseUsername = "walikelas.{$tingkat}.{$kodeSafe}{$nomor}";
        $username = $baseUsername;
        $i = 1;
        
        while (User::where('username', $username)->exists()) {
            $i++;
            $username = $baseUsername . $i;
        }
        
        // STEP 3: Generate password (line 111)
        // Standardized password
        $password = 'smkn1.walikelas.' . $tingkat . $kodeSafe . $nomor;
        
        // STEP 4: Find Wali Kelas role (line 112)
        $role = Role::findByName('Wali Kelas');
        
        // STEP 5: Create user (lines 113-119)
        $user = User::create([
            'role_id' => $role?->id,
            'nama' => 'Wali Kelas ' . $kelas->nama_kelas,
            'username' => $username,
            'email' => $username . '@no-reply.local',
            'password' => $password,
        ]);
        
        // STEP 6: Link user to kelas (lines 122-123)
        $kelas->wali_kelas_user_id = $user->id;
        $kelas->save();
        
        // STEP 7: Flash credentials for operator (line 125)
        session()->flash('wali_created', [
            'username' => $username,
            'password' => $password
        ]);
        
        return $user;
    }
    
    /**
     * Update Wali Kelas user when kelas changes
     * 
     * EXACT LOGIC from KelasController::update() (lines 165-194)
     * 
     * @param Kelas $kelas
     * @return void
     */
    private function updateWaliKelasUser(Kelas $kelas): void
    {
        $wali = User::find($kelas->wali_kelas_user_id);
        
        if (!$wali) {
            return;
        }
        
        // STEP 1: Get jurusan and kode (lines 168-169)
        $jurusan = $kelas->jurusan()->first();
        $kode = $jurusan?->kode_jurusan ?? '';
        
        // STEP 2: Extract nomor suffix from nama_kelas (lines 172-175)
        $nomor = 1;
        if (preg_match('/\s(\d+)$/', $kelas->nama_kelas, $m)) {
            $nomor = intval($m[1]);
        }
        
        // STEP 3: Generate username components (lines 177-182)
        $tingkat = Str::lower($kelas->tingkat);
        $kodeSafe = preg_replace('/[^a-z0-9]+/i', '', (string) $kode);
        $kodeSafe = Str::lower($kodeSafe);
        
        if ($kodeSafe === '') {
            $kodeSafe = Str::lower($this->generateKode($jurusan->nama_jurusan ?? ''));
        }
        
        // STEP 4: Generate new unique username (lines 183-189)
        $baseUsername = "walikelas.{$tingkat}.{$kodeSafe}{$nomor}";
        $newUsername = $baseUsername;
        $i = 1;
        
        while (User::where('username', $newUsername)->where('id', '!=', $wali->id)->exists()) {
            $i++;
            $newUsername = $baseUsername . $i;
        }
        
        // STEP 5: Update wali user (lines 191-193)
        $wali->username = $newUsername;
        $wali->nama = 'Wali Kelas ' . $kelas->nama_kelas;
        $wali->save();
    }
}
