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
     * Migration yang IDEMPOTENT - aman dijalankan berkali-kali.
     * Cek existence index sebelum create untuk avoid duplicate key error.
     */
    public function up(): void
    {
        // Definisi indexes yang akan ditambahkan
        // NOTE: Kolom 'status' tidak ada di tabel siswa, jadi tidak dibuat indexnya
        $indexes = [
            ['column' => 'kelas_id', 'name' => 'idx_siswa_kelas_id'],
            ['column' => 'wali_murid_user_id', 'name' => 'idx_siswa_wali_murid_user_id'],
            ['column' => 'nisn', 'name' => 'idx_siswa_nisn'],
            ['column' => 'nama_siswa', 'name' => 'idx_siswa_nama_siswa'],
        ];

        $compositeIndexes = [
            // Composite index removed karena kolom 'status' tidak ada
        ];

        // Add individual indexes (skip jika sudah ada)
        foreach ($indexes as $index) {
            if (!$this->indexExists('siswa', $index['name'])) {
                Schema::table('siswa', function (Blueprint $table) use ($index) {
                    $table->index($index['column'], $index['name']);
                });
            }
        }

        // Add composite indexes (skip jika sudah ada)
        foreach ($compositeIndexes as $index) {
            if (!$this->indexExists('siswa', $index['name'])) {
                Schema::table('siswa', function (Blueprint $table) use ($index) {
                    $table->index($index['columns'], $index['name']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     * 
     * Drop indexes dengan safe check (skip jika tidak ada).
     */
    public function down(): void
    {
        $indexes = [
            // 'idx_siswa_kelas_status', // Removed - kolom status tidak ada
            // 'idx_siswa_status', // Removed - kolom status tidak ada
            'idx_siswa_nama_siswa',
            'idx_siswa_nisn',
            'idx_siswa_wali_murid_user_id',
            'idx_siswa_kelas_id',
        ];

        foreach ($indexes as $indexName) {
            if ($this->indexExists('siswa', $indexName)) {
                Schema::table('siswa', function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        }
    }

    /**
     * Check if an index exists on a table.
     *
     * @param string $tableName
     * @param string $indexName
     * @return bool
     */
    private function indexExists(string $tableName, string $indexName): bool
    {
        $databaseName = DB::connection()->getDatabaseName();

        $result = DB::select(
            "SELECT COUNT(*) as count 
             FROM information_schema.STATISTICS 
             WHERE table_schema = ? 
             AND table_name = ? 
             AND index_name = ?",
            [$databaseName, $tableName, $indexName]
        );

        return $result[0]->count > 0;
    }
};
