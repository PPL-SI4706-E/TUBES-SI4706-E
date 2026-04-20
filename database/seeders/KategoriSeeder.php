<?php

namespace Database\Seeders;

use App\Models\KategoriLaporan;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama_kategori' => 'Pipa Bocor',            'deskripsi' => 'Laporan kebocoran pipa distribusi air di area rumah',                       'tarif' => 50000,  'icon' => '🔧'],
            ['nama_kategori' => 'Air Keruh / Berbau',    'deskripsi' => 'Laporan kualitas air yang keruh, berbau, atau berubah warna',               'tarif' => 0,      'icon' => '💧'],
            ['nama_kategori' => 'Permintaan Tangki Air', 'deskripsi' => 'Permintaan pasokan air darurat via tangki ke rumah',                          'tarif' => 75000,  'icon' => '🚛'],
            ['nama_kategori' => 'Kerusakan Meteran Air', 'deskripsi' => 'Kerusakan atau masalah pada alat meteran air pelanggan.',                     'tarif' => 100000, 'icon' => '📟'],
            ['nama_kategori' => 'Pipa Tersumbat',        'deskripsi' => 'Laporan pipa yang tersumbat atau aliran air kecil/mati',                       'tarif' => 35000,  'icon' => '🚫'],
            ['nama_kategori' => 'Sambungan Baru',        'deskripsi' => 'Permohonan pemasangan sambungan air baru ke rumah',                       'tarif' => 250000, 'icon' => '🏠'],
        ];

        foreach ($data as $row) {
            KategoriLaporan::updateOrCreate(['nama_kategori' => $row['nama_kategori']], $row);
        }
    }
}