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
     * Update ENUM tipe_surat untuk include 'Surat 4'.
     * Karena MySQL tidak support ALTER ENUM langsung, kita harus:
     * 1. Ubah kolom ke VARCHAR sementara
     * 2. Ubah kembali ke ENUM dengan values baru
     */
    public function up(): void
    {
        // Step 1: Change to VARCHAR temporarily
        DB::statement("ALTER TABLE surat_panggilan MODIFY COLUMN tipe_surat VARCHAR(10)");
        
        // Step 2: Change back to ENUM with new values
        DB::statement("ALTER TABLE surat_panggilan MODIFY COLUMN tipe_surat ENUM('Surat 1', 'Surat 2', 'Surat 3', 'Surat 4') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: Remove 'Surat 4' from ENUM
        DB::statement("ALTER TABLE surat_panggilan MODIFY COLUMN tipe_surat VARCHAR(10)");
        DB::statement("ALTER TABLE surat_panggilan MODIFY COLUMN tipe_surat ENUM('Surat 1', 'Surat 2', 'Surat 3') NOT NULL");
    }
};
