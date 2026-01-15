<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel pivot untuk relasi many-to-many antara Guru dan Mata Pelajaran.
     * Satu mata pelajaran bisa diajarkan oleh beberapa guru.
     * Satu guru bisa mengajar beberapa mata pelajaran.
     */
    public function up(): void
    {
        Schema::create('guru_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false)->comment('Apakah guru utama untuk mapel ini');
            $table->timestamps();

            // Unique constraint: satu guru hanya bisa terdaftar 1x per mapel
            $table->unique(['user_id', 'mata_pelajaran_id'], 'guru_mapel_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guru_mata_pelajaran');
    }
};
