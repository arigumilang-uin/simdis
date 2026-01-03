<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel pembinaan_status
 * 
 * Tabel ini merekam status pembinaan internal siswa.
 * Setiap kali siswa mencapai threshold poin tertentu DAN belum ada
 * record aktif untuk threshold tersebut, sistem akan membuat record baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembinaan_status', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke siswa
            $table->foreignId('siswa_id')
                  ->constrained('siswa')
                  ->onDelete('cascade');
            
            // Relasi ke rule yang trigger pembinaan ini
            $table->foreignId('pembinaan_rule_id')
                  ->constrained('pembinaan_internal_rules')
                  ->onDelete('cascade');
            
            // Snapshot data saat pembinaan di-trigger
            $table->integer('total_poin_saat_trigger');
            $table->string('range_text');  // e.g., "55-109 poin"
            $table->string('keterangan_pembinaan');  // e.g., "Pembinaan oleh Wali Kelas"
            $table->json('pembina_roles');  // Siapa yang harus membina
            
            // Status pembinaan
            $table->enum('status', ['Perlu Pembinaan', 'Sedang Dibina', 'Selesai'])
                  ->default('Perlu Pembinaan');
            
            // Tracking siapa yang membina
            $table->foreignId('dibina_oleh_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('dibina_at')->nullable();
            
            // Tracking penyelesaian
            $table->foreignId('diselesaikan_oleh_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('selesai_at')->nullable();
            
            // Catatan/hasil pembinaan
            $table->text('catatan_pembinaan')->nullable();
            $table->text('hasil_pembinaan')->nullable();
            
            $table->timestamps();
            
            // Index untuk query yang sering digunakan
            $table->index(['siswa_id', 'status']);
            $table->index(['pembinaan_rule_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembinaan_status');
    }
};
