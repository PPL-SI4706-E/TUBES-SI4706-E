<?php

namespace Database\Seeders;

use App\Models\KategoriLaporan;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama_kategori' => 'Pipa Bocor',            'deskripsi' => 'Laporan kebocoran pipa distribusi air bersih di area pemukiman.',           'tarif' => 75000,  'icon' => 'droplet'],
            ['nama_kategori' => 'Air Keruh / Berbau',    'deskripsi' => 'Kualitas air yang dikonsumsi tidak layak, keruh, berbau, atau berasa.',       'tarif' => 50000,  'icon' => 'beaker'],
            ['nama_kategori' => 'Permintaan Tangki Air', 'deskripsi' => 'Permintaan pengiriman tangki air bersih ke lokasi kekurangan air.',           'tarif' => 200000, 'icon' => 'truck'],
            ['nama_kategori' => 'Kerusakan Meteran Air', 'deskripsi' => 'Kerusakan atau masalah pada alat meteran air pelanggan.',                     'tarif' => 100000, 'icon' => 'chart-bar'],
            ['nama_kategori' => 'Pipa Tersumbat',        'deskripsi' => 'Penyumbatan pada saluran pipa yang menyebabkan aliran air terhambat.',        'tarif' => 85000,  'icon' => 'x-circle'],
        ];

        foreach ($data as $row) {
            KategoriLaporan::firstOrCreate(['nama_kategori' => $row['nama_kategori']], $row);
        }
    }
}