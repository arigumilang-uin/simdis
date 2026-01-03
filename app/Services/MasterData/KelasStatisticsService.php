<?php

namespace App\Services\MasterData;

use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

/**
 * Kelas Statistics Service
 * 
 * RESPONSIBILITY: Calculate statistical data for Kelas monitoring
 * ARCHITECTURE: Service Layer (Clean Architecture)
 * PERFORMANCE: Uses DB queries instead of Model hydration
 */
class KelasStatisticsService
{
    /**
     * Get comprehensive statistics for a kelas
     * 
     * OPTIMIZATION: Raw DB queries for aggregations
     * Returns only calculated values, not Models
     * 
     * @param Kelas $kelas
     * @return array
     */
    public function getKelasStatistics(Kelas $kelas): array
    {
        // OPTIMIZATION 1: Count siswa WITHOUT loading models
        $totalSiswa = DB::table('siswa')
            ->where('kelas_id', $kelas->id)
            ->whereNull('deleted_at')
            ->count();
        
        // Get siswa IDs
        $siswaIds = DB::table('siswa')
            ->where('kelas_id', $kelas->id)
            ->whereNull('deleted_at')
            ->pluck('id');
        
        // OPTIMIZATION 2: Count total pelanggaran
        $totalPelanggaran = DB::table('riwayat_pelanggaran')
            ->whereIn('siswa_id', $siswaIds)
            ->whereNull('deleted_at')
            ->count();
        
        // OPTIMIZATION 3: Count siswa perlu pembinaan
        $siswaPerluPembinaan = DB::table('siswa')
            ->where('siswa.kelas_id', $kelas->id)
            ->whereNull('siswa.deleted_at')
            ->whereRaw('(
                SELECT COALESCE(SUM(jp.poin), 0)
                FROM riwayat_pelanggaran rp
                JOIN jenis_pelanggaran jp ON rp.jenis_pelanggaran_id = jp.id
                WHERE rp.siswa_id = siswa.id
                AND rp.deleted_at IS NULL
            ) >= 55')
            ->count();
        
        // OPTIMIZATION 4: Calculate total poin (for average)
        $totalPoin = DB::table('riwayat_pelanggaran')
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->whereIn('riwayat_pelanggaran.siswa_id', $siswaIds)
            ->whereNull('riwayat_pelanggaran.deleted_at')
            ->sum('jenis_pelanggaran.poin');
        
        $avgPoin = $totalSiswa > 0 ? $totalPoin / $totalSiswa : 0;
        
        return [
            'total_siswa' => $totalSiswa,
            'total_pelanggaran' => $totalPelanggaran,
            'siswa_perlu_pembinaan' => $siswaPerluPembinaan,
            'avg_poin' => $avgPoin,
        ];
    }
    
    /**
     * Get siswa list with pre-calculated points
     * 
     * USAGE: For display purposes (need actual data, not just counts)
     * Uses subquery to batch-calculate points
     * 
     * @param int $kelasId
     * @return \Illuminate\Support\Collection
     */
    public function getSiswaWithPoints(int $kelasId): \Illuminate\Support\Collection
    {
        return DB::table('siswa')
            ->where('kelas_id', $kelasId)
            ->whereNull('deleted_at')
            ->select([
                'siswa.*',
                DB::raw('(
                    SELECT COALESCE(SUM(jp.poin), 0)
                    FROM riwayat_pelanggaran rp
                    JOIN jenis_pelanggaran jp ON rp.jenis_pelanggaran_id = jp.id
                    WHERE rp.siswa_id = siswa.id
                    AND rp.deleted_at IS NULL
                ) as total_poin')
            ])
            ->orderBy('nama_siswa')
            ->get();
    }
}
