<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jurusan', function (Blueprint $table) {
            $table->string('kode_jurusan', 20)->nullable()->unique()->after('nama_jurusan');
        });
    }

    public function down()
    {
        Schema::table('jurusan', function (Blueprint $table) {
            $table->dropColumn('kode_jurusan');
        });
    }
};
