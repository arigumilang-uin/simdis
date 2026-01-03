<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('jurusan')) {
            Schema::table('jurusan', function (Blueprint $table) {
                // Membuat kaprodi_user_id menjadi nullable
                if (Schema::hasColumn('jurusan', 'kaprodi_user_id')) {
                    $table->unsignedBigInteger('kaprodi_user_id')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('jurusan')) {
            Schema::table('jurusan', function (Blueprint $table) {
                if (Schema::hasColumn('jurusan', 'kaprodi_user_id')) {
                    $table->unsignedBigInteger('kaprodi_user_id')->nullable(false)->change();
                }
            });
        }
    }
};
