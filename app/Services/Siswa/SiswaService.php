<?php

namespace App\Services\Siswa;

use App\Data\Siswa\SiswaData;
use App\Data\Siswa\SiswaFilterData;
use App\Data\User\UserData;
use App\Repositories\Contracts\SiswaRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\User\UserNamingService;
use App\Models\Role;
use App\Exceptions\BusinessValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SiswaService
{
    public function __construct(
        private SiswaRepositoryInterface $siswaRepository,
        private UserRepositoryInterface $userRepository,
        private \App\Services\Pelanggaran\PelanggaranService $pelanggaranService
    ) {}

    public function createSiswa(SiswaData $siswaData, bool $createWali = false): array
    {
        DB::beginTransaction();
        
        try {
            $waliCredentials = null;
            $waliMuridUserId = $siswaData->wali_murid_user_id;

            if ($createWali && !$waliMuridUserId) {
                $waliCredentials = $this->findOrCreateWaliByPhone(
                    $siswaData->nomor_hp_wali_murid,
                    $siswaData->nama_siswa,
                    $siswaData->nisn  // Add NISN for username
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
                'siswa' => SiswaData::from($createdSiswa),
                'wali_credentials' => $waliCredentials,
            ];

        } catch (BusinessValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new BusinessValidationException(
                'Gagal membuat data siswa: ' . $e->getMessage()
            );
        }
    }

    public function updateSiswa(int $siswaId, SiswaData $siswaData, bool $isWaliKelas = false): SiswaData
    {
        $updateData = [];

        if ($isWaliKelas) {
            $updateData = [
                'nomor_hp_wali_murid' => $siswaData->nomor_hp_wali_murid,
            ];
        } else {
            $updateData = [
                'kelas_id' => $siswaData->kelas_id,
                'wali_murid_user_id' => $siswaData->wali_murid_user_id,
                'nisn' => $siswaData->nisn,
                'nama_siswa' => $siswaData->nama_siswa,
                'nomor_hp_wali_murid' => $siswaData->nomor_hp_wali_murid,
            ];
        }

        $updatedSiswa = $this->siswaRepository->update($siswaId, $updateData);
        
        return SiswaData::from($updatedSiswa);
    }

    public function deleteSiswa(int $siswaId, ?string $alasanKeluar = null, ?string $keteranganKeluar = null): bool
    {
        $siswa = \App\Models\Siswa::find($siswaId);
        
        if (!$siswa) {
    return false;
}

    // Set alasan keluar before soft delete
       if ($alasanKeluar) {
           $siswa->alasan_keluar = $alasanKeluar;
           $siswa->keterangan_keluar = $keteranganKeluar;
    $siswa->save();
}

$waliId = $siswa->wali_murid_user_id;
        
        // Delete siswa first
        $deleted = $this->siswaRepository->delete($siswaId);
        
        // Check if wali should be deleted (orphaned check)
        if ($deleted && $waliId) {
            $hasOtherSiswa = \App\Models\Siswa::where('wali_murid_user_id', $waliId)
                ->exists();
                
            if (!$hasOtherSiswa) {
                // Wali is orphaned, delete it
                \App\Models\User::where('id', $waliId)->delete();
            }
        }
        
        return $deleted;
    }

    public function findSiswa(int $siswaId): ?SiswaData
    {
        $siswa = $this->siswaRepository->find($siswaId);
        return $siswa ? SiswaData::from($siswa) : null;
    }

    public function findByNisn(string $nisn): ?SiswaData
    {
        return $this->siswaRepository->findByNisn($nisn);
    }

    public function getFilteredSiswa(SiswaFilterData $filters): LengthAwarePaginator
    {
        return $this->siswaRepository->filterAndPaginate($filters);
    }

    public function getSiswaByKelas(int $kelasId)
    {
        return $this->siswaRepository->findByKelas($kelasId);
    }

    public function getSiswaByJurusan(int $jurusanId)
    {
        return $this->siswaRepository->findByJurusan($jurusanId);
    }

    public function getSiswaByWaliMurid(int $waliMuridId)
    {
        return $this->siswaRepository->findByWaliMurid($waliMuridId);
    }

    public function getSiswaDetail(int $siswaId): array
    {
        $siswa = \App\Models\Siswa::with([
            'kelas.jurusan.kaprodi',
            'kelas.waliKelas',
            'waliMurid',
            'riwayatPelanggaran.jenisPelanggaran.kategoriPelanggaran',
            'riwayatPelanggaran.guruPencatat',
            'tindakLanjut'
        ])->findOrFail($siswaId);

        $totalPoin = $this->pelanggaranService->calculateTotalPoin($siswaId);

        $pembinaanRekomendasi = $this->pelanggaranService->getStatistikSiswa($siswaId)['pembinaan_rekomendasi'] ?? [
            'pembina_roles' => [],
            'keterangan' => '',
            'range_text' => '',
        ];

        // Get pembinaan status aktif untuk siswa ini (Perlu Pembinaan atau Sedang Dibina)
        $pembinaanAktif = \App\Models\PembinaanStatus::forSiswa($siswaId)
            ->active()
            ->with(['rule', 'dibinaOleh'])
            ->latest()
            ->first();

        // Get pembinaan terakhir yang SELESAI untuk siswa ini (untuk ditampilkan di profil)
        $pembinaanSelesai = null;
        if (!$pembinaanAktif) {
            // Find rule yang match dengan poin saat ini
            $matchingRule = \App\Models\PembinaanInternalRule::orderBy('display_order')
                ->get()
                ->first(fn($rule) => $rule->matchesPoin($totalPoin));
            
            if ($matchingRule) {
                $pembinaanSelesai = \App\Models\PembinaanStatus::forSiswa($siswaId)
                    ->forRule($matchingRule->id)
                    ->completed()
                    ->with(['rule', 'dibinaOleh', 'diselesaikanOleh'])
                    ->latest('selesai_at')
                    ->first();
            }
        }

        // Jika sudah ada pembinaan selesai untuk range ini, kosongkan rekomendasi
        if ($pembinaanSelesai) {
            $pembinaanRekomendasi = [
                'pembina_roles' => [],
                'keterangan' => '',
                'range_text' => '',
            ];
        }

        return [
            'siswa' => $siswa,
            'totalPoin' => $totalPoin,
            'pembinaanRekomendasi' => $pembinaanRekomendasi,
            'pembinaanAktif' => $pembinaanAktif,
            'pembinaanSelesai' => $pembinaanSelesai,
        ];
    }

    public function getSiswaForEdit(int $siswaId)
    {
        return \App\Models\Siswa::findOrFail($siswaId);
    }

    public function getAllJurusanForFilter()
    {
        return \App\Models\Jurusan::orderBy('nama_jurusan')->get();
    }

    public function getAllKelasForFilter()
    {
        return \App\Models\Kelas::orderBy('nama_kelas')->get();
    }

    public function getAllKelas()
    {
        return \Illuminate\Support\Facades\DB::table('kelas')
            ->leftJoin('jurusan', 'kelas.jurusan_id', '=', 'jurusan.id')
            ->select('kelas.id', 'kelas.nama_kelas', 'kelas.jurusan_id', 'jurusan.nama_jurusan')
            ->orderBy('kelas.nama_kelas')
            ->get();
    }

    public function getAvailableWaliMurid()
    {
        return \Illuminate\Support\Facades\DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereIn('roles.nama_role', ['Wali Murid', 'Developer']) 
            ->select('users.id', 'users.nama', 'users.username', 'users.email')
            ->orderBy('users.nama')
            ->get();
    }

    /**
     * Find existing wali or create new wali based on phone number.
     * 
     * UPDATED LOGIC (2025-12-26):
     * - Pencarian akun existing: Berdasarkan NOMOR HP (untuk handle sibling/kakak-adik)
     * - Nama akun: "Wali dari {nama_siswa_pertama}" 
     * - Username akun: "wali.{nisn_siswa_pertama}"
     * - Email: "{username}@walimurid.local"
     * 
     * SIBLING HANDLING:
     * - Jika ada siswa baru dengan nomor HP sama → Connect ke akun wali yang sudah ada
     * - Nama dan username tetap dari siswa pertama yang terhubung
     * - Jika siswa pertama dihapus → Nama dan username diupdate via Observer
     *
     * @param string $nomorHp Nomor HP wali murid (untuk lookup existing)
     * @param string $namaSiswa Nama siswa (untuk display name)
     * @param string|null $nisn NISN siswa (untuk username)
     * @return array{user_id: int, username: string, password: string, is_new: bool}
     * @throws BusinessValidationException
     */
    private function findOrCreateWaliByPhone(string $nomorHp, string $namaSiswa, ?string $nisn = null): array
    {
        $phoneClean = preg_replace('/\D+/', '', $nomorHp);
        
        if ($phoneClean === '') {
            throw new BusinessValidationException(
                'Nomor HP wali murid tidak valid. Harus berisi angka.'
            );
        }
        
        // Check if wali with this phone already exists
        // Store phone in user's 'phone' column for lookup
        $existingWali = \App\Models\User::where('phone', $phoneClean)
            ->whereHas('role', function($q) {
                $q->where('nama_role', 'Wali Murid');
            })
            ->first();
        
        if ($existingWali) {
            // Reuse existing wali account (sibling case)
            return [
                'user_id' => $existingWali->id,
                'username' => $existingWali->username,
                'password' => 'smkn1.walimurid.' . $phoneClean,
                'is_new' => false,
            ];
        }
        
        // Create new wali account
        // Username berdasarkan NISN, bukan nomor HP
        $nisnClean = $nisn ? preg_replace('/\D+/', '', $nisn) : $phoneClean;
        $baseUsername = 'wali.' . $nisnClean;
        $username = $baseUsername;
        $counter = 1;
        
        while ($this->userRepository->usernameExists($username)) {
            $counter++;
            $username = $baseUsername . $counter;
        }
        
        // Password berdasarkan nomor HP (lebih mudah diingat wali)
        $password = 'smkn1.walimurid.' . $phoneClean;
        
        // Email menggunakan username
        $email = $username . '@walimurid.local';
        
        // Nama = "Wali dari {nama_siswa}"
        $nama = 'Wali dari ' . $namaSiswa;
        
        $role = Role::where('nama_role', 'Wali Murid')->first();
        
        if (!$role) {
            throw new BusinessValidationException(
                'Role Wali Murid tidak ditemukan dalam database. Silakan hubungi administrator.'
            );
        }
        
        $userData = [
            'role_id' => $role->id,
            'nama' => $nama,
            'username' => $username,
            'email' => $email,
            'phone' => $phoneClean, // Store phone for sibling lookup
            'password' => $password,
            'is_active' => true,
        ];
        
        $createdUser = $this->userRepository->create($userData);
        
        return [
            'user_id' => $createdUser->id,
            'username' => $username,
            'password' => $password,
            'is_new' => true,
        ];
    }

    public function bulkCreateSiswa(array $rows, int $kelasId, bool $createWaliAll = false): array
    {
        DB::beginTransaction();
        
        try {
            $successCount = 0;
            $waliCredentials = [];
            
            foreach ($rows as $row) {
                $waliMuridUserId = null;
                
                if ($createWaliAll) {
                    $waliCred = $this->findOrCreateWaliByPhone(
                        $row['nomor_hp_wali_murid'] ?? '',
                        $row['nama'],
                        $row['nisn'] ?? null  // Add NISN for username
                    );
                    
                    $waliMuridUserId = $waliCred['user_id'];
                    
                    // Only add to credentials if it's a new account
                    if ($waliCred['is_new']) {
                        $waliCredentials[] = $waliCred;
                    }
                }
                
                $siswaArray = [
                    'kelas_id' => $kelasId,
                    'wali_murid_user_id' => $waliMuridUserId,
                    'nisn' => $row['nisn'],
                    'nama_siswa' => $row['nama'],
                    'nomor_hp_wali_murid' => $row['nomor_hp_wali_murid'] ?? null,
                ];
                
                $this->siswaRepository->create($siswaArray);
                $successCount++;
            }
            
            DB::commit();
            
            return [
                'success_count' => $successCount,
                'wali_credentials' => $waliCredentials,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Bulk delete siswa by kelas with comprehensive cleanup.
     * 
     * Clean Architecture Pattern:
     * - Transaction-safe
     * - Orphan detection
     * - Activity logging
     * - Based on console command logic
     *
     * @param int $kelasId
     * @param array $options ['deleteOrphanedWali' => bool]
     * @return array ['count' => int, 'deleted_ids' => array, 'orphaned_wali_deleted' => int]
     */
    public function bulkDeleteByKelas(int $kelasId, array $options = []): array
    {
        $deleteOrphanedWali = $options['deleteOrphanedWali'] ?? false;
$alasanKeluar = $options['alasanKeluar'] ?? null;
$keteranganKeluar = $options['keteranganKeluar'] ?? null;

DB::beginTransaction();
        
        try {
            $siswaList = \App\Models\Siswa::where('kelas_id', $kelasId)->get();
            $count = $siswaList->count();
            $deletedIds = [];
            $orphanedWaliDeleted = 0;
            
            $orphanedWaliIds = [];
            if ($deleteOrphanedWali) {
                $orphanedWaliIds = $this->detectOrphanedWali($kelasId);
            }
            
            foreach ($siswaList as $siswa) {
    // Set alasan keluar before delete
    if ($alasanKeluar) {
        $siswa->alasan_keluar = $alasanKeluar;
        $siswa->keterangan_keluar = $keteranganKeluar;
        $siswa->save();
    }
    
        \App\Models\RiwayatPelanggaran::where('siswa_id', $siswa->id)->delete();
        \App\Models\TindakLanjut::where('siswa_id', $siswa->id)->delete();
    
        $deletedIds[] = $siswa->id;
        $siswa->delete();
    }
            
            if ($deleteOrphanedWali && !empty($orphanedWaliIds)) {
                foreach ($orphanedWaliIds as $waliId) {
                    \App\Models\User::where('id', $waliId)->delete();
                    $orphanedWaliDeleted++;
                }
            }
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'kelas_id' => $kelasId,
                    'count' => $count,
                    'deleted_ids' => $deletedIds,
                    'orphaned_wali_deleted' => $orphanedWaliDeleted
                ])
                ->log("Bulk delete {$count} siswa dari kelas ID {$kelasId}");
            
            return [
                'count' => $count,
                'deleted_ids' => $deletedIds,
                'orphaned_wali_deleted' => $orphanedWaliDeleted
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Detect orphaned wali murid for kelas.
     * Logic from SiswaBulkDeleteCommand.
     */
    private function detectOrphanedWali(int $kelasId): array
    {
        $siswaInKelas = \App\Models\Siswa::where('kelas_id', $kelasId)->get();
        $waliIds = $siswaInKelas->pluck('wali_murid_user_id')->filter()->unique()->toArray();
        
        if (empty($waliIds)) {
            return [];
        }
        
        $orphanedWaliIds = [];
        foreach ($waliIds as $waliId) {
            $otherSiswaCount = \App\Models\Siswa::where('wali_murid_user_id', $waliId)
                ->where('kelas_id', '!=', $kelasId)
                ->count();
                
            if ($otherSiswaCount === 0) {
                $orphanedWaliIds[] = $waliId;
            }
        }
        
        return $orphanedWaliIds;
    }

    /**
     * Get deleted siswa with filters and pagination.
     */
    public function getDeletedSiswa(array $filters = []): LengthAwarePaginator
    {
        $query = \App\Models\Siswa::onlyTrashed()
            ->with(['kelas.jurusan', 'waliMurid'])
            ->orderBy('deleted_at', 'desc');
        
        // Filter by alasan keluar
        if (!empty($filters['alasan_keluar'])) {
            $query->where('alasan_keluar', $filters['alasan_keluar']);
        }
        
        // Filter by kelas
        if (!empty($filters['kelas_id'])) {
            $query->where('kelas_id', $filters['kelas_id']);
        }
        
        // Search by nama or NISN
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }
        
        return $query->paginate(20);
    }
    
    /**
     * Restore soft deleted siswa and all related data.
     * Also handle wali murid - reconnect or create if needed.
     */
    public function restoreSiswa(int $siswaId): bool
    {
        DB::beginTransaction();
        
        try {
            // Get deleted siswa without triggering events
            $siswa = \App\Models\Siswa::onlyTrashed()->findOrFail($siswaId);
            $namaSiswa = $siswa->nama_siswa;
            $nomorHp = $siswa->nomor_hp_wali_murid;
            
            // Handle wali murid - find or create
            $waliId = null;
            if ($nomorHp) {
                $phoneClean = preg_replace('/\D+/', '', $nomorHp);
                
                // Check if wali with this phone exists
                $existingWali = $this->userRepository->findByUsername('wali.' . $phoneClean);
                
                if ($existingWali) {
                    // Wali exists, use it
                    $waliId = $existingWali->id;
                } else {
                    // Wali not found, create new one
                    $waliData = $this->findOrCreateWaliByPhone($nomorHp, $namaSiswa);
                    $waliId = $waliData['user_id'];
                }
                
                // Update siswa wali_murid_user_id
                $siswa->wali_murid_user_id = $waliId;
            }
            
            // Restore siswa WITHOUT triggering LogsActivity trait events
            \App\Models\Siswa::withoutEvents(function () use ($siswa) {
                $siswa->restore();
            });
            
            // Restore related riwayat pelanggaran
            \App\Models\RiwayatPelanggaran::withoutEvents(function () use ($siswaId) {
                \App\Models\RiwayatPelanggaran::onlyTrashed()
                    ->where('siswa_id', $siswaId)
                    ->restore();
            });
            
            // Restore related tindak lanjut
            \App\Models\TindakLanjut::withoutEvents(function () use ($siswaId) {
                \App\Models\TindakLanjut::onlyTrashed()
                    ->where('siswa_id', $siswaId)
                    ->restore();
            });
            
            DB::commit();
            
            // Manual activity logging AFTER successful restore
            try {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'siswa_id' => $siswaId,
                        'nama_siswa' => $namaSiswa,
                        'wali_restored' => $waliId ? 'reconnected/created' : 'none',
                    ])
                    ->log("Restored siswa {$namaSiswa}");
            } catch (\Exception $logError) {
                \Log::warning('Activity log failed: ' . $logError->getMessage());
            }
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Restore siswa failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Permanently delete siswa and all related data from database.
     * This operation CANNOT be undone.
     */
    public function permanentDeleteSiswa(int $siswaId): bool
    {
        DB::beginTransaction();
        
        try {
            $siswa = \App\Models\Siswa::onlyTrashed()->findOrFail($siswaId);
            $namaSiswa = $siswa->nama_siswa;
            
            // Force delete related data (bypass soft delete, permanent delete)
            \App\Models\RiwayatPelanggaran::onlyTrashed()
                ->where('siswa_id', $siswaId)
                ->forceDelete();
            
            \App\Models\TindakLanjut::onlyTrashed()
                ->where('siswa_id', $siswaId)
                ->forceDelete();
            
            // Force delete siswa (permanent)
            $siswa->forceDelete();
            
            DB::commit();
            
            // Log activity
            try {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'siswa_id' => $siswaId,
                        'nama_siswa' => $namaSiswa,
                        'action' => 'PERMANENT DELETE',
                    ])
                    ->log("PERMANENT DELETE siswa {$namaSiswa}");
            } catch (\Exception $logError) {
                \Log::warning('Activity log failed: ' . $logError->getMessage());
            }
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Permanent delete failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Bulk permanent delete by IDs.
     */
    public function bulkPermanentDelete(array $siswaIds): int
    {
        DB::beginTransaction();
        
        try {
            $count = 0;
            
            foreach ($siswaIds as $siswaId) {
                $this->permanentDeleteSiswa($siswaId);
                $count++;
            }
            
            DB::commit();
            
            return $count;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Bulk transfer siswa to another class.
     * 
     * FITUR KENAIKAN KELAS / PINDAH KELAS:
     * - Hanya mengubah kelas_id, semua data historis tetap terjaga
     * - Riwayat pelanggaran, pembinaan, dan wali murid tidak terpengaruh
     * - Cocok untuk kenaikan kelas (X → XI → XII) atau pindah konsentrasi
     *
     * @param array $siswaIds Array of siswa IDs to transfer
     * @param int $targetKelasId Target class ID
     * @return array ['success_count' => int, 'failed_count' => int, 'transferred_names' => array]
     */
    public function bulkTransferSiswa(array $siswaIds, int $targetKelasId): array
    {
        DB::beginTransaction();
        
        try {
            $successCount = 0;
            $failedCount = 0;
            $transferredNames = [];
            $sourceKelasInfo = null;
            
            // Validate target kelas exists
            $targetKelas = \App\Models\Kelas::with('jurusan')->find($targetKelasId);
            if (!$targetKelas) {
                throw new BusinessValidationException('Kelas tujuan tidak ditemukan.');
            }
            
            foreach ($siswaIds as $siswaId) {
                $siswa = \App\Models\Siswa::find($siswaId);
                
                if (!$siswa) {
                    $failedCount++;
                    continue;
                }
                
                // Get source kelas info for logging (only once)
                if (!$sourceKelasInfo && $siswa->kelas) {
                    $sourceKelasInfo = $siswa->kelas->nama_kelas;
                }
                
                // Skip if already in target class
                if ($siswa->kelas_id === $targetKelasId) {
                    $failedCount++;
                    continue;
                }
                
                // Update only kelas_id - all other data remains intact
                $siswa->kelas_id = $targetKelasId;
                $siswa->save();
                
                $transferredNames[] = $siswa->nama_siswa;
                $successCount++;
            }
            
            DB::commit();
            
            // Log activity
            try {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'source_kelas' => $sourceKelasInfo,
                        'target_kelas' => $targetKelas->nama_kelas,
                        'target_kelas_id' => $targetKelasId,
                        'success_count' => $successCount,
                        'transferred_names' => $transferredNames,
                    ])
                    ->log("Transfer {$successCount} siswa dari {$sourceKelasInfo} ke {$targetKelas->nama_kelas}");
            } catch (\Exception $logError) {
                \Log::warning('Activity log failed: ' . $logError->getMessage());
            }
            
            return [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'transferred_names' => $transferredNames,
                'target_kelas' => $targetKelas->nama_kelas,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get siswa grouped by kelas for transfer UI.
     * 
     * OPTIMIZED: Calculate total_poin in single query using subquery
     * to avoid N+1 problem.
     * 
     * @param int|null $kelasId Filter by specific kelas
     * @return \Illuminate\Support\Collection
     */
    public function getSiswaForTransfer(?int $kelasId = null)
    {
        $query = \App\Models\Siswa::query()
            ->select([
                'siswa.id',
                'siswa.nisn',
                'siswa.nama_siswa',
                'siswa.nomor_hp_wali_murid',
                'siswa.kelas_id',
            ])
            // Calculate total_poin in single query with subquery
            ->selectSub(
                \App\Models\RiwayatPelanggaran::query()
                    ->selectRaw('COALESCE(SUM(jenis_pelanggaran.poin), 0)')
                    ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
                    ->whereColumn('riwayat_pelanggaran.siswa_id', 'siswa.id')
                    ->whereNull('riwayat_pelanggaran.deleted_at'),
                'total_poin'
            )
            ->orderBy('nama_siswa');
        
        if ($kelasId) {
            $query->where('siswa.kelas_id', $kelasId);
        }
        
        return $query->get();
    }
}