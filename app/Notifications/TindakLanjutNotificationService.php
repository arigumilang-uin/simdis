<?php

namespace App\Notifications;

use App\Models\TindakLanjut;
use App\Models\User;
use App\Notifications\KasusButuhApprovalNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Service untuk mengelola notifikasi terkait Tindak Lanjut.
 * 
 * Tanggung jawab:
 * - Kirim notifikasi ke Kepala Sekolah saat kasus butuh approval
 * - Kirim notifikasi ke Waka Kesiswaan untuk awareness (Surat 2)
 * - Kirim notifikasi pembinaan internal ke pembina terkait
 * - Handle notification logic secara terpusat
 * 
 * Design Pattern: Service Layer (Single Responsibility)
 */
class TindakLanjutNotificationService
{
    /**
     * Kirim notifikasi saat kasus baru dibuat yang butuh approval.
     * 
     * @param TindakLanjut $tindakLanjut
     * @return void
     */
    public function notifyKasusButuhApproval(TindakLanjut $tindakLanjut): void
    {
        // Hanya kirim notifikasi jika status "Menunggu Persetujuan"
        if ($tindakLanjut->status !== 'Menunggu Persetujuan') {
            return;
        }

        // Ambil Kepala Sekolah
        $kepalaSekolah = $this->getKepalaSekolah();
        
        if (!$kepalaSekolah) {
            \Log::warning('Kepala Sekolah tidak ditemukan untuk notifikasi approval', [
                'tindak_lanjut_id' => $tindakLanjut->id,
            ]);
            return;
        }

        // Kirim notifikasi (email + database)
        $kepalaSekolah->notify(new KasusButuhApprovalNotification($tindakLanjut));
    }

    /**
     * Kirim notifikasi awareness ke Waka Kesiswaan untuk Surat 2.
     * 
     * @param TindakLanjut $tindakLanjut
     * @return void
     */
    public function notifyWakaForSurat2(TindakLanjut $tindakLanjut): void
    {
        $surat = $tindakLanjut->suratPanggilan;
        
        // Hanya untuk Surat 2
        if (!$surat || $surat->tipe_surat !== 'Surat 2') {
            return;
        }

        $waka = $this->getWakaKesiswaan();
        
        if (!$waka) {
            return;
        }

        // TODO: Implement Surat2AwarenessNotification (Phase 2)
        // $waka->notify(new Surat2AwarenessNotification($tindakLanjut));
    }

    /**
     * Kirim notifikasi pembinaan internal ke pembina terkait.
     * 
     * Dipanggil setelah catat pelanggaran untuk memberitahu pembina
     * bahwa siswa perlu pembinaan sesuai dengan akumulasi poin.
     * 
     * Clean Architecture: Notification Logic di Service Layer
     * 
     * @param \App\Models\Siswa $siswa
     * @param array $rekomendasi ['pembina_roles' => [], 'keterangan' => '', 'range_text' => '', 'total_poin' => int]
     * @return void
     */
    public function notifyPembinaanInternal(\App\Models\Siswa $siswa, array $rekomendasi): void
    {
        // Validate rekomendasi data
        if (empty($rekomendasi['pembina_roles']) || empty($rekomendasi['keterangan'])) {
            return;
        }

        // Get pembina users by roles
        $pembinaUsers = $this->getPembinaByRoles($rekomendasi['pembina_roles'], $siswa);

        if ($pembinaUsers->isEmpty()) {
            \Log::warning('No pembina found for internal coaching notification', [
                'siswa_id' => $siswa->id,
                'roles' => $rekomendasi['pembina_roles'],
            ]);
            return;
        }

        // Create notification for each pembina
        foreach ($pembinaUsers as $pembina) {
            $pembina->notify(new \App\Notifications\PembinaanInternalNotification(
                $siswa,
                $rekomendasi
            ));
        }
    }

    /**
     * Get Kepala Sekolah user.
     * 
     * @return User|null
     */
    private function getKepalaSekolah(): ?User
    {
        return User::whereHas('role', function ($q) {
            $q->where('nama_role', 'Kepala Sekolah');
        })->first();
    }

    /**
     * Get Waka Kesiswaan user.
     * 
     * @return User|null
     */
    private function getWakaKesiswaan(): ?User
    {
        return User::whereHas('role', function ($q) {
            $q->where('nama_role', 'Waka Kesiswaan');
        })->first();
    }

    /**
     * Get pembina users by roles with siswa context.
     * 
     * Handles special cases:
     * - Wali Kelas: Get from siswa's kelas
     * - Kaprodi: Get from siswa's jurusan
     * - Other roles: Get by role name
     * 
     * @param array $roles Array of role names
     * @param \App\Models\Siswa $siswa
     * @return \Illuminate\Support\Collection
     */
    private function getPembinaByRoles(array $roles, \App\Models\Siswa $siswa): \Illuminate\Support\Collection
    {
        $users = collect();

        foreach ($roles as $roleName) {
            $user = null;

            switch ($roleName) {
                case 'Wali Kelas':
                    // Get wali kelas from siswa's kelas
                    if ($siswa->kelas && $siswa->kelas->waliKelas) {
                        $user = $siswa->kelas->waliKelas;
                    }
                    break;

                case 'Kaprodi':
                    // Get kaprodi from siswa's jurusan
                    if ($siswa->kelas && $siswa->kelas->jurusan && $siswa->kelas->jurusan->kaprodi) {
                        $user = $siswa->kelas->jurusan->kaprodi;
                    }
                    break;

                case 'Waka Kesiswaan':
                    $user = $this->getWakaKesiswaan();
                    break;

                case 'Kepala Sekolah':
                    $user = $this->getKepalaSekolah();
                    break;

                default:
                    // Try to find by role name
                    $user = User::whereHas('role', function ($q) use ($roleName) {
                        $q->where('nama_role', $roleName);
                    })->first();
                    break;
            }

            if ($user) {
                $users->push($user);
            }
        }

        return $users->unique('id');
    }

    /**
     * Get jumlah notifikasi yang belum dibaca untuk user.
     * 
     * @param User $user
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Mark notifikasi sebagai sudah dibaca.
     * 
     * @param User $user
     * @param string $notificationId
     * @return void
     */
    public function markAsRead(User $user, string $notificationId): void
    {
        $user->notifications()->where('id', $notificationId)->update(['read_at' => now()]);
    }

    /**
     * Mark semua notifikasi sebagai sudah dibaca.
     * 
     * @param User $user
     * @return void
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}
