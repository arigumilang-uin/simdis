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
        Schema::create('riwayat_pelanggaran', function (Blueprint $table) {
            $table->id(); // (BIGINT UNSIGNED, PK)

            // Foreign Key ke Siswa yang melanggar
            $table->unsignedBigInteger('siswa_id');
            
            // Foreign Key ke Jenis Pelanggaran yang dilanggar
            $table->unsignedBigInteger('jenis_pelanggaran_id');
            
            // Foreign Key ke Guru yang mencatat
            $table->unsignedBigInteger('guru_pencatat_user_id');

            // Tanggal kejadian (defaultnya waktu sekarang)
            $table->timestamp('tanggal_kejadian')->useCurrent();

            // Keterangan tambahan (bisa NULL)
            $table->text('keterangan')->nullable();

            // Path ke bukti foto (bisa NULL)
            $table->string('bukti_foto_path')->nullable();
            
            // Kita tidak perlu $table->timestamps() karena 'tanggal_kejadian' sudah mencakupnya
            // Jika Anda tetap ingin 'created_at' dan 'updated_at', Anda bisa mengganti
            // $table->timestamp('tanggal_kejadian') dengan $table->timestamps()
            // dan menambahkan $table->timestamp('tanggal_kejadian')->nullable();
            // Tapi untuk kasus ini, 'tanggal_kejadian' sudah cukup sebagai 'created_at'.

            // --- Definisikan relasi foreign key ---

            // Relasi ke tabel 'siswa'
            $table->foreign('siswa_id')
                  ->references('id')
                  ->on('siswa')
                  ->onDelete('cascade'); // (CASCADE: Jika siswa dihapus, semua riwayatnya ikut terhapus)

            // Relasi ke tabel 'jenis_pelanggaran'
            $table->foreign('jenis_pelanggaran_id')
                  ->references('id')
                  ->on('jenis_pelanggaran')
                  ->onDelete('restrict'); // (RESTRICT: Jenis pelanggaran tidak boleh dihapus jika sudah tercatat)

            // Relasi ke tabel 'users'
            $table->foreign('guru_pencatat_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict'); // (RESTRICT: Guru tidak boleh dihapus jika sudah pernah mencatat)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pelanggaran');
    }
};