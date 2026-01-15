<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table("roles")->insert([
            "nama_role" => "Waka Kurikulum",
        ]);
    }

    public function down(): void
    {
        DB::table("roles")->where("nama_role", "Waka Kurikulum")->delete();
    }
};
