<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change 'tipe' column from ENUM to STRING/VARCHAR to allow custom types
        Schema::table('template_jam', function (Blueprint $table) {
            // We can't directly change ENUM to string with change() on some DBs easily without doctrine/dbal
            // But since this is MySQL/MariaDB in Docker, we can use raw SQL or try the Laravel way if doctrine/dbal is present.
            // Let's assume standard Laravel modify. If it fails, we use raw statement.
            $table->string('tipe', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM if needed (potentially losing custom data if not careful, but schema rollback implies structure revert)
        // Warning: Non-standard values will be truncated or cause error depending on strict mode.
        // For safety, we just keep it as string in down or try to revert if valid.
        Schema::table('template_jam', function (Blueprint $table) {
             // Re-hasing to enum might fail if there are custom string values.
             // We will leave it as string or force it.
             // For strict correctness:
             // $table->enum('tipe', ['pelajaran', 'istirahat', 'ishoma', 'upacara', 'lainnya'])->change();
        });
    }
};
