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
        // Gunakan 'kategori_pelanggaran' (singular) sesuai SQL script kita
        Schema::create('kategori_pelanggaran', function (Blueprint $table) {
            $table->id(); // (BIGINT UNSIGNED, PK)

            // Kolom Nama Kategori (sesuai SQL script: VARCHAR 100)
            $table->string('nama_kategori', 100);
            
            // Kita tidak perlu timestamps untuk tabel master ini
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_pelanggaran');
    }
};