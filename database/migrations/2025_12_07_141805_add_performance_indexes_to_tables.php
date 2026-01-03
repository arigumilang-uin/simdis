<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PHASE 1 (CRITICAL): Indexes untuk query yang paling sering & lambat
     * - Riwayat filtering (digunakan di hampir semua halaman)
     * - Frequency calculation (core logic rules engine)
     * - Tindak lanjut status filtering (approval workflow)
     * 
     * Expected Impact: 70% improvement pada query lambat
     */
    public function up(): void
    {
        Schema::table('riwayat_pelanggaran', function (Blueprint $table) {
            // Index untuk filtering riwayat by siswa + tanggal (DESC untuk latest first)
            // Digunakan di: RiwayatController@index, dashboard, reports
            $table->index(['siswa_id', 'tanggal_kejadian'], 'idx_riwayat_siswa_tanggal');
            
            // Index untuk frequency calculation (core rules engine logic)
            // Digunakan di: PelanggaranRulesEngine@evaluateFrequencyRules
            $table->index(['siswa_id', 'jenis_pelanggaran_id'], 'idx_riwayat_siswa_jenis');
            
            // Index untuk filtering by tanggal (dashboard & reports)
            // Digunakan di: Dashboard statistics, date range filters
            $table->index('tanggal_kejadian', 'idx_riwayat_tanggal');
            
            // Index untuk filtering by pencatat (my riwayat page)
            // Digunakan di: RiwayatController@myIndex
            $table->index('guru_pencatat_user_id', 'idx_riwayat_pencatat');
        });

        Schema::table('tindak_lanjut', function (Blueprint $table) {
            // Index untuk approval workflow (status filtering)
            // Digunakan di: ApprovalController, dashboard pending count
            $table->index(['status', 'created_at'], 'idx_tindaklanjut_status');
            
            // Index untuk siswa scope queries
            // Digunakan di: Reconciliation, siswa detail page
            $table->index(['siswa_id', 'status'], 'idx_tindaklanjut_siswa_status');
        });

        Schema::table('siswa', function (Blueprint $table) {
            // Index untuk kelas scope queries (Wali Kelas filtering)
            // Digunakan di: RiwayatController role-based scoping
            $table->index('kelas_id', 'idx_siswa_kelas');
        });

        Schema::table('kelas', function (Blueprint $table) {
            // Index untuk jurusan scope queries (Kaprodi filtering)
            // Digunakan di: RiwayatController role-based scoping
            $table->index('jurusan_id', 'idx_kelas_jurusan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pelanggaran', function (Blueprint $table) {
            $table->dropIndex('idx_riwayat_siswa_tanggal');
            $table->dropIndex('idx_riwayat_siswa_jenis');
            $table->dropIndex('idx_riwayat_tanggal');
            $table->dropIndex('idx_riwayat_pencatat');
        });

        Schema::table('tindak_lanjut', function (Blueprint $table) {
            $table->dropIndex('idx_tindaklanjut_status');
            $table->dropIndex('idx_tindaklanjut_siswa_status');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropIndex('idx_siswa_kelas');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropIndex('idx_kelas_jurusan');
        });
    }
};
