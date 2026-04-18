<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayahs')->nullOnDelete();
            $table->foreignId('kategori_laporan_id')->nullable()->constrained('kategori_laporans')->nullOnDelete();
            $table->string('judul')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('alamat')->nullable();
            $table->string('foto')->nullable();
            $table->enum('status', ['pending', 'diterima', 'ditolak', 'dikerjakan', 'selesai'])->default('pending');
            $table->timestamp('tanggal_lapor')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
