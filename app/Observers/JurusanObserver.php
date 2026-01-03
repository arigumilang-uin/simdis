<?php

namespace App\Observers;

use App\Models\Jurusan;
use App\Models\User;

/**
 * Jurusan Observer
 * 
 * PURPOSE: Auto-update Kaprodi name when assigned to Jurusan
 */
class JurusanObserver
{
    /**
     * Handle the Jurusan "created" event.
     */
    public function created(Jurusan $jurusan): void
    {
        $this->syncKaprodiName($jurusan);
    }

    /**
     * Handle the Jurusan "updated" event.
     */
    public function updated(Jurusan $jurusan): void
    {
        // If kaprodi_user_id changed or nama_jurusan changed
        if ($jurusan->wasChanged(['kaprodi_user_id', 'nama_jurusan'])) {
            $this->syncKaprodiName($jurusan);
            
            // If kaprodi changed, also update old kaprodi name
            if ($jurusan->wasChanged('kaprodi_user_id')) {
                $oldKaprodiId = $jurusan->getOriginal('kaprodi_user_id');
                if ($oldKaprodiId) {
                    $oldKaprodi = User::find($oldKaprodiId);
                    if ($oldKaprodi && str_starts_with($oldKaprodi->nama, 'Kaprodi ')) {
                        // Reset to just "Kaprodi" or role name
                        $oldKaprodi->updateQuietly(['nama' => $oldKaprodi->role->nama_role ?? 'Kaprodi']);
                    }
                }
            }
        }
    }
    
    /**
     * Sync Kaprodi name with Jurusan
     * 
     * EXCLUSION: Developer role is NEVER auto-synced
     */
    private function syncKaprodiName(Jurusan $jurusan): void
    {
        if ($jurusan->kaprodi_user_id) {
            $kaprodi = User::find($jurusan->kaprodi_user_id);
            
            if ($kaprodi) {
                // SKIP auto-sync for Developer role
                if ($kaprodi->role && $kaprodi->role->nama_role === 'Developer') {
                    return; // Developer names stay as-is
                }
                
                $newName = "Kaprodi {$jurusan->nama_jurusan}";
                if ($kaprodi->nama !== $newName) {
                    $kaprodi->updateQuietly(['nama' => $newName]);
                }
            }
        }
    }
}
