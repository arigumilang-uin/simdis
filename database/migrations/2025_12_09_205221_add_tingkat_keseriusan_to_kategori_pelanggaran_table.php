<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * STEP 1: Add new columns
     * STEP 2: Seed critical reference data
     */
    public function up(): void
    {
        // STEP 1: Add columns
        Schema::table('kategori_pelanggaran', function (Blueprint $table) {
            $table->string('tingkat_keseriusan', 20)->nullable()->after('nama_kategori');
            $table->timestamps(); // Add created_at and updated_at
        });
        
        // STEP 2: Seed critical data (now that columns exist!)
        $now = now();
        
        DB::table('kategori_pelanggaran')->insert([
            [
                'nama_kategori' => 'Pelanggaran Ringan',
                'tingkat_keseriusan' => 'ringan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_kategori' => 'Pelanggaran Sedang',
                'tingkat_keseriusan' => 'sedang',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_kategori' => 'Pelanggaran Berat',
                'tingkat_keseriusan' => 'berat',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete seeded data first
        DB::table('kategori_pelanggaran')->whereIn('tingkat_keseriusan', ['ringan', 'sedang', 'berat'])->delete();
        
        // Then drop columns
        Schema::table('kategori_pelanggaran', function (Blueprint $table) {
            $table->dropColumn('tingkat_keseriusan');
            $table->dropTimestamps();
        });
    }
};
