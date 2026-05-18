<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ulasan', function (Blueprint $table) {
            if (!Schema::hasColumn('ulasan', 'penugasan_id')) {
                $table->foreignId('penugasan_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('penugasans')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ulasan', function (Blueprint $table) {
            if (Schema::hasColumn('ulasan', 'penugasan_id')) {
                $table->dropConstrainedForeignId('penugasan_id');
            }
        });
    }
};