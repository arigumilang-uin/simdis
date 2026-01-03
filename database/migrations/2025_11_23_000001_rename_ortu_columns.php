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
        // Rename columns in 'siswa' table: orang_tua_user_id -> wali_murid_user_id
        // and nomor_hp_ortu -> nomor_hp_wali_murid
        Schema::table('siswa', function (Blueprint $table) {
            // Drop FK if exists, then rename column
            if (Schema::hasColumn('siswa', 'orang_tua_user_id')) {
                // Attempt to drop foreign key constraint for the column
                try {
                    $table->dropForeign(['orang_tua_user_id']);
                } catch (\Exception $e) {
                    // ignore if constraint name differs or doesn't exist
                }

                // Rename column
                $table->renameColumn('orang_tua_user_id', 'wali_murid_user_id');

                // Recreate foreign key
                $table->foreign('wali_murid_user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            }

            if (Schema::hasColumn('siswa', 'nomor_hp_ortu')) {
                $table->renameColumn('nomor_hp_ortu', 'nomor_hp_wali_murid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (Schema::hasColumn('siswa', 'wali_murid_user_id')) {
                try {
                    $table->dropForeign(['wali_murid_user_id']);
                } catch (\Exception $e) {
                }

                $table->renameColumn('wali_murid_user_id', 'orang_tua_user_id');

                $table->foreign('orang_tua_user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            }

            if (Schema::hasColumn('siswa', 'nomor_hp_wali_murid')) {
                $table->renameColumn('nomor_hp_wali_murid', 'nomor_hp_ortu');
            }
        });
    }
};
