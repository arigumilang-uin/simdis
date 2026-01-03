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
        // Add tingkat first
        Schema::table('kelas', function (Blueprint $table) {
            $table->string('tingkat', 10)->after('id')->default('X');
        });

        // Then alter wali_kelas_user_id to be nullable and re-add FK with set null
        Schema::table('kelas', function (Blueprint $table) {
            try {
                $table->dropForeign(['wali_kelas_user_id']);
            } catch (\Exception $e) {
                // ignore if drop fails
            }

            $table->unsignedBigInteger('wali_kelas_user_id')->nullable()->change();

            $table->foreign('wali_kelas_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            // Drop new foreign key then change column back to non-nullable
            try {
                $table->dropForeign(['wali_kelas_user_id']);
            } catch (\Exception $e) {
                // ignore
            }

            $table->unsignedBigInteger('wali_kelas_user_id')->nullable(false)->change();

            // Drop tingkat column
            $table->dropColumn('tingkat');
        });
    }
};
