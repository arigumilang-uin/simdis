<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add soft delete to siswa table
        if (Schema::hasTable('siswa') && !Schema::hasColumn('siswa', 'deleted_at')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft delete to riwayat_pelanggaran table
        if (Schema::hasTable('riwayat_pelanggaran') && !Schema::hasColumn('riwayat_pelanggaran', 'deleted_at')) {
            Schema::table('riwayat_pelanggaran', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft delete to tindak_lanjut table
        if (Schema::hasTable('tindak_lanjut') && !Schema::hasColumn('tindak_lanjut', 'deleted_at')) {
            Schema::table('tindak_lanjut', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft delete to surat_panggilan table
        if (Schema::hasTable('surat_panggilan') && !Schema::hasColumn('surat_panggilan', 'deleted_at')) {
            Schema::table('surat_panggilan', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('riwayat_pelanggaran', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('tindak_lanjut', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('surat_panggilan', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
