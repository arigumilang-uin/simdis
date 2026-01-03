<?php

namespace App\Services\Statistics;

use App\Models\RiwayatPelanggaran;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * StatisticsService
 * 
 * Centralized service for calculating statistics across the application.
 * Reduces code duplication in dashboard and data controllers.
 */
class StatisticsService
{
    /**
     * Get statistics for a collection of siswa IDs
     * 
     * @param Collection $siswaIds
     * @return array
     */
    public function getSiswaStatistics(Collection $siswaIds): array
    {
        return [
            'total_siswa' => $siswaIds->count(),
            'total_pelanggaran' => RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)->count(),
            'pelanggaran_bulan_ini' => RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)
                ->whereMonth('tanggal_kejadian', now()->month)
                ->count(),
            'pelanggaran_tahun_ini' => RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)
                ->whereYear('tanggal_kejadian', now()->year)
                ->count(),
        ];
    }

    /**
     * Get chart data for pelanggaran over time
     * 
     * @param Collection $siswaIds
     * @param int $months Number of months to look back
     * @return Collection
     */
    public function getPelanggaranChartData(Collection $siswaIds, int $months = 6): Collection
    {
        return RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)
            ->where('tanggal_kejadian', '>=', now()->subMonths($months))
            ->selectRaw('MONTH(tanggal_kejadian) as bulan, YEAR(tanggal_kejadian) as tahun, COUNT(*) as total')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();
    }

    /**
     * Get top violators (siswa with most violations)
     * 
     * @param Collection $siswaIds
     * @param int $limit
     * @return Collection
     */
    public function getTopViolators(Collection $siswaIds, int $limit = 10): Collection
    {
        return DB::table('riwayat_pelanggaran')
            ->select('siswa_id', DB::raw('COUNT(*) as total_pelanggaran'))
            ->whereIn('siswa_id', $siswaIds)
            ->groupBy('siswa_id')
            ->orderByDesc('total_pelanggaran')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $siswa = Siswa::with('kelas')->find($item->siswa_id);
                return [
                    'siswa' => $siswa,
                    'total_pelanggaran' => $item->total_pelanggaran,
                ];
            });
    }

    /**
     * Get violations grouped by category
     * 
     * @param Collection $siswaIds
     * @return Collection
     */
    public function getPelanggaranByCategory(Collection $siswaIds): Collection
    {
        return RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->join('kategori_pelanggaran', 'jenis_pelanggaran.kategori_id', '=', 'kategori_pelanggaran.id')
            ->select('kategori_pelanggaran.nama_kategori', DB::raw('COUNT(*) as total'))
            ->groupBy('kategori_pelanggaran.id', 'kategori_pelanggaran.nama_kategori')
            ->get();
    }

    /**
     * Get complete statistics package for jurusan/kelas detail view
     * 
     * @param Collection $siswaIds
     * @param int $chartMonths
     * @param int $topLimit
     * @return array
     */
    public function getCompleteStatistics(Collection $siswaIds, int $chartMonths = 6, int $topLimit = 10): array
    {
        return [
            'stats' => $this->getSiswaStatistics($siswaIds),
            'chartData' => $this->getPelanggaranChartData($siswaIds, $chartMonths),
            'topSiswa' => $this->getTopViolators($siswaIds, $topLimit),
            'pelanggaranPerKategori' => $this->getPelanggaranByCategory($siswaIds),
        ];
    }

    /**
     * Get dashboard statistics for filtered data
     * 
     * @param array $filters
     * @return array
     */
    public function getDashboardStatistics(array $filters = []): array
    {
        $query = RiwayatPelanggaran::query();

        // Apply filters if provided
        if (isset($filters['start_date'])) {
            $query->whereDate('tanggal_kejadian', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->whereDate('tanggal_kejadian', '<=', $filters['end_date']);
        }
        if (isset($filters['kelas_id'])) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $filters['kelas_id']));
        }
        if (isset($filters['jurusan_id'])) {
            $query->whereHas('siswa.kelas', fn($q) => $q->where('jurusan_id', $filters['jurusan_id']));
        }
        if (isset($filters['angkatan'])) {
            $query->whereHas('siswa.kelas', fn($q) => $q->where('nama_kelas', 'like', $filters['angkatan'] . ' %'));
        }

        $pelanggaran = $query->get();

        return [
            'total' => $pelanggaran->count(),
            'by_jenis' => $pelanggaran->groupBy('jenis_pelanggaran_id'),
            'by_month' => $pelanggaran->groupBy(fn($item) => $item->tanggal_kejadian->format('Y-m')),
        ];
    }

    /**
     * Get top violations by type
     * 
     * @param int $limit
     * @param array $filters
     * @return Collection
     */
    public function getTopViolationTypes(int $limit = 5, array $filters = []): Collection
    {
        $query = RiwayatPelanggaran::query();

        // Apply filters
        if (isset($filters['start_date'])) {
            $query->whereDate('tanggal_kejadian', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->whereDate('tanggal_kejadian', '<=', $filters['end_date']);
        }

        return $query->select('jenis_pelanggaran_id', DB::raw('count(*) as total'))
            ->groupBy('jenis_pelanggaran_id')
            ->with('jenisPelanggaran')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }
}
