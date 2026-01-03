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
        Schema::create('surat_panggilan', function (Blueprint $table) {
            $table->id(); // (BIGINT UNSIGNED, PK)

            // Foreign Key ke Kasus yang terkait
            $table->unsignedBigInteger('tindak_lanjut_id');

            // Kolom Nomor Surat (harus unik)
            $table->string('nomor_surat', 100)->unique();
            
            // Kolom Tipe Surat (ENUM dari Class Diagram)
            $table->enum('tipe_surat', ['Surat 1', 'Surat 2', 'Surat 3']);
            
            // Tanggal surat dibuat
            $table->date('tanggal_surat');
            
            // Path ke file PDF (bisa NULL)
            $table->string('file_path_pdf')->nullable();

            $table->timestamps(); // (created_at dan updated_at)

            // Definisikan relasi foreign key
            $table->foreign('tindak_lanjut_id')
                  ->references('id')
                  ->on('tindak_lanjut')
                  ->onDelete('cascade'); // (CASCADE: Jika kasus dihapus, log suratnya ikut terhapus)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_panggilan');
    }
};