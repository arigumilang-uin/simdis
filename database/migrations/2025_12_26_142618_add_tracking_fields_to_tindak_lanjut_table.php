<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk menambah field tracking status kasus:
 * - ditangani_oleh_user_id: siapa yang menekan tombol "Mulai Tangani"
 * - ditangani_at: kapan status berubah ke "Ditangani"
 * - diselesaikan_oleh_user_id: siapa yang menekan tombol "Selesaikan"
 * - diselesaikan_at: kapan status berubah ke "Selesai"
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tindak_lanjut', function (Blueprint $table) {
            // Field untuk tracking siapa & kapan menangani
            $table->foreignId('ditangani_oleh_user_id')
                  ->nullable()
                  ->after('penyetuju_user_id')
                  ->constrained('users')
                  ->onDelete('set null');
            
            $table->timestamp('ditangani_at')
                  ->nullable()
                  ->after('ditangani_oleh_user_id');
            
            // Field untuk tracking siapa & kapan menyelesaikan
            $table->foreignId('diselesaikan_oleh_user_id')
                  ->nullable()
                  ->after('ditangani_at')
                  ->constrained('users')
                  ->onDelete('set null');
            
            $table->timestamp('diselesaikan_at')
                  ->nullable()
                  ->after('diselesaikan_oleh_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tindak_lanjut', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['ditangani_oleh_user_id']);
            $table->dropForeign(['diselesaikan_oleh_user_id']);
            
            // Then drop columns
            $table->dropColumn([
                'ditangani_oleh_user_id',
                'ditangani_at',
                'diselesaikan_oleh_user_id',
                'diselesaikan_at',
            ]);
        });
    }
};
