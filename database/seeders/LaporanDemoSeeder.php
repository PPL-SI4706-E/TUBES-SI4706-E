<?php

namespace Database\Seeders;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class LaporanDemoSeeder extends Seeder
{
    public function run(): void
    {
        $andi = User::where('email', 'andi@gmail.com')->first();
        $dewi = User::where('email', 'dewi@gmail.com')->first();
        $nur = User::where('email', 'nur@gmail.com')->first();

        $sukamaju = Wilayah::where('kode_wilayah', 'DSM-001')->first();
        $mekarsari = Wilayah::where('kode_wilayah', 'DMS-002')->first();
        $cisadak = Wilayah::where('kode_wilayah', 'KCS-003')->first();
        $cipanas = Wilayah::where('kode_wilayah', 'DCP-004')->first();

        $pipaBocor = KategoriLaporan::where('nama_kategori', 'Pipa Bocor')->first();
        $airKeruh = KategoriLaporan::where('nama_kategori', 'Air Keruh / Berbau')->first();
        $pipaTersumbat = KategoriLaporan::where('nama_kategori', 'Pipa Tersumbat')->first();
        $meteran = KategoriLaporan::where('nama_kategori', 'Kerusakan Meteran Air')->first();

        $laporans = [
            [
                'judul' => 'Demo - Pipa bocor di Sukamaju',
                'user_id' => $andi?->id,
                'wilayah_id' => $sukamaju?->id,
                'kategori_laporan_id' => $pipaBocor?->id,
                'alamat' => 'Jalan Melati No. 7, Sukamaju',
                'deskripsi' => 'Pipa distribusi bocor di depan rumah warga.',
                'status' => 'selesai',
                'tanggal_lapor' => now()->subMonths(2)->setTime(9, 15),
                'pembayaran' => 'Lunas',
            ],
            [
                'judul' => 'Demo - Air keruh di Cisadak',
                'user_id' => $dewi?->id,
                'wilayah_id' => $cisadak?->id,
                'kategori_laporan_id' => $airKeruh?->id,
                'alamat' => 'Gang Anggrek RT 02, Cisadak',
                'deskripsi' => 'Air berwarna keruh sejak pagi.',
                'status' => 'diterima',
                'tanggal_lapor' => now()->subMonth()->setTime(13, 30),
                'pembayaran' => 'Terverifikasi',
            ],
            [
                'judul' => 'Demo - Pipa tersumbat Mekarsari',
                'user_id' => $nur?->id,
                'wilayah_id' => $mekarsari?->id,
                'kategori_laporan_id' => $pipaTersumbat?->id,
                'alamat' => 'Perum Mekarsari Blok B4',
                'deskripsi' => 'Aliran air kecil dan sering mati.',
                'status' => 'pending',
                'tanggal_lapor' => now()->subDays(10)->setTime(8, 45),
                'pembayaran' => 'Menunggu',
            ],
            [
                'judul' => 'Demo - Meteran rusak Cipanas',
                'user_id' => $andi?->id,
                'wilayah_id' => $cipanas?->id,
                'kategori_laporan_id' => $meteran?->id,
                'alamat' => 'Jalan Raya Cipanas No. 21',
                'deskripsi' => 'Meteran air tidak bergerak meski air mengalir.',
                'status' => 'dikerjakan',
                'tanggal_lapor' => now()->subDays(3)->setTime(15, 10),
                'pembayaran' => 'Ditolak',
            ],
        ];

        foreach ($laporans as $item) {
            $statusPembayaran = $item['pembayaran'];
            unset($item['pembayaran']);

            $laporan = Laporan::updateOrCreate(
                ['judul' => $item['judul']],
                array_merge($item, [
                    'foto' => null,
                    'catatan_admin' => null,
                ])
            );

            Pembayaran::updateOrCreate(
                ['laporan_id' => $laporan->id],
                [
                    'user_id' => $laporan->user_id,
                    'harga' => $laporan->kategoriLaporan?->tarif ?? 0,
                    'metode_pembayaran' => $statusPembayaran === 'Lunas' ? 'Transfer Bank' : null,
                    'status_pembayaran' => $statusPembayaran,
                ]
            );
        }
    }
}
