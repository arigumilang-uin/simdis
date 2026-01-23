<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalMengajar;
use App\Models\PeriodeSemester;
use App\Models\TemplateJam;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use App\Models\TingkatKurikulum;
use App\Enums\Hari;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

/**
 * Jadwal Mengajar Controller (Admin)
 * 
 * CRUD untuk master data jadwal mengajar.
 * Refactored: sekarang menggunakan template_jam_id dan periode_semester_id
 */
class JadwalMengajarController extends Controller
{
    /**
     * Display list of jadwal
     */
    public function index(Request $request): View
    {
        $kelasId = $request->input('kelas_id');
        $guruId = $request->input('user_id');
        $hari = $request->input('hari');
        $periodeId = $request->input('periode_id');

        // Get all periods for selector
        $allPeriodes = PeriodeSemester::orderByDesc('is_active')
            ->orderByDesc('tanggal_mulai')
            ->get();

        // Determine selected periode
        $selectedPeriode = $periodeId 
            ? PeriodeSemester::find($periodeId) 
            : PeriodeSemester::current();

        // Build query
        $query = JadwalMengajar::with(['templateJam', 'guru', 'mataPelajaran', 'kelas.jurusan']);

        // Filter by selected periode
        if ($selectedPeriode) {
            $query->where('jadwal_mengajar.periode_semester_id', $selectedPeriode->id);
        }

        if ($kelasId) {
            $query->forKelas($kelasId);
        }

        if ($guruId) {
            $query->forGuru($guruId);
        }

        if ($hari) {
            $query->forHari($hari);
        }

        // Order by hari and urutan (via template_jam)
        $jadwals = $query->join('template_jam', 'jadwal_mengajar.template_jam_id', '=', 'template_jam.id')
            ->orderBy('template_jam.hari')
            ->orderBy('template_jam.urutan')
            ->select('jadwal_mengajar.*')
            ->paginate(30);

        // Get dropdown data
        $kelasList = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $guruList = User::whereHas('role', function($q) {
            $q->whereIn('nama_role', ['Guru', 'Wali Kelas', 'Kaprodi']);
        })->orderBy('username')->get();
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return view('admin.jadwal-mengajar.index', [
            'jadwals' => $jadwals,
            'allPeriodes' => $allPeriodes,
            'selectedPeriode' => $selectedPeriode,
            'kelasId' => $kelasId,
            'guruId' => $guruId,
            'hari' => $hari,
            'kelas' => $kelasList,
            'guru' => $guruList,
            'hariList' => $hariList,
        ]);
    }

    /**
     * Matrix view for bulk jadwal input per kelas
     */
    public function matrix(Request $request): View
    {
        $kelasId = $request->input('kelas_id');
        $periodeId = $request->input('periode_id');
        $selectedHari = $request->input('hari', 'Senin');

        // Get all periods
        $allPeriodes = PeriodeSemester::orderByDesc('is_active')
            ->orderByDesc('tanggal_mulai')
            ->get();

        // Determine selected periode
        $selectedPeriode = $periodeId 
            ? PeriodeSemester::find($periodeId) 
            : PeriodeSemester::current();

        // Get all kelas
        $kelasList = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $selectedKelas = $kelasId ? Kelas::find($kelasId) : null;

        // Get template jam for selected periode and hari
        $templateJams = collect();
        if ($selectedPeriode) {
            $templateJams = TemplateJam::forPeriode($selectedPeriode->id)
                ->forHari($selectedHari)
                ->active()
                ->ordered()
                ->get();
        }

        // Get mapel for selected kelas (filtered by kurikulum)
        $mapelList = collect();
        $selectedKurikulum = null;
        if ($selectedKelas && $selectedPeriode) {
            $selectedKurikulum = $selectedKelas->getKurikulumFor($selectedPeriode->id);
            if ($selectedKurikulum) {
                $mapelList = MataPelajaran::forKurikulum($selectedKurikulum->id)
                    ->active()
                    ->orderBy('nama_mapel')
                    ->get();
            }
        }

        // Get guru list
        $guruList = User::whereHas('role', function($q) {
            $q->whereIn('nama_role', ['Guru', 'Wali Kelas', 'Kaprodi']);
        })->orderBy('username')->get();

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        // Get existing jadwal for selected kelas, periode, and hari
        $existingJadwal = [];
        if ($selectedKelas && $selectedPeriode) {
            $jadwalList = JadwalMengajar::with(['templateJam', 'mataPelajaran', 'guru'])
                ->forPeriode($selectedPeriode->id)
                ->forKelas($selectedKelas->id)
                ->forHari($selectedHari)
                ->get();

            foreach ($jadwalList as $j) {
                $existingJadwal[$j->template_jam_id] = $j;
            }
        }

        return view('admin.jadwal-mengajar.matrix', [
            'allPeriodes' => $allPeriodes,
            'selectedPeriode' => $selectedPeriode,
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'selectedKurikulum' => $selectedKurikulum,
            'templateJams' => $templateJams,
            'selectedHari' => $selectedHari,
            'hariList' => $hariList,
            'mapelList' => $mapelList,
            'guruList' => $guruList,
            'existingJadwal' => $existingJadwal,
        ]);
    }

    /**
     * Update or create jadwal cell via AJAX
     */
    public function updateCell(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'periode_semester_id' => 'required|exists:periode_semester,id',
            'template_jam_id' => 'required|exists:template_jam,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajaran,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Find existing jadwal for this slot
        $existingJadwal = JadwalMengajar::where('periode_semester_id', $validated['periode_semester_id'])
            ->where('template_jam_id', $validated['template_jam_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->first();

        // If no mapel selected, delete existing
        if (empty($validated['mata_pelajaran_id']) || empty($validated['user_id'])) {
            if ($existingJadwal) {
                // Check if has absensi
                if ($existingJadwal->absensi()->exists()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Tidak dapat menghapus jadwal yang sudah memiliki data absensi.'
                    ], 400);
                }
                $existingJadwal->delete();
            }
            return response()->json(['success' => true, 'action' => 'deleted']);
        }

        // === VALIDASI BENTROK ===
        // Cek apakah guru sudah punya jadwal di slot waktu yang sama (di kelas lain)
        $conflictQuery = JadwalMengajar::where('periode_semester_id', $validated['periode_semester_id'])
            ->where('template_jam_id', $validated['template_jam_id'])
            ->where('user_id', $validated['user_id'])
            ->where('kelas_id', '!=', $validated['kelas_id']); // Di kelas lain

        // Jika update, exclude jadwal yang sedang diedit
        if ($existingJadwal) {
            $conflictQuery->where('id', '!=', $existingJadwal->id);
        }

        $conflictJadwal = $conflictQuery->with(['kelas', 'mataPelajaran'])->first();

        if ($conflictJadwal) {
            $templateJam = TemplateJam::find($validated['template_jam_id']);
            $guru = User::find($validated['user_id']);
            
            return response()->json([
                'success' => false,
                'message' => sprintf(
                    'Guru %s sudah mengajar di kelas %s (%s) pada waktu %s %s. Satu guru hanya dapat mengajar di satu kelas pada satu waktu.',
                    $guru->username ?? 'Unknown',
                    $conflictJadwal->kelas->nama_kelas ?? 'Unknown',
                    $conflictJadwal->mataPelajaran->nama_mapel ?? 'Unknown',
                    $templateJam->hari ?? '',
                    $templateJam->waktu ?? ''
                )
            ], 400);
        }

        // Create or update
        $data = [
            'periode_semester_id' => $validated['periode_semester_id'],
            'template_jam_id' => $validated['template_jam_id'],
            'kelas_id' => $validated['kelas_id'],
            'mata_pelajaran_id' => $validated['mata_pelajaran_id'],
            'user_id' => $validated['user_id'],
            'is_active' => true,
        ];

        if ($existingJadwal) {
            $existingJadwal->update($data);
            $jadwal = $existingJadwal;
            $action = 'updated';
        } else {
            $jadwal = JadwalMengajar::create($data);
            $action = 'created';
        }

        // Load relations for response
        $jadwal->load(['mataPelajaran', 'guru']);

        return response()->json([
            'success' => true,
            'action' => $action,
            'jadwal_id' => $jadwal->id,
            'mapel_nama' => $jadwal->mataPelajaran->nama_mapel ?? '-',
            'guru_nama' => $jadwal->guru->username ?? '-',
        ]);
    }

    /**
     * Delete jadwal
     */
    public function destroy(int $id): RedirectResponse
    {
        $jadwal = JadwalMengajar::findOrFail($id);

        // Check if has absensi
        if ($jadwal->absensi()->exists()) {
            return back()->with('error', 'Jadwal tidak dapat dihapus karena sudah ada data absensi.');
        }

        $jadwal->delete();

        return redirect()
            ->route('admin.jadwal-mengajar.index')
            ->with('success', 'Jadwal mengajar berhasil dihapus.');
    }

    /**
     * API: Get mapel for a kelas (by kurikulum)
     */
    public function getMapelForKelas(Request $request): JsonResponse
    {
        $kelasId = $request->input('kelas_id');
        $periodeId = $request->input('periode_id');

        if (!$kelasId || !$periodeId) {
            return response()->json([]);
        }

        $kelas = Kelas::find($kelasId);
        if (!$kelas) {
            return response()->json([]);
        }

        $kurikulumId = $kelas->getKurikulumIdFor($periodeId);
        if (!$kurikulumId) {
            return response()->json(['error' => 'Kurikulum belum dikonfigurasi untuk tingkat ' . $kelas->tingkat], 400);
        }

        $mapels = MataPelajaran::forKurikulum($kurikulumId)
            ->active()
            ->orderBy('nama_mapel')
            ->get(['id', 'nama_mapel', 'kode_mapel']);

        return response()->json($mapels);
    }

    /**
     * API: Get template jam for periode and hari
     */
    public function getTemplateJam(Request $request): JsonResponse
    {
        $periodeId = $request->input('periode_id');
        $hari = $request->input('hari');

        if (!$periodeId || !$hari) {
            return response()->json([]);
        }

        $slots = TemplateJam::forPeriode($periodeId)
            ->forHari($hari)
            ->pelajaranOnly()
            ->active()
            ->ordered()
            ->get(['id', 'label', 'jam_mulai', 'jam_selesai', 'tipe']);

        return response()->json($slots);
    }
}
