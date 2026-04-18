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
        Schema::create('map_lokasi', function (Blueprint $table) {
            $table->id('map_id');
            $table->foreignId('laporan_id')->constrained('laporan')->cascadeOnDelete();
            $table->decimal('latitude', 15, 8);
            $table->decimal('longitude', 15, 8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_lokasi');
    }
};
