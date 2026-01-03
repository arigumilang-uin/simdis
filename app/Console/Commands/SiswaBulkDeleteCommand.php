<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SiswaBulkDeleteCommand extends Command
{
    protected $signature = 'siswa:bulk-delete
                            {--kelas= : Kelas ID to delete students from}
                            {--jurusan= : Jurusan ID to delete students from}
                            {--tingkat= : Tingkat (e.g., "10" untuk X) to delete students}
                            {--ids= : Comma-separated student IDs to delete}
                            {--dry-run : Only show counts, do not delete}
                            {--export= : Export backup CSV to this path before delete}
                            {--confirm : Skip confirmation prompt}
                            {--force : Force delete (hard-delete, not soft-delete)}
                            {--delete-wali : Delete orphaned Wali Murid accounts}';

    protected $description = 'Bulk delete students with dry-run, export, and confirmation. Soft-deletes are cascaded to relations (riwayat pelanggaran, tindak lanjut, surat panggilan).';

    public function handle()
    {
        $this->info('=== SISWA BULK DELETE AUDIT ===');
        $this->newLine();

        // ========== STEP 1: Build query based on scope ==========
        $query = Siswa::query();

        if ($this->option('kelas')) {
            $query->where('kelas_id', $this->option('kelas'));
            $kelas = Kelas::find($this->option('kelas'));
            if (!$kelas) {
                $this->error("Kelas dengan ID {$this->option('kelas')} tidak ditemukan.");
                return 1;
            }
            $this->info("Scope: Kelas {$kelas->nama_kelas}");
        } elseif ($this->option('jurusan')) {
            $jurusan = Jurusan::find($this->option('jurusan'));
            if (!$jurusan) {
                $this->error("Jurusan dengan ID {$this->option('jurusan')} tidak ditemukan.");
                return 1;
            }
            $query->whereHas('kelas', function ($q) use ($jurusan) {
                $q->where('jurusan_id', $jurusan->id);
            });
            $this->info("Scope: Jurusan {$jurusan->nama_jurusan}");
        } elseif ($this->option('tingkat')) {
            $tingkat = $this->option('tingkat');
            $query->whereHas('kelas', function ($q) use ($tingkat) {
                $q->where('nama_kelas', 'like', "{$tingkat} %");
            });
            $this->info("Scope: Tingkat {$tingkat}");
        } elseif ($this->option('ids')) {
            $ids = explode(',', $this->option('ids'));
            $ids = array_map('intval', $ids);
            $query->whereIn('id', $ids);
            $this->info("Scope: Manual IDs: " . implode(', ', $ids));
        } else {
            $this->error('Anda harus memberikan scope: --kelas, --jurusan, --tingkat, atau --ids');
            return 1;
        }

        $this->newLine();

        // ========== STEP 2: Get siswas to delete ==========
        $siswas = $query->get();
        $totalSiswa = $siswas->count();

        if ($totalSiswa === 0) {
            $this->warn('Tidak ada siswa yang sesuai dengan kriteria.');
            return 0;
        }

        $this->info("Total siswa yang akan dihapus: {$totalSiswa}");

        // ========== STEP 3: Count related records ==========
        $siswaIds = $siswas->pluck('id');

        $totalRiwayat = DB::table('riwayat_pelanggaran')
            ->whereIn('siswa_id', $siswaIds)
            ->count();

        $totalTindak = DB::table('tindak_lanjut')
            ->whereIn('siswa_id', $siswaIds)
            ->count();

        $totalSurat = DB::table('surat_panggilan')
            ->join('tindak_lanjut', 'surat_panggilan.tindak_lanjut_id', '=', 'tindak_lanjut.id')
            ->whereIn('tindak_lanjut.siswa_id', $siswaIds)
            ->count();

        $totalWali = DB::table('siswa')
            ->whereIn('id', $siswaIds)
            ->whereNotNull('wali_murid_user_id')
            ->distinct()
            ->count('wali_murid_user_id');

        $this->newLine();
        $this->info('--- RINGKASAN DATA YANG AKAN TERHAPUS ---');
        $this->info("Siswa: {$totalSiswa}");
        $this->info("Riwayat Pelanggaran: {$totalRiwayat}");
        $this->info("Tindak Lanjut: {$totalTindak}");
        $this->info("Surat Panggilan: {$totalSurat}");
        $this->info("Akun Wali Murid terkait (TIDAK akan dihapus): {$totalWali}");
        
        // ========== STEP 3B: Detect orphaned wali murid if --delete-wali flag ==========
        $orphanedWaliIds = [];
        if ($this->option('delete-wali')) {
            $orphanedWaliIds = $this->detectOrphanedWali($siswas);
            if (count($orphanedWaliIds) > 0) {
                $this->newLine();
                $this->warn('⚠ Akun Wali Murid Orphaned terdeteksi: ' . count($orphanedWaliIds) . ' akun');
                $orphanedWalis = DB::table('users')
                    ->whereIn('id', $orphanedWaliIds)
                    ->select('id', 'nama', 'username', 'email')
                    ->get();
                foreach ($orphanedWalis as $wali) {
                    $this->line("  - {$wali->nama} ({$wali->username})");
                }
            } else {
                $this->info('ℹ Tidak ada akun Wali Murid yang orphaned.');
            }
        }
        
        $this->newLine();

        // ========== STEP 4: Dry-run only ==========
        if ($this->option('dry-run')) {
            $this->info('✓ DRY-RUN MODE: Tidak ada perubahan yang dilakukan.');
            return 0;
        }

        // ========== STEP 5: Export backup if requested ==========
        $exportPath = null;
        if ($this->option('export')) {
            $exportPath = $this->option('export');
            $this->exportBackup($siswas, $siswaIds, $exportPath);
            $this->info("✓ Backup exported ke: {$exportPath}");
        }

        // ========== STEP 6: Confirmation ==========
        if (!$this->option('confirm')) {
            $this->warn('⚠ PERHATIAN: Operasi ini akan MENGHAPUS data secara PERMANEN (soft-delete dengan restore dapat).');
            if (count($orphanedWaliIds) > 0 && $this->option('delete-wali')) {
                $this->warn('   Termasuk ' . count($orphanedWaliIds) . ' akun Wali Murid yang orphaned.');
            }
            $this->newLine();

            $confirmation = $this->ask(
                "Ketik 'DELETE' untuk mengkonfirmasi penghapusan {$totalSiswa} siswa dan data terkait",
                ''
            );

            if ($confirmation !== 'DELETE') {
                $this->info('❌ Operasi dibatalkan.');
                return 1;
            }
        }

        // ========== STEP 7: Perform deletion ==========
        $isForceDelete = $this->option('force');
        $deleteMethod = $isForceDelete ? 'forceDelete' : 'delete';

        try {
            DB::beginTransaction();

            $deletedCount = 0;
            foreach ($siswas as $siswa) {
                $siswa->{$deleteMethod}();
                $deletedCount++;
            }

            // Delete orphaned wali accounts if --delete-wali flag is set
            $deletedWaliCount = 0;
            if ($this->option('delete-wali') && count($orphanedWaliIds) > 0) {
                $waliModel = 'App\Models\User';
                foreach ($orphanedWaliIds as $waliId) {
                    $wali = DB::table('users')->where('id', $waliId)->first();
                    if ($wali) {
                        if ($isForceDelete) {
                            DB::table('users')->where('id', $waliId)->forceDelete();
                        } else {
                            DB::table('users')->where('id', $waliId)->update(['deleted_at' => now()]);
                        }
                        $deletedWaliCount++;
                    }
                }
            }

            DB::commit();

            $this->newLine();
            $this->info('✓ Operasi selesai berhasil!');
            $this->info("Siswa dihapus: {$deletedCount}");
            if ($this->option('delete-wali') && $deletedWaliCount > 0) {
                $this->info("Akun Wali Murid dihapus: {$deletedWaliCount}");
            }
            if ($isForceDelete) {
                $this->info('Mode: Hard-delete (permanent)');
            } else {
                $this->info('Mode: Soft-delete (dapat di-restore)');
            }

            if ($exportPath) {
                $this->info("Backup file: {$exportPath}");
            }

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Terjadi kesalahan: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Export data to CSV backup before deletion
     */
    private function exportBackup($siswas, $siswaIds, $exportPath)
    {
        $siswaData = [];
        foreach ($siswas as $siswa) {
            $siswaData[] = [
                $siswa->id,
                $siswa->nisn,
                $siswa->nama_siswa,
                $siswa->kelas->nama_kelas ?? 'N/A',
                $siswa->waliMurid?->nama ?? 'Tidak ada',
                $siswa->nomor_hp_wali_murid ?? '-',
                $siswa->created_at,
            ];
        }

        $riwayatData = DB::table('riwayat_pelanggaran')
            ->whereIn('siswa_id', $siswaIds)
            ->get(['id', 'siswa_id', 'jenis_pelanggaran_id', 'guru_pencatat_user_id', 'tanggal_kejadian', 'keterangan']);

        $tindakData = DB::table('tindak_lanjut')
            ->whereIn('siswa_id', $siswaIds)
            ->get(['id', 'siswa_id', 'pemicu', 'status', 'tanggal_tindak_lanjut', 'created_at']);

        $suratData = DB::table('surat_panggilan')
            ->join('tindak_lanjut', 'surat_panggilan.tindak_lanjut_id', '=', 'tindak_lanjut.id')
            ->whereIn('tindak_lanjut.siswa_id', $siswaIds)
            ->get(['surat_panggilan.id', 'surat_panggilan.nomor_surat', 'surat_panggilan.tipe_surat', 'surat_panggilan.tanggal_surat']);

        $csv = "=== BACKUP PENGHAPUSAN SISWA ===\n";
        $csv .= "Tanggal: " . now()->format('Y-m-d H:i:s') . "\n";
        $csv .= "Total Siswa: " . count($siswaData) . "\n";
        $csv .= "Total Riwayat Pelanggaran: " . $riwayatData->count() . "\n";
        $csv .= "Total Tindak Lanjut: " . $tindakData->count() . "\n";
        $csv .= "Total Surat Panggilan: " . $suratData->count() . "\n\n";

        $csv .= "--- DATA SISWA ---\n";
        $csv .= "ID,NISN,Nama,Kelas,Wali Murid,No HP,Created At\n";
        foreach ($siswaData as $row) {
            $csv .= implode(',', $row) . "\n";
        }

        $csv .= "\n--- DATA RIWAYAT PELANGGARAN ---\n";
        $csv .= "ID,Siswa ID,Jenis Pelanggaran ID,Guru Pencatat,Tanggal,Keterangan\n";
        foreach ($riwayatData as $row) {
            $csv .= "{$row->id},{$row->siswa_id},{$row->jenis_pelanggaran_id},{$row->guru_pencatat_user_id},{$row->tanggal_kejadian},{$row->keterangan}\n";
        }

        $csv .= "\n--- DATA TINDAK LANJUT ---\n";
        $csv .= "ID,Siswa ID,Pemicu,Status,Tanggal Tindak Lanjut,Created At\n";
        foreach ($tindakData as $row) {
            $csv .= "{$row->id},{$row->siswa_id},{$row->pemicu},{$row->status},{$row->tanggal_tindak_lanjut},{$row->created_at}\n";
        }

        $csv .= "\n--- DATA SURAT PANGGILAN ---\n";
        $csv .= "ID,Nomor Surat,Tipe Surat,Tanggal Surat\n";
        foreach ($suratData as $row) {
            $csv .= "{$row->id},{$row->nomor_surat},{$row->tipe_surat},{$row->tanggal_surat}\n";
        }

        // Write to file (can be storage/ or public/ path)
        file_put_contents($exportPath, $csv);
    }

    /**
     * Detect orphaned Wali Murid accounts
     * A wali is orphaned if they have no other students after deletion
     */
    private function detectOrphanedWali($siswasToDelete)
    {
        $waliIds = $siswasToDelete->pluck('wali_murid_user_id')->filter()->unique()->toArray();
        if (empty($waliIds)) {
            return [];
        }

        $orphanedWaliIds = [];
        foreach ($waliIds as $waliId) {
            $otherSiswaCount = DB::table('siswa')
                ->where('wali_murid_user_id', $waliId)
                ->whereNotIn('id', $siswasToDelete->pluck('id')->toArray())
                ->count();

            if ($otherSiswaCount === 0) {
                $orphanedWaliIds[] = $waliId;
            }
        }

        return $orphanedWaliIds;
    }
}

