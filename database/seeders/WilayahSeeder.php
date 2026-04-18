<?php

namespace Database\Seeders;

use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama_wilayah' => 'Desa Sukamaju',     'tipe' => 'desa',      'kode_wilayah' => 'DSM-001'],
            ['nama_wilayah' => 'Desa Mekarsari',    'tipe' => 'desa',      'kode_wilayah' => 'DMS-002'],
            ['nama_wilayah' => 'Kelurahan Cisadak', 'tipe' => 'kelurahan', 'kode_wilayah' => 'KCS-003'],
            ['nama_wilayah' => 'Desa Cipanas',      'tipe' => 'desa',      'kode_wilayah' => 'DCP-004'],
            ['nama_wilayah' => 'Kecamatan Cianjur', 'tipe' => 'kecamatan', 'kode_wilayah' => 'KCJ-005'],
            ['nama_wilayah' => 'Kelurahan Merdeka', 'tipe' => 'kelurahan', 'kode_wilayah' => 'KMR-006'],
        ];

        foreach ($data as $row) {
            Wilayah::firstOrCreate(['kode_wilayah' => $row['kode_wilayah']], $row);
        }
    }
}