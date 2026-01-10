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
     * (Fired BEFORE Siswa is soft-deleted/deleted.)
     */
    public function deleting(Siswa $siswa): void
    {
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
     * Handle the Siswa "deleted" event.
     * (Fired AFTER Siswa is soft-deleted.)
     */
    public function deleted(Siswa $siswa): void
    {
        if ($siswa->wali_murid_user_id) {
            $wali = User::find($siswa->wali_murid_user_id);
            if ($wali) {
                // 1. Sync Nama (akan ambil next available ACTIVE sibling karena deleted_at not null)
                app(UserNameSyncObserver::class)->syncUserName($wali);
                
                // 2. Check Orphans (Soft Delete Logic: Nonaktifkan akun)
                // We count only ACTIVE siswa. If 0 active siswa left, deactivate wali.
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
        // Logic: Keep relations deleted? Or restore them?
        // Usually we keep them deleted to avoid confusion, unless needed.
    }

    /**
     * Handle the Siswa "restored" event.
     * (Fired AFTER Siswa is restored.)
     */
    public function restored(Siswa $siswa): void
    {
        if ($siswa->wali_murid_user_id) {
            $wali = User::find($siswa->wali_murid_user_id);
            if ($wali) {
                // 1. Activate Wali if inactive
                if (!$wali->is_active) {
                    $wali->updateQuietly(['is_active' => true]);
                    \Log::info("Wali Murid {$wali->username} reactivated because a connected siswa was restored.");
                }
                
                // 2. Sync Nama (Ensure name is up to date, e.g. if this is now the first child)
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
        // Hard-delete all riwayat pelanggaran
        $siswa->riwayatPelanggaran()->forceDelete();

        // Hard-delete all tindak lanjut
        $siswa->tindakLanjut()->forceDelete();
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
                // Check if any children exist (Active OR Soft Deleted)
                // If NO children at all -> Force Delete Wali Account
                $anyChild = \App\Models\Siswa::withTrashed()
                         ->where('wali_murid_user_id', $wali->id)
                         ->exists();
                         
                if (!$anyChild) {
                     $wali->forceDelete();
                     \Log::info("Wali Murid {$wali->username} force deleted because no connected siswa remain.");
                } else {
                     // Still has children (maybe soft deleted ones). Sync Name.
                     app(UserNameSyncObserver::class)->syncUserName($wali);
                }
            }
        }
    }
}
