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
        if (Schema::hasTable('surat_panggilan') && !Schema::hasColumn('surat_panggilan', 'pembina_roles')) {
            Schema::table('surat_panggilan', function (Blueprint $table) {
                $table->json('pembina_roles')->nullable()->after('pembina_data')->comment('Role pembina yang terlibat (json array)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('surat_panggilan') && Schema::hasColumn('surat_panggilan', 'pembina_roles')) {
            Schema::table('surat_panggilan', function (Blueprint $table) {
                $table->dropColumn('pembina_roles');
            });
        }
    }
};
