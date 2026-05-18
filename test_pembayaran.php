<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'masyarakat')->first();
$kategori = App\Models\KategoriLaporan::first();

if (!$user || !$kategori) {
    echo "No user or kategori found.\n";
    exit;
}

try {
    $laporan = App\Models\Laporan::create([
        'user_id' => $user->id,
        'wilayah_id' => 1,
        'kategori_laporan_id' => $kategori->id,
        'judul' => 'Test Laporan ' . now(),
        'deskripsi' => 'Test deskripsi Test deskripsi',
        'alamat' => 'Test alamat',
        'status' => 'pending',
        'tanggal_lapor' => now()
    ]);

    $pembayaran = \App\Models\Pembayaran::create([
        'laporan_id' => $laporan->id,
        'user_id' => $user->id,
        'harga' => $kategori->tarif,
        'metode_pembayaran' => $kategori->tarif == 0 ? 'Sistem (Gratis)' : null,
        'status_pembayaran' => $kategori->tarif == 0 ? 'Lunas' : 'Menunggu',
    ]);

    echo "Laporan ID: " . $laporan->id . "\n";
    echo "Pembayaran ID: " . $pembayaran->id . "\n";
    echo "Status Pembayaran: " . $pembayaran->status_pembayaran . "\n";
    echo "Harga: " . $pembayaran->harga . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
