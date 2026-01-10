<?php

namespace App\Observers;

use App\Models\Kelas;
use App\Models\User;
use App\Models\Role;

/**
 * Kelas Observer
 * 
 * PURPOSE: 
 * - Auto-update Wali Kelas name when assigned to Kelas
 * - Auto-swap roles when wali kelas changes:
 *   - Old wali kelas → role becomes Guru
 *   - New wali kelas → role becomes Wali Kelas
 */
class KelasObserver
{
    /**
     * Handle the Kelas "created" event.
     */
    public function created(Kelas $kelas): void
    {
        $this->syncNewWaliKelas($kelas, null);
    }

    /**
     * Handle the Kelas "updated" event.
     */
    public function updated(Kelas $kelas): void
    {
        // If wali_kelas_user_id changed
        if ($kelas->wasChanged('wali_kelas_user_id')) {
            $oldWaliId = $kelas->getOriginal('wali_kelas_user_id');
            $this->syncNewWaliKelas($kelas, $oldWaliId);
        } 
        // If only nama_kelas changed, update current wali kelas name
        elseif ($kelas->wasChanged('nama_kelas')) {
            $this->updateWaliKelasName($kelas);
        }
    }
    
    /**
     * Handle the Kelas "deleted" event.
     */
    public function deleted(Kelas $kelas): void
    {
        // Demote wali kelas to Guru if exists
        if ($kelas->wali_kelas_user_id) {
            $wali = User::find($kelas->wali_kelas_user_id);
            $guruRole = Role::where('nama_role', 'Guru')->first();
            
            if ($wali && $guruRole && $wali->role_id !== $guruRole->id && $wali->role?->nama_role !== 'Developer') {
                $wali->updateQuietly([
                    'role_id' => $guruRole->id,
                    'nama' => 'Guru', // Reset name to generic role name
                ]);
                
                \Log::info("KelasObserver: User {$wali->username} demoted from Wali Kelas to Guru (Kelas deleted).");
            }
        }
    }
    
    /**
     * Sync new wali kelas and demote old wali kelas to Guru.
     * 
     * LOGIC:
     * 1. Old wali kelas (if exists and not Developer):
     *    - Check if still assigned to any other kelas
     *    - If not → role becomes Guru, nama becomes "Guru"
     * 2. New wali kelas (if exists and not Developer):
     *    - Role becomes Wali Kelas
     *    - Nama becomes "Wali Kelas [Nama Kelas]"
     *    - If was Kaprodi → detach from jurusan
     */
    private function syncNewWaliKelas(Kelas $kelas, ?int $oldWaliId): void
    {
        $waliKelasRole = Role::where('nama_role', 'Wali Kelas')->first();
        $guruRole = Role::where('nama_role', 'Guru')->first();
        
        if (!$waliKelasRole || !$guruRole) {
            \Log::warning('KelasObserver: Wali Kelas or Guru role not found in database.');
            return;
        }
        
        // STEP 1: Handle OLD wali kelas (demote to Guru)
        // Karena relasi one-to-one (1 wali kelas = 1 kelas), langsung demote ke Guru
        if ($oldWaliId) {
            $oldWali = User::find($oldWaliId);
            
            if ($oldWali && $oldWali->role?->nama_role !== 'Developer') {
                // Old wali kelas langsung jadi Guru (tidak perlu cek kelas lain karena one-to-one)
                $oldWali->updateQuietly([
                    'role_id' => $guruRole->id,
                    'nama' => 'Guru',
                ]);
                
                \Log::info("KelasObserver: User {$oldWali->username} demoted from Wali Kelas to Guru.");
            }
        }
        
        // STEP 2: Handle NEW wali kelas (promote to Wali Kelas)
        if ($kelas->wali_kelas_user_id) {
            $newWali = User::find($kelas->wali_kelas_user_id);
            
            if ($newWali && $newWali->role?->nama_role !== 'Developer') {
                // CRITICAL: Jika user ini sudah wali kelas di kelas LAIN, lepaskan dulu
                // Karena 1 wali kelas = 1 kelas (one-to-one)
                Kelas::where('wali_kelas_user_id', $newWali->id)
                    ->where('id', '!=', $kelas->id)
                    ->update(['wali_kelas_user_id' => null]);
                
                $updates = [
                    'nama' => "Wali Kelas {$kelas->nama_kelas}",
                ];
                
                // If not already Wali Kelas, update role
                if ($newWali->role_id !== $waliKelasRole->id) {
                    $updates['role_id'] = $waliKelasRole->id;
                    
                    // If was Kaprodi, detach from jurusan
                    if ($newWali->role?->nama_role === 'Kaprodi') {
                        \App\Models\Jurusan::where('kaprodi_user_id', $newWali->id)
                            ->update(['kaprodi_user_id' => null]);
                        
                        \Log::info("KelasObserver: User {$newWali->username} was Kaprodi, detached from jurusan.");
                    }
                    
                    \Log::info("KelasObserver: User {$newWali->username} promoted to Wali Kelas of {$kelas->nama_kelas}.");
                }
                
                $newWali->updateQuietly($updates);
            }
        }
    }
    
    /**
     * Update wali kelas name when kelas name changes.
     */
    private function updateWaliKelasName(Kelas $kelas): void
    {
        if ($kelas->wali_kelas_user_id) {
            $waliKelas = User::find($kelas->wali_kelas_user_id);
            
            if ($waliKelas && $waliKelas->role?->nama_role !== 'Developer') {
                $newName = "Wali Kelas {$kelas->nama_kelas}";
                if ($waliKelas->nama !== $newName) {
                    $waliKelas->updateQuietly(['nama' => $newName]);
                }
            }
        }
    }
}

