<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Admin Dashboard Controller (Operator Sekolah & Waka Kesiswaan)
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Panggil DashboardService untuk data
 * - Return View
 * 
 * ATURAN:
 * - TIDAK BOLEH ada query database langsung
 * - Semua statistik dari DashboardService
 * 
 * @package App\Http\Controllers\Dashboard
 */
class AdminDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Display dashboard based on user role.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();

        // =============================================================
        // SCENARIO A: OPERATOR SEKOLAH & WAKA KURIKULUM
        // =============================================================
        if ($user->hasRole('Operator Sekolah') || $user->hasRole('Waka Kurikulum')) {
            $stats = $this->dashboardService->getOperatorStats();
            return view('dashboards.operator', $stats);
        }

        // =============================================================
        // SCENARIO B: WAKA KESISWAAN
        // =============================================================
        
        // Build filters from request
        $filters = [
            'start_date' => $request->input('start_date', date('Y-m-01')),
            'end_date' => $request->input('end_date', date('Y-m-d')),
            'jurusan_id' => $request->input('jurusan_id'),
            'kelas_id' => $request->input('kelas_id'),
        ];

        // Get data from service
        $stats = $this->dashboardService->getWakaStats($filters);
        $daftarKasus = $this->dashboardService->getWakaKasus($filters);
        $chartPelanggaran = $this->dashboardService->getChartPelanggaranByJenis($filters);
        $chartKelas = $this->dashboardService->getChartPelanggaranByKelas($filters);

        // AJAX Response for Live Filtering
        if ($request->ajax()) {
            return response()->json([
                'stats' => view('dashboards._waka_stats', $stats)->render(),
                'table' => view('dashboards._waka_table', compact('daftarKasus'))->render(),
                'charts' => [
                    'pelanggaran' => $chartPelanggaran,
                    'kelas' => $chartKelas,
                ]
            ]);
        }

        // Get filter options
        $allJurusan = $this->dashboardService->getAllJurusan();
        $allKelas = $this->dashboardService->getAllKelas();

        return view('dashboards.waka', array_merge($stats, [
            'daftarKasus' => $daftarKasus,
            'chartLabels' => $chartPelanggaran['labels'],
            'chartData' => $chartPelanggaran['data'],
            'chartKelasLabels' => $chartKelas['labels'],
            'chartKelasData' => $chartKelas['data'],
            'allJurusan' => $allJurusan,
            'allKelas' => $allKelas,
            'startDate' => $filters['start_date'],
            'endDate' => $filters['end_date'],
        ]));
    }
}