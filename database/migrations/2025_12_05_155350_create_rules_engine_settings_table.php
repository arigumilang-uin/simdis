<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create table untuk menyimpan konfigurasi threshold Rules Engine.
     * Operator dapat mengubah nilai threshold tanpa edit code.
     */
    public function up(): void
    {
        Schema::create('rules_engine_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('Setting key (e.g., surat_2_min_poin)');
            $table->string('value')->comment('Threshold value (stored as string for flexibility)');
            $table->string('label', 200)->comment('Display label for UI');
            $table->text('description')->nullable()->comment('Human-readable description');
            $table->string('category', 50)->comment('Category: threshold_poin, threshold_akumulasi, threshold_frekuensi');
            $table->string('data_type', 20)->default('integer')->comment('Data type: integer, float, boolean, string');
            $table->string('validation_rules')->nullable()->comment('Laravel validation rules');
            $table->unsignedInteger('display_order')->default(0)->comment('Order for display in UI');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('category');
            $table->index('key');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules_engine_settings');
    }
};
