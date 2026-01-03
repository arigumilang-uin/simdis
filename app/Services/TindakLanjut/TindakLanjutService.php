<?php

namespace App\Services\TindakLanjut;

use App\Data\TindakLanjut\TindakLanjutData;
use App\Data\TindakLanjut\TindakLanjutFilterData;
use App\Repositories\Contracts\TindakLanjutRepositoryInterface;
use App\Repositories\Contracts\SiswaRepositoryInterface;
use App\Services\Pelanggaran\SuratPanggilanService;
use App\Enums\StatusTindakLanjut;
use App\Jobs\SendNotificationEmail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Tindak Lanjut Service - The Orchestrator
 * 
 * Tanggung jawab:
 * - Koordinasi antara Repositories
 * - Handle database transactions
 * - Data persistence via repositories
 * - Update status siswa jika diperlukan (skorsing, dll)
 * 
 * CRITICAL: Service ini TIDAK BOLEH menerima Request object.
 * Semua input harus berupa DTO atau primitive types.
 */
class TindakLanjutService
{
    /**
     * TindakLanjutService constructor.
     *
     * @param TindakLanjutRepositoryInterface $tindakLanjutRepo
     * @param SiswaRepositoryInterface $siswaRepo
     * @param SuratPanggilanService $suratService
     */
    public function __construct(
        private TindakLanjutRepositoryInterface $tindakLanjutRepo,
        private SiswaRepositoryInterface $siswaRepo,
        private SuratPanggilanService $suratService
    ) {}

    /**
     * Create tindak lanjut baru.
     * 
     * ALUR:
     * 1. Simpan tindak lanjut via repository (dalam transaction)
     * 2. Update status siswa jika diperlukan (skorsing)
     * 3. Return data hasil simpan
     *
     * @param TindakLanjutData $data
     * @return TindakLanjutData
     * @throws \Exception
     */
    public function createTindakLanjut(TindakLanjutData $data): TindakLanjutData
    {
        DB::beginTransaction();

        try {
            // Siapkan data untuk disimpan
            $tindakLanjutArray = [
                'siswa_id' => $data->siswa_id,
                'pemicu' => $data->pemicu,
                'sanksi_deskripsi' => $data->sanksi_deskripsi,
                'denda_deskripsi' => $data->denda_deskripsi,
                'status' => $data->status,
                'tanggal_tindak_lanjut' => $data->tanggal_tindak_lanjut,
                'penyetuju_user_id' => $data->penyetuju_user_id,
            ];

            // Simpan tindak lanjut via repository
            $createdTindakLanjut = $this->tindakLanjutRepo->create($tindakLanjutArray);

            // Update status siswa jika ada skorsing
            if ($this->isSkorsingSanksi($data->sanksi_deskripsi)) {
                $this->updateSiswaStatusToSkorsing($data->siswa_id);
            }

            // Kirim notifikasi ke wali murid (async via queue)
            $this->notifyWaliMurid($createdTindakLanjut->siswa_id, 
                'Tindak Lanjut Baru', 
                "Anak Anda mendapat tindak lanjut: {$data->sanksi_deskripsi}"
            );

            DB::commit();

            return TindakLanjutData::from($createdTindakLanjut);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update tindak lanjut.
     *
     * @param int $id
     * @param TindakLanjutData $data
     * @return TindakLanjutData
     * @throws \Exception
     */
    public function updateTindakLanjut(int $id, TindakLanjutData $data): TindakLanjutData
    {
        DB::beginTransaction();

        try {
            $updateArray = [
                'pemicu' => $data->pemicu,
                'sanksi_deskripsi' => $data->sanksi_deskripsi,
                'denda_deskripsi' => $data->denda_deskripsi,
                'status' => $data->status,
                'tanggal_tindak_lanjut' => $data->tanggal_tindak_lanjut,
                'penyetuju_user_id' => $data->penyetuju_user_id,
            ];

            $updatedTindakLanjut = $this->tindakLanjutRepo->update($id, $updateArray);

            // Jika status berubah ke SELESAI, restore status siswa ke aktif
            if ($data->status === StatusTindakLanjut::SELESAI) {
                $this->restoreSiswaStatus($data->siswa_id);
            }

            DB::commit();

            return TindakLanjutData::from($updatedTindakLanjut);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve tindak lanjut (change status from Menunggu Persetujuan to Disetujui).
     *
     * @param int $id
     * @param int $penyetujuUserId
     * @return TindakLanjutData
     */
    public function approveTindakLanjut(int $id, int $penyetujuUserId): TindakLanjutData
    {
        $updateArray = [
            'status' => StatusTindakLanjut::DISETUJUI,
            'penyetuju_user_id' => $penyetujuUserId,
        ];

        $updated = $this->tindakLanjutRepo->update($id, $updateArray);
        
        return TindakLanjutData::from($updated);
    }

    /**
     * Reject tindak lanjut.
     *
     * @param int $id
     * @param int $penyetujuUserId
     * @param string $reason
     * @return TindakLanjutData
     */
    public function rejectTindakLanjut(int $id, int $penyetujuUserId, string $reason = ''): TindakLanjutData
    {
        $updateArray = [
            'status' => StatusTindakLanjut::DITOLAK,
            'penyetuju_user_id' => $penyetujuUserId,
            'pemicu' => $reason ? "Ditolak: {$reason}" : 'Ditolak',
        ];

        $updated = $this->tindakLanjutRepo->update($id, $updateArray);
        
        return TindakLanjutData::from($updated);
    }

    /**
     * Complete tindak lanjut (mark as Selesai).
     *
     * @param int $id
     * @return TindakLanjutData
     */
    public function completeTindakLanjut(int $id): TindakLanjutData
    {
        $tindakLanjut = $this->tindakLanjutRepo->find($id);
        
        DB::beginTransaction();
        try {
            $updated = $this->tindakLanjutRepo->update($id, [
                'status' => StatusTindakLanjut::SELESAI,
            ]);

            // Restore status siswa ke aktif
            $this->restoreSiswaStatus($tindakLanjut->siswa_id);

            // Kirim notifikasi ke wali murid (async via queue)
            $this->notifyWaliMurid($tindakLanjut->siswa_id,
                'Tindak Lanjut Selesai',
                'Tindak lanjut untuk anak Anda telah diselesaikan.'
            );

            DB::commit();

            return TindakLanjutData::from($updated);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete tindak lanjut.
     *
     * @param int $id
     * @return bool
     */
    public function deleteTindakLanjut(int $id): bool
    {
        $tindakLanjut = $this->tindakLanjutRepo->find($id);
        
        $deleted = $this->tindakLanjutRepo->delete($id);

        if ($deleted) {
            // Restore status siswa jika ada
            $this->restoreSiswaStatus($tindakLanjut->siswa_id);
        }

        return $deleted;
    }

    /**
     * Get filtered tindak lanjut with pagination.
     *
     * @param TindakLanjutFilterData $filters
     * @return LengthAwarePaginator
     */
    public function getFilteredTindakLanjut(TindakLanjutFilterData $filters): LengthAwarePaginator
    {
        return $this->tindakLanjutRepo->filterAndPaginate($filters);
    }

    /**
     * Get tindak lanjut by siswa.
     *
     * @param int $siswaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTindakLanjutBySiswa(int $siswaId)
    {
        return $this->tindakLanjutRepo->findBySiswa($siswaId);
    }

    /**
     * Get active tindak lanjut for siswa.
     *
     * @param int $siswaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveTindakLanjutBySiswa(int $siswaId)
    {
        return $this->tindakLanjutRepo->findActiveBySiswa($siswaId);
    }

    /**
     * Get pending approval tindak lanjut.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingApproval()
    {
        return $this->tindakLanjutRepo->findPendingApproval();
    }

    /**
     * Generate nomor surat via SuratPanggilanService.
     *
     * @return string
     */
    public function generateNomorSurat(): string
    {
        return $this->suratService->generateNomorSurat();
    }

    /**
     * Get all siswa for dropdown (with kelas relationship).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSiswaForDropdown()
    {
        return \App\Models\Siswa::with('kelas')->orderBy('nama_siswa')->get();
    }

    /**
     * Get tindak lanjut detail untuk show dengan relationships.
     *
     * @param int $id
     * @return \App\Models\TindakLanjut
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getTindakLanjutDetail(int $id)
    {
        return \App\Models\TindakLanjut::with([
            'siswa.kelas.jurusan',
            'penyetuju',
            'suratPanggilan'
        ])->findOrFail($id);
    }

    /**
     * Get tindak lanjut untuk edit dengan relationships.
     *
     * @param int $id
     * @return \App\Models\TindakLanjut
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getTindakLanjutForEdit(int $id)
    {
        return \App\Models\TindakLanjut::with('siswa')->findOrFail($id);
    }

    /**
     * Get tindak lanjut by ID (simple find).
     *
     * @param int $id
     * @return \App\Models\TindakLanjut
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getTindakLanjutById(int $id)
    {
        return \App\Models\TindakLanjut::findOrFail($id);
    }

    /**
     * Check if sanksi deskripsi contains skorsing.
     *
     * @param string|null $sanksiDeskripsi
     * @return bool
     */
    private function isSkorsingSanksi(?string $sanksiDeskripsi): bool
    {
        if (!$sanksiDeskripsi) {
            return false;
        }

        $lower = strtolower($sanksiDeskripsi);
        return str_contains($lower, 'skors') || str_contains($lower, 'skorsing');
    }

    /**
     * Update status siswa to skorsing.
     *
     * @param int $siswaId
     * @return void
     */
    private function updateSiswaStatusToSkorsing(int $siswaId): void
    {
        // Cek apakah enum StatusSiswa memiliki SKORSING
        // Jika tidak ada, skip update
        try {
            $this->siswaRepo->update($siswaId, [
                'status' => 'skorsing', // atau StatusSiswa::SKORSING jika ada
            ]);
        } catch (\Exception $e) {
            // Log error tapi jangan throw (status update is optional)
            logger()->warning("Failed to update siswa status to skorsing: " . $e->getMessage());
        }
    }

    /**
     * Restore status siswa ke aktif (setelah tindak lanjut selesai).
     *
     * @param int $siswaId
     * @return void
     */
    private function restoreSiswaStatus(int $siswaId): void
    {
        // Cek apakah masih ada kasus aktif lainnya
        $activeCount = $this->tindakLanjutRepo->countActiveCasesBySiswa($siswaId);

        // Hanya restore jika tidak ada kasus aktif lagi
        if ($activeCount === 0) {
            try {
                $this->siswaRepo->update($siswaId, [
                    'status' => 'aktif', // atau StatusSiswa::AKTIF jika ada
                ]);
            } catch (\Exception $e) {
                logger()->warning("Failed to restore siswa status: " . $e->getMessage());
            }
        }
    }

    /**
     * Kirim notifikasi email ke wali murid (async via queue job).
     *
     * @param int $siswaId
     * @param string $subject
     * @param string $message
     * @return void
     */
    private function notifyWaliMurid(int $siswaId, string $subject, string $message): void
    {
        try {
            // Get siswa dengan wali murid
            $siswa = $this->siswaRepo->find($siswaId);
            
            // Check apakah siswa punya wali murid
            if (!$siswa || !$siswa->waliMurid || !$siswa->waliMurid->email) {
                logger()->info("Siswa {$siswaId} tidak punya wali murid atau email wali tidak tersedia.");
                return;
            }

            // Dispatch queue job untuk kirim email (asynchronous)
            SendNotificationEmail::dispatch(
                $siswa->waliMurid->email,
                $subject,
                [
                    'nama_siswa' => $siswa->nama_siswa,
                    'nisn' => $siswa->nisn,
                    'kelas' => $siswa->kelas?->nama_kelas ?? 'N/A',
                    'message' => $message,
                ]
            );

            logger()->info("Email notification queued for wali murid: {$siswa->waliMurid->email}");

        } catch (\Exception $e) {
            // Log error tapi jangan throw - notification failure tidak boleh stop proses utama
            logger()->error("Failed to queue notification email: " . $e->getMessage());
        }
    }
}
