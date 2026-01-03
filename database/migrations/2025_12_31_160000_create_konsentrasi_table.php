<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Buat tabel konsentrasi (Konsentrasi Keahlian)
 * 
 * Struktur Kurikulum Merdeka SMK:
 * - Jurusan (Program Keahlian) -> dipimpin Kaprodi
 * - Konsentrasi (Konsentrasi Keahlian) -> spesialisasi dalam jurusan
 * - Kelas -> terikat ke konsentrasi
 * 
 * Kelas X biasanya belum masuk konsentrasi (dasar program keahlian)
 * Kelas XI-XII masuk konsentrasi tertentu
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('konsentrasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurusan_id')
                  ->constrained('jurusan')
                  ->onDelete('cascade')
                  ->comment('FK ke tabel jurusan (Program Keahlian)');
            $table->string('nama_konsentrasi')
                  ->comment('Nama lengkap konsentrasi keahlian');
            $table->string('kode_konsentrasi', 20)->nullable()
                  ->comment('Kode singkat konsentrasi (opsional)');
            $table->boolean('is_active')->default(true)
                  ->comment('Status aktif konsentrasi');
            $table->text('deskripsi')->nullable()
                  ->comment('Deskripsi konsentrasi (opsional)');
            
            // Index untuk performa query
            $table->index('jurusan_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konsentrasi');
    }
};
