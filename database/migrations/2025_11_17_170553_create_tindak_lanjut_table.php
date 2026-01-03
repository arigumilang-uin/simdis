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
        Schema::create('tindak_lanjut', function (Blueprint $table) {
            $table->id(); // (BIGINT UNSIGNED, PK)

            // Foreign Key ke Siswa yang punya kasus
            $table->unsignedBigInteger('siswa_id');

            // Deskripsi pemicu (dari Class Diagram)
            $table->string('pemicu');
            $table->string('sanksi_deskripsi');
            $table->text('denda_deskripsi')->nullable();

            // Kolom Status (ENUM dari Class Diagram)
            $table->enum('status', [
                'Baru',
                'Menunggu Persetujuan',
                'Disetujui',
                'Ditolak',
                'Ditangani',
                'Selesai'
            ])->default('Baru');

            // Tanggal kasus ditangani (bisa NULL)
            $table->date('tanggal_tindak_lanjut')->nullable();

            // Foreign Key ke Penyetuju (Kepsek/Kaprodi) (bisa NULL)
            $table->unsignedBigInteger('penyetuju_user_id')->nullable();
            
            $table->timestamps(); // (created_at dan updated_at)

            // --- Definisikan relasi foreign key ---

            // Relasi ke tabel 'siswa'
            $table->foreign('siswa_id')
                  ->references('id')
                  ->on('siswa')
                  ->onDelete('cascade'); // (CASCADE: Jika siswa dihapus, kasusnya ikut terhapus)

            // Relasi ke tabel 'users' (untuk penyetuju)
            $table->foreign('penyetuju_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null'); // (SET NULL: Jika user penyetuju dihapus, kasus tetap ada)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut');
    }
};