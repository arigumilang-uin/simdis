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
        // Gunakan 'siswa' (singular) sesuai SQL script kita
        Schema::create('siswa', function (Blueprint $table) {
            $table->id(); // (BIGINT UNSIGNED, PK)

            // Foreign Key ke Kelas
            $table->unsignedBigInteger('kelas_id');
            
            // Foreign Key ke User Ortu (bisa NULL)
            $table->unsignedBigInteger('orang_tua_user_id')->nullable();

            // Kolom NISN (sesuai SQL script: VARCHAR 20, Unique)
            $table->string('nisn', 20)->unique();
            
            // Kolom Nama Siswa
            $table->string('nama_siswa'); // Default (255)

            // Kolom No HP Ortu (bisa NULL)
            $table->string('nomor_hp_ortu', 20)->nullable();

            $table->timestamps(); // (created_at dan updated_at)

            // --- Definisikan relasi foreign key ---

            // Relasi ke tabel 'kelas'
            $table->foreign('kelas_id')
                  ->references('id')
                  ->on('kelas')
                  ->onDelete('restrict'); // (RESTRICT: Jangan biarkan kelas dihapus jika masih ada siswa)

            // Relasi ke tabel 'users' (untuk ortu)
            $table->foreign('orang_tua_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null'); // (SET NULL: Jika user ortu dihapus, data siswa tetap ada, kolom ini jadi NULL)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};