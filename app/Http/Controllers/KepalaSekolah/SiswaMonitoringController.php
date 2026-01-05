<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Services\Pelanggaran\PelanggaranRulesEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaMonitoringController extends Controller
{
    protected $rulesEngine;

    public function __construct(PelanggaranRulesEngine $rulesEngine)
    {
        $this->rulesEngine = $rulesEngine;
    }

    /**
     * Display monitoring data siswa.
     */
    public function index(Request $request)
    {
        // 1. Prepare Query
        $query = Siswa::with(['kelas.jurusan']);
        
        // 2. Apply Filters
        // Filter Jurusan
        if ($request->filled('jurusan_id')) {
            $query->whereHas('kelas', function($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        // Filter Kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Search Name/NISN
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }
        
        // Filter Status (Bermasalah Only)
        if ($request->boolean('bermasalah_only')) {
            $query->whereHas('riwayatPelanggaran');
        }

        // 3. Calculate Statistics (Global based on filter)
        // Clone query for stats to avoid modifying the main query
        $statsQuery = clone $query;
        $totalSiswa = $statsQuery->count();
        
        $bermasalahQuery = clone $query;
        $siswaBermasalah = $bermasalahQuery->whereHas('riwayatPelanggaran')->count();
        
        // 4. Paginate Results
        $siswa = $query->orderBy('nama_siswa')->paginate(20)->withQueryString();

        // 5. Calculate Points for Displayed Data
        $siswa->getCollection()->transform(function ($student) {
            $student->total_poin = $this->rulesEngine->hitungTotalPoinAkumulasi($student->id);
            $student->jumlah_pelanggaran = $student->riwayatPelanggaran()->count();
            return $student;
        });

        // 6. Support Data for View
        $jurusanList = Jurusan::orderBy('nama_jurusan')->get();
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('kepala_sekolah.data.siswa.index', compact(
            'siswa', 
            'jurusanList', 
            'kelasList',
            'totalSiswa',
            'siswaBermasalah'
        ));
    }

    /**
     * Show detailed stats for a specific student.
     */
    public function show(Siswa $siswa)
    {
        $siswa->load(['kelas.jurusan', 'waliMurid']);
        
        // Calculate points
        $totalPoin = $this->rulesEngine->hitungTotalPoinAkumulasi($siswa->id);
        
        // Get violations with rules
        $riwayat = $siswa->riwayatPelanggaran()
            ->with(['jenisPelanggaran', 'user'])
            ->latest('tanggal_kejadian')
            ->get();
            
        // Get active cases
        $kasus = $siswa->tindakLanjut()
            ->with(['suratPanggilan'])
            ->latest()
            ->get();

        return view('kepala_sekolah.data.siswa.show', compact('siswa', 'totalPoin', 'riwayat', 'kasus'));
    }
}
