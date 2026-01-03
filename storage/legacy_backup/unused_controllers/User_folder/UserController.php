<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\HasFilters;
use App\Models\User;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Services\User\UserNamingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

/**
 * UserController
 *
 * Controller untuk pengelolaan user (CRUD) dengan role assignment.
 * Fitur: index dengan filter role/pencarian, create/edit user dengan role dropdown, assign wali kelas/kaprodi.
 */
class UserController extends Controller
{
    use HasFilters;

    /**
     * Tampilkan daftar user dengan fitur filter by role dan pencarian nama/username/email.
     */
    public function index(Request $request)
    {
        $roles = Role::all();
        $query = User::with(['role', 'anakWali']);

        // Apply filters using trait (simplified)
        $filters = $this->getFilters(['cari', 'role_id']);
        
        if (isset($filters['cari'])) {
            $this->applySearch($query, $filters['cari'], ['nama', 'username', 'email']);
        }
        
        if (isset($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        $users = $query->orderBy('role_id')
                       ->orderBy('nama')
                       ->paginate(10)
                       ->withQueryString();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        
        // DATA PENDUKUNG FILTER (WAJIB ADA AGAR TIDAK ERROR)
        $jurusan = Jurusan::all();
        $kelas = Kelas::orderBy('nama_kelas')->get();

        // Load siswa beserta kelas & jurusan untuk filtering di frontend
        $siswa = Siswa::with('kelas.jurusan')->orderBy('nama_siswa')->get();

        // cek apakah sudah ada Kepala Sekolah di sistem
        $kepsek = User::whereHas('role', function($q){ $q->where('nama_role','Kepala Sekolah'); })->first();
        $kepsekExists = $kepsek ? true : false;
        $kepsekId = $kepsek->id ?? null;
        $kepsekUsername = $kepsek->username ?? null;

        return view('users.create', compact('roles', 'siswa', 'jurusan', 'kelas', 'kepsekExists', 'kepsekId', 'kepsekUsername'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'nip' => 'nullable|string|size:18',
            'nuptk' => 'nullable|string|size:18',
            'role_id' => 'required|exists:roles,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'siswa_ids' => 'nullable|array',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        // Jika role Kaprodi atau Developer dan jurusan dipilih, pastikan jurusan belum punya Kaprodi.
        $roleKaprodi = Role::findByName('Kaprodi');
        $roleDev = Role::findByName('Developer');
        
        if (($roleKaprodi && $request->role_id == $roleKaprodi->id || $roleDev && $request->role_id == $roleDev->id) && $request->filled('jurusan_id')) {
            $jur = Jurusan::find($request->jurusan_id);
            if ($jur && $jur->kaprodi_user_id) {
                return back()->withInput()->withErrors(['jurusan_id' => 'Jurusan ini sudah memiliki Kaprodi: ' . (optional($jur->kaprodi)->nama ?? '—') . '. Pilih jurusan lain atau lepaskan role Kaprodi pada akun yang bersangkutan.']);
            }
        }

        // Jika role Wali Kelas atau Developer dan kelas dipilih, pastikan kelas belum punya wali
        $roleWali = Role::findByName('Wali Kelas');
        if (($roleWali && $request->role_id == $roleWali->id || $roleDev && $request->role_id == $roleDev->id) && $request->filled('kelas_id')) {
            $kel = Kelas::find($request->kelas_id);
            if ($kel && $kel->wali_kelas_user_id) {
                return back()->withInput()->withErrors(['kelas_id' => 'Kelas ini sudah memiliki Wali Kelas: ' . (optional($kel->waliKelas)->nama ?? '—') . '. Pilih kelas lain atau lepaskan role Wali Kelas pada akun yang bersangkutan.']);
            }
        }

        // Jika role Kepala Sekolah, pastikan belum ada Kepala Sekolah lain di sistem
        $roleKepsek = Role::findByName('Kepala Sekolah');
        if ($roleKepsek && $request->role_id == $roleKepsek->id) {
            $exists = User::where('role_id', $roleKepsek->id)->exists();
            if ($exists) {
                return back()->withInput()->withErrors(['role_id' => 'Sudah ada Kepala Sekolah pada sistem. Pilih role lain atau hapus/ubah role Kepala Sekolah yang sekarang terlebih dahulu.']);
            }
        }

        DB::transaction(function() use ($request, $roleKaprodi) {
            // Buat user sementara dengan role_id dulu untuk bisa generate nama/username/password
            $user = User::create([
                'role_id' => $request->role_id,
                'nama' => '', // Temporary, akan di-update setelah relasi dibuat
                'username' => '', // Temporary, akan di-update setelah relasi dibuat
                'email' => $request->email,
                'phone' => (function () use ($request) {
                    $roleOrtu = Role::findByName('Wali Murid');
                    if ($roleOrtu && (int) $request->role_id === (int) $roleOrtu->id) {
                        return null;
                    }
                    return $request->phone ?: null;
                })(),
                'nip' => $request->nip ?: null,
                'nuptk' => $request->nuptk ?: null,
                'password' => '', // Temporary, akan di-update setelah relasi dibuat
            ]);

            $roleOrtu = Role::findByName('Wali Murid');
            $roleDev = Role::findByName('Developer');
            
            // Wali Murid atau Developer bisa assign siswa
            if (($roleOrtu && $request->role_id == $roleOrtu->id) || ($roleDev && $request->role_id == $roleDev->id)) {
                if ($request->filled('siswa_ids')) {
                    Siswa::whereIn('id', $request->siswa_ids)->update([
                        'wali_murid_user_id' => $user->id
                    ]);
                }
            }

            // Jika role adalah Kaprodi atau Developer, link ke jurusan yang dipilih (jika ada)
            if (($roleKaprodi && $request->role_id == $roleKaprodi->id) || ($roleDev && $request->role_id == $roleDev->id)) {
                if ($request->filled('jurusan_id')) {
                    Jurusan::where('id', $request->jurusan_id)->update(['kaprodi_user_id' => $user->id]);
                }
            }

            // Jika role adalah Wali Kelas atau Developer, link ke kelas yang dipilih (jika ada)
            $roleWali = Role::findByName('Wali Kelas');
            if (($roleWali && $request->role_id == $roleWali->id) || ($roleDev && $request->role_id == $roleDev->id)) {
                if ($request->filled('kelas_id')) {
                    Kelas::where('id', $request->kelas_id)->update(['wali_kelas_user_id' => $user->id]);
                }
            }

            // Reload user dengan relasi untuk generate nama/username/password
            $user->refresh();
            $user->load(['role', 'jurusanDiampu', 'kelasDiampu', 'anakWali']);

            // Generate nama, username, dan password otomatis
            $user->nama = UserNamingService::generateNama($user);
            $user->username = UserNamingService::generateUsername($user);
            $user->password = Hash::make(UserNamingService::generatePassword($user));
            $user->save();
        });

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        
        // DATA PENDUKUNG FILTER
        $jurusan = Jurusan::all();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        $siswa = Siswa::with('kelas.jurusan')->orderBy('nama_siswa')->get();
        
        // Ambil anak yg sudah terhubung
        $connectedSiswaIds = $user->anakWali->pluck('id')->toArray();

        $kepsek = User::whereHas('role', function($q){ $q->where('nama_role','Kepala Sekolah'); })->first();
        $kepsekExists = $kepsek ? true : false;
        $kepsekId = $kepsek->id ?? null;
        $kepsekUsername = $kepsek->username ?? null;

        return view('users.edit', compact('user', 'roles', 'siswa', 'connectedSiswaIds', 'jurusan', 'kelas', 'kepsekExists', 'kepsekId', 'kepsekUsername'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:8',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'nip' => 'nullable|string|size:18',
            'nuptk' => 'nullable|string|size:18',
            'role_id' => 'required|exists:roles,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'siswa_ids' => 'nullable|array',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        // Jika role Kaprodi atau Developer dan jurusan dipilih, pastikan jurusan belum punya Kaprodi yang bukan user ini
        $roleKaprodi = Role::findByName('Kaprodi');
        $roleDev = Role::findByName('Developer');
        
        if (($roleKaprodi && $request->role_id == $roleKaprodi->id || $roleDev && $request->role_id == $roleDev->id) && $request->filled('jurusan_id')) {
            $jur = Jurusan::find($request->jurusan_id);
            if ($jur && $jur->kaprodi_user_id && $jur->kaprodi_user_id != $user->id) {
                return back()->withInput()->withErrors(['jurusan_id' => 'Jurusan ini sudah dimiliki oleh Kaprodi lain: ' . (optional($jur->kaprodi)->nama ?? '—') . '. Pilih jurusan lain atau lepaskan role Kaprodi pada akun yang bersangkutan.']);
            }
        }

        // Jika role Wali Kelas atau Developer dan kelas dipilih, pastikan kelas belum punya wali yang bukan user ini
        $roleWali = Role::findByName('Wali Kelas');
        if (($roleWali && $request->role_id == $roleWali->id || $roleDev && $request->role_id == $roleDev->id) && $request->filled('kelas_id')) {
            $kel = Kelas::find($request->kelas_id);
            if ($kel && $kel->wali_kelas_user_id && $kel->wali_kelas_user_id != $user->id) {
                return back()->withInput()->withErrors(['kelas_id' => 'Kelas ini sudah dimiliki oleh Wali Kelas lain: ' . (optional($kel->waliKelas)->nama ?? '—') . '. Pilih kelas lain atau lepaskan role Wali Kelas pada akun yang bersangkutan.']);
            }
        }

        // Jika role Kepala Sekolah, pastikan belum ada Kepala Sekolah lain di sistem
        $roleKepsek = Role::findByName('Kepala Sekolah');
        if ($roleKepsek && $request->role_id == $roleKepsek->id) {
            $exists = User::where('role_id', $roleKepsek->id)->where('id', '!=', $user->id)->exists();
            if ($exists) {
                return back()->withInput()->withErrors(['role_id' => 'Sudah ada Kepala Sekolah pada sistem. Pilih role lain atau hapus/ubah role Kepala Sekolah yang sekarang terlebih dahulu.']);
            }
        }

        DB::transaction(function() use ($request, $user) {
            // Update nama (manual input dari operator)
            if ($request->filled('nama')) {
                $user->nama = $request->nama;
            }

            // Update username (manual input dari operator)
            if ($request->filled('username') && $request->username !== $user->username) {
                $user->username = $request->username;
                // Mark bahwa username sudah diubah
                if (!$user->hasChangedUsername()) {
                    $user->username_changed_at = now();
                }
            }

            // Update password jika diisi (manual input dari operator)
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                // Mark bahwa password sudah diubah
                if (!$user->hasChangedPassword()) {
                    $user->password_changed_at = now();
                }
            }

            // Update role dulu agar relasi bisa di-generate dengan benar
            $user->role_id = $request->role_id;
            $user->email = $request->email;
            
            // Untuk Wali Murid, tetap gunakan kontak dari data siswa (nomor_hp_wali_murid),
            // sehingga kolom phone pada user diset null agar tidak membingungkan.
            $roleOrtu = Role::findByName('Wali Murid');
            if ($roleOrtu && (int) $request->role_id === (int) $roleOrtu->id) {
                $user->phone = null;
            } else {
                $user->phone = $request->phone ?: null;
            }
            
            // Update NIP/NUPTK
            $user->nip = $request->nip ?: null;
            $user->nuptk = $request->nuptk ?: null;
            
            $user->save();

            $roleOrtu = Role::findByName('Wali Murid');
            $roleDev = Role::findByName('Developer');

            // Wali Murid atau Developer bisa assign siswa
            if (($roleOrtu && $request->role_id == $roleOrtu->id) || ($roleDev && $request->role_id == $roleDev->id)) {
                // Reset anak lama
                Siswa::where('wali_murid_user_id', $user->id)->update(['wali_murid_user_id' => null]);

                // Set anak baru
                if ($request->filled('siswa_ids')) {
                    Siswa::whereIn('id', $request->siswa_ids)->update([
                        'wali_murid_user_id' => $user->id
                    ]);
                }
            } elseif ($roleOrtu) {
                // If user is no longer Wali Murid, remove any siswa assignment they had
                Siswa::where('wali_murid_user_id', $user->id)->update(['wali_murid_user_id' => null]);
            }

            // --- Kaprodi assignment handling (Kaprodi atau Developer) ---
            $roleKaprodi = Role::findByName('Kaprodi');

            if (($roleKaprodi && $request->role_id == $roleKaprodi->id) || ($roleDev && $request->role_id == $roleDev->id)) {
                // Unset any jurusan previously pointing to this user (defensive)
                Jurusan::where('kaprodi_user_id', $user->id)->update(['kaprodi_user_id' => null]);

                // If a jurusan was selected, assign it to this user
                if ($request->filled('jurusan_id')) {
                    Jurusan::where('id', $request->jurusan_id)->update(['kaprodi_user_id' => $user->id]);
                }
            } elseif ($roleKaprodi) {
                // If user is no longer Kaprodi, remove any jurusan assignment they had
                Jurusan::where('kaprodi_user_id', $user->id)->update(['kaprodi_user_id' => null]);
            }

            // --- Wali Kelas assignment handling (Wali Kelas atau Developer) ---
            $roleWali = Role::findByName('Wali Kelas');

            if (($roleWali && $request->role_id == $roleWali->id) || ($roleDev && $request->role_id == $roleDev->id)) {
                // Unset any kelas previously pointing to this user (defensive)
                Kelas::where('wali_kelas_user_id', $user->id)->update(['wali_kelas_user_id' => null]);

                // If a kelas was selected, assign it to this user
                if ($request->filled('kelas_id')) {
                    Kelas::where('id', $request->kelas_id)->update(['wali_kelas_user_id' => $user->id]);
                }
            } elseif ($roleWali) {
                // If user is no longer Wali Kelas, remove any kelas assignment they had
                Kelas::where('wali_kelas_user_id', $user->id)->update(['wali_kelas_user_id' => null]);
            }

            // Reload user dengan relasi
            $user->refresh();
            $user->load(['role', 'jurusanDiampu', 'kelasDiampu', 'anakWali']);
        });

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        // Prevent deletion if user still holds important relations (kaprodi/wali kelas, pencatat riwayat, penyetuju tindak lanjut)
        if ($user->jurusanDiampu) {
            return back()->with('error', 'Gagal menghapus: User ini masih menjadi Kaprodi pada sebuah jurusan. Hapus atau pindahkan relasi terlebih dahulu.');
        }

        if ($user->kelasDiampu) {
            return back()->with('error', 'Gagal menghapus: User ini masih menjadi Wali Kelas untuk sebuah kelas. Hapus atau pindahkan relasi terlebih dahulu.');
        }

        if ($user->riwayatDicatat()->exists()) {
            return back()->with('error', 'Gagal menghapus: User ini pernah mencatat riwayat pelanggaran. Harap tinjau data terlebih dahulu.');
        }

        if ($user->tindakLanjutDisetujui()->exists()) {
            return back()->with('error', 'Gagal menghapus: User ini pernah menyetujui tindak lanjut. Harap tinjau data terlebih dahulu.');
        }

        // For parent/ortu: detach anak-anaknya (set null)
        if ($user->anakWali()->exists()) {
            Siswa::where('wali_murid_user_id', $user->id)->update(['wali_murid_user_id' => null]);
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus!');
    }

    /**
     * Toggle user active/inactive status.
     */
    public function toggleActive(User $user)
    {
        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun sendiri!');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "User berhasil {$status}!");
    }
}


