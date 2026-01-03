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
        // Gunakan 'jurusan' (singular) sesuai SQL script kita
        Schema::create('jurusan', function (Blueprint $table) {
            $table->id(); // (BIGINT UNSIGNED, PK)
            
            // Kolom Foreign Key untuk Kaprodi
            $table->unsignedBigInteger('kaprodi_user_id');
            
            // Kolom Nama Jurusan (sesuai SQL script: VARCHAR 255)
            $table->string('nama_jurusan'); // Defaultnya 255
            
            // Definisikan relasi foreign key
            $table->foreign('kaprodi_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict'); // (RESTRICT agar Kaprodi tidak bisa dihapus jika masih terikat)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurusan');
    }
};