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
        Schema::create('pelanggaran_frequency_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_pelanggaran_id')
                  ->constrained('jenis_pelanggaran')
                  ->onDelete('cascade');
            $table->integer('frequency_min');
            $table->integer('frequency_max')->nullable();
            $table->integer('poin');
            $table->text('sanksi_description');
            $table->boolean('trigger_surat')->default(false);
            $table->json('pembina_roles');
            $table->integer('display_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('jenis_pelanggaran_id', 'idx_jenis_pelanggaran');
            $table->index('display_order', 'idx_display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_frequency_rules');
    }
};
