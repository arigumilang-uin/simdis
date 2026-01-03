<?php

namespace App\Repositories;

use App\Models\Jurusan;
use Illuminate\Database\Eloquent\Collection;

/**
 * Jurusan Repository
 * 
 * Purpose: Encapsulate all database operations for Jurusan
 * Pattern: Repository Pattern
 * Responsibility: Data Access ONLY (no business logic!)
 */
class JurusanRepository
{
    /**
     * Get all jurusan with counts (for index)
     */
    public function getAllWithCounts(): Collection
    {
        return Jurusan::withCount(['kelas', 'siswa', 'konsentrasi'])
            ->with('kaprodi')
            ->orderBy('nama_jurusan')
            ->get();
    }
    
    /**
     * Get jurusan with relationships (for show)
     */
    public function getWithRelationships(int $id): ?Jurusan
    {
        return Jurusan::with([
            'kaprodi', 
            'konsentrasi.kelas', 
            'kelas.konsentrasi',
            'kelas.waliKelas', 
            'kelas.siswa',
            'siswa'
        ])->find($id);
    }
    
    /**
     * Create new jurusan
     */
    public function create(array $data): Jurusan
    {
        return Jurusan::create($data);
    }
    
    /**
     * Update existing jurusan
     */
    public function update(Jurusan $jurusan, array $data): bool
    {
        return $jurusan->update($data);
    }
    
    /**
     * Delete jurusan
     */
    public function delete(Jurusan $jurusan): bool
    {
        return $jurusan->delete();
    }
    
    /**
     * Check if kode_jurusan exists
     */
    public function kodeExists(string $kode, ?int $excludeId = null): bool
    {
        $query = Jurusan::where('kode_jurusan', $kode);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
    
    /**
     * Generate unique kode_jurusan by appending number
     * 
     * EXACT LOGIC from original controller (lines 44-49, 117-122)
     */
    public function generateUniqueKode(string $baseKode, ?int $excludeId = null): string
    {
        $kode = $baseKode;
        $i = 1;
        
        while ($this->kodeExists($kode, $excludeId)) {
            $i++;
            $kode = $baseKode . $i;
        }
        
        return $kode;
    }
    
    /**
     * Get all kelas for a jurusan (grouped by tingkat)
     */
    public function getKelasGroupedByTingkat(Jurusan $jurusan): Collection
    {
        return $jurusan->kelas()
            ->orderBy('id')
            ->get()
            ->groupBy('tingkat');
    }
    
    /**
     * Get jurusan count statistics (for deletion validation)
     */
    public function getCounts(Jurusan $jurusan): array
    {
        return [
            'kelas' => $jurusan->kelas()->count(),
            'siswa' => $jurusan->siswa()->count(),
        ];
    }
    
    /**
     * Get jurusan with monitoring relationships (Kepala Sekolah view)
     */
    public function getAllForMonitoring(): Collection
    {
        return Jurusan::withCount(['kelas', 'siswa'])
            ->with('kaprodi')
            ->orderBy('nama_jurusan')
            ->get();
    }
    
    /**
     * Get jurusan for monitoring show view
     */
    public function getForMonitoringShow(int $id): ?Jurusan
    {
        return Jurusan::with([
            'kaprodi',
            'kelas' => function($query) {
                $query->withCount('siswa')
                      ->with('waliKelas');
            }
        ])->find($id);
    }
}
