<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create table untuk audit trail perubahan settings Rules Engine.
     * Setiap perubahan threshold akan dicatat untuk transparansi.
     */
    public function up(): void
    {
        Schema::create('rules_engine_settings_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('setting_id')->comment('ID of setting that was changed');
            $table->string('old_value')->comment('Previous value');
            $table->string('new_value')->comment('New value');
            $table->unsignedBigInteger('changed_by')->nullable()->comment('User ID who made the change');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('setting_id')->references('id')->on('rules_engine_settings')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index('setting_id');
            $table->index('changed_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules_engine_settings_history');
    }
};
