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
        Schema::table('siswa', function (Blueprint $table) {
            $table->enum('alasan_keluar', [
                'Alumni', 
                'Dikeluarkan', 
                'Pindah Sekolah', 
                'Lainnya'
            ])->nullable()->after('deleted_at')
                ->comment('Alasan siswa keluar dari sistem');
            
            $table->text('keterangan_keluar')->nullable()->after('alasan_keluar')
                ->comment('Keterangan detail alasan keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn(['alasan_keluar', 'keterangan_keluar']);
        });
    }
};
