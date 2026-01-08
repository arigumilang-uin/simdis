<?php

namespace App\Http\Controllers\TindakLanjut;

use App\Http\Controllers\Controller;
use App\Services\TindakLanjut\TindakLanjutService;
use App\Data\TindakLanjut\TindakLanjutData;
use App\Data\TindakLanjut\TindakLanjutFilterData;
use App\Http\Requests\TindakLanjut\CreateTindakLanjutRequest;
use App\Http\Requests\TindakLanjut\UpdateTindakLanjutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tindak Lanjut Controller - Clean Architecture Pattern
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Validasi (via FormRequest)
 * - Convert ke DTO
 * - Panggil Service
 * - Return Response
 * 
 * ATURAN:
 * - TIDAK BOLEH ada business logic
 * - TIDAK BOLEH ada query database complex
 * - Target: < 20 baris per method
 */
class TindakLanjutController extends Controller
{
    /**
     * Inject TindakLanjutService via constructor.
     *
     * @param TindakLanjutService $tindakLanjutService
     */
    public function __construct(
        private TindakLanjutService $tindakLanjutService
    ) {}

    /**
     * Tampilkan daftar tindak lanjut dengan filter.
     * 
     * ROLE-BASED ACCESS:
     * - Wali Kelas: hanya kasus siswa di kelasnya DAN melibatkan Wali Kelas sebagai pembina
     * - Kaprodi: hanya kasus siswa di jurusannya DAN melibatkan Kaprodi sebagai pembina
     * - Waka Kesiswaan: hanya kasus yang melibatkan Waka Kesiswaan sebagai pembina
     * - Kepala Sekolah: hanya kasus yang melibatkan Kepala Sekolah sebagai pembina
     * - Operator Sekolah, Developer: full access (tidak ada filter pembina)
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $role = \App\Services\User\RoleService::effectiveRoleName($user);
        
        // Auto-apply role-based filter
        $kelasId = $request->input('kelas_id');
        $jurusanId = $request->input('jurusan_id');
        $pembinaRole = null; // NEW: Filter by pembina role
        
        // Wali Kelas: siswa di kelasnya + kasus yang melibatkan Wali Kelas
        if ($role === 'Wali Kelas') {
            $kelasBinaan = $user->kelasDiampu;
            if ($kelasBinaan) {
                $kelasId = $kelasBinaan->id;
            }
            $pembinaRole = 'Wali Kelas';
        }
        
        // Kaprodi: siswa di jurusannya + kasus yang melibatkan Kaprodi
        if ($role === 'Kaprodi') {
            $jurusanBinaan = $user->jurusanDiampu;
            if ($jurusanBinaan) {
                $jurusanId = $jurusanBinaan->id;
            }
            $pembinaRole = 'Kaprodi';
        }
        
        // Waka Kesiswaan: kasus yang melibatkan Waka Kesiswaan
        if ($role === 'Waka Kesiswaan') {
            $pembinaRole = 'Waka Kesiswaan';
        }
        
        // Waka Sarana: kasus yang melibatkan Waka Sarana
        if ($role === 'Waka Sarana') {
            $pembinaRole = 'Waka Sarana';
        }
        
        // Kepala Sekolah: kasus yang melibatkan Kepala Sekolah
        if ($role === 'Kepala Sekolah') {
            $pembinaRole = 'Kepala Sekolah';
        }
        
        // Operator Sekolah & Developer: full access (no pembina filter)
        
        // Build filter data
        $filters = TindakLanjutFilterData::from([
            'siswa_id' => $request->input('siswa_id'),
            'kelas_id' => $kelasId,
            'jurusan_id' => $jurusanId,
            'status' => $request->input('status') ? \App\Enums\StatusTindakLanjut::from($request->input('status')) : null,
            'pending_approval_only' => $request->boolean('pending_approval'),
            'active_only' => $request->boolean('active_only'),
            'perPage' => $request->input('perPage', 20),
            'pembina_role' => $pembinaRole, // NEW: Apply pembina filter
        ]);

        $tindakLanjut = $this->tindakLanjutService->getFilteredTindakLanjut($filters);

        return view('tindaklanjut.index', compact('tindakLanjut', 'role'));
    }

    /**
     * Tampilkan daftar tindak lanjut yang menunggu persetujuan.
     * 
     * Shortcut untuk akses cepat approval.
     */
    public function pendingApproval(Request $request): View
    {
        // Force filter pending_approval = true
        $request->merge(['pending_approval' => true]);
        
        return $this->index($request);
    }

    /**
     * Tampilkan form create tindak lanjut.
     * 
     * ALUR:
     * 1. Panggil service untuk master data
     * 2. Return view
     */
    public function create(): View
    {
        $siswa = $this->tindakLanjutService->getAllSiswaForDropdown();
        
        return view('tindaklanjut.create', compact('siswa'));
    }

    /**
     * Simpan tindak lanjut baru.
     */
    public function store(CreateTindakLanjutRequest $request): RedirectResponse
    {
        // Convert validated request ke DTO
        $tindakLanjutData = TindakLanjutData::from([
            'id' => null,
            'siswa_id' => $request->siswa_id,
            'pemicu' => $request->pemicu,
            'sanksi_deskripsi' => $request->sanksi_deskripsi,
            'denda_deskripsi' => $request->denda_deskripsi,
            'status' => \App\Enums\StatusTindakLanjut::from($request->status),
            'tanggal_tindak_lanjut' => $request->tanggal_tindak_lanjut,
            'penyetuju_user_id' => $request->penyetuju_user_id,
        ]);

        // Panggil service
        $this->tindakLanjutService->createTindakLanjut($tindakLanjutData);

        return redirect()
            ->route('tindak-lanjut.index')
            ->with('success', 'Tindak lanjut berhasil dibuat.');
    }

    /**
     * Tampilkan detail tindak lanjut.
     * 
     * ALUR:
     * 1. Panggil service untuk get detail dengan relationships
     * 2. Return view berdasarkan context (approval vs detail umum)
     */
    public function show(int $id): View
    {
        $tindakLanjut = $this->tindakLanjutService->getTindakLanjutDetail($id);
        
        // Tambahkan ini untuk memastikan semua data relasi terbawa
        $tindakLanjut->load(['siswa.kelas.jurusan', 'siswa.kelas.waliKelas', 'siswa.waliMurid', 'penyetuju', 'suratPanggilan']);

        return view('tindaklanjut.show', [
            'tindakLanjut' => $tindakLanjut
        ]);
    }

    /**
     * Tampilkan form edit tindak lanjut.
     * 
     * ALUR:
     * 1. Panggil service untuk get tindak lanjut
     * 2. Return view
     */
    public function edit(int $id): View
    {
        $kasus = $this->tindakLanjutService->getTindakLanjutForEdit($id);
        
        // Eager load relationships yang dibutuhkan view
        $kasus->load(['siswa.kelas.jurusan', 'siswa.kelas.waliKelas', 'suratPanggilan', 'penyetuju']);
        
        return view('tindaklanjut.edit', compact('kasus'));
    }

    /**
     * Update tindak lanjut.
     * 
     * ALUR:
     * 1. Validasi (via UpdateTindakLanjutRequest)
     * 2. Panggil service untuk get existing record
     * 3. Convert ke DTO
     * 4. Panggil service untuk update
     * 5. Redirect dengan success message
     */
    public function update(UpdateTindakLanjutRequest $request, int $id): RedirectResponse
    {
        // Panggil service untuk get existing record
        $existingTindakLanjut = $this->tindakLanjutService->getTindakLanjutById($id);

        // Convert validated request ke DTO
        $tindakLanjutData = TindakLanjutData::from([
            'id' => $id,
            'siswa_id' => $existingTindakLanjut->siswa_id,
            'pemicu' => $request->pemicu,
            'sanksi_deskripsi' => $request->sanksi_deskripsi,
            'denda_deskripsi' => $request->denda_deskripsi,
            'status' => \App\Enums\StatusTindakLanjut::from($request->status),
            'tanggal_tindak_lanjut' => $request->tanggal_tindak_lanjut,
            'penyetuju_user_id' => $request->penyetuju_user_id,
        ]);

        // Panggil service (will auto-restore siswa status if completed)
        $this->tindakLanjutService->updateTindakLanjut($id, $tindakLanjutData);

        return redirect()
            ->route('tindak-lanjut.index')
            ->with('success', 'Tindak lanjut berhasil diperbarui.');
    }

    /**
     * Approve tindak lanjut.
     */
    public function approve(int $id): RedirectResponse
    {
        $tindakLanjut = \App\Models\TindakLanjut::findOrFail($id);
        
        // Authorization via Gate
        \Gate::authorize('approve', $tindakLanjut);
        
        $this->tindakLanjutService->approveTindakLanjut($id, auth()->id());

        return redirect()
            ->route('tindak-lanjut.show', $id)
            ->with('success', 'Tindak lanjut berhasil disetujui.');
    }

    /**
     * Reject tindak lanjut.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $tindakLanjut = \App\Models\TindakLanjut::findOrFail($id);
        
        // Authorization via Gate
        \Gate::authorize('reject', $tindakLanjut);
        
        $reason = $request->input('reason', '');
        
        $this->tindakLanjutService->rejectTindakLanjut($id, auth()->id(), $reason);

        return redirect()
            ->route('tindak-lanjut.pending-approval')
            ->with('success', 'Tindak lanjut berhasil ditolak.');
    }

    /**
     * Complete tindak lanjut (mark as selesai).
     */
    public function completeTindakLanjut(int $id): RedirectResponse
    {
        $this->tindakLanjutService->completeTindakLanjut($id);

        return redirect()
            ->route('tindak-lanjut.show', $id)
            ->with('success', 'Tindak lanjut berhasil diselesaikan.');
    }

    /**
     * Hapus tindak lanjut.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->tindakLanjutService->deleteTindakLanjut($id);

        return redirect()
            ->route('tindak-lanjut.index')
            ->with('success', 'Tindak lanjut berhasil dihapus.');
    }

    /**
     * Preview surat panggilan sebelum cetak.
     */
    public function previewSurat(int $id): View
    {
        $kasus = $this->tindakLanjutService->getTindakLanjutDetail($id);
        $kasus->load(['siswa.kelas.jurusan', 'suratPanggilan.printLogs.user']);
        
        return view('kepala_sekolah.kasus.preview_surat', [
            'kasus' => $kasus,
            'surat' => $kasus->suratPanggilan,
        ]);
    }

    /**
     * Edit surat panggilan.
     */
    public function editSurat(int $id): View
    {
        $kasus = $this->tindakLanjutService->getTindakLanjutDetail($id);
        $kasus->load(['siswa.kelas', 'suratPanggilan']);
        
        return view('kepala_sekolah.kasus.edit_surat', [
            'kasus' => $kasus,
            'surat' => $kasus->suratPanggilan,
        ]);
    }

    /**
     * Update surat panggilan.
     */
    public function updateSurat(Request $request, int $id): RedirectResponse
    {
        $kasus = \App\Models\TindakLanjut::with('suratPanggilan')->findOrFail($id);
        $surat = $kasus->suratPanggilan;

        if (!$surat) {
            return back()->with('error', 'Surat belum tersedia.');
        }

        $validated = $request->validate([
    'nomor_surat' => 'required|string|max:255',
    'lampiran' => 'nullable|string|max:255',
    'hal' => 'required|string|max:255',
    'tanggal_pertemuan' => 'required|date',
    'waktu_pertemuan' => 'required',
    'tempat_pertemuan' => 'required|string|max:255',
    'keperluan' => 'required|string',
]);

        $surat->update($validated);

        return redirect()->route('tindak-lanjut.preview-surat', $id)->with('success', 'Surat berhasil diperbarui!');
    }

    /**
     * Cetak surat (Download PDF + Log).
     */
    public function cetakSurat(int $id)
    {
        $kasus = \App\Models\TindakLanjut::with(['siswa.kelas.jurusan', 'siswa.waliMurid', 'suratPanggilan'])->findOrFail($id);
        $surat = $kasus->suratPanggilan;

        // Log print activity
        \App\Models\SuratPanggilanPrintLog::create([
            'surat_panggilan_id' => $surat->id,
            'user_id' => auth()->id(),
            'printed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Convert logo to Base64
        $path = public_path('assets/images/logo_riau.png');
        $logoBase64 = null;
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        // Convert pembina roles
        $pembinaRoles = $surat->pembina_roles ?? ['Wali Kelas', 'Waka Kesiswaan', 'Kepala Sekolah'];
        $pihakTerlibat = [
            'wali_kelas' => in_array('Wali Kelas', $pembinaRoles),
            'kaprodi' => in_array('Kaprodi', $pembinaRoles),
            'waka_kesiswaan' => in_array('Waka Kesiswaan', $pembinaRoles) || in_array('Waka Sarana', $pembinaRoles),
            'kepala_sekolah' => in_array('Kepala Sekolah', $pembinaRoles),
        ];

        // Generate PDF
        $pdf = \PDF::loadView('pdf.surat-panggilan', [
            'siswa' => $kasus->siswa,
            'surat' => $surat,
            'logoBase64' => $logoBase64,
            'pihakTerlibat' => $pihakTerlibat,
        ]);

        $pdf->setPaper([0, 0, 609.4488, 935.433], 'portrait');
        $filename = 'Surat_Panggilan_' . $kasus->siswa->nisn . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Mulai tangani kasus (change status: Baru/Disetujui -> Sedang Ditangani).
     */
    public function mulaiTangani(int $id): RedirectResponse
    {
        $kasus = \App\Models\TindakLanjut::findOrFail($id);

        // Hanya bisa mulai tangani jika status Baru atau Disetujui
        if (!in_array($kasus->status->value, ['Baru', 'Disetujui'])) {
            return back()->with('error', 'Kasus sudah dalam proses penanganan atau tidak bisa ditangani.');
        }

        $oldStatus = $kasus->status->value;

        $kasus->update([
            'status' => 'Ditangani',
            'tanggal_tindak_lanjut' => now(),
            // Tracking fields
            'ditangani_oleh_user_id' => auth()->id(),
            'ditangani_at' => now(),
        ]);

        // Log activity
        activity()
            ->performedOn($kasus)
            ->causedBy(auth()->user())
            ->withProperties(['old_status' => $oldStatus, 'new_status' => 'Ditangani'])
            ->log('Status kasus diubah ke Sedang Ditangani');

        return back()->with('success', 'Kasus berhasil dimulai penanganannya!');
    }

    /**
     * Selesaikan kasus (change status: Ditangani -> Selesai).
     */
    public function selesaikan(int $id): RedirectResponse
    {
        $kasus = \App\Models\TindakLanjut::findOrFail($id);

        if ($kasus->status->value !== 'Ditangani') {
            return back()->with('error', 'Hanya kasus yang sedang ditangani yang bisa diselesaikan.');
        }

        $kasus->update([
            'status' => 'Selesai',
            // Tracking fields
            'diselesaikan_oleh_user_id' => auth()->id(),
            'diselesaikan_at' => now(),
        ]);

        // Log activity
        activity()
            ->performedOn($kasus)
            ->causedBy(auth()->user())
            ->withProperties(['old_status' => 'Ditangani', 'new_status' => 'Selesai'])
            ->log('Kasus berhasil diselesaikan');

        return back()->with('success', 'Kasus berhasil diselesaikan!');
    }
}
