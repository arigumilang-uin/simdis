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
            'konsentrasiList' => $kelas->jurusan ? $kelas->jurusan->konsentrasi()->orderBy('nama_konsentrasi')->get() : [],
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
        
        // Determine Rombel Number
        $rombelSuffix = '';
        
        if ($data->rombel !== null) {
            // Manual rombel supplied (e.g. "none" for no number, "1" for 1)
            if ($data->rombel === 'none') {
                $rombelSuffix = '';
            } else {
                $rombelSuffix = $data->rombel !== '' ? ' ' . $data->rombel : '';
            }
        } else {
            // No rombel supplied (e.g. from bulk import or legacy call) -> Auto Number
            // STEP 4: Find next sequential number
            $next = $this->findNextSequentialNumber($jurusan->id, $base, $data->konsentrasi_id);
            $rombelSuffix = ' ' . $next;
        }
        
        // STEP 5: Set auto-generated nama_kelas
        $namaKelas = $base . $rombelSuffix;
        
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
            
            // Determine Rombel Number
            $rombelSuffix = '';
            
            // Priority 1: Use explicit rombel input from DTO if provided (including empty string)
            if ($data->rombel !== null) {
                // If rombel is "none", suffix is empty (X TKJ)
                if ($data->rombel === 'none') {
                    $rombelSuffix = '';
                } else {
                    $rombelSuffix = $data->rombel !== '' ? ' ' . $data->rombel : '';
                }
            } 
            // Priority 2: Fallback to extracting from existing name (Legacy behavior)
            else {
                if (preg_match('/\s(\d+)$/', $kelas->nama_kelas, $m)) {
                    $rombelSuffix = ' ' . $m[1];
                } else {
                    // If no number found in old name, default to 1 (legacy safety) or empty?
                    // Let's keep legacy safety of ' 1' if extraction fails but logic implies numbering
                    $rombelSuffix = ' 1';
                }
            }
            
            // Generate new nama_kelas
            $namaKelas = $data->tingkat . ' ' . $kode . $rombelSuffix;
        }
        
        // STEP 2: Disconnect wali kelas from other class if changing to a different wali
        if ($data->wali_kelas_user_id && $data->wali_kelas_user_id !== $kelas->wali_kelas_user_id) {
            $this->disconnectWaliFromOtherKelas($data->wali_kelas_user_id, $kelas->id);
        }
        
        // STEP 3: Update kelas
        
        // Prepare data
        $updateData = [
            'nama_kelas' => $namaKelas,
            'tingkat' => $data->tingkat,
            'jurusan_id' => $data->jurusan_id,
            'wali_kelas_user_id' => $data->wali_kelas_user_id,
        ];
        
        // Smart Logic for Konsentrasi:
        // Prevent accidental loss of konsentrasi if user forgot to select it while editing other fields
        // Only reset konsentrasi if Jurusan changed OR if user explicitly selected a new one
        if ($data->jurusan_id === $kelas->jurusan_id && $data->konsentrasi_id === null) {
            $updateData['konsentrasi_id'] = $kelas->konsentrasi_id;
        } else {
            $updateData['konsentrasi_id'] = $data->konsentrasi_id;
        }
        
        $this->kelasRepository->update($kelas->id, $updateData);
        
        // Nama wali kelas dihandle otomatis oleh KelasObserver
        // saat wali_kelas_user_id atau nama_kelas berubah
        
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
        // Validate: Cannot delete if has siswa
        if ($kelas->siswa()->exists()) {
            throw new \Exception("Gagal menghapus kelas. Masih terdapat " . $kelas->siswa()->count() . " siswa di kelas ini.");
        }

        // Logic demotion wali kelas handled by KelasObserver::deleted
        
        $this->kelasRepository->delete($kelas->id);
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
}

