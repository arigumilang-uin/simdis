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
            $table->json('pembina_data')->nullable()->after('tipe_surat')->comment('Data pembina yang terlibat (nama, NIP, jabatan)');
            $table->date('tanggal_pertemuan')->nullable()->after('tanggal_surat')->comment('Tanggal jadwal pertemuan dengan orang tua');
            $table->time('waktu_pertemuan')->nullable()->after('tanggal_pertemuan')->comment('Waktu jadwal pertemuan');
            $table->text('keperluan')->nullable()->after('waktu_pertemuan')->comment('Keperluan/deskripsi pelanggaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_panggilan', function (Blueprint $table) {
            $table->dropColumn(['pembina_data', 'tanggal_pertemuan', 'waktu_pertemuan', 'keperluan']);
        });
    }
};
