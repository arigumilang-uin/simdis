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
        // Ini adalah "blueprint" untuk tabel 'users' yang SUDAH ADA
        Schema::table('users', function (Blueprint $table) {
            // Kita tambahkan foreign key yang tadi kita hapus
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ini adalah cara untuk "membatalkan" foreign key
            $table->dropForeign(['role_id']);
        });
    }
};