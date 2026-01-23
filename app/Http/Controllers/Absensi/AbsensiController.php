<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use App\Services\Absensi\AbsensiService;
use App\Services\Absensi\JadwalService;
use App\Services\Absensi\PertemuanService;
use App\Models\JadwalMengajar;
use App\Models\Pertemuan;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Enums\Hari;
use App\Enums\Semester;
use App\Enums\StatusAbsensi;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

/**
 * Absensi Controller
 * 
 * Handle proses pencatatan absensi dengan grid view.
 */
class AbsensiController extends Controller
{
    public function __construct(
        private AbsensiService $absensiService,
        private JadwalService $jadwalService,
        private PertemuanService $pertemuanService
    ) {}

    /**
     * Dashboard absensi - tampilkan SEMUA jadwal per hari
     * (consecutive jadwal with same kelas/mapel are merged by service)
     */
    public function index(): View
    {
        $user = auth()->user();
        $jadwalByHari = $this->jadwalService->getJadwalForGuru($user->id);

        return view('absensi.index', [
            'jadwalByHari' => $jadwalByHari,
            'hariIni' => Hari::today()?->value,
            'currentSemester' => Semester::current()->value,
            'currentTahunAjaran' => Semester::currentTahunAjaran(),
        ]);
    }

    /**
     * Grid view absensi - siswa sebagai baris, pertemuan sebagai kolom
     */
    public function grid(int $jadwalId): View
    {
        $jadwal = JadwalMengajar::with(['mataPelajaran', 'kelas.jurusan', 'guru', 'templateJam', 'periodeSemester'])
            ->findOrFail($jadwalId);
        
        // Get hari from template_jam
        $hari = $jadwal->templateJam?->hari;
        $hariValue = $hari instanceof \App\Enums\Hari ? $hari->value : $hari;
        
        // Find all consecutive jadwal with same kelas, mapel, guru, hari, periode to calculate merged time
        $allRelatedJadwal = JadwalMengajar::where('jadwal_mengajar.kelas_id', $jadwal->kelas_id)
            ->where('jadwal_mengajar.mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->where('jadwal_mengajar.user_id', $jadwal->user_id)
            ->where('jadwal_mengajar.periode_semester_id', $jadwal->periode_semester_id)
            ->whereHas('templateJam', function($q) use ($hariValue) {
                $q->where('hari', $hariValue);
            })
            ->active()
            ->join('template_jam', 'jadwal_mengajar.template_jam_id', '=', 'template_jam.id')
            ->orderBy('template_jam.urutan')
            ->select('jadwal_mengajar.*')
            ->with('templateJam')
            ->get();
        
        // Find the merged time range for consecutive slots
        $mergedJamMulai = $jadwal->templateJam?->jam_mulai;
        $mergedJamSelesai = $jadwal->templateJam?->jam_selesai;
        $mergedJadwalIds = [$jadwal->id];
        
        if ($allRelatedJadwal->count() > 1) {
            // Find consecutive group that includes current jadwal
            $currentGroup = [];
            $prevUrutan = null;
            
            foreach ($allRelatedJadwal as $j) {
                $currUrutan = $j->templateJam?->urutan ?? 0;
                
                if ($prevUrutan !== null && $currUrutan !== $prevUrutan + 1) {
                    // Gap found - check if current jadwal is in this group
                    if (in_array($jadwal->id, array_column($currentGroup, 'id'))) {
                        break;
                    }
                    $currentGroup = [];
                }
                
                $currentGroup[] = [
                    'id' => $j->id, 
                    'urutan' => $currUrutan,
                    'jam_mulai' => $j->templateJam?->jam_mulai, 
                    'jam_selesai' => $j->templateJam?->jam_selesai
                ];
                $prevUrutan = $currUrutan;
            }
            
            // If current jadwal is in the group, use merged times
            if (in_array($jadwal->id, array_column($currentGroup, 'id')) && count($currentGroup) > 0) {
                $mergedJamMulai = $currentGroup[0]['jam_mulai'];
                $mergedJamSelesai = end($currentGroup)['jam_selesai'];
                $mergedJadwalIds = array_column($currentGroup, 'id');
            }
        }
        
        // Set merged time on jadwal for display
        $jadwal->setAttribute('merged_jam_mulai', $mergedJamMulai);
        $jadwal->setAttribute('merged_jam_selesai', $mergedJamSelesai);
        $jadwal->setAttribute('merged_jadwal_ids', $mergedJadwalIds);
        
        // Get all pertemuan for this jadwal
        $pertemuanList = Pertemuan::forJadwal($jadwalId)
            ->ordered()
            ->get();
        
        // Get siswa di kelas ini
        $siswaList = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->orderBy('nama_siswa')
            ->get();
        
        // Build absensi matrix [siswa_id][pertemuan_id] => Absensi
        $absensiMatrix = [];
        $allAbsensi = Absensi::where('jadwal_mengajar_id', $jadwalId)
            ->whereIn('pertemuan_id', $pertemuanList->pluck('id'))
            ->get();
        
        foreach ($allAbsensi as $absensi) {
            $absensiMatrix[$absensi->siswa_id][$absensi->pertemuan_id] = $absensi;
        }

        // Find today's pertemuan if any
        $todayPertemuan = $pertemuanList->first(fn($p) => $p->tanggal->isToday());

        return view('absensi.grid', [
            'jadwal' => $jadwal,
            'pertemuanList' => $pertemuanList,
            'siswaList' => $siswaList,
            'absensiMatrix' => $absensiMatrix,
            'todayPertemuan' => $todayPertemuan,
        ]);
    }

    /**
     * Update single absensi via AJAX
     */
    public function updateSingle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'status' => 'nullable|in:Hadir,Sakit,Izin,Alfa,',
        ]);

        try {
            $pertemuan = Pertemuan::with('jadwalMengajar')->findOrFail($validated['pertemuan_id']);
            
            if (empty($validated['status'])) {
                // Delete existing absensi
                Absensi::where('siswa_id', $validated['siswa_id'])
                    ->where('pertemuan_id', $validated['pertemuan_id'])
                    ->delete();
            } else {
                // Create or update
                $absensi = $this->absensiService->recordAbsensiWithPertemuan(
                    siswaId: $validated['siswa_id'],
                    pertemuanId: $validated['pertemuan_id'],
                    jadwalMengajarId: $pertemuan->jadwal_mengajar_id,
                    tanggal: $pertemuan->tanggal->toDateString(),
                    status: StatusAbsensi::from($validated['status']),
                    pencatatId: auth()->id()
                );
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch update all siswa for specific pertemuan
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'status' => 'required|in:Hadir,Sakit,Izin,Alfa',
        ]);

        try {
            $pertemuan = Pertemuan::with('jadwalMengajar.kelas')->findOrFail($validated['pertemuan_id']);
            $siswaList = Siswa::where('kelas_id', $pertemuan->jadwalMengajar->kelas_id)->get();

            foreach ($siswaList as $siswa) {
                $this->absensiService->recordAbsensiWithPertemuan(
                    siswaId: $siswa->id,
                    pertemuanId: $validated['pertemuan_id'],
                    jadwalMengajarId: $pertemuan->jadwal_mengajar_id,
                    tanggal: $pertemuan->tanggal->toDateString(),
                    status: StatusAbsensi::from($validated['status']),
                    pencatatId: auth()->id()
                );
            }

            return response()->json([
                'success' => true,
                'count' => $siswaList->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Form absensi untuk jadwal tertentu (legacy - redirect to grid)
     */
    public function create(int $jadwalId, Request $request): RedirectResponse
    {
        return redirect()->route('absensi.grid', $jadwalId);
    }

    /**
     * Simpan absensi batch (legacy)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jadwal_mengajar_id' => 'required|exists:jadwal_mengajar,id',
            'tanggal' => 'required|date',
            'absensi' => 'required|array',
            'absensi.*.status' => 'required|in:Hadir,Sakit,Izin,Alfa',
            'absensi.*.keterangan' => 'nullable|string|max:500',
        ]);

        $this->absensiService->recordAbsensiBatch(
            jadwalMengajarId: $validated['jadwal_mengajar_id'],
            tanggal: $validated['tanggal'],
            absensiData: $validated['absensi'],
            pencatatId: auth()->id()
        );

        return redirect()
            ->route('absensi.index')
            ->with('success', 'Absensi berhasil disimpan.');
    }

    /**
     * Lihat detail absensi untuk jadwal tertentu
     */
    public function show(int $jadwalId, Request $request): View
    {
        $tanggal = $request->input('tanggal', today()->toDateString());
        
        $jadwal = JadwalMengajar::with(['mataPelajaran', 'kelas.jurusan', 'guru'])
            ->findOrFail($jadwalId);
        
        $absensi = $this->absensiService->getAbsensiByJadwal($jadwalId, $tanggal);
        $statistik = $this->absensiService->getStatistikAbsensi($jadwalId, $tanggal);
        
        return view('absensi.show', [
            'jadwal' => $jadwal,
            'absensi' => $absensi,
            'statistik' => $statistik,
            'tanggal' => $tanggal,
        ]);
    }

    /**
     * Laporan rekap absensi per kelas
     * 
     * Access Control:
     * - Wali Kelas: hanya kelas yang diampu
     * - Kaprodi: semua kelas di jurusan/konsentrasinya
     * - Waka Kurikulum, Waka Kesiswaan, Operator: semua kelas
     * - Guru: TIDAK BISA AKSES (redirect)
     */
    public function report(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        
        // Get accessible classes based on role
        $accessibleKelas = $this->getAccessibleKelasForReport($user);
        
        // If user has no access (regular guru), redirect
        if ($accessibleKelas->isEmpty()) {
            return redirect()
                ->route('absensi.index')
                ->with('error', 'Anda tidak memiliki akses ke fitur rekap absensi.');
        }
        
        $kelasId = $request->input('kelas_id');
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        
        // Validate that selected kelas is accessible
        if ($kelasId && !$accessibleKelas->pluck('id')->contains($kelasId)) {
            $kelasId = null; // Reset if trying to access unauthorized kelas
        }
        
        $rekap = null;
        if ($kelasId) {
            $rekap = $this->absensiService->getRekapKelas($kelasId, $startDate, $endDate);
        }
        
        return view('absensi.report', [
            'rekap' => $rekap,
            'kelasList' => $accessibleKelas,
            'selectedKelasId' => $kelasId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'userRole' => $user->effectiveRoleName(),
            'currentSemester' => \App\Enums\Semester::current(),
            'semesterDates' => \App\Enums\Semester::currentPeriodDates(),
        ]);
    }
    
    /**
     * Get list of kelas that user can access for report
     */
    private function getAccessibleKelasForReport($user): \Illuminate\Support\Collection
    {
        $roleName = $user->effectiveRoleName();
        
        // Wali Kelas: only their class
        if ($roleName === 'Wali Kelas') {
            $kelas = $user->kelasDiampu;
            return $kelas ? collect([$kelas]) : collect([]);
        }
        
        // Kaprodi: all classes in their jurusan and konsentrasi
        if ($roleName === 'Kaprodi') {
            $jurusan = $user->jurusanDiampu;
            if (!$jurusan) {
                return collect([]);
            }
            
            return \App\Models\Kelas::with('jurusan')
                ->where(function($query) use ($jurusan) {
                    $query->where('jurusan_id', $jurusan->id)
                          ->orWhere('konsentrasi_id', $jurusan->id);
                })
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();
        }
        
        // Waka Kurikulum, Waka Kesiswaan, Operator, Kepala Sekolah: all classes
        if (in_array($roleName, ['Waka Kurikulum', 'Waka Kesiswaan', 'Operator Sekolah', 'Kepala Sekolah'])) {
            return \App\Models\Kelas::with('jurusan')
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();
        }
        
        // Regular Guru: no access
        return collect([]);
    }
}
