<?php

namespace App\Services\Pelanggaran;

use App\Models\JenisPelanggaran;
use App\Models\RiwayatPelanggaran;
use App\Models\Siswa;
use App\Models\TindakLanjut;
use App\Services\TindakLanjut\TindakLanjutNotificationService;

/**
 * Service untuk Rules Engine Pelanggaran (v2.0 - Frequency-Based)
 *
 * Tanggung jawab:
 * - Mengevaluasi poin berdasarkan threshold frekuensi
 * - Menentukan jenis surat berdasarkan pembina yang terlibat
 * - Membuat/update TindakLanjut dan SuratPanggilan otomatis
 * - Memberikan rekomendasi pembinaan internal (TIDAK trigger surat)
 * - Trigger notifikasi untuk approval workflow
 */
class PelanggaranRulesEngine
{
    /**
     * @var TindakLanjutNotificationService
     */
    protected $notificationService;

    /**
     * Constructor dengan dependency injection.
     */
    public function __construct(TindakLanjutNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Konstanta tipe surat (eskalasi levels)
     */
    const SURAT_1 = 'Surat 1';
    const SURAT_2 = 'Surat 2';
    const SURAT_3 = 'Surat 3';
    const SURAT_4 = 'Surat 4';

    /**
     * Proses batch pelanggaran untuk satu siswa.
     * Dipanggil saat pencatatan multiple pelanggaran.
     *
     * @param int $siswaId
     * @param array $pelanggaranIds Array ID jenis pelanggaran
     * @return void
     */
    public function processBatch(int $siswaId, array $pelanggaranIds): void
    {
        $siswa = Siswa::find($siswaId);
        if (!$siswa) return;

        // Eager load frequency rules
        $pelanggaranObjs = JenisPelanggaran::with('frequencyRules')
            ->whereIn('id', $pelanggaranIds)
            ->get();

        if ($pelanggaranObjs->isEmpty()) return;

        $totalPoinBaru = 0;
        $suratTypes = [];
        $pemicuList = [];       // Format: "Terlambat 4 kali" atau "Merokok" - untuk field pemicu di TindakLanjut
        $keperluanList = [];    // Deskripsi sanksi dari rule - untuk field keperluan di Surat
        $pembinaRolesForSurat = [];

        // Evaluasi setiap pelanggaran
        foreach ($pelanggaranObjs as $pelanggaran) {
            if ($pelanggaran->usesFrequencyRules()) {
                // Gunakan frequency-based evaluation
                $result = $this->evaluateFrequencyRules($siswaId, $pelanggaran);
                $totalPoinBaru += $result['poin_ditambahkan'];

                if ($result['surat_type']) {
                    $suratTypes[] = $result['surat_type'];
                    // Simpan pembina roles dari rule yang trigger surat
                    $pembinaRolesForSurat = $result['pembina_roles'] ?? [];
                    
                    // Generate pemicu text: "Terlambat 4 kali" atau "Merokok"
                    $pemicuList[] = $this->generatePemicuText(
                        $result['nama_pelanggaran'],
                        $result['frequency']
                    );
                    
                    // Keperluan: deskripsi sanksi dari rule
                    if (!empty($result['sanksi'])) {
                        $keperluanList[] = $result['sanksi'];
                    }
                }
            } else {
                // Fallback: immediate accumulation (backward compatibility)
                $totalPoinBaru += $pelanggaran->poin;
                
                // REMOVED: Auto-trigger surat berdasarkan poin
                // REASON: Surat HANYA trigger dari frequency rules dengan trigger_surat=true
                // Pembinaan Internal (akumulasi poin) HANYA untuk rekomendasi, TIDAK trigger surat
            }
        }

        // Tentukan tipe surat tertinggi (HANYA dari frequency rules)
        $tipeSurat = $this->tentukanTipeSuratTertinggi($suratTypes);

        // Buat/update TindakLanjut jika diperlukan
        if ($tipeSurat) {
            // Pemicu: nama pelanggaran + frekuensi
            $pemicu = implode(', ', array_unique(array_filter($pemicuList)));
            
            // Keperluan: deskripsi sanksi (untuk surat)
            $keperluan = implode('; ', array_unique(array_filter($keperluanList)));
            if (empty($keperluan)) {
                // Fallback jika tidak ada deskripsi sanksi
                $keperluan = "Pembinaan terkait: {$pemicu}";
            }
            
            // Tentukan status berdasarkan pembina yang terlibat (bukan tipe surat)
            $status = $this->tentukanStatusBerdasarkanPembina($pembinaRolesForSurat);

            $this->buatAtauUpdateTindakLanjut($siswaId, $tipeSurat, $pemicu, $keperluan, $status, $pembinaRolesForSurat);
        }
    }

    /**
     * Evaluasi frequency rules untuk satu siswa dan satu jenis pelanggaran.
     *
     * @param int $siswaId
     * @param JenisPelanggaran $pelanggaran
     * @return array ['poin_ditambahkan' => int, 'surat_type' => string|null, 'sanksi' => string, 'pembina_roles' => array, 'nama_pelanggaran' => string, 'frequency' => int]
     */
    private function evaluateFrequencyRules(int $siswaId, JenisPelanggaran $pelanggaran): array
    {
        // Hitung frekuensi total pelanggaran ini untuk siswa
        $currentFrequency = RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->where('jenis_pelanggaran_id', $pelanggaran->id)
            ->count();

        // Ambil semua frequency rules untuk pelanggaran ini
        $rules = $pelanggaran->frequencyRules;

        if ($rules->isEmpty()) {
            // Fallback: tidak ada rules, gunakan poin langsung
            return [
                'poin_ditambahkan' => $pelanggaran->poin,
                'surat_type' => null,
                'sanksi' => 'Pembinaan',
                'pembina_roles' => [],
                'nama_pelanggaran' => $pelanggaran->nama_pelanggaran,
                'frequency' => $currentFrequency,
            ];
        }

        // Cari rule yang TEPAT match dengan frekuensi saat ini
        // matchesFrequency() sekarang check EXACT match (frequency === max, atau === min jika no max)
        $matchedRule = $rules->first(function ($rule) use ($currentFrequency) {
            return $rule->matchesFrequency($currentFrequency);
        });

        if (!$matchedRule) {
            // Tidak ada rule yang match, tidak ada poin (belum mencapai threshold)
            return [
                'poin_ditambahkan' => 0,
                'surat_type' => null,
                'sanksi' => 'Belum mencapai threshold',
                'pembina_roles' => [],
                'nama_pelanggaran' => $pelanggaran->nama_pelanggaran,
                'frequency' => $currentFrequency,
            ];
        }

        // Threshold tercapai! Tambahkan poin
        return [
            'poin_ditambahkan' => $matchedRule->poin,
            'surat_type' => $matchedRule->getSuratType(),
            'sanksi' => $matchedRule->sanksi_description,
            'pembina_roles' => $matchedRule->pembina_roles ?? [],
            'nama_pelanggaran' => $pelanggaran->nama_pelanggaran,
            'frequency' => $currentFrequency,
        ];
    }

    /**
     * Tentukan tipe surat tertinggi dari array surat types.
     * Prioritas: Surat 4 > Surat 3 > Surat 2 > Surat 1
     *
     * CATATAN PENTING: Akumulasi poin TIDAK trigger surat otomatis!
     * Surat HANYA dari frequency rules yang memiliki trigger_surat = TRUE
     *
     * @param array $suratTypes
     * @return string|null
     */
    private function tentukanTipeSuratTertinggi(array $suratTypes): ?string
    {
        if (empty($suratTypes)) {
            // Tidak ada surat dari frequency rules
            return null;
        }

        // Extract level dari surat types
        $levels = array_map(function ($surat) {
            return (int) filter_var($surat, FILTER_SANITIZE_NUMBER_INT);
        }, $suratTypes);

        $maxLevel = max($levels);

        return $maxLevel > 0 ? "Surat {$maxLevel}" : null;
    }

    /**
     * Tentukan status tindak lanjut berdasarkan pembina yang terlibat.
     * 
     * Business Rule:
     * - Jika Kepala Sekolah terlibat sebagai pembina → Menunggu Persetujuan
     * - Jika hanya pembina level bawah (Wali Kelas, Kaprodi, Waka) → Baru
     * 
     * Filosofi: Kepala Sekolah harus approve keterlibatannya sendiri dalam pembinaan.
     * Ini lebih fleksibel dan konsisten dibanding hardcoded berdasarkan tipe surat.
     * 
     * Contoh:
     * - Wali Kelas + Kaprodi (Surat 2) → Status: Baru
     * - Wali Kelas + Kaprodi + Waka (Surat 3) → Status: Baru
     * - Wali Kelas + Kaprodi + Waka + Kepsek (Surat 4) → Status: Menunggu Persetujuan
     * - Wali Kelas + Kepsek (Surat 2) → Status: Menunggu Persetujuan (edge case handled!)
     * 
     * @param array $pembinaRoles Array of pembina role names
     * @return string Status: 'Menunggu Persetujuan' atau 'Baru'
     */
    private function tentukanStatusBerdasarkanPembina(array $pembinaRoles): string
    {
        // Jika Kepala Sekolah terlibat sebagai pembina, butuh approval
        if (in_array('Kepala Sekolah', $pembinaRoles)) {
            return 'Menunggu Persetujuan';
        }
        
        // Jika hanya pembina level bawah, langsung proses
        return 'Baru';
    }

    /**
     * Generate teks pemicu yang jelas untuk surat panggilan.
     * 
     * Format output:
     * - Jika frekuensi > 1: "Terlambat 4 kali"
     * - Jika frekuensi = 1: "Merokok" (tanpa "1 kali")
     * 
     * @param string $namaPelanggaran Nama jenis pelanggaran
     * @param int $frequency Frekuensi pelanggaran yang trigger surat
     * @return string Teks pemicu yang jelas
     */
    private function generatePemicuText(string $namaPelanggaran, int $frequency): string
    {
        if ($frequency <= 1) {
            // Frekuensi 1 kali: hanya nama pelanggaran
            return $namaPelanggaran;
        }
        
        // Frekuensi > 1: "Nama Pelanggaran X kali"
        return "{$namaPelanggaran} {$frequency} kali";
    }

    /**
     * Tentukan rekomendasi pembina untuk pembinaan internal berdasarkan akumulasi poin.
     * CATATAN: Ini HANYA rekomendasi konseling, TIDAK trigger surat pemanggilan.
     * 
     * LOGIC GAP HANDLING:
     * - UI menampilkan gap (0-50, 55-100, 105-300, 305-500) sesuai tata tertib
     * - Sistem otomatis isi gap dengan rule sebelumnya
     * - Contoh: Poin 53 → Masuk rule "0-50" (karena belum sampai 55)
     * - Contoh: Poin 303 → Masuk rule "105-300" (karena belum sampai 305)
     *
     * @param int $totalPoin Total poin akumulasi siswa
     * @return array ['pembina_roles' => array, 'keterangan' => string, 'range_text' => string]
     */
    public function getPembinaanInternalRekomendasi(int $totalPoin): array
    {
        // Query rules dari database (ordered by display_order)
        $rules = \App\Models\PembinaanInternalRule::orderBy('display_order')->get();

        if ($rules->isEmpty()) {
            return [
                'pembina_roles' => [],
                'keterangan' => 'Tidak ada pembinaan',
                'range_text' => 'N/A',
            ];
        }

        // Cari rule yang match dengan total poin
        // Logic: Cari rule terakhir yang poin_min <= totalPoin
        $matchedRule = null;
        
        foreach ($rules as $rule) {
            // Jika poin siswa >= poin_min rule ini
            if ($totalPoin >= $rule->poin_min) {
                // Jika rule ini punya poin_max dan poin siswa <= poin_max, match!
                if ($rule->poin_max !== null && $totalPoin <= $rule->poin_max) {
                    $matchedRule = $rule;
                    break;
                }
                
                // Jika rule ini open-ended (poin_max = null), match!
                if ($rule->poin_max === null) {
                    $matchedRule = $rule;
                    break;
                }
                
                // Jika poin siswa > poin_max rule ini, cek apakah ada rule berikutnya
                $nextRule = $rules->where('display_order', '>', $rule->display_order)->first();
                
                // Jika tidak ada rule berikutnya, atau poin siswa < poin_min rule berikutnya
                // Maka gunakan rule ini (untuk handle gap)
                if (!$nextRule || $totalPoin < $nextRule->poin_min) {
                    $matchedRule = $rule;
                    break;
                }
            }
        }

        if (!$matchedRule) {
            return [
                'pembina_roles' => [],
                'keterangan' => 'Tidak ada pembinaan',
                'range_text' => 'N/A',
            ];
        }

        return [
            'pembina_roles' => $matchedRule->pembina_roles,
            'keterangan' => $matchedRule->keterangan,
            'range_text' => $matchedRule->getRangeText(),
        ];
    }

    /**
     * Hitung total poin akumulasi siswa dari semua riwayat pelanggaran.
     * 
     * UPDATED LOGIC:
     * - For frequency-based rules: Calculate using evaluateFrequencyRules()
     * - For legacy rules: Use poin from jenis_pelanggaran table
     * 
     * @param int $siswaId
     * @return int
     */
    public function hitungTotalPoinAkumulasi(int $siswaId): int
    {
        // Get ALL riwayat for this siswa, grouped by jenis_pelanggaran
        $riwayat = RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->with('jenisPelanggaran.frequencyRules')
            ->get()
            ->groupBy('jenis_pelanggaran_id');

        $totalPoin = 0;

        // For each jenis pelanggaran, calculate poin based on its rules
        foreach ($riwayat as $jenisPelanggaranId => $records) {
            $jenisPelanggaran = $records->first()?->jenisPelanggaran;
            
            if (!$jenisPelanggaran) continue;

            if ($jenisPelanggaran->usesFrequencyRules()) {
                // FREQUENCY-BASED: Iterate through ALL frequencies and sum matched poin
                // FIX: Previous logic only checked current frequency, missing cumulative poin
                $currentFrequency = $records->count();
                $rules = $jenisPelanggaran->frequencyRules;
                
                // Iterate from frequency 1 to current
                for ($freq = 1; $freq <= $currentFrequency; $freq++) {
                    // Check each rule if it matches this frequency
                    foreach ($rules as $rule) {
                        if ($rule->matchesFrequency($freq)) {
                            $totalPoin += $rule->poin;
                        }
                    }
                }
            } else {
                // LEGACY: Sum poin from all records (backward compatibility)
                $totalPoin += $records->count() * $jenisPelanggaran->poin;
            }
        }

        return $totalPoin;
    }

    /**
     * Get siswa yang perlu pembinaan berdasarkan akumulasi poin.
     * 
     * UPDATED: Use frequency-based point calculation (hitungTotalPoinAkumulasi)
     * Old method used simple SUM which doesn't work with frequency rules!
     * 
     * @param int|null $poinMin Filter minimum poin (optional)
     * @param int|null $poinMax Filter maximum poin (optional)
     * @return \Illuminate\Support\Collection
     */
    public function getSiswaPerluPembinaan(?int $poinMin = null, ?int $poinMax = null)
    {
        // Fetch pembinaan rules ONCE
        $rules = \App\Models\PembinaanInternalRule::orderBy('display_order')->get();
        
        // Get ALL siswa yang punya riwayat pelanggaran
        // Then calculate poin using frequency-based logic
        $siswaIds = \App\Models\RiwayatPelanggaran::distinct()
            ->pluck('siswa_id');
        
        // Calculate poin for each siswa using correct frequency logic
        $siswaList = collect();
        
        foreach ($siswaIds as $siswaId) {
            // Use the CORRECT frequency-based calculation
            $totalPoin = $this->hitungTotalPoinAkumulasi($siswaId);
            
            // Skip if poin is 0
            if ($totalPoin == 0) {
                continue;
            }
            
            // Apply poin filters
            if ($poinMin !== null && $totalPoin < $poinMin) {
                continue;
            }
            if ($poinMax !== null && $totalPoin > $poinMax) {
                continue;
            }
            
            // Get rekomendasi
            $rekomendasi = $this->getPembinaanInternalRekomendasiOptimized($totalPoin, $rules);
            
            // Skip if no matching rule (no recommendation)
            if (empty($rekomendasi['pembina_roles'])) {
                continue;
            }
            
            // Load siswa with relations
            $siswa = Siswa::with(['kelas.jurusan', 'kelas.waliKelas'])->find($siswaId);
            
            if (!$siswa) {
                continue;
            }
            
            $siswaList->push([
                'siswa' => $siswa,
                'total_poin' => $totalPoin,
                'rekomendasi' => $rekomendasi,
            ]);
        }
        
        return $siswaList->sortByDesc('total_poin')->values();
    }
    
    /**
     * OPTIMIZED VERSION: Process recommendation using pre-fetched rules collection
     * This eliminates the N queries to pembinaan_internal_rules table
     * 
     * @param int $totalPoin
     * @param \Illuminate\Support\Collection $rules Pre-fetched rules collection
     * @return array
     */
    protected function getPembinaanInternalRekomendasiOptimized(int $totalPoin, $rules): array
    {
        if ($rules->isEmpty()) {
            return [
                'pembina_roles' => [],
                'keterangan' => 'Tidak ada pembinaan',
                'range_text' => 'N/A',
            ];
        }

        // Cari rule yang match dengan total poin
        $matchedRule = null;
        
        foreach ($rules as $rule) {
            if ($totalPoin >= $rule->poin_min) {
                if ($rule->poin_max !== null && $totalPoin <= $rule->poin_max) {
                    $matchedRule = $rule;
                    break;
                }
                
                if ($rule->poin_max === null) {
                    $matchedRule = $rule;
                    break;
                }
                
                $nextRule = $rules->where('display_order', '>', $rule->display_order)->first();
                
                if (!$nextRule || $totalPoin < $nextRule->poin_min) {
                    $matchedRule = $rule;
                    break;
                }
            }
        }

        if (!$matchedRule) {
            return [
                'pembina_roles' => [],
                'keterangan' => 'Tidak ada pembinaan',
                'range_text' => 'N/A',
            ];
        }

        return [
            'pembina_roles' => $matchedRule->pembina_roles,
            'keterangan' => $matchedRule->keterangan,
            'range_text' => $matchedRule->getRangeText(),
        ];
    }

    /**
     * Buat atau update TindakLanjut dan SuratPanggilan untuk siswa.
     *
     * @param int $siswaId
     * @param string $tipeSurat
     * @param string $pemicu Nama pelanggaran + frekuensi (untuk TindakLanjut)
     * @param string $keperluan Deskripsi sanksi (untuk SuratPanggilan)
     * @param string $status
     * @param array $pembinaRoles Array of pembina roles from frequency rule
     * @return void
     */
    private function buatAtauUpdateTindakLanjut(
        int $siswaId,
        string $tipeSurat,
        string $pemicu,
        string $keperluan,
        string $status,
        array $pembinaRoles = []
    ): void {
        $sanksi = "Pemanggilan Wali Murid ({$tipeSurat})";

        // Cari kasus aktif siswa
        $kasusAktif = TindakLanjut::with('suratPanggilan')
            ->where('siswa_id', $siswaId)
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->first();

        if (!$kasusAktif) {
            try {
                // Buat TindakLanjut baru
                $tl = TindakLanjut::create([
                    'siswa_id' => $siswaId,
                    'pemicu' => $pemicu,
                    'sanksi_deskripsi' => $sanksi,
                    'pembina_roles' => $pembinaRoles,  // ✅ CRITICAL: Untuk filtering dashboard
                    'status' => $status,
                ]);

                // Build pembina data menggunakan service
                $siswa = Siswa::with(['kelas.waliKelas', 'kelas.jurusan.kaprodi'])->find($siswaId);
                
                if (!$siswa) {
                    \Log::error("Siswa not found for TindakLanjut", ['siswa_id' => $siswaId]);
                    throw new \Exception("Siswa dengan ID {$siswaId} tidak ditemukan");
                }
                
                $suratService = new SuratPanggilanService();
                $pembinaData = $suratService->buildPembinaData($pembinaRoles, $siswa);
                
                if (empty($pembinaData)) {
                    \Log::warning("Pembina data is empty", [
                        'siswa_id' => $siswaId,
                        'pembina_roles' => $pembinaRoles
                    ]);
                }
                
                $meetingSchedule = $suratService->setDefaultMeetingSchedule();

                // Buat SuratPanggilan dengan pembina data
                $tl->suratPanggilan()->create([
                    'nomor_surat' => $suratService->generateNomorSurat(),
                    'tipe_surat' => $tipeSurat,
                    'tanggal_surat' => now(),
                    'pembina_data' => $pembinaData,
                    'pembina_roles' => $pembinaRoles,  // ✅ CRITICAL: Untuk template tanda tangan
                    'tanggal_pertemuan' => $meetingSchedule['tanggal_pertemuan'],
                    'waktu_pertemuan' => $meetingSchedule['waktu_pertemuan'],
                    'keperluan' => $keperluan,  // ✅ Keperluan = deskripsi sanksi, bukan pemicu
                ]);

                // Trigger notifikasi jika butuh approval (Surat 3 & 4)
                $this->notificationService->notifyKasusButuhApproval($tl);
                
                // Trigger notifikasi awareness untuk Waka (Surat 2)
                $this->notificationService->notifyWakaForSurat2($tl);
                
            } catch (\Exception $e) {
                \Log::error("Error creating TindakLanjut + SuratPanggilan", [
                    'siswa_id' => $siswaId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'tipe_surat' => $tipeSurat,
                    'pembina_roles' => $pembinaRoles
                ]);
                
                // Re-throw untuk memastikan transaction rollback
                throw $e;
            }
        } else {
            // Update jika eskalasi diperlukan
            $this->akumulasiKasusAktif($kasusAktif, $tipeSurat, $pemicu, $keperluan, $status, $pembinaRoles);
        }
    }

    /**
     * Rekonsiliasi tindak lanjut untuk seorang siswa berdasarkan riwayat saat ini.
     *
     * Jika setelah edit/hapus riwayat poin akumulasi turun sehingga tidak lagi memenuhi
     * threshold, maka kasus aktif akan dibatalkan. Jika masih memenuhi, akan dibuat
     * atau di-escalate sesuai kebutuhan.
     *
     * @param int $siswaId
     * @param bool $deleteIfNoSurat
     * @return void
     */
    public function reconcileForSiswa(int $siswaId, bool $deleteIfNoSurat = false): void
    {
        $siswa = Siswa::find($siswaId);
        if (!$siswa) return;

        // Ambil semua jenis pelanggaran yang pernah dicatat untuk siswa ini
        $jenisIds = RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->pluck('jenis_pelanggaran_id')
            ->unique()
            ->toArray();

        $pelanggaranObjs = JenisPelanggaran::with('frequencyRules')
            ->whereIn('id', $jenisIds)
            ->get();

        $suratTypes = [];
        $pembinaRolesForSurat = [];
        $pemicuList = [];       // Format: "Terlambat 4 kali" atau "Merokok"
        $keperluanList = [];    // Deskripsi sanksi dari rule

        // Re-evaluasi setiap pelanggaran
        foreach ($pelanggaranObjs as $pelanggaran) {
            if ($pelanggaran->usesFrequencyRules()) {
                $result = $this->evaluateFrequencyRules($siswaId, $pelanggaran);
                if ($result['surat_type']) {
                    $suratTypes[] = $result['surat_type'];
                    $pembinaRolesForSurat = $result['pembina_roles'] ?? [];
                    
                    // Generate pemicu text yang jelas
                    $pemicuList[] = $this->generatePemicuText(
                        $result['nama_pelanggaran'],
                        $result['frequency']
                    );
                    
                    // Keperluan: deskripsi sanksi dari rule
                    if (!empty($result['sanksi'])) {
                        $keperluanList[] = $result['sanksi'];
                    }
                }
            } else {
                // Backward compatibility
                if ($pelanggaran->poin >= 100) {
                    if ($pelanggaran->poin >= 200) {
                        $suratTypes[] = self::SURAT_3;
                    } elseif ($pelanggaran->poin > 500) {
                        $suratTypes[] = self::SURAT_4;
                    } else {
                        $suratTypes[] = self::SURAT_2;
                    }
                    $pemicuList[] = $pelanggaran->nama_pelanggaran;
                }
            }
        }

        $tipeSurat = $this->tentukanTipeSuratTertinggi($suratTypes);
        
        // Generate pemicu yang jelas
        $pemicu = implode(', ', array_unique(array_filter($pemicuList)));
        if (empty($pemicu)) {
            $pemicu = 'Pelanggaran berulang';  // Fallback jika tidak ada pemicu
        }
        
        // Keperluan: deskripsi sanksi (untuk surat)
        $keperluan = implode('; ', array_unique(array_filter($keperluanList)));
        if (empty($keperluan)) {
            $keperluan = "Pembinaan terkait: {$pemicu}";
        }

        // Cari kasus aktif yang mungkin ada
        $kasusAktif = TindakLanjut::with('suratPanggilan')
            ->where('siswa_id', $siswaId)
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->first();

        if ($tipeSurat) {
            // Jika masih perlu tindak lanjut: buat baru atau update kasus yang ada
            if (!$kasusAktif) {
                // Tentukan status berdasarkan pembina
                $status = $this->tentukanStatusBerdasarkanPembina($pembinaRolesForSurat);
                $this->buatAtauUpdateTindakLanjut($siswaId, $tipeSurat, $pemicu, $keperluan, $status, $pembinaRolesForSurat);
                return;
            }

            // Jika ada kasus aktif, perbarui agar sesuai dengan tipe surat baru
            // Tentukan status berdasarkan pembina (bukan tipe surat)
            $statusBaru = $this->tentukanStatusBerdasarkanPembina($pembinaRolesForSurat);
            
            $kasusAktif->update([
                'pemicu' => $pemicu,
                'sanksi_deskripsi' => "Pemanggilan Wali Murid ({$tipeSurat})",
                'status' => $statusBaru,
            ]);

            if ($kasusAktif->suratPanggilan) {
                // Update pembina data
                $suratService = new SuratPanggilanService();
                $pembinaData = $suratService->buildPembinaData($pembinaRolesForSurat, $siswa);
                
                $kasusAktif->suratPanggilan()->update([
                    'tipe_surat' => $tipeSurat,
                    'pembina_data' => $pembinaData,
                    'pembina_roles' => $pembinaRolesForSurat,
                    'keperluan' => $keperluan,  // ✅ Keperluan = deskripsi sanksi
                ]);
            } else {
                // jika sebelumnya tidak ada surat, buat satu
                $suratService = new SuratPanggilanService();
                $pembinaData = $suratService->buildPembinaData($pembinaRolesForSurat, $siswa);
                $meetingSchedule = $suratService->setDefaultMeetingSchedule();

                $kasusAktif->suratPanggilan()->create([
                    'nomor_surat' => $suratService->generateNomorSurat(),
                    'tipe_surat' => $tipeSurat,
                    'tanggal_surat' => now(),
                    'pembina_data' => $pembinaData,
                    'pembina_roles' => $pembinaRolesForSurat,
                    'tanggal_pertemuan' => $meetingSchedule['tanggal_pertemuan'],
                    'waktu_pertemuan' => $meetingSchedule['waktu_pertemuan'],
                    'keperluan' => $keperluan,  // ✅ Keperluan = deskripsi sanksi
                ]);
            }

            return;
        }

        // Jika tidak ada tipe surat lagi namun ada kasus aktif
        if ($kasusAktif) {
            if ($deleteIfNoSurat) {
                // Hapus seluruh kasus (beserta suratnya) jika dipicu oleh penghapusan oleh pelapor
                $kasusAktif->delete();
                return;
            }

            // Jika tidak dihapus, tutup kasus secara rapi (set Selesai) dan hapus surat panggilan
            $kasusAktif->update([
                'status' => 'Selesai',
                'pemicu' => 'Dibatalkan otomatis setelah penyesuaian poin',
                'sanksi_deskripsi' => 'Dibatalkan oleh sistem',
            ]);

            if ($kasusAktif->suratPanggilan) {
                $kasusAktif->suratPanggilan()->delete();
            }
        }
    }

    /**
     * Akumulasi data pelanggaran baru ke kasus aktif yang sudah ada.
     * 
     * Logika:
     * - Pemicu: Gabungan dari semua nama pelanggaran + frekuensi (jika belum ada)
     * - Keperluan: Gabungan dari semua deskripsi sanksi (jika belum ada)
     * - Pembina: Union/merge dari semua pembina roles (mengambil yang terbanyak)
     * - Tipe Surat: Ditentukan dari jumlah pembina terbanyak
     *
     * @param TindakLanjut $kasusAktif Kasus aktif yang akan di-update
     * @param string $tipeSuratBaru Tipe surat dari pelanggaran baru
     * @param string $pemicuBaru Nama pelanggaran + frekuensi baru
     * @param string $keperluanBaru Deskripsi sanksi baru
     * @param string $statusBaru (akan dihitung ulang berdasarkan pembina final)
     * @param array $pembinaRolesBaru Pembina roles dari pelanggaran baru
     * @return void
     */
    private function akumulasiKasusAktif(
        TindakLanjut $kasusAktif,
        string $tipeSuratBaru,
        string $pemicuBaru,
        string $keperluanBaru,
        string $statusBaru,
        array $pembinaRolesBaru = []
    ): void {
        // === 1. AKUMULASI PEMICU ===
        $pemicuLama = $kasusAktif->pemicu ?? '';
        $pemicuItems = array_filter(array_map('trim', explode(',', $pemicuLama)));
        
        // Tambahkan pemicu baru jika belum ada
        foreach (array_filter(array_map('trim', explode(',', $pemicuBaru))) as $item) {
            if (!in_array($item, $pemicuItems)) {
                $pemicuItems[] = $item;
            }
        }
        $pemicuFinal = implode(', ', array_unique($pemicuItems));

        // === 2. AKUMULASI KEPERLUAN ===
        $keperluanLama = $kasusAktif->suratPanggilan?->keperluan ?? '';
        $keperluanItems = array_filter(array_map('trim', explode(';', $keperluanLama)));
        
        // Tambahkan keperluan baru jika belum ada
        foreach (array_filter(array_map('trim', explode(';', $keperluanBaru))) as $item) {
            if (!in_array($item, $keperluanItems)) {
                $keperluanItems[] = $item;
            }
        }
        $keperluanFinal = implode('; ', array_unique($keperluanItems));

        // === 3. AKUMULASI PEMBINA (UNION) ===
        $pembinaLama = $kasusAktif->pembina_roles ?? [];
        $pembinaFinal = array_values(array_unique(array_merge($pembinaLama, $pembinaRolesBaru)));

        // === 4. TENTUKAN TIPE SURAT BERDASARKAN JUMLAH PEMBINA ===
        $tipeSuratFinal = $this->tentukanTipeSuratDariPembina($pembinaFinal);

        // === 5. TENTUKAN STATUS BERDASARKAN PEMBINA FINAL ===
        $statusFinal = $this->tentukanStatusBerdasarkanPembina($pembinaFinal);

        // === 6. UPDATE TINDAK LANJUT ===
        $kasusAktif->update([
            'pemicu' => $pemicuFinal,
            'sanksi_deskripsi' => "Pemanggilan Wali Murid ({$tipeSuratFinal})",
            'pembina_roles' => $pembinaFinal,
            'status' => $statusFinal,
        ]);

        // === 7. UPDATE SURAT PANGGILAN ===
        if ($kasusAktif->suratPanggilan) {
            $siswa = $kasusAktif->siswa;
            $suratService = new SuratPanggilanService();
            $pembinaData = $suratService->buildPembinaData($pembinaFinal, $siswa);

            $kasusAktif->suratPanggilan()->update([
                'tipe_surat' => $tipeSuratFinal,
                'pembina_data' => $pembinaData,
                'pembina_roles' => $pembinaFinal,
                'keperluan' => $keperluanFinal,
            ]);
        }

        // === 8. NOTIFIKASI JIKA KEPALA SEKOLAH BARU TERLIBAT ===
        $kepsekBaruTerlibat = in_array('Kepala Sekolah', $pembinaRolesBaru) && 
                              !in_array('Kepala Sekolah', $pembinaLama);
        
        if ($kepsekBaruTerlibat) {
            $this->notificationService->notifyKasusButuhApproval($kasusAktif);
        }
    }

    /**
     * Tentukan tipe surat berdasarkan jumlah pembina yang terlibat.
     * 
     * @param array $pembinaRoles
     * @return string Tipe surat (Surat 1, 2, 3, atau 4)
     */
    private function tentukanTipeSuratDariPembina(array $pembinaRoles): string
    {
        // Hitung pembina yang dihitung (exclude "Semua Guru & Staff")
        $pembinaCount = count(array_filter($pembinaRoles, function($role) {
            return $role !== 'Semua Guru & Staff';
        }));

        return match(true) {
            $pembinaCount >= 4 => 'Surat 4',
            $pembinaCount === 3 => 'Surat 3',
            $pembinaCount === 2 => 'Surat 2',
            default => 'Surat 1',
        };
    }
}

