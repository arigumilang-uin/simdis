<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;

/**
 * User Name Sync Observer
 * 
 * PURPOSE: Auto-update user name based on role and assignment
 * 
 * RULES:
 * - Kaprodi → "Kaprodi [Nama Jurusan]"
 * - Wali Kelas → "Wali Kelas [Nama Kelas]"
 * - Wali Murid → "Wali dari [Nama Siswa Pertama]" + username "wali.[nisn_pertama]"
 * - Other roles → Keep original name (unless manually changed)
 * 
 * TRIGGERS:
 * - When user is assigned as Kaprodi (Jurusan.kaprodi_user_id updated)
 * - When user is assigned as Wali Kelas (Kelas.wali_kelas_user_id updated)
 * - When user role changes
 * - When siswa.wali_murid_user_id changes (via SiswaObserver)
 */
class UserNameSyncObserver
{
    /**
     * Handle the User "updated" event.
     * 
     * CRITICAL: When role changes, auto-detach invalid assignments
     */
    public function updated(User $user): void
    {
        // CRITICAL: If role changed, detach invalid assignments
        if ($user->wasChanged('role_id')) {
            $this->detachInvalidAssignments($user);
            $this->syncUserName($user);
        }
    }
    
    /**
     * Detach user from assignments that are invalid for their new role.
     * 
     * EXAMPLES:
     * - Kaprodi → Guru: Remove from all Jurusan
     * - Wali Kelas → Guru: Remove from all Kelas
     * - Wali Murid → Guru: Remove from all Siswa
     * 
     * @param User $user
     * @return void
     */
    private function detachInvalidAssignments(User $user): void
    {
        $newRole = $user->role;
        
        if (!$newRole) {
            return;
        }
        
        // Get old role from original attributes
        $oldRoleId = $user->getOriginal('role_id');
        $oldRole = $oldRoleId ? \App\Models\Role::find($oldRoleId) : null;
        
        if (!$oldRole) {
            return;
        }
        
        // CASE 1: Was Kaprodi, now something else → Detach from Jurusan
        if ($oldRole->nama_role === 'Kaprodi' && $newRole->nama_role !== 'Kaprodi') {
            Jurusan::where('kaprodi_user_id', $user->id)
                ->update(['kaprodi_user_id' => null]);
            
            \Log::info("User {$user->username} role changed from Kaprodi to {$newRole->nama_role}. Detached from all Jurusan.");
        }
        
        // CASE 2: Was Wali Kelas, now something else → Detach from Kelas
        if ($oldRole->nama_role === 'Wali Kelas' && $newRole->nama_role !== 'Wali Kelas') {
            Kelas::where('wali_kelas_user_id', $user->id)
                ->update(['wali_kelas_user_id' => null]);
            
            \Log::info("User {$user->username} role changed from Wali Kelas to {$newRole->nama_role}. Detached from all Kelas.");
        }
        
        // CASE 3: Was Wali Murid, now something else → Detach from Siswa
        if ($oldRole->nama_role === 'Wali Murid' && $newRole->nama_role !== 'Wali Murid') {
            \App\Models\Siswa::where('wali_murid_user_id', $user->id)
                ->update(['wali_murid_user_id' => null]);
            
            \Log::info("User {$user->username} role changed from Wali Murid to {$newRole->nama_role}. Detached from all Siswa.");
        }
    }
    
    /**
     * Sync user name (and username for Wali Murid) based on role and assignment.
     * 
     * RULES (Updated 2025-12-26):
     * - Kaprodi → nama = "Kaprodi [Nama Jurusan]"
     * - Wali Kelas → nama = "Wali Kelas [Nama Kelas]"
     * - Wali Murid → nama = "Wali dari [Nama Siswa Pertama]", username = "wali.[nisn_pertama]"
     * - Guru/Staff → nama = Generic role name ("Guru", "Operator Sekolah", etc.)
     * - Developer → SKIP (never auto-synced for testing flexibility)
     * 
     * @param User $user
     * @return void
     */
    public function syncUserName(User $user): void
    {
        $role = $user->role;
        
        if (!$role) {
            return;
        }
        
        // CRITICAL: Skip auto-sync for Developer role
        if ($role->nama_role === 'Developer') {
            return; // Developer names stay as-is, even if assigned to Jurusan/Kelas
        }
        
        $newName = null;
        $newUsername = null;
        $newEmail = null;
        
        switch ($role->nama_role) {
            case 'Kaprodi':
                // Find jurusan where this user is kaprodi
                $jurusan = Jurusan::where('kaprodi_user_id', $user->id)->first();
                if ($jurusan) {
                    $newName = "Kaprodi {$jurusan->nama_jurusan}";
                } else {
                    // Not assigned yet
                    $newName = "Kaprodi";
                }
                break;
                
            case 'Wali Kelas':
                // Find kelas where this user is wali kelas
                $kelas = Kelas::where('wali_kelas_user_id', $user->id)->first();
                if ($kelas) {
                    $newName = "Wali Kelas {$kelas->nama_kelas}";
                } else {
                    // Not assigned yet
                    $newName = "Wali Kelas";
                }
                break;
                
            case 'Wali Murid':
                // Find first siswa where this user is wali murid (ordered by id = siswa pertama)
                $siswa = \App\Models\Siswa::where('wali_murid_user_id', $user->id)
                    ->orderBy('id')
                    ->first();
                
                if ($siswa) {
                    // Update nama berdasarkan nama siswa pertama
                    $newName = "Wali dari {$siswa->nama_siswa}";
                    
                    // IMPORTANT: Hanya update username jika BELUM pernah diubah manual oleh user
                    // Jika user sudah mengubah username sendiri (hasChangedUsername = true), kita hormati keputusan mereka
                    if (!$user->hasChangedUsername()) {
                        $nisnClean = preg_replace('/\D+/', '', $siswa->nisn ?? '');
                        if ($nisnClean !== '') {
                            $baseUsername = 'wali.' . $nisnClean;
                            
                            // Check if username needs to change
                            if ($user->username !== $baseUsername) {
                                // Check if new username is available
                                $usernameToUse = $baseUsername;
                                $counter = 1;
                                
                                while (User::where('username', $usernameToUse)
                                    ->where('id', '!=', $user->id)
                                    ->exists()) {
                                    $counter++;
                                    $usernameToUse = $baseUsername . $counter;
                                }
                                
                                $newUsername = $usernameToUse;
                                $newEmail = $usernameToUse . '@walimurid.local';
                            }
                        }
                    }
                } else {
                    // No siswa connected - keep generic name
                    $newName = "Wali Murid";
                }
                break;
                
            default:
                // For other roles: Use role name directly
                // Examples: "Operator Sekolah", "Kepala Sekolah", "Waka Kesiswaan", "Guru"
                $newName = $role->nama_role;
                break;
        }
        
        // Build update array
        $updates = [];
        
        // Nama selalu di-update otomatis (tidak ada tracking untuk nama)
        if ($newName && $newName !== $user->nama) {
            $updates['nama'] = $newName;
        }
        
        // Username hanya di-update jika BELUM pernah diubah manual
        if ($newUsername && $newUsername !== $user->username && !$user->hasChangedUsername()) {
            $updates['username'] = $newUsername;
        }
        
        // Email ikut username
        if ($newEmail && $newEmail !== $user->email && !$user->hasChangedUsername()) {
            $updates['email'] = $newEmail;
        }
        
        // Update if we have changes
        if (!empty($updates)) {
            // Use updateQuietly to prevent infinite loop
            $user->updateQuietly($updates);
        }
    }
}
