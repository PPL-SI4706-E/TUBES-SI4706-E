<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahkan nilai 'menunggu_konfirmasi' ke enum status pada tabel laporan.
     * Nilai ini dipakai saat petugas selesai upload bukti dan menunggu konfirmasi warga.
     *
     * Catatan: migration ini hanya berlaku untuk MySQL/MariaDB.
     * Untuk SQLite (testing), enum sudah didefinisikan di migration asli create_laporans_table.
     */
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE laporan MODIFY COLUMN status ENUM('pending','diterima','ditolak','dikerjakan','menunggu_konfirmasi','selesai') NOT NULL DEFAULT 'pending'");
        } catch (\Exception $e) {
            // SQLite tidak mendukung ALTER TABLE MODIFY COLUMN.
            // Tidak masalah karena enum sudah didefinisikan di migration asli.
        }
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE laporan MODIFY COLUMN status ENUM('pending','diterima','ditolak','dikerjakan','selesai') NOT NULL DEFAULT 'pending'");
        } catch (\Exception $e) {
            // Skip untuk SQLite
        }
    }
};
