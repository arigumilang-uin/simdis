<?php

namespace App\Http\Controllers\Pelanggaran;

use App\Http\Controllers\Controller;
use App\Traits\HasFilters;
use Illuminate\Http\Request;
use App\Models\RiwayatPelanggaran;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\JenisPelanggaran;
use App\Services\Pelanggaran\PelanggaranRulesEngine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * RiwayatController
 *
 * Unified controller untuk riwayat pelanggaran siswa.
 * 
 * Features:
 * - View: Data scoping berbasis role (Wali Kelas, Kaprodi, Wali Murid, Admin/Kepsek)
 * - Filter: Tanggal, jenis, pencatat, kelas, jurusan, nama siswa
 * - CRUD: Edit/Delete dengan authorization (Operator: semua, Role lain: hanya yang mereka catat)
 */
class RiwayatController extends Controller
{
    use HasFilters;

    protected PelanggaranRulesEngine $rulesEngine;

    public function __construct(PelanggaranRulesEngine $rulesEngine)
    {
        $this->rulesEngine = $rulesEngine;
    }

    /**
     * Tampilkan daftar riwayat pelanggaran dengan fitur filter (tanggal, jenis, pencatat, siswa).
     * Data di-scope otomatis berdasarkan role user (Wali Kelas, Kaprodi, Wali Murid, Admin/Kepsek).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Siapkan dropdown filter data
        $allJurusan = Jurusan::all();
        $allKelas = Kelas::all();
        $allPelanggaran = JenisPelanggaran::orderBy('nama_pelanggaran')->get();

        // Mulai query dengan eager loading untuk performa
        $query = RiwayatPelanggaran::with([
            'siswa.kelas.jurusan', 
            'jenisPelanggaran.kategoriPelanggaran', 
            'guruPencatat',
            'siswa.riwayatPelanggaran.jenisPelanggaran'
        ]);

        // Terapkan data scoping berdasarkan role user
        $this->applyRoleBasedScoping($query, $user);

        // Terapkan filter menggunakan trait (simplified)
        $filters = $this->getFilters([
            'start_date',
            'end_date',
            'jenis_pelanggaran_id',
            'pencatat_id',
            'kelas_id',
            'jurusan_id',
            'cari_siswa'
        ]);
        
        $this->applyRiwayatFilters($query, $filters, $user);

        // Eksekusi query dengan pagination
        $riwayat = $query->latest('tanggal_kejadian')->paginate(20)->withQueryString();

        return view('riwayat.index', compact('riwayat', 'allJurusan', 'allKelas', 'allPelanggaran'));
    }

    /**
     * Terapkan data scoping berdasarkan role user.
     * Wali Kelas: scope ke kelas binaan
     * Kaprodi: scope ke jurusan binaan
     * Wali Murid: scope ke anak-anak sendiri
     * Admin/Kepsek: lihat semua
     */
    private function applyRoleBasedScoping($query, $user): void
    {
        if ($user->hasRole('Wali Kelas')) {
            $kelasBinaan = $user->kelasDiampu;
            if ($kelasBinaan) {
                $query->whereHas('siswa', function($q) use ($kelasBinaan) {
                    $q->where('kelas_id', $kelasBinaan->id);
                });
            } else {
                // Jika Wali Kelas tidak punya kelas binaan, tampilkan 0 records
                $query->where('id', 0);
            }
        } elseif ($user->hasRole('Kaprodi')) {
            $jurusanBinaan = $user->jurusanDiampu;
            if ($jurusanBinaan) {
                $query->whereHas('siswa.kelas', function($q) use ($jurusanBinaan) {
                    $q->where('jurusan_id', $jurusanBinaan->id);
                });
            } else {
                // Jika Kaprodi tidak punya jurusan binaan, tampilkan 0 records
                $query->where('id', 0);
            }
        } elseif ($user->hasRole('Wali Murid')) {
            $anakIds = $user->anakWali->pluck('id');
            $query->whereIn('siswa_id', $anakIds);
        }
        // Jika admin/kepsek, tidak ada scoping (lihat semua)
    }

    /**
     * Terapkan filter riwayat pelanggaran (simplified using trait)
     */
    private function applyRiwayatFilters($query, array $filters, $user): void
    {
        foreach ($filters as $key => $value) {
            // Date range filters
            if ($key === 'start_date') {
                $query->whereDate('tanggal_kejadian', '>=', $value);
                continue;
            }
            
            if ($key === 'end_date') {
                $query->whereDate('tanggal_kejadian', '<=', $value);
                continue;
            }

            // Jenis pelanggaran filter
            if ($key === 'jenis_pelanggaran_id') {
                $query->where('jenis_pelanggaran_id', $value);
                continue;
            }

            // Guru pencatat filter
            if ($key === 'pencatat_id') {
                $query->where('guru_pencatat_user_id', $value);
                continue;
            }

            // Kelas filter (role-based)
            if ($key === 'kelas_id' && !$user->hasRole('Wali Kelas')) {
                $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $value));
                continue;
            }

            // Jurusan filter (role-based)
            if ($key === 'jurusan_id' && !$user->hasAnyRole(['Wali Kelas', 'Kaprodi'])) {
                $query->whereHas('siswa.kelas', fn($q) => $q->where('jurusan_id', $value));
                continue;
            }

            // Search siswa by name
            if ($key === 'cari_siswa') {
                $query->whereHas('siswa', fn($q) => $q->where('nama_siswa', 'like', "%{$value}%"));
                continue;
            }
        }
    }

    /**
     * Tampilkan daftar riwayat yang dicatat oleh user saat ini (My Riwayat).
     * Operator Sekolah dapat melihat SEMUA riwayat pelanggaran.
     * Role lain hanya melihat yang mereka catat sendiri.
     */
    public function myIndex(Request $request)
    {
        $user = Auth::user();
        $userId = Auth::id();

        $query = RiwayatPelanggaran::with(['siswa.kelas', 'jenisPelanggaran', 'guruPencatat']);

        // Operator Sekolah bisa lihat semua riwayat
        // Role lain hanya bisa lihat riwayat yang mereka catat sendiri
        if (!$user->hasRole('Operator Sekolah')) {
            $query->where('guru_pencatat_user_id', $userId);
        }

        // Apply date filters
        $filters = $this->getFilters(['start_date', 'end_date']);
        
        if (isset($filters['start_date'])) {
            $query->whereDate('tanggal_kejadian', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->whereDate('tanggal_kejadian', '<=', $filters['end_date']);
        }

        $riwayat = $query->latest('tanggal_kejadian')->paginate(20)->withQueryString();

        return view('riwayat.my_index', compact('riwayat'));
    }

    /**
     * Tampilkan form edit untuk riwayat.
     * Authorization: Operator dapat edit semua, role lain hanya yang mereka catat (max 3 hari).
     */
    public function edit(Request $request, $id)
    {
        $record = RiwayatPelanggaran::findOrFail($id);
        $this->authorizeOwnership($record);

        // Store return URL in session
        if ($request->has('return_url')) {
            session(['riwayat_return_url' => $request->return_url]);
        } elseif ($request->headers->get('referer')) {
            session(['riwayat_return_url' => $request->headers->get('referer')]);
        }

        $jenis = JenisPelanggaran::orderBy('nama_pelanggaran')->get();
        return view('riwayat.edit_my', ['r' => $record, 'jenis' => $jenis]);
    }

    /**
     * Update record riwayat pelanggaran.
     * Authorization: Operator dapat edit semua, role lain hanya yang mereka catat (max 3 hari).
     */
    public function update(Request $request, $id)
    {
        $record = RiwayatPelanggaran::findOrFail($id);
        $this->authorizeOwnership($record);

        $request->validate([
            'jenis_pelanggaran_id' => 'required|exists:jenis_pelanggaran,id',
            'tanggal_kejadian' => 'required|date',
            'jam_kejadian' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::transaction(function () use ($request, $record) {
            // Combine date + time
            $time = $request->jam_kejadian ?? Carbon::now()->format('H:i');
            try {
                $dt = Carbon::createFromFormat('Y-m-d H:i', $request->tanggal_kejadian . ' ' . $time)->toDateTimeString();
            } catch (\Exception $e) {
                $dt = Carbon::parse($request->tanggal_kejadian . ' ' . $time)->toDateTimeString();
            }

            $data = [
                'jenis_pelanggaran_id' => $request->jenis_pelanggaran_id,
                'tanggal_kejadian' => $dt,
                'keterangan' => $request->keterangan,
            ];

            // Handle file upload
            if ($request->hasFile('bukti_foto')) {
                // Remove old file if exists
                if ($record->bukti_foto_path) {
                    Storage::disk('public')->delete($record->bukti_foto_path);
                }
                $path = $request->file('bukti_foto')->store('bukti_pelanggaran', 'public');
                $data['bukti_foto_path'] = $path;
            }

            $record->update($data);
        });

        // Rekalkulasi tindak lanjut untuk siswa terkait
        $this->rulesEngine->reconcileForSiswa($record->siswa_id, false);

        // Redirect back to previous page or default
        $redirectUrl = session('riwayat_return_url', route('my-riwayat.index'));
        session()->forget('riwayat_return_url');
        
        return redirect($redirectUrl)->with('success', 'Riwayat pelanggaran berhasil diperbarui.');
    }

    /**
     * Hapus riwayat pelanggaran.
     * Authorization: Operator dapat hapus semua, role lain hanya yang mereka catat (max 3 hari).
     */
    public function destroy(Request $request, $id)
    {
        $record = RiwayatPelanggaran::findOrFail($id);
        $this->authorizeOwnership($record);

        // Remove file if exists
        if ($record->bukti_foto_path) {
            Storage::disk('public')->delete($record->bukti_foto_path);
        }

        $siswaId = $record->siswa_id;
        $record->delete();

        // Rekalkulasi tindak lanjut setelah penghapusan
        $this->rulesEngine->reconcileForSiswa($siswaId, true);

        // Redirect back to previous page or default
        $redirectUrl = $request->input('return_url') 
                    ?? session('riwayat_return_url') 
                    ?? route('my-riwayat.index');
        session()->forget('riwayat_return_url');
        
        return redirect($redirectUrl)->with('success', 'Riwayat pelanggaran berhasil dihapus.');
    }

    /**
     * Authorization helper: Pastikan user berhak mengedit/hapus record.
     * 
     * Rules:
     * - Operator Sekolah: Dapat edit/hapus semua record tanpa batasan
     * - Role lain: Hanya dapat edit/hapus record yang mereka catat sendiri (max 3 hari)
     */
    private function authorizeOwnership(RiwayatPelanggaran $record): void
    {
        $user = Auth::user();

        // Operator Sekolah bisa edit/hapus semua record tanpa batasan waktu
        if ($user->hasRole('Operator Sekolah')) {
            return;
        }

        // Untuk role lain, hanya bisa edit/hapus record yang mereka catat sendiri
        if ($record->guru_pencatat_user_id !== Auth::id()) {
            abort(403, 'AKSES DITOLAK: Anda hanya dapat mengelola riwayat yang Anda catat.');
        }

        // Batasi kemampuan edit/hapus sampai 3 hari sejak pencatatan
        if ($record->created_at) {
            $created = Carbon::parse($record->created_at);
            if (Carbon::now()->greaterThan($created->copy()->addDays(3))) {
                abort(403, 'Batas waktu edit/hapus telah lewat (lebih dari 3 hari sejak pencatatan).');
            }
        }
    }
}
