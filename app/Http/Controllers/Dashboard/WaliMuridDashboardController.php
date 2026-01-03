<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;

class WaliMuridDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil User Wali Murid yang login
        $user = Auth::user();

        // 2. Ambil SEMUA Data Anak (HasMany)
        $semuaAnak = $user->anakWali; // Ini sekarang me-return Collection (List)

        // Cek jika belum ada anak yang di-link ke akun ini
        if ($semuaAnak->isEmpty()) {
            return view('dashboards.wali_murid_no_data');
        }

        // 3. LOGIKA PEMILIHAN ANAK (SWITCH CHILD)
        $selectedSiswaId = $request->query('siswa_id');
        
        if ($selectedSiswaId) {
            $siswaAktif = $semuaAnak->where('id', $selectedSiswaId)->first();
            if (!$siswaAktif) {
                $siswaAktif = $semuaAnak->first();
            }
        } else {
            $siswaAktif = $semuaAnak->first();
        }

        // 4. Ambil Statistik Poin (FIXED: Use PelanggaranService)
        $pelanggaranService = app(\App\Services\Pelanggaran\PelanggaranService::class);
        $totalPoin = $pelanggaranService->calculateTotalPoin($siswaAktif->id);

        // 5. Ambil Riwayat Pelanggaran (Anak Aktif)
        $riwayat = RiwayatPelanggaran::with('jenisPelanggaran')
            ->where('siswa_id', $siswaAktif->id)
            ->orderByDesc('tanggal_kejadian')
            ->get();

        // 6. Ambil Kasus / Sanksi (Anak Aktif)
        $kasus = TindakLanjut::where('siswa_id', $siswaAktif->id)
            ->orderByDesc('created_at')
            ->get();

        // 7. Ambil Pembinaan Aktif (jika ada)
        $pembinaanAktif = \App\Models\PembinaanStatus::forSiswa($siswaAktif->id)
            ->active()
            ->with(['rule', 'dibinaOleh'])
            ->latest()
            ->first();

        return view('dashboards.wali_murid', [
            'semuaAnak' => $semuaAnak,
            'siswa' => $siswaAktif,
            'totalPoin' => $totalPoin,
            'riwayat' => $riwayat,
            'kasus' => $kasus,
            'pembinaanAktif' => $pembinaanAktif,
        ]);
    }
}
