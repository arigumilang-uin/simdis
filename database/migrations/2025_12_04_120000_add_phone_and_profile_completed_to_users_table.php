<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom kontak dasar ke tabel users.
     *
     * - phone: nomor HP/WA opsional untuk sebagian besar role (kecuali Wali Murid yang
     *   kontak utamanya berasal dari data siswa).
     * - profile_completed_at: penanda bahwa user sudah melengkapi profil minimal
     *   (email dan, untuk non-Wali Murid, kontak) pada login pertama.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'profile_completed_at')) {
                $table->timestamp('profile_completed_at')->nullable()->after('remember_token');
            }
        });
    }

    /**
     * Rollback perubahan.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('users', 'profile_completed_at')) {
                $table->dropColumn('profile_completed_at');
            }
        });
    }
};





