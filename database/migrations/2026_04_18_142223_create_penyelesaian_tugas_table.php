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
        Schema::create('penyelesaian_tugas', function (Blueprint $table) {
            $table->id('penyelesaian_id');
            $table->foreignId('penugasan_id')->constrained('penugasan')->cascadeOnDelete();
            $table->string('foto_bukti');
            $table->date('tanggal_selesai');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyelesaian_tugas');
    }
};
