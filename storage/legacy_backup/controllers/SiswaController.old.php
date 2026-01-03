<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Traits\HasFilters;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    use HasFilters;

    /**
     * Apply siswa-specific filters (helper method)
     */
    private function applySiswaFilters($query, array $filters, $user): void
    {
        foreach ($filters as $key => $value) {
            // Search filter
            if ($key === 'cari') {
                $this->applySearch($query, $value, ['nama_siswa', 'nisn']);
                continue;
            }

            // Kelas filter (role-based)
            if ($key === 'kelas_id' && !$user->hasRole('Wali Kelas')) {
                $query->where('kelas_id', $value);
                continue;
            }

            // Jurusan filter (role-based)
            if ($key === 'jurusan_id' && !$user->hasAnyRole(['Wali Kelas', 'Kaprodi'])) {
                $query->whereHas('kelas', fn($q) => $q->where('jurusan_id', $value));
                continue;
            }

            // Tingkat filter
            if ($key === 'tingkat') {
                $query->whereHas('kelas', fn($q) => $q->where('nama_kelas', 'like', $value . ' %'));
                continue;
            }
        }
    }

    /**
     * MENAMPILKAN DAFTAR SISWA
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Prepare filter dropdowns according to user role
        // Default: operator/waka see all jurusan and all kelas
        $allJurusan = Jurusan::orderBy('nama_jurusan')->get();
        $allKelas = Kelas::orderBy('nama_kelas')->get();

        $query = Siswa::with('kelas.jurusan');

        // --- LOGIKA DATA SCOPING ---
        if ($user->hasRole('Wali Kelas')) {
            $kelasBinaan = $user->kelasDiampu;
            if ($kelasBinaan) {
                $query->where('kelas_id', $kelasBinaan->id);
                // Wali Kelas should only see their own class in kelas dropdown
                $allKelas = Kelas::where('id', $kelasBinaan->id)->orderBy('nama_kelas')->get();
            } else {
                $query->where('id', 0); 
            }
        }
        elseif ($user->hasRole('Kaprodi')) {
            $jurusanBinaan = $user->jurusanDiampu;
            if ($jurusanBinaan) {
                $query->whereHas('kelas', function($q) use ($jurusanBinaan) {
                    $q->where('jurusan_id', $jurusanBinaan->id);
                });
                // Kaprodi should only see classes within their jurusan
                $allJurusan = collect(); // hide jurusan filter in view
                $allKelas = Kelas::where('jurusan_id', $jurusanBinaan->id)->orderBy('nama_kelas')->get();
            } else {
                $query->where('id', 0);
            }
        }

        // --- LOGIKA FILTER (using trait) ---
        $filters = $this->getFilters(['cari', 'kelas_id', 'jurusan_id', 'tingkat']);
        $this->applySiswaFilters($query, $filters, $user);

        $siswa = $query->orderBy('kelas_id')->orderBy('nama_siswa')
                       ->paginate(20)->withQueryString();

        return view('siswa.index', compact('siswa', 'allJurusan', 'allKelas'));
    }

    /**
     * TAMPILKAN FORM TAMBAH SISWA
     */
    public function create()
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        // [UPDATE] Ambil daftar user dengan role 'Wali Murid' untuk dropdown
        $waliMurid = User::whereHas('role', function($q){
            $q->where('nama_role', 'Wali Murid');
        })->orderBy('nama')->get();

        return view('siswa.create', compact('kelas', 'waliMurid'));
    }

    /**
     * SIMPAN DATA SISWA BARU
     */
    public function store(Request $request)
    {
        $request->validate([
            'nisn' => 'required|numeric|unique:siswa,nisn',
            'nama_siswa' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'nomor_hp_wali_murid' => 'nullable|numeric',
            // [UPDATE] Validasi opsional untuk wali murid
            'wali_murid_user_id' => 'nullable|exists:users,id',
            'create_wali' => 'nullable|boolean',
        ]);

        $data = $request->only(['nisn', 'nama_siswa', 'kelas_id', 'nomor_hp_wali_murid', 'wali_murid_user_id']);

        // Jika diminta membuat akun wali otomatis dan tidak memilih wali yang sudah ada
        if ($request->boolean('create_wali') && empty($data['wali_murid_user_id'])) {
            // gunakan NISN sebagai basis username untuk menghindari ambiguitas
            $nisnRaw = (string) ($data['nisn'] ?? '');
            $nisnClean = preg_replace('/\D+/', '', $nisnRaw);
            if ($nisnClean === '') {
                // fallback: gunakan slug dari nama siswa
                $nisnClean = Str::slug($data['nama_siswa']);
            }

            $baseUsername = 'wali.' . $nisnClean;
            $username = $baseUsername;
            $i = 1;
            while (User::where('username', $username)->exists()) {
                $i++;
                $username = $baseUsername . $i;
            }

            // Password standardized: smkn1.walimurid.{nisn}
            $password = 'smkn1.walimurid.' . $nisnClean;
            $role = Role::findByName('Wali Murid');

            $user = User::create([
                'role_id' => $role?->id,
                'nama' => 'Wali dari ' . $data['nama_siswa'],
                'username' => $username,
                'email' => $username . '@no-reply.local',
                'password' => $password,
            ]);

            $data['wali_murid_user_id'] = $user->id;

            // flash kredensial supaya operator dapat menyalin setelah redirect
            session()->flash('wali_created', ['username' => $username, 'password' => $password]);
        }

        Siswa::create($data);

        return redirect()->route('siswa.index')->with('success', 'Data Siswa Berhasil Ditambahkan');
    }

    /**
     * TAMPILKAN PROFIL LENGKAP SISWA
     */
    public function show(Siswa $siswa)
    {
        $user = Auth::user();

        // Data scoping: cek apakah user berhak melihat profil siswa ini
        if ($user->hasRole('Wali Kelas')) {
            $kelasBinaan = $user->kelasDiampu;
            if (!$kelasBinaan || $siswa->kelas_id !== $kelasBinaan->id) {
                abort(403, 'Anda tidak memiliki akses untuk melihat profil siswa ini.');
            }
        } elseif ($user->hasRole('Kaprodi')) {
            $jurusanBinaan = $user->jurusanDiampu;
            if (!$jurusanBinaan || $siswa->kelas->jurusan_id !== $jurusanBinaan->id) {
                abort(403, 'Anda tidak memiliki akses untuk melihat profil siswa ini.');
            }
        }

        // Load relasi yang diperlukan
        $siswa->load([
            'kelas.jurusan.kaprodi',
            'kelas.waliKelas',
            'waliMurid',
            'riwayatPelanggaran.jenisPelanggaran.kategoriPelanggaran',
            'riwayatPelanggaran.guruPencatat',
            'tindakLanjut'
        ]);

        // Hitung total poin pelanggaran
        $totalPoin = $siswa->riwayatPelanggaran->sum(function($riwayat) {
            return $riwayat->jenisPelanggaran->poin ?? 0;
        });

        return view('siswa.show', compact('siswa', 'totalPoin'));
    }

    /**
     * TAMPILKAN FORM EDIT
     */
    public function edit(Siswa $siswa)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        $waliMurid = User::whereHas('role', function($q){
            $q->where('nama_role', 'Wali Murid');
        })->orderBy('nama')->get();

        return view('siswa.edit', compact('siswa', 'kelas', 'waliMurid'));
    }

    /**
     * UPDATE DATA SISWA
     */
    public function update(Request $request, Siswa $siswa)
    {
        $user = Auth::user();

        // Jika Wali Kelas, Validasi lebih longgar (Cuma HP)
            if ($user->hasRole('Wali Kelas')) {
            // Pastikan Wali Kelas hanya dapat mengubah siswa di kelas yang dia ampuh
            $kelasBinaan = Auth::user()->kelasDiampu;
            if (!$kelasBinaan || $siswa->kelas_id !== $kelasBinaan->id) {
                abort(403, 'AKSES DITOLAK: Anda hanya dapat memperbarui data siswa di kelas yang Anda ampu.');
            }
            $request->validate([
                'nomor_hp_wali_murid' => 'nullable|numeric',
            ]);

            $siswa->update([
                'nomor_hp_wali_murid' => $request->nomor_hp_wali_murid
            ]);
        } 
        // Jika Operator, Validasi Ketat
        else {
            $request->validate([
                'nisn' => 'required|numeric|unique:siswa,nisn,' . $siswa->id,
                'nama_siswa' => 'required|string|max:255',
                'kelas_id' => 'required|exists:kelas,id',
                'nomor_hp_wali_murid' => 'nullable|numeric',
                'wali_murid_user_id' => 'nullable|exists:users,id',
            ]);

            // Map incoming request keys if older forms still submit old names
            $data = $request->all();
            if ($request->filled('orang_tua_user_id') && !$request->filled('wali_murid_user_id')) {
                $data['wali_murid_user_id'] = $request->input('orang_tua_user_id');
            }
            if ($request->filled('nomor_hp_ortu') && !$request->filled('nomor_hp_wali_murid')) {
                $data['nomor_hp_wali_murid'] = $request->input('nomor_hp_ortu');
            }

            $siswa->update($data);
        }

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * HAPUS SISWA
     */
    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return redirect()->route('siswa.index')->with('success', 'Data Siswa Berhasil Dihapus');
    }

    /**
     * Show bulk create form
     */
    public function bulkCreate()
    {
        return view('siswa.bulk_create');
    }

    /**
     * Process bulk create students
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'bulk_data' => 'nullable|string',
            'create_wali_all' => 'nullable|boolean',
            'bulk_file' => 'nullable|file|mimes:csv,txt,xlsx',
        ]);

        // determine input source: uploaded CSV/XLSX file or textarea
        $lines = [];
        if ($request->hasFile('bulk_file')) {
            $file = $request->file('bulk_file');
            $mimeType = $file->getMimeType();
            $filename = $file->getClientOriginalName();
            
            // handle CSV files
            if (in_array($mimeType, ['text/csv', 'text/plain']) || str_ends_with(strtolower($filename), '.csv')) {
                if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                    while (($row = fgetcsv($handle, 0, ',')) !== false) {
                        $lines[] = implode(';', $row);
                    }
                    fclose($handle);
                }
            }
            // handle XLSX files (simple parsing using built-in PHP functionality or CSV convert)
            else if (str_ends_with(strtolower($filename), '.xlsx') || $mimeType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                // attempt to parse XLSX by unzipping and reading XML (basic approach)
                // alternatively, convert XLSX to CSV server-side or ask user to use CSV
                // for now, we'll try a simple approach: use fgetcsv as fallback or reject
                try {
                    // simple approach: read first few rows; full parsing requires library
                    // we'll use a workaround: convert XLSX to CSV on-the-fly using temp file
                    // or fallback to CSV parsing
                    // for MVP: ask user to use CSV or provide simple parser
                    if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                        while (($row = fgetcsv($handle, 0, ',')) !== false) {
                            if (empty(array_filter($row))) continue; // skip empty rows
                            $lines[] = implode(';', $row);
                        }
                        fclose($handle);
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'File XLSX tidak dapat diproses. Gunakan file CSV atau format Excel yang kompatibel.');
                }
            } else {
                DB::rollBack();
                return back()->withInput()->with('error', 'Format file tidak didukung. Gunakan CSV atau XLSX.');
            }
        } else {
            $lines = preg_split('/\r\n|\r|\n/', $request->input('bulk_data'));
        }
        $errors = [];
        $created = [];
        $waliCreated = [];

        DB::beginTransaction();
        try {
            $lineNo = 0;
            foreach ($lines as $line) {
                $lineNo++;
                $line = trim($line);
                if ($line === '') continue;

                // expect format NISN;Nama;NomorHP (nomor HP optional)
                $parts = array_map('trim', explode(';', $line));
                if (count($parts) < 2) {
                    $errors[] = "Baris {$lineNo}: format salah (minimal NISN;Nama).";
                    continue;
                }

                $nisn = preg_replace('/\D+/', '', $parts[0]);
                $nama = $parts[1];
                $nomor = $parts[2] ?? null;

                if ($nisn === '') {
                    $errors[] = "Baris {$lineNo}: NISN kosong atau tidak valid.";
                    continue;
                }

                if (Siswa::where('nisn', $nisn)->exists()) {
                    $errors[] = "Baris {$lineNo}: NISN {$nisn} sudah terdaftar.";
                    continue;
                }

                $data = [
                    'nisn' => $nisn,
                    'nama_siswa' => $nama,
                    'kelas_id' => $request->kelas_id,
                    'nomor_hp_wali_murid' => $nomor,
                ];

                // create wali if requested
                    if ($request->boolean('create_wali_all')) {
                    // username base: wali.{nisn}
                    $baseUsername = 'wali.' . $nisn;
                    $username = $baseUsername;
                    $i = 1;
                    while (User::where('username', $username)->exists()) {
                        $i++;
                        $username = $baseUsername . $i;
                    }

                    // Password standardized for bulk-created wali: smkn1.walimurid.{nisn}
                    $password = 'smkn1.walimurid.' . $nisn;
                    $role = Role::findByName('Wali Murid');
                    $user = User::create([
                        'role_id' => $role?->id,
                        'nama' => 'Wali dari ' . $nama,
                        'username' => $username,
                        'email' => $username . '@no-reply.local',
                        'password' => $password,
                    ]);

                    $data['wali_murid_user_id'] = $user->id;
                    $waliCreated[] = ['nisn' => $nisn, 'username' => $username, 'password' => $password];
                }

                $s = Siswa::create($data);
                $created[] = $s;
            }

            if (count($errors) > 0) {
                DB::rollBack();
                return back()->withInput()->with('bulk_errors', $errors);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses: ' . $e->getMessage());
        }

        // Flash data to session untuk ditampilkan di halaman sukses
        session(['bulk_wali_created' => $waliCreated]);
        session(['total_created' => count($created)]);
        session(['total_wali_created' => count($waliCreated)]);
        
        return redirect()->route('siswa.bulk.success');
    }

    /**
     * Show success page after bulk create
     */
    public function bulkSuccess()
    {
        $totalCreated = session('total_created', 0);
        $totalWaliCreated = session('total_wali_created', 0);
        $waliCreated = session('bulk_wali_created', []);
        
        return view('siswa.bulk_success', [
            'totalCreated' => $totalCreated,
            'totalWaliCreated' => $totalWaliCreated,
            'waliCreated' => $waliCreated,
            'autoDownloadFile' => route('siswa.download-bulk-wali-csv'),
        ]);
    }

    /**
     * Download CSV containing bulk-created wali credentials stored in session
     */
    public function downloadBulkWaliCsv(Request $request)
    {
        $data = session('bulk_wali_created');
        if (empty($data) || !is_array($data)) {
            return redirect()->back()->with('error', 'Tidak ada kredensial wali bulk yang tersedia untuk diunduh.');
        }

        $filename = 'bulk_wali_credentials_' . date('Ymd_His') . '.xlsx.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-16LE',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            // UTF-16LE BOM untuk Excel (agar terbuka rapi di Excel)
            echo "\xFF\xFE";
            
            $headerRow = "NISN\tUsername\tPassword\n";
            echo mb_convert_encoding($headerRow, 'UTF-16LE', 'UTF-8');
            
            foreach ($data as $row) {
                $dataRow = ($row['nisn'] ?? '') . "\t" . ($row['username'] ?? '') . "\t" . ($row['password'] ?? '') . "\n";
                echo mb_convert_encoding($dataRow, 'UTF-16LE', 'UTF-8');
            }
        };

        return response()->stream($callback, 200, $headers);
    }
}

