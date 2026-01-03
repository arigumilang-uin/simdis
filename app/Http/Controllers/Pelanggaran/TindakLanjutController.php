<?php

namespace App\Http\Controllers\Pelanggaran;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\TindakLanjut;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * TindakLanjutController (LEGACY)
 *
 * @deprecated Gunakan App\Http\Controllers\TindakLanjut\TindakLanjutController sebagai gantinya.
 *             Controller ini dipertahankan untuk backward compatibility dengan route `kasus.*`.
 *             Akan dihapus setelah semua views di-migrate ke route `tindak-lanjut.*`.
 *
 * Controller untuk pengelolaan tindak lanjut kasus pelanggaran siswa.
 * Fitur: validasi akses berbasis role, update status dengan business rules, cetak surat PDF.
 * Penjaga keamanan: hindari downgrade status illegal, proteksi status "Disetujui" (hanya Kepsek).
 *
 * @see \App\Http\Controllers\TindakLanjut\TindakLanjutController - Clean Architecture version
 */
class TindakLanjutController extends Controller
{
    /**
     * Tampilkan halaman detail/kelola kasus pelanggaran.
     * Validasi akses: Wali Kelas (kelas binaan), Kaprodi (jurusan binaan), Wali Murid (anak sendiri).
     */
    public function edit($id)
    {
        // Ambil data kasus beserta relasinya (Siswa, Surat, Tracking)
        $kasus = TindakLanjut::with([
            'siswa.kelas', 
            'suratPanggilan.printLogs.user',
            'ditanganiOleh',
            'diselesaikanOleh',
        ])->findOrFail($id);

        // Validasi akses berpasangan: pastikan user punya scope untuk melihat/kelola kasus ini
        $this->validateAccessToKasus($kasus);

        return view('tindaklanjut.edit', ['kasus' => $kasus]);
    }

    /**
     * Perbarui status dan data kasus dengan proteksi business rules.
     * Aturan:
     *   - Status "Disetujui" hanya boleh diset oleh Kepala Sekolah
     *   - Status "Menunggu Persetujuan" hanya bisa diubah oleh Kepala Sekolah
     *   - Kasus "Disetujui" tidak boleh downgrade ke "Baru" atau "Menunggu Persetujuan"
     *   - Kasus "Selesai" tidak boleh diubah kembali (final)
     */
    public function update(Request $request, $id)
    {
        // Validasi input dasar
        $request->validate([
            'status' => 'required|in:Baru,Ditangani,Selesai,Menunggu Persetujuan,Disetujui', 
            'denda_deskripsi' => 'nullable|string',
            'tanggal_tindak_lanjut' => 'required|date',
        ]);

        // Ambil data kasus dan user
        $kasus = TindakLanjut::findOrFail($id);
        $user = Auth::user();
        $statusLama = $kasus->status;
        $statusBaru = $request->status;

        // Jalankan business rules / penjaga keamanan status
        $this->validateStatusTransition($statusLama, $statusBaru, $user);

        // Siapkan data untuk update
        $dataUpdate = [
            'status' => $statusBaru,
            'denda_deskripsi' => $request->denda_deskripsi,
            'tanggal_tindak_lanjut' => $request->tanggal_tindak_lanjut,
        ];

        // Catat siapa yang menyetujui jika status berubah menjadi "Disetujui"
        if ($statusBaru === 'Disetujui') {
            $dataUpdate['penyetuju_user_id'] = Auth::id();
        }

        // Eksekusi update
        $kasus->update($dataUpdate);

        // Redirect dinamis berdasarkan role user
        return $this->redirectAfterUpdate($user);
    }
    /**
     * Generate dan cetak/download PDF surat panggilan.
     * Logika: otomatis update status "Baru" atau "Disetujui" ke "Ditangani" ketika surat dicetak.
     * Proteksi: jangan cetak surat jika masih "Menunggu Persetujuan" (belum acc Kepsek).
     */
    public function cetakSurat($id)
    {
        $kasus = TindakLanjut::with(['siswa.kelas.jurusan', 'suratPanggilan', 'siswa.waliMurid', 'siswa.kelas.waliKelas'])
            ->findOrFail($id);

        // Proteksi: jangan cetak jika belum disetujui (kasus Surat 3)
        if ($kasus->status === 'Menunggu Persetujuan') {
            return back()->with('error', 'DITOLAK: Surat tidak dapat dicetak karena kasus belum disetujui oleh Kepala Sekolah.');
        }

        // Validasi: data surat harus sudah ada
        if (!$kasus->suratPanggilan) {
            return back()->with('error', 'Draft surat belum tersedia.');
        }

        // Otomasi status: jika surat dicetak, artinya proses pemanggilan dimulai
        // Update "Baru" atau "Disetujui" menjadi "Ditangani"
        if (in_array($kasus->status, ['Baru', 'Disetujui'])) {
            $kasus->update([
                'status' => 'Ditangani',
                'tanggal_tindak_lanjut' => now(),  // Catat tanggal mulai ditangani otomatis
            ]);
        }

        // Convert logo to Base64 for DomPDF compatibility
        $path = public_path('assets/images/logo_riau.png');
        $logoBase64 = null;
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        // **OPTIMIZED**: Gunakan pembina_roles langsung untuk template tanda tangan
    // Mapping pembina_roles ke format yang dibutuhkan template
    $pembinaRoles = $kasus->suratPanggilan->pembina_roles ?? ['Wali Kelas', 'Waka Kesiswaan', 'Kepala Sekolah'];
    
    // Convert pembina roles menjadi format yang dibutuhkan template
    $pihakTerlibat = [
        'wali_kelas' => in_array('Wali Kelas', $pembinaRoles),
        'kaprodi' => in_array('Kaprodi', $pembinaRoles),
        'waka_kesiswaan' => in_array('Waka Kesiswaan', $pembinaRoles) || in_array('Waka Sarana', $pembinaRoles),
        'kepala_sekolah' => in_array('Kepala Sekolah', $pembinaRoles),
    ];

    // Siapkan data untuk view PDF
    $dataForPdf = [
        'siswa' => $kasus->siswa,
        'surat' => $kasus->suratPanggilan,
        'logoBase64' => $logoBase64,
        'pihakTerlibat' => $pihakTerlibat,
    ];

    // Generate PDF dengan view hardcoded
    $pdf = Pdf::loadView('pdf.surat-panggilan', $dataForPdf);
    // Set Paper F4 (Folio): 215mm x 330mm
    $pdf->setPaper([0, 0, 609.4488, 935.433], 'portrait');

    // Return stream untuk ditampilkan di browser
    return $pdf->stream('Surat_Panggilan_' . $kasus->siswa->nisn . '.pdf');
}

    /**
     * Validasi akses user terhadap kasus berdasarkan role.
     * Throw 403 jika tidak memiliki akses.
     */
    private function validateAccessToKasus(TindakLanjut $kasus): void
    {
        $user = Auth::user();

        if ($user->hasRole('Wali Kelas')) {
            $kelasBinaan = $user->kelasDiampu;
            if (!$kelasBinaan || $kasus->siswa->kelas_id !== $kelasBinaan->id) {
                abort(403, 'AKSES DITOLAK: Anda hanya dapat mengelola kasus siswa di kelas yang Anda ampu.');
            }
        } elseif ($user->hasRole('Kaprodi')) {
            $jurusanBinaan = $user->jurusanDiampu;
            if (!$jurusanBinaan || $kasus->siswa->kelas->jurusan_id !== $jurusanBinaan->id) {
                abort(403, 'AKSES DITOLAK: Anda hanya dapat mengelola kasus di jurusan Anda.');
            }
        } elseif ($user->hasRole('Wali Murid')) {
            $anakIds = $user->anakWali->pluck('id');
            if (!$anakIds->contains($kasus->siswa_id)) {
                abort(403, 'AKSES DITOLAK: Anda hanya dapat melihat kasus untuk anak Anda.');
            }
        }
    }

    /**
     * Validasi transisi status dengan business rules.
     * Throw validation error jika transisi tidak diperbolehkan.
     */
    private function validateStatusTransition(string $statusLama, string $statusBaru, $user): void
    {
        // Aturan 1: Kasus "Disetujui" tidak boleh downgrade ke "Baru" atau "Menunggu Persetujuan"
        if ($statusLama === 'Disetujui' && in_array($statusBaru, ['Baru', 'Menunggu Persetujuan'])) {
            $this->throwValidationError('status', 'ILLEGAL ACTION: Kasus yang sudah disetujui Kepala Sekolah tidak bisa dikembalikan ke status awal! Silakan lanjutkan ke proses penanganan.');
        }

        // Aturan 2: Status "Menunggu Persetujuan" hanya bisa diubah oleh Kepala Sekolah
        if ($statusLama === 'Menunggu Persetujuan' && !$user->hasRole('Kepala Sekolah')) {
            $this->throwValidationError('status', 'AKSES DITOLAK: Kasus ini sedang menunggu persetujuan Kepala Sekolah. Anda tidak dapat mengubah statusnya saat ini.');
        }

        // Aturan 3: Status "Disetujui" hanya boleh diset oleh Kepala Sekolah
        if ($statusBaru === 'Disetujui' && !$user->hasRole('Kepala Sekolah')) {
            $this->throwValidationError('status', 'AKSES DITOLAK: Hanya Kepala Sekolah yang berhak memberikan status Disetujui.');
        }

        // Aturan 4: Kasus "Selesai" adalah final (tidak boleh diubah)
        if ($statusLama === 'Selesai') {
            $this->throwValidationError('status', 'FINAL: Kasus ini sudah ditutup (Selesai). Anda tidak dapat mengubah statusnya lagi.');
        }
    }

    /**
     * Redirect dinamis setelah update status berdasarkan role user.
     */
    private function redirectAfterUpdate($user)
    {
        if ($user->hasRole('Kepala Sekolah')) {
            return redirect()->route('dashboard.kepsek')->with('success', 'Dokumen berhasil disetujui!');
        } elseif ($user->hasAnyRole(['Waka Kesiswaan', 'Operator Sekolah'])) {
            return redirect()->route('dashboard.admin')->with('success', 'Kasus berhasil diperbarui!');
        } else {
            return redirect()->route('dashboard.walikelas')->with('success', 'Kasus berhasil diperbarui!');
        }
    }

    /**
     * Helper untuk throw validation error (redirect back dengan error).
     */
    private function throwValidationError(string $field, string $message): void
    {
        throw \Illuminate\Validation\ValidationException::withMessages([$field => $message]);
    }
}

