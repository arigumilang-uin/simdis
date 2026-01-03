<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom untuk filter kategori dan keywords/alias
     * ke tabel jenis_pelanggaran agar lebih fleksibel.
     */
    public function up(): void
    {
        Schema::table('jenis_pelanggaran', function (Blueprint $table) {
            // Filter kategori: atribut, absensi, kerapian, ibadah, berat
            // Nullable karena opsional
            $table->string('filter_category')->nullable()->comment('Kategori filter: atribut, absensi, kerapian, ibadah, berat');

            // Keywords/alias untuk pencarian fuzzy
            // Format: "keyword1|keyword2|keyword3"
            $table->text('keywords')->nullable()->comment('Alias/keywords untuk pencarian, dipisahkan dengan |');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_pelanggaran', function (Blueprint $table) {
            $table->dropColumn(['filter_category', 'keywords']);
        });
    }
};
