<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambah kolom konsentrasi_id ke tabel kelas
 * 
 * Catatan:
 * - konsentrasi_id NULLABLE karena kelas X belum masuk konsentrasi
 * - jurusan_id tetap dipertahankan untuk backward compatibility
 * - Kelas bisa akses jurusan via konsentrasi (jika ada) ATAU langsung via jurusan_id
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            // Tambah kolom konsentrasi_id setelah jurusan_id
            $table->foreignId('konsentrasi_id')
                  ->nullable()
                  ->after('jurusan_id')
                  ->constrained('konsentrasi')
                  ->onDelete('set null')
                  ->comment('FK ke tabel konsentrasi (nullable untuk kelas X)');
            
            // Index untuk performa
            $table->index('konsentrasi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['konsentrasi_id']);
            $table->dropColumn('konsentrasi_id');
        });
    }
};
