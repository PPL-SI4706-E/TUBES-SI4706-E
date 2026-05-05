<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimoni_publik', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('email')->nullable();
            $table->text('pesan');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimoni_publik');
    }
};
