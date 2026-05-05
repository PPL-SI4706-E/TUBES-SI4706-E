<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE pembayaran MODIFY COLUMN status_pembayaran ENUM('Menunggu', 'Terverifikasi', 'Lunas', 'Ditolak', 'Kadaluarsa') DEFAULT 'Menunggu'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE pembayaran MODIFY COLUMN status_pembayaran ENUM('Menunggu', 'Terverifikasi', 'Lunas', 'Ditolak') DEFAULT 'Menunggu'");
    }
};
