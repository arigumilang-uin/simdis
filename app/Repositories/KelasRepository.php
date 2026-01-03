<?php

namespace App\Repositories;

use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Kelas Repository
 * 
 * Purpose: Encapsulate all database operations for Kelas
 * Pattern: Repository Pattern
 * Responsibility: Data Access ONLY (no business logic!)
 */
class KelasRepository
{
    /**
     * Get all kelas with relationships (for index)
     */
    public function getAllWithRelationships(): Collection
    {
        return Kelas::with('jurusan', 'konsentrasi', 'waliKelas')
            ->withCount('siswa')
            ->orderBy('nama_kelas')
            ->get();
    }
    
    /**
     * Get kelas with relationships (for show)
     */
    public function getWithRelationships(int $id): ?Kelas
    {
        return Kelas::with(['jurusan', 'waliKelas', 'siswa.waliMurid'])
            ->find($id);
    }
    
    /**
     * Get jurusan by ID
     */
    public function getJurusan(int $jurusanId): ?Jurusan
    {
        return Jurusan::findOrFail($jurusanId);
    }
    
    /**
     * Get available wali kelas users
     */
    public function getAvailableWaliKelas(): Collection
    {
        return User::whereHas('role', function($q) {
            $q->where('nama_role', 'Wali Kelas');
        })->get();
    }
    
    /**
     * Create new kelas
     */
    public function create(array $data): Kelas
    {
        return Kelas::create($data);
    }
    
    /**
     * Update existing kelas
     */
    public function update(Kelas $kelas, array $data): bool
    {
        return $kelas->update($data);
    }
    
    /**
     * Delete kelas
     */
    public function delete(Kelas $kelas): bool
    {
        return $kelas->delete();
    }
    
    /**
     * Get existing kelas names for a jurusan/konsentrasi with base name pattern
     * 
     * @param int $jurusanId
     * @param string $basePattern
     * @param int|null $konsentrasiId
     * @return array
     */
    public function getExistingKelasNames(int $jurusanId, string $basePattern, ?int $konsentrasiId = null): array
    {
        $query = Kelas::where('jurusan_id', $jurusanId)
            ->where('nama_kelas', 'like', $basePattern . '%');
        
        // If konsentrasi is specified, filter by it for accurate sequential numbering
        if ($konsentrasiId !== null) {
            $query->where('konsentrasi_id', $konsentrasiId);
        }
        
        return $query->pluck('nama_kelas')->toArray();
    }
    
    /**
     * Get all kelas for monitoring (Kepala Sekolah view)
     */
    public function getAllForMonitoring(): Collection
    {
        return Kelas::with(['jurusan', 'waliKelas', 'siswa'])
            ->orderBy('nama_kelas')
            ->get();
    }
}
