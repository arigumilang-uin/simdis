<?php

namespace App\Observers;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Observers\UserNameSyncObserver;

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
                $oldWali = User::find($oldWaliId);
                if ($oldWali) {
                    app(UserNameSyncObserver::class)->syncUserName($oldWali);
                }
            }
            
            // Sync new wali (if exists)
            if ($newWaliId) {
                $newWali = User::find($newWaliId);
                if ($newWali) {
                    app(UserNameSyncObserver::class)->syncUserName($newWali);
                }
            }
        }
    }
    
    /**
     * Handle the Siswa "deleting" event.
     * (Fired BEFORE Siswa is soft-deleted.)
     */
    public function deleting(Siswa $siswa): void
    {
        // 1. Soft-delete all riwayat pelanggaran
        $siswa->riwayatPelanggaran()->each(function ($riwayat) {
            $riwayat->delete();
        });

        // 2. Soft-delete all tindak lanjut
        $siswa->tindakLanjut()->each(function ($tindak) {
            $tindak->delete();
        });

        // 3. Soft-delete all absensi
        $siswa->absensi()->each(function ($absen) {
            $absen->delete();
        });

        // 4. Soft-delete all pembinaan status
        $siswa->pembinaanStatus()->each(function ($pembinaan) {
            $pembinaan->delete();
        });
    }

    /**
     * Handle the Siswa "deleted" event.
     * (Fired AFTER Siswa is soft-deleted.)
     */
    public function deleted(Siswa $siswa): void
    {
        if ($siswa->wali_murid_user_id) {
            $wali = User::find($siswa->wali_murid_user_id);
            if ($wali) {
                // 1. Sync Nama
                app(UserNameSyncObserver::class)->syncUserName($wali);
                
                // 2. Check Orphans. If 0 active siswa left, deactivate wali.
                $activeSiblingsCount = \App\Models\Siswa::where('wali_murid_user_id', $wali->id)->count();
                
                if ($activeSiblingsCount === 0) {
                     $wali->updateQuietly(['is_active' => false]);
                     \Log::info("Wali Murid {$wali->username} deactivated because all connected siswa are deleted/archived.");
                }
            }
        }
    }

    /**
     * Handle the Siswa "restoring" event.
     */
    public function restoring(Siswa $siswa): void
    {
        // No action needed before restoring
    }

    /**
     * Handle the Siswa "restored" event.
     * (Fired AFTER Siswa is restored.)
     */
    public function restored(Siswa $siswa): void
    {
        // 1. Restore History Data (Cascading Restore)
        // Kita gunakan onlyTrashed() untuk mengambil data yang terhapus
        // Dan memanggil restore() pada builder relations
        
        $siswa->riwayatPelanggaran()->onlyTrashed()->restore();
        $siswa->tindakLanjut()->onlyTrashed()->restore();
        $siswa->absensi()->onlyTrashed()->restore();
        $siswa->pembinaanStatus()->onlyTrashed()->restore();

        // 2. Handle Wali Murid Logic
        if ($siswa->wali_murid_user_id) {
            $wali = User::withTrashed()->find($siswa->wali_murid_user_id);
            if ($wali) {
                // Activate Wali if inactive
                if ($wali->trashed()) {
                    $wali->restore();
                }
                if (!$wali->is_active) {
                    $wali->updateQuietly(['is_active' => true]);
                    \Log::info("Wali Murid {$wali->username} reactivated because a connected siswa was restored.");
                }
                
                // Sync Nama
                app(UserNameSyncObserver::class)->syncUserName($wali);
            }
        }
    }

    /**
     * Handle the Siswa "force deleting" event.
     * (Fired BEFORE Siswa is force-deleted.)
     */
    public function forceDeleting(Siswa $siswa): void
    {
        // Hard-delete manual untuk memicu event deletion pada child model (jika ada observer child)
        // Meskipun DB Cascade akan menghapusnya, melakukan ini di level Eloquent lebih aman untuk side-effects lain (misal log file deletion)
        
        $siswa->riwayatPelanggaran()->forceDelete();
        $siswa->tindakLanjut()->forceDelete();
        $siswa->absensi()->forceDelete();
        $siswa->pembinaanStatus()->forceDelete();
    }

    /**
     * Handle the Siswa "force deleted" event.
     * (Fired AFTER Siswa is force-deleted.)
     */
    public function forceDeleted(Siswa $siswa): void
    {
        if ($siswa->wali_murid_user_id) {
            $wali = User::find($siswa->wali_murid_user_id);
            if ($wali) {
                // If NO children at all -> Force Delete Wali Account
                $anyChild = \App\Models\Siswa::withTrashed()
                         ->where('wali_murid_user_id', $wali->id)
                         ->exists();
                         
                if (!$anyChild) {
                     $wali->forceDelete();
                     \Log::info("Wali Murid {$wali->username} force deleted because no connected siswa remain.");
                } else {
                     app(UserNameSyncObserver::class)->syncUserName($wali);
                }
            }
        }
    }
}
