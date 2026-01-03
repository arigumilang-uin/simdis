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
        Schema::create('surat_panggilan_print_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_panggilan_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('printed_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            
            $table->foreign('surat_panggilan_id')
                  ->references('id')
                  ->on('surat_panggilan')
                  ->onDelete('cascade');
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->index(['surat_panggilan_id', 'printed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_panggilan_print_log');
    }
};
