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
        Schema::create('roles', function (Blueprint $table) {
            // Sesuai Class Diagram kita:
            // $table->id() adalah 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY'
            $table->id(); 
            
            // Kolom nama_role
            $table->string('nama_role', 100)->unique();
            
            // Kita tidak perlu timestamps (created_at/updated_at) untuk tabel roles
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};