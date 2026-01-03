<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembinaan_internal_rules', function (Blueprint $table) {
            $table->id();
            $table->integer('poin_min')->unsigned();
            $table->integer('poin_max')->unsigned()->nullable();
            $table->json('pembina_roles');
            $table->string('keterangan', 500);
            $table->integer('display_order')->unsigned()->default(1);
            $table->timestamps();

            // Indexes
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembinaan_internal_rules');
    }
};
