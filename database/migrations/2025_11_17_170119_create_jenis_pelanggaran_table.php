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
        // Gunakan 'jenis_pelanggaran' (singular) sesuai SQL script kita
        Schema::create('jenis_pelanggaran', function (Blueprint $table) {
            $table->id(); // (BIGINT UNSIGNED, PK)
            
            // Foreign Key ke Kategori
            $table->unsignedBigInteger('kategori_id');

            // Kolom Nama Pelanggaran
            $table->string('nama_pelanggaran'); // Default (255)

            // Kolom Poin (sesuai SQL script: INT, default 0)
            $table->integer('poin')->default(0);

            // Definisikan relasi foreign key
            $table->foreign('kategori_id')
                  ->references('id')
                  ->on('kategori_pelanggaran') // (Tabel 'kategori_pelanggaran' harus ada dulu)
                  ->onDelete('restrict');
                  
            // Kita tidak perlu timestamps untuk tabel master ini
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_pelanggaran');
    }
};