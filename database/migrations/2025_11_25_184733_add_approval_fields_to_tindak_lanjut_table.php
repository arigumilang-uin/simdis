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
        Schema::table('tindak_lanjut', function (Blueprint $table) {
            // Approval fields for Kepala Sekolah
            $table->unsignedBigInteger('disetujui_oleh')->nullable()->after('sanksi_deskripsi');
            $table->timestamp('tanggal_disetujui')->nullable()->after('disetujui_oleh');
            $table->text('catatan_kepala_sekolah')->nullable()->after('tanggal_disetujui');
            
            // Foreign key to users table for approval tracking
            $table->foreign('disetujui_oleh')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tindak_lanjut', function (Blueprint $table) {
            $table->dropForeign(['disetujui_oleh']);
            $table->dropColumn(['disetujui_oleh', 'tanggal_disetujui', 'catatan_kepala_sekolah']);
        });
    }
};
