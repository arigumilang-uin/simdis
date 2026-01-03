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
        // Drop tables dari sistem lama yang sudah tidak digunakan
        Schema::dropIfExists('rules_engine_settings_history');
        Schema::dropIfExists('rules_engine_settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu recreate karena sistem lama sudah deprecated
        // Jika perlu rollback, restore dari backup database
    }
};
