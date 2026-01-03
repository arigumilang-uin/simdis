<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\TindakLanjut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    /**
     * List semua kasus menunggu persetujuan
     */
    public function index()
    {
        $kasusMenunggu = TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->where('status', 'Menunggu Persetujuan')
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('kepala_sekolah.approvals.index', [
            'kasusMenunggu' => $kasusMenunggu,
        ]);
    }

    /**
     * Preview Laporan - SUDAH FIX RELATION USER
     */
public function preview(Request $request)
{
    // 1. Ambil input filter
    $filters = $request->all();
    $reportType = $request->input('report_type');

    // 2. Mulai Query dasar
    $query = TindakLanjut::with(['siswa.kelas.jurusan']);

    // 3. FILTER JURUSAN - Menggunakan Nama Jurusan (String) seperti di screenshot kamu
    if ($request->filled('jurusan_id')) {
        $query->whereHas('siswa.kelas.jurusan', function($q) use ($request) {
            $q->where('nama_jurusan', $request->jurusan_id);
        });
    }

    // 4. FILTER KELAS
    if ($request->filled('kelas_id') && $request->kelas_id != '-- Semua Kelas --') {
        $query->whereHas('siswa.kelas', function($q) use ($request) {
            $q->where('nama_kelas', $request->kelas_id);
        });
    }

    // 5. FILTER JENIS LAPORAN (Berdasarkan Status di sistem kamu)
    if ($reportType == 'Laporan Pelanggaran') {
        // Hanya yang belum disetujui/masih proses
        $query->where('status', 'Menunggu Persetujuan');
    } elseif ($reportType == 'Laporan Siswa Bermasalah') {
        // Semua siswa yang ada catatan tindak lanjutnya (termasuk yang poinnya 0)
        $query->whereIn('status', ['Menunggu Persetujuan', 'Disetujui']);
    }

    // 6. FILTER TANGGAL
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
    }

    $data = $query->get();

    return view('kepala_sekolah.reports.preview', [
        'data' => $data,
        'filters' => $filters,
        'reportType' => $reportType ?? 'Laporan'
    ]);
}
    public function exportCsv(Request $request)
    {
        return "Fungsi Export CSV sedang dalam pengerjaan";
    }

    public function exportPdf(Request $request)
    {
        return "Fungsi Export PDF sedang dalam pengerjaan";
    }

    /**
     * Show detail kasus - SUDAH FIX RELATION USER
     */
    public function show(TindakLanjut $tindakLanjut)
    {
        // INFO: 'user' dihapus dari load() agar tidak error
        $tindakLanjut->load(['siswa.kelas.jurusan', 'siswa.waliMurid', 'suratPanggilan']);

        return view('kepala_sekolah.approvals.show', [
            'kasus' => $tindakLanjut,
        ]);
    }

    /**
     * Process approval
     */
    public function process(Request $request, TindakLanjut $tindakLanjut)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'catatan_kepala_sekolah' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $action = $request->input('action');
        $catatan = $request->input('catatan_kepala_sekolah');

        DB::beginTransaction();
        try {
            $status = ($action === 'approve') ? 'Disetujui' : 'Ditolak';
            
            $tindakLanjut->update([
                'status' => $status,
                'catatan_kepala_sekolah' => $catatan,
                'disetujui_oleh' => $user->id,
                'tanggal_disetujui' => now(),
            ]);

            DB::commit();
            return redirect()->route('kepala-sekolah.approvals.index')
                ->with('success', "Kasus berhasil $status.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}