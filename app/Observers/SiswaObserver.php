<?php

namespace App\Observers;

use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class SiswaObserver
{
    /**
     * Handle the Siswa "updated" event.
     * 
     * Trigger name sync for Wali Murid when wali_murid_user_id changes.
     */
    public function updated(Siswa $siswa): void
    {
        // If wali_murid_user_id changed, sync name for both old and new wali
        if ($siswa->wasChanged('wali_murid_user_id')) {
            $oldWaliId = $siswa->getOriginal('wali_murid_user_id');
            $newWaliId = $siswa->wali_murid_user_id;
            
            // Sync old wali (if exists)
            if ($oldWaliId) {
                $oldWali = \App\Models\User::find($oldWaliId);
                if ($oldWali) {
                    app(\App\Observers\UserNameSyncObserver::class)->syncUserName($oldWali);
                }
            }
            
            // Sync new wali (if exists)
            if ($newWaliId) {
                $newWali = \App\Models\User::find($newWaliId);
                if ($newWali) {
                    app(\App\Observers\UserNameSyncObserver::class)->syncUserName($newWali);
                }
            }
        }
    }
    
    /**
     * Handle the Siswa "deleting" event.
     * (Fired when Siswa::delete() is called; includes soft-deletes.)
     * Cascade soft-delete ke relations: riwayat_pelanggaran, tindak_lanjut.
     * Track wali_murid_user_ids untuk potential deletion.
     */
    public function deleting(Siswa $siswa): void
    {
        // Track wali_murid_user_id jika ada
        if ($siswa->wali_murid_user_id) {
            $waliIds = session('wali_ids_for_deletion', []);
            $waliIds[] = $siswa->wali_murid_user_id;
            session(['wali_ids_for_deletion' => array_unique($waliIds)]);
        }

        // Soft-delete all riwayat pelanggaran terkait siswa ini
        $siswa->riwayatPelanggaran()->each(function ($riwayat) {
            $riwayat->delete();
        });

        // Soft-delete all tindak lanjut (dan via cascade, surat_panggilan)
        $siswa->tindakLanjut()->each(function ($tindak) {
            $tindak->delete();
        });
    }

    /**
     * Handle the Siswa "restoring" event.
     * (Fired when Siswa::restore() is called.)
     * Restore related records if needed.
     */
    public function restoring(Siswa $siswa): void
    {
        // Optionally restore relations (if you want cascade restore)
        // For now, we keep relations as soft-deleted unless explicitly restored
        // This prevents accidental cascade-restore of historical data
    }

    /**
     * Handle the Siswa "force deleting" event.
     * (Fired when Siswa::forceDelete() is called.)
     * Perform hard-delete of relations; cleanup storage files if needed.
     */
    public function forceDeleting(Siswa $siswa): void
    {
        // Track wali_murid_user_id untuk deletion check
        if ($siswa->wali_murid_user_id) {
            $waliIds = session('wali_ids_for_deletion', []);
            $waliIds[] = $siswa->wali_murid_user_id;
            session(['wali_ids_for_deletion' => array_unique($waliIds)]);
        }

        // Hard-delete all riwayat pelanggaran
        $siswa->riwayatPelanggaran()->forceDelete();

        // Hard-delete all tindak lanjut (and via cascade, surat_panggilan)
        $siswa->tindakLanjut()->forceDelete();

        // Optionally delete file storage (bukti_foto, file_path_pdf)
        // This requires additional logic to track and delete from storage
    }

    /**
     * Helper: Check which wali accounts are now orphaned (no more siswa relations)
     */
    public static function getOrphanedWaliAccounts(array $waliIds): array
    {
        if (empty($waliIds)) {
            return [];
        }

        $orphaned = [];
        foreach ($waliIds as $waliId) {
            $count = DB::table('siswa')
                ->where('wali_murid_user_id', $waliId)
                ->where('deleted_at', null) // Only count non-deleted siswa
                ->count();

            if ($count === 0) {
                $orphaned[] = $waliId;
            }
        }

        return $orphaned;
    }
}
