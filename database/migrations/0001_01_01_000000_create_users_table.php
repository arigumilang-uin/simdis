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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // 1. Foreign Key ke 'roles' (Wajib)
            $table->unsignedBigInteger('role_id');
            
            // 2. Kolom 'nama'
            $table->string('nama');

            // 3. Kolom 'username' (Untuk login)
            $table->string('username')->unique();

            // 4. Kolom 'email' (Untuk Lupa Password) - KITA KEMBALIKAN
            $table->string('email')->unique();
            
            // 5. 'email_verified_at' - KITA KEMBALIKAN
            $table->timestamp('email_verified_at')->nullable();

            // 6. Kolom 'password'
            $table->string('password');
            
            // 7. 'rememberToken' - KITA KEMBALIKAN
            $table->rememberToken();
            
            // 8. Timestamps
            $table->timestamps();

            // 9. Definisikan relasi foreign key
           // $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};