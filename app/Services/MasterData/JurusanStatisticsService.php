<?php

namespace App\Services\MasterData;

use App\Models\Jurusan;
use Illuminate\Support\Facades\DB;

/**
 * Jurusan Statistics Service
 * 
 * RESPONSIBILITY: Calculate statistical data for Jurusan monitoring
 * ARCHITECTURE: Service Layer (Clean Architecture)
 * PERFORMANCE: Uses DB queries instead of Model hydration for aggregations
 */
class JurusanStatisticsService
{
    /**
     * Get comprehensive statistics for a jurusan
     * 
     * OPTIMIZATION: Uses raw DB queries for counts/aggregations
     * NO Model hydration for calculations!
     * 
     * @param Jurusan $jurusan
     * @return array
     */
    public function getJurusanStatistics(Jurusan $jurusan): array
    {
        // Get kelas IDs for this jurusan (lightweight query)
        $kelasIds = DB::table('kelas')
            ->where('jurusan_id', $jurusan->id)
            ->pluck('id');
        
        // OPTIMIZATION 1: Count siswa WITHOUT loading models
        $totalSiswa = DB::table('siswa')
            ->whereIn('kelas_id', $kelasIds)
            ->whereNull('deleted_at')
            ->count();
        
        // OPTIMIZATION 2: Get siswa IDs for further calculations
        $siswaIds = DB::table('siswa')
            ->whereIn('kelas_id', $kelasIds)
            ->whereNull('deleted_at')
            ->pluck('id');
        
        // OPTIMIZATION 3: Count total pelanggaran
        $totalPelanggaran = DB::table('riwayat_pelanggaran')
            ->whereIn('siswa_id', $siswaIds)
            ->whereNull('deleted_at')
            ->count();
        
        // OPTIMIZATION 4: Count siswa perlu pembinaan (poin >= 55)
        // Using subquery to calculate points efficiently
        $siswaPerluPembinaan = DB::table('siswa')
            ->whereIn('siswa.id', $siswaIds)
            ->whereNull('siswa.deleted_at')
            ->whereRaw('(
                SELECT COALESCE(SUM(jp.poin), 0)
                FROM riwayat_pelanggaran rp
                JOIN jenis_pelanggaran jp ON rp.jenis_pelanggaran_id = jp.id
                WHERE rp.siswa_id = siswa.id
                AND rp.deleted_at IS NULL
            ) >= 55')
            ->count();
        
        return [
            'total_siswa' => $totalSiswa,
            'total_pelanggaran' => $totalPelanggaran,
            'siswa_perlu_pembinaan' => $siswaPerluPembinaan,
        ];
    }
    
    /**
     * Get pelanggaran count per kelas (batch calculation)
     * 
     * OPTIMIZATION: Single query with GROUP BY instead of N queries
     * 
     * @param \Illuminate\Support\Collection $kelasIds
     * @return \Illuminate\Support\Collection [kelas_id => count]
     */
    public function getPelanggaranCountPerKelas($kelasIds): \Illuminate\Support\Collection
    {
        return DB::table('riwayat_pelanggaran')
            ->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
            ->whereIn('siswa.kelas_id', $kelasIds)
            ->whereNull('riwayat_pelanggaran.deleted_at')
            ->whereNull('siswa.deleted_at')
            ->select('siswa.kelas_id', DB::raw('COUNT(*) as total'))
            ->groupBy('siswa.kelas_id')
            ->pluck('total', 'kelas_id');
    }
}
