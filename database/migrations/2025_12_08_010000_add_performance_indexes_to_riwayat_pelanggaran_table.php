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
        $indexes = [
            ['column' => 'siswa_id', 'name' => 'idx_riwayat_siswa_id'],
            ['column' => 'jenis_pelanggaran_id', 'name' => 'idx_riwayat_jenis_pelanggaran_id'],
            ['column' => 'guru_pencatat_user_id', 'name' => 'idx_riwayat_guru_pencatat_user_id'],
            ['column' => 'tanggal_kejadian', 'name' => 'idx_riwayat_tanggal_kejadian'],
        ];

        $compositeIndexes = [
            ['columns' => ['siswa_id', 'tanggal_kejadian'], 'name' => 'idx_riwayat_siswa_tanggal'],
        ];

        // Add individual indexes (skip jika sudah ada)
        foreach ($indexes as $index) {
            if (!$this->indexExists('riwayat_pelanggaran', $index['name'])) {
                Schema::table('riwayat_pelanggaran', function (Blueprint $table) use ($index) {
                    $table->index($index['column'], $index['name']);
                });
            }
        }

        // Add composite indexes (skip jika sudah ada)
        foreach ($compositeIndexes as $index) {
            if (!$this->indexExists('riwayat_pelanggaran', $index['name'])) {
                Schema::table('riwayat_pelanggaran', function (Blueprint $table) use ($index) {
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
            'idx_riwayat_siswa_tanggal',
            'idx_riwayat_tanggal_kejadian',
            'idx_riwayat_guru_pencatat_user_id',
            'idx_riwayat_jenis_pelanggaran_id',
            'idx_riwayat_siswa_id',
        ];

        foreach ($indexes as $indexName) {
            if ($this->indexExists('riwayat_pelanggaran', $indexName)) {
                Schema::table('riwayat_pelanggaran', function (Blueprint $table) use ($indexName) {
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
