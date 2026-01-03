<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add pembina_roles column to tindak_lanjut table
 * 
 * PURPOSE: Allow filtering tindak_lanjut by pembina role
 * Used by: scopeForPembina() in TindakLanjut model
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tindak_lanjut', function (Blueprint $table) {
            $table->json('pembina_roles')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tindak_lanjut', function (Blueprint $table) {
            $table->dropColumn('pembina_roles');
        });
    }
};
