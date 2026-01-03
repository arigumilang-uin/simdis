<?php

namespace App\Observers;

use App\Models\Kelas;
use App\Models\User;

/**
 * Kelas Observer
 * 
 * PURPOSE: Auto-update Wali Kelas name when assigned to Kelas
 */
class KelasObserver
{
    /**
     * Handle the Kelas "created" event.
     */
    public function created(Kelas $kelas): void
    {
        $this->syncWaliKelasName($kelas);
    }

    /**
     * Handle the Kelas "updated" event.
     */
    public function updated(Kelas $kelas): void
    {
        // If wali_kelas_user_id changed or nama_kelas changed
        if ($kelas->wasChanged(['wali_kelas_user_id', 'nama_kelas'])) {
            $this->syncWaliKelasName($kelas);
            
            // If wali kelas changed, also update old wali kelas name
            if ($kelas->wasChanged('wali_kelas_user_id')) {
                $oldWaliId = $kelas->getOriginal('wali_kelas_user_id');
                if ($oldWaliId) {
                    $oldWali = User::find($oldWaliId);
                    if ($oldWali && str_starts_with($oldWali->nama, 'Wali Kelas ')) {
                        // Reset to just "Wali Kelas" or role name
                        $oldWali->updateQuietly(['nama' => $oldWali->role->nama_role ?? 'Wali Kelas']);
                    }
                }
            }
        }
    }
    
    /**
     * Sync Wali Kelas name with Kelas
     * 
     * EXCLUSION: Developer role is NEVER auto-synced
     */
    private function syncWaliKelasName(Kelas $kelas): void
    {
        if ($kelas->wali_kelas_user_id) {
            $waliKelas = User::find($kelas->wali_kelas_user_id);
            
            if ($waliKelas) {
                // SKIP auto-sync for Developer role
                if ($waliKelas->role && $waliKelas->role->nama_role === 'Developer') {
                    return; // Developer names stay as-is
                }
                
                $newName = "Wali Kelas {$kelas->nama_kelas}";
                if ($waliKelas->nama !== $newName) {
                    $waliKelas->updateQuietly(['nama' => $newName]);
                }
            }
        }
    }
}
