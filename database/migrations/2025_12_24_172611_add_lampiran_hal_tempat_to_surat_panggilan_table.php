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
        Schema::table('surat_panggilan', function (Blueprint $table) {
            $table->string('lampiran')->nullable()->after('nomor_surat');
            $table->string('hal')->default('Panggilan Orang Tua / Wali Murid')->after('lampiran');
            $table->string('tempat_pertemuan')->default('Ruang BK SMK Negeri 1 Lubuk Dalam')->after('waktu_pertemuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_panggilan', function (Blueprint $table) {
            $table->dropColumn(['lampiran', 'hal', 'tempat_pertemuan']);
        });
    }
};
