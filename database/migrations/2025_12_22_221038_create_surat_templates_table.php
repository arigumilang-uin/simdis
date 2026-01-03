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
        Schema::create('surat_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template');
            $table->enum('jenis_surat', ['panggilan_wali', 'peringatan', 'lainnya'])->default('panggilan_wali');
            $table->text('kop_surat')->nullable()->comment('Header surat (logo, nama sekolah)');
            $table->text('isi_template')->nullable()->comment('Body surat (rich text HTML)');
            $table->text('footer_template')->nullable()->comment('Footer/TTD area');
            $table->boolean('is_active')->default(true)->comment('Template aktif atau tidak');
            $table->boolean('is_default')->default(false)->comment('Template default untuk jenis ini');
            $table->json('placeholder_variables')->nullable()->comment('List available variables');
            $table->text('css_styles')->nullable()->comment('Custom CSS untuk template');
            
            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('jenis_surat');
            $table->index('is_active');
            $table->index('is_default');
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_templates');
    }
};
