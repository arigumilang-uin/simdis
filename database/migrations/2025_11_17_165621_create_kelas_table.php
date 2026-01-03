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
        // Gunakan 'kelas' (singular) sesuai SQL script kita
        Schema::create('kelas', function (Blueprint $table) {
            $table->id(); // (BIGINT UNSIGNED, PK)

            // Foreign Key ke Jurusan
            $table->unsignedBigInteger('jurusan_id');
            
            // Foreign Key ke Wali Kelas
            $table->unsignedBigInteger('wali_kelas_user_id');

            // Kolom Nama Kelas (sesuai SQL script: VARCHAR 100)
            $table->string('nama_kelas', 100);

            // Definisikan relasi foreign key
            $table->foreign('jurusan_id')
                  ->references('id')
                  ->on('jurusan') // (Tabel 'jurusan' harus ada dulu)
                  ->onDelete('restrict');

            $table->foreign('wali_kelas_user_id')
                  ->references('id')
                  ->on('users') // (Tabel 'users' harus ada dulu)
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};