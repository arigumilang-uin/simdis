<?php

namespace App\Services\Pelanggaran;

use App\Data\Pelanggaran\RiwayatPelanggaranData;
use App\Data\Pelanggaran\RiwayatPelanggaranFilterData;
use App\Repositories\Contracts\RiwayatPelanggaranRepositoryInterface;
use App\Repositories\Contracts\JenisPelanggaranRepositoryInterface;
use App\Repositories\Contracts\SiswaRepositoryInterface;
use App\Exceptions\BusinessValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Pelanggaran Service - The Orchestrator
 * 
 * Tanggung jawab:
 * - Koordinasi antara Repositories dan RulesEngine
 * - Handle database transactions
 * - Data persistence via repositories
 * - Delegasi business logic calculations ke RulesEngine
 * 
 * CRITICAL: Service ini TIDAK BOLEH menerima Request object.
 * Semua input harus berupa DTO atau primitive types.
 */
class PelanggaranService
{
    /**
     * PelanggaranService constructor.
     * 
     * Dependency injection: repositories dan rules engine.
     *
     * @param RiwayatPelanggaranRepositoryInterface $riwayatRepo
     * @param JenisPelanggaranRepositoryInterface $jenisRepo
     * @param SiswaRepositoryInterface $siswaRepo
     * @param PelanggaranRulesEngine $rulesEngine
     */
    public function __construct(
        private RiwayatPelanggaranRepositoryInterface $riwayatRepo,
        private JenisPelanggaranRepositoryInterface $jenisRepo,
        private SiswaRepositoryInterface $siswaRepo,
        private PelanggaranRulesEngine $rulesEngine,
        private \App\Notifications\TindakLanjutNotificationService $notificationService
    ) {}

    /**
     * Catat pelanggaran baru.
     * 
     * ALUR:
     * 1. Simpan riwayat via repository (dalam transaction)
     * 2. Panggil RulesEngine untuk evaluasi dampak
     * 3. Return data hasil simpan
     *
     * @param RiwayatPelanggaranData $data
     * @return RiwayatPelanggaranData
     * @throws BusinessValidationException
     */
    public function catatPelanggaran(RiwayatPelanggaranData $data): RiwayatPelanggaranData
    {
        DB::beginTransaction();

        try {
            // Siapkan data untuk disimpan
            $riwayatArray = [
                'siswa_id' => $data->siswa_id,
                'jenis_pelanggaran_id' => $data->jenis_pelanggaran_id,
                'guru_pencatat_user_id' => $data->guru_pencatat_user_id,
                'tanggal_kejadian' => $data->tanggal_kejadian,
                'keterangan' => $data->keterangan,
                'bukti_foto_path' => $data->bukti_foto_path,
            ];

            // Simpan riwayat pelanggaran via repository
            $createdRiwayat = $this->riwayatRepo->create($riwayatArray);

            // Evaluasi dampak pelanggaran menggunakan Rules Engine
            // RulesEngine akan:
            // - Cek frekuensi pelanggaran
            // - Hitung total poin
            // - Tentukan apakah perlu buat tindak lanjut
            // - Generate surat panggilan jika perlu
            $this->rulesEngine->processBatch(
                $data->siswa_id,
                [$data->jenis_pelanggaran_id]
            );

            // Evaluasi pembinaan internal & kirim notifikasi ke pembina
            $totalPoin = $this->rulesEngine->hitungTotalPoinAkumulasi($data->siswa_id);
            $pembinaanRekomendasi = $this->rulesEngine->getPembinaanInternalRekomendasi($totalPoin);
            
            // SMART NOTIFICATION: Hanya kirim jika naik level (range berubah)
            // Cek apakah ini pertama kali masuk range ini, atau naik dari range sebelumnya
            $shouldNotify = $this->shouldSendPembinaanNotification(
                $data->siswa_id,
                $totalPoin,
                $pembinaanRekomendasi
            );
            
            // Kirim notifikasi jika ada pembina yang perlu diberitahu DAN ini level baru
            if (!empty($pembinaanRekomendasi['pembina_roles']) && $shouldNotify) {
                $siswa = $createdRiwayat->siswa;
                $pembinaanRekomendasi['total_poin'] = $totalPoin;
                
                $this->notificationService->notifyPembinaanInternal(
                    $siswa,
                    $pembinaanRekomendasi
                );
            }

            DB::commit();

            return RiwayatPelanggaranData::from($createdRiwayat);

        } catch (BusinessValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new BusinessValidationException(
                'Gagal mencatat pelanggaran: ' . $e->getMessage()
            );
        }
    }

    /**
     * Update riwayat pelanggaran.
     * 
     * ALUR:
     * 1. Update data via repository (dalam transaction)
     * 2. Reconcile tindak lanjut siswa (karena poin/frekuensi berubah)
     * 3. Return data hasil update
     *
     * @param int $id
     * @param RiwayatPelanggaranData $data
     * @param string|null $oldBuktiFotoPath Path lama untuk dihapus jika ada upload baru
     * @return RiwayatPelanggaranData
     * @throws BusinessValidationException
     */
    public function updatePelanggaran(
        int $id,
        RiwayatPelanggaranData $data,
        ?string $oldBuktiFotoPath = null
    ): RiwayatPelanggaranData {
        DB::beginTransaction();

        try {
            // Siapkan data update
            $updateArray = [
                'jenis_pelanggaran_id' => $data->jenis_pelanggaran_id,
                'tanggal_kejadian' => $data->tanggal_kejadian,
                'keterangan' => $data->keterangan,
            ];

            // Handle file upload jika ada
            if ($data->bukti_foto_path) {
                // Hapus file lama jika ada
                if ($oldBuktiFotoPath) {
                    Storage::disk('public')->delete($oldBuktiFotoPath);
                }
                $updateArray['bukti_foto_path'] = $data->bukti_foto_path;
            }

            // Update via repository
            $updatedRiwayat = $this->riwayatRepo->update($id, $updateArray);

            // Reconcile tindak lanjut karena data berubah
            // RulesEngine akan re-evaluasi semua pelanggaran siswa ini
            $this->rulesEngine->reconcileForSiswa($data->siswa_id, false);

            DB::commit();

            return RiwayatPelanggaranData::from($updatedRiwayat);

        } catch (BusinessValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new BusinessValidationException(
                'Gagal mengupdate pelanggaran: ' . $e->getMessage()
            );
        }
    }

    /**
     * Hapus riwayat pelanggaran.
     * 
     * ALUR:
     * 1. Hapus file bukti foto jika ada
     * 2. Hapus record via repository
     * 3. Reconcile tindak lanjut (poin/frekuensi berkurang)
     *
     * @param int $id
     * @param int $siswaId Siswa ID untuk reconcile
     * @param string|null $buktiFotoPath Path file untuk dihapus
     * @return bool
     */
    public function deletePelanggaran(int $id, int $siswaId, ?string $buktiFotoPath = null): bool
    {
        // Hapus file bukti foto jika ada
        if ($buktiFotoPath) {
            Storage::disk('public')->delete($buktiFotoPath);
        }

        // Hapus record via repository
        $deleted = $this->riwayatRepo->delete($id);

        if ($deleted) {
            // Reconcile dengan flag deleteIfNoSurat = true
            // Jika setelah hapus tidak ada yang trigger surat, kasus akan dihapus
            $this->rulesEngine->reconcileForSiswa($siswaId, true);
        }

        return $deleted;
    }

    /**
     * Dapatkan riwayat pelanggaran dengan filter dan pagination.
     * 
     * CRITICAL: Method ini menerima RiwayatPelanggaranFilterData (DTO),
     * BUKAN Request object.
     *
     * @param RiwayatPelanggaranFilterData $filters
     * @return LengthAwarePaginator
     */
    public function getFilteredRiwayat(RiwayatPelanggaranFilterData $filters): LengthAwarePaginator
    {
        return $this->riwayatRepo->filterAndPaginate($filters);
    }

    /**
     * Hitung total poin pelanggaran siswa.
     * 
     * Delegasi ke RulesEngine untuk calculation.
     *
     * @param int $siswaId
     * @return int
     */
    public function calculateTotalPoin(int $siswaId): int
    {
        return $this->rulesEngine->hitungTotalPoinAkumulasi($siswaId);
    }

    /**
     * Cek frekuensi pelanggaran tertentu untuk siswa.
     * 
     * Delegasi ke repository untuk counting.
     *
     * @param int $siswaId
     * @param int $jenisPelanggaranId
     * @return int
     */
    public function checkFrequency(int $siswaId, int $jenisPelanggaranId): int
    {
        return $this->riwayatRepo->countBySiswaAndJenis($siswaId, $jenisPelanggaranId);
    }

    /**
     * Dapatkan riwayat pelanggaran siswa dengan relasi lengkap.
     *
     * @param int $siswaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRiwayatBySiswa(int $siswaId)
    {
        return $this->riwayatRepo->findBySiswa($siswaId);
    }

    /**
     * Dapatkan recent violations untuk siswa.
     *
     * @param int $siswaId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentViolations(int $siswaId, int $limit = 10)
    {
        return $this->riwayatRepo->getRecentBySiswa($siswaId, $limit);
    }

    /**
     * Dapatkan statistik pelanggaran untuk siswa.
     * 
     * CONTOH USE CASE: Dashboard siswa, profil siswa
     *
     * @param int $siswaId
     * @return array{
     *     total_poin: int,
     *     total_violations: int,
     *     recent_violations: \Illuminate\Database\Eloquent\Collection,
     *     pembinaan_rekomendasi: array
     * }
     */
    public function getStatistikSiswa(int $siswaId): array
    {
        $totalPoin = $this->calculateTotalPoin($siswaId);
        $riwayat = $this->riwayatRepo->findBySiswa($siswaId);
        $recentViolations = $this->riwayatRepo->getRecentBySiswa($siswaId, 5);
        
        // Dapatkan rekomendasi pembinaan dari RulesEngine
        $pembinaanRekomendasi = $this->rulesEngine->getPembinaanInternalRekomendasi($totalPoin);

        return [
            'total_poin' => $totalPoin,
            'total_violations' => $riwayat->count(),
            'recent_violations' => $recentViolations,
            'pembinaan_rekomendasi' => $pembinaanRekomendasi,
        ];
    }

    /**
     * Dapatkan semua jenis pelanggaran aktif.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveJenisPelanggaran()
    {
        return $this->jenisRepo->getActive();
    }

    /**
     * Dapatkan jenis pelanggaran by filter category.
     *
     * @param string $filterCategory
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getJenisByCategory(string $filterCategory)
    {
        return $this->jenisRepo->getByFilterCategory($filterCategory);
    }

    /**
     * Dapatkan semua jurusan untuk dropdown filter.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllJurusanForFilter()
    {
        // OPTIMIZATION: Use Query Builder to avoid Model Hydration overhead
        return DB::table('jurusan')
            ->select('id', 'nama_jurusan', 'kode_jurusan')
            ->orderBy('nama_jurusan')
            ->get();
    }

    /**
     * Dapatkan semua kelas untuk dropdown filter.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllKelasForFilter()
    {
        // OPTIMIZATION: Use Query Builder to avoid Model Hydration overhead
        return DB::table('kelas')
            ->select('id', 'nama_kelas')
            ->orderBy('nama_kelas')
            ->get();
    }

    /**
     * Dapatkan riwayat pelanggaran untuk edit dengan relationships.
     *
     * @param int $id
     * @return \App\Models\RiwayatPelanggaran
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getRiwayatForEdit(int $id)
    {
        return \App\Models\RiwayatPelanggaran::with(['siswa', 'jenisPelanggaran'])
            ->findOrFail($id);
    }

    /**
     * Dapatkan riwayat pelanggaran by ID (simple find).
     *
     * @param int $id
     * @return \App\Models\RiwayatPelanggaran
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getRiwayatById(int $id)
    {
        return \App\Models\RiwayatPelanggaran::findOrFail($id);
    }

    /**
     * Dapatkan semua siswa untuk form create pelanggaran.
     * 
     * PERFORMANCE: Uses lightweight DB query instead of loading full Models
     * 
     * NO ROLE-BASED FILTER:
     * - Semua role (Operator, Wali Kelas, Kaprodi) bisa catat pelanggaran untuk SEMUA siswa
     * - Filter hanya diterapkan di riwayat dan data siswa, BUKAN di catat pelanggaran
     *
     * @param int|null $userId User ID (not used, kept for backward compatibility)
     * @return \Illuminate\Support\Collection<stdClass> NOT Eloquent Models!
     */
    public function getAllSiswaForCreate(?int $userId = null)
    {
        // NO FILTER - semua siswa ditampilkan untuk semua role
        // Kaprodi dan Wali Kelas tetap bisa mencatat pelanggaran siswa lain
        return app(\App\Repositories\SiswaRepository::class)->getForDropdown(null, null);
    }

    /**
     * Search siswa untuk dropdown AJAX (Server-side Filtering).
     * Mengatasi masalah performa load ribuan data siswa.
     */
    public function searchSiswa(?string $query, int $limit = 20)
    {
        return DB::table('siswa')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->whereNull('siswa.deleted_at')
            ->where(function ($q) use ($query) {
                if ($query) {
                    $q->where('siswa.nama_siswa', 'like', "%{$query}%")
                      ->orWhere('siswa.nisn', 'like', "%{$query}%")
                      ->orWhere('kelas.nama_kelas', 'like', "%{$query}%");
                }
            })
            ->limit($limit)
            ->select('siswa.id', 'siswa.nama_siswa', 'siswa.nisn', 'kelas.nama_kelas')
            ->orderBy('siswa.nama_siswa')
            ->get();
    }

    /**
     * Search pelanggaran untuk dropdown AJAX.
     * Poin diambil dari frequency_rules (frekuensi pertama/1x) bukan dari jenis_pelanggaran.poin
     */
    public function searchPelanggaran(?string $query, int $limit = 20)
    {
        // Subquery untuk mendapatkan poin dari frequency_rules (frekuensi pertama)
        $poinSubquery = DB::table('pelanggaran_frequency_rules')
            ->select('jenis_pelanggaran_id', 'poin')
            ->where('frequency_min', 1) // Frekuensi pertama (1x pelanggaran)
            ->groupBy('jenis_pelanggaran_id', 'poin');

        return DB::table('jenis_pelanggaran')
            ->leftJoin('kategori_pelanggaran', 'jenis_pelanggaran.kategori_id', '=', 'kategori_pelanggaran.id')
            ->leftJoinSub($poinSubquery, 'freq_rules', function ($join) {
                $join->on('jenis_pelanggaran.id', '=', 'freq_rules.jenis_pelanggaran_id');
            })
            ->where('jenis_pelanggaran.is_active', true)
            ->where(function ($q) use ($query) {
                 if ($query) {
                    $q->where('jenis_pelanggaran.nama_pelanggaran', 'like', "%{$query}%")
                      ->orWhere('kategori_pelanggaran.nama_kategori', 'like', "%{$query}%");
                 }
            })
            ->limit($limit)
            ->select(
                'jenis_pelanggaran.id', 
                'jenis_pelanggaran.nama_pelanggaran', 
                DB::raw('COALESCE(freq_rules.poin, 0) as poin'), // Ambil dari frequency_rules, default 0
                'kategori_pelanggaran.nama_kategori as kategori'
            )
            ->orderBy('jenis_pelanggaran.nama_pelanggaran')
            ->get();
    }

    /**
     * Check if pembinaan notification should be sent.
     * 
     * Only send when range changes (level up), not every time.
     *
     * @param int $siswaId
     * @param int $currentPoin
     * @param array $currentRekomendasi  
     * @return bool
     */
    protected function shouldSendPembinaanNotification(
        int $siswaId,
        int $currentPoin,
        array $currentRekomendasi
    ): bool {
        // Get siswa's last pembinaan notification
        $lastNotification = \DB::table('notifications')
            ->where('type', 'App\\Notifications\\PembinaanInternalNotification')
            ->whereRaw("JSON_EXTRACT(data, '$.siswa_id') = ?", [$siswaId])
            ->orderBy('created_at', 'desc')
            ->first();

        // If no previous notification, this is first time - SEND!
        if (!$lastNotification) {
            return true;
        }

        // Get previous range from last notification
        $lastData = json_decode($lastNotification->data, true);
        $lastRangeText = $lastData['range_text'] ?? null;
        $currentRangeText = $currentRekomendasi['range_text'] ?? null;

        // If range changed (level up) - SEND!
        if ($lastRangeText !== $currentRangeText) {
            return true;
        }

        // Same range - DON'T send duplicate
        return false;
    }
}
