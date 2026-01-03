<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migration ini menambahkan semua role default ke database.
     * Ini memastikan role tersedia bahkan tanpa menjalankan seeder.
     * 
     * IMPORTANT: Migration ini IDEMPOTENT - aman dijalankan berkali-kali.
     * Menggunakan INSERT IGNORE untuk menghindari duplikasi.
     */
    public function up(): void
    {
        // Definisi semua role default
        $roles = [
            ['nama_role' => 'Developer'],
            ['nama_role' => 'Operator Sekolah'],
            ['nama_role' => 'Waka Kesiswaan'],
            ['nama_role' => 'Waka Sarana'],
            ['nama_role' => 'Kepala Sekolah'],
            ['nama_role' => 'Kaprodi'],
            ['nama_role' => 'Wali Kelas'],
            ['nama_role' => 'Guru'],
            ['nama_role' => 'Wali Murid'],
        ];

        // Insert roles dengan INSERT IGNORE untuk menghindari duplikasi
        // Jika role sudah ada (dari seeder atau migration lain), skip
        // NOTE: Tabel roles tidak memiliki timestamps (created_at/updated_at)
        foreach ($roles as $role) {
            DB::statement(
                "INSERT IGNORE INTO roles (nama_role) VALUES (?)",
                [$role['nama_role']]
            );
        }
    }

    /**
     * Reverse the migrations.
     * 
     * NOTE: Down method tidak menghapus role karena:
     * 1. Role mungkin sudah digunakan oleh user
     * 2. Menghapus role akan menyebabkan foreign key constraint error
     * 3. Lebih aman membiarkan role tetap ada
     */
    public function down(): void
    {
        // Tidak menghapus role untuk keamanan data
        // Jika benar-benar ingin menghapus, gunakan:
        // DB::table('roles')->whereIn('nama_role', [...])->delete();
    }
};
