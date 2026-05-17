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
            $table->string('nama');
            $table->string('email')->nullable();
            $table->text('pesan');
            $table->string('status')->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('editable_until');
            $table->string('session_token', 100)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimoni_publik');
    }
};
