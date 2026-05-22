<?php

namespace Database\Seeders;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Pembayaran;
use App\Models\Penugasan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaporanDemoSeeder extends Seeder
{
    public function run(): void
    {
        $andi = User::where('email', 'andi@gmail.com')->first();
        $dewi = User::where('email', 'dewi@gmail.com')->first();
        $nur = User::where('email', 'nur@gmail.com')->first();
        
        $budi = User::where('email', 'budi@tirtabantu.id')->first();
        $siti = User::where('email', 'siti@tirtabantu.id')->first();

        $sukamaju = Wilayah::where('kode_wilayah', 'DSM-001')->first();
        $mekarsari = Wilayah::where('kode_wilayah', 'DMS-002')->first();
        $cisadak = Wilayah::where('kode_wilayah', 'KCS-003')->first();
        $cipanas = Wilayah::where('kode_wilayah', 'DCP-004')->first();

        $pipaBocor = KategoriLaporan::where('nama_kategori', 'Pipa Bocor')->first();
        $airKeruh = KategoriLaporan::where('nama_kategori', 'Air Keruh / Berbau')->first();
        $pipaTersumbat = KategoriLaporan::where('nama_kategori', 'Pipa Tersumbat')->first();
        $meteran = KategoriLaporan::where('nama_kategori', 'Kerusakan Meteran Air')->first();
        $tangkiAir = KategoriLaporan::where('nama_kategori', 'Permintaan Tangki Air')->first();

        // Base coordinates (e.g., somewhere in a city)
        $baseLat = -6.917464; // Bandung
        $baseLng = 107.619123;

        $laporans = [
            // 1. Gratis, Belum Di-assign (Baru Masuk)
            [
                'judul' => 'Air keruh di bak mandi',
                'user_id' => $andi?->id,
                'wilayah_id' => $sukamaju?->id,
                'kategori_laporan_id' => $airKeruh?->id,
                'alamat' => 'Jalan Melati No. 7, Sukamaju',
                'deskripsi' => 'Air berwarna kuning dan keruh sejak pagi, tidak bisa digunakan mandi.',
                'status' => 'pending',
                'tanggal_lapor' => now()->subHours(2),
                'pembayaran_status' => 'Lunas',
                'metode_pembayaran' => null,
                'penugasan' => null,
                'lat' => $baseLat + 0.01,
                'lng' => $baseLng + 0.01,
            ],
            // 2. Berbayar, Belum Dibayar, Belum Assign
            [
                'judul' => 'Pipa bocor di depan garasi',
                'user_id' => $dewi?->id,
                'wilayah_id' => $cisadak?->id,
                'kategori_laporan_id' => $pipaBocor?->id,
                'alamat' => 'Gang Anggrek RT 02, Cisadak',
                'deskripsi' => 'Ada genangan air dari pipa bawah tanah yang bocor.',
                'status' => 'pending',
                'tanggal_lapor' => now()->subDays(1),
                'pembayaran_status' => 'Menunggu',
                'metode_pembayaran' => null,
                'penugasan' => null,
                'lat' => $baseLat - 0.015,
                'lng' => $baseLng + 0.005,
            ],
            // 3. Berbayar, Dibayar Transfer, Udah Assign tapi Belum Selesai (Sedang Dikerjakan)
            [
                'judul' => 'Meteran air mati total',
                'user_id' => $nur?->id,
                'wilayah_id' => $mekarsari?->id,
                'kategori_laporan_id' => $meteran?->id,
                'alamat' => 'Perum Mekarsari Blok B4',
                'deskripsi' => 'Jarum meteran tidak berputar sama sekali.',
                'status' => 'dikerjakan',
                'tanggal_lapor' => now()->subDays(3),
                'pembayaran_status' => 'Lunas',
                'metode_pembayaran' => 'Transfer Bank',
                'penugasan' => [
                    'petugas_id' => $budi?->id,
                    'status_tugas' => 'Sedang Dikerjakan',
                    'tanggal_penugasan' => now()->subDays(2),
                    'penyelesaian' => null
                ],
                'lat' => $baseLat + 0.008,
                'lng' => $baseLng - 0.012,
            ],
            // 4. Gratis, Udah Assign, Selesai (Penyelesaian via petugas)
            [
                'judul' => 'Cek air bau tanah',
                'user_id' => $andi?->id,
                'wilayah_id' => $cipanas?->id,
                'kategori_laporan_id' => $airKeruh?->id,
                'alamat' => 'Jalan Raya Cipanas No. 21',
                'deskripsi' => 'Air dari keran bau tanah dan berlumut.',
                'status' => 'selesai',
                'tanggal_lapor' => now()->subDays(10),
                'pembayaran_status' => 'Lunas',
                'metode_pembayaran' => null,
                'penugasan' => [
                    'petugas_id' => $siti?->id,
                    'status_tugas' => 'Selesai',
                    'tanggal_penugasan' => now()->subDays(9),
                    'penyelesaian' => [
                        'tanggal_selesai' => now()->subDays(8),
                        'keterangan' => 'Telah dilakukan flushing (pengurasan) pipa distribusi utama.',
                        'foto_bukti' => 'demo/penyelesaian-1.jpg',
                        'ulasan' => [
                            'rating' => 5,
                            'komentar' => 'Air sudah bersih dan tidak berbau lagi. Mantap!',
                        ],
                    ]
                ],
                'lat' => $baseLat - 0.02,
                'lng' => $baseLng - 0.02,
            ],
            // 5. Berbayar, Pembayaran Cash (Tunai), Selesai via Petugas
            [
                'judul' => 'Pipa tersumbat total',
                'user_id' => $dewi?->id,
                'wilayah_id' => $cisadak?->id,
                'kategori_laporan_id' => $pipaTersumbat?->id,
                'alamat' => 'Gang Anggrek RT 02, Cisadak',
                'deskripsi' => 'Air tidak mengalir sama sekali ke dalam rumah.',
                'status' => 'selesai',
                'tanggal_lapor' => now()->subDays(15),
                'pembayaran_status' => 'Lunas',
                'metode_pembayaran' => 'Tunai',
                'penugasan' => [
                    'petugas_id' => $budi?->id,
                    'status_tugas' => 'Selesai',
                    'tanggal_penugasan' => now()->subDays(14),
                    'penyelesaian' => [
                        'tanggal_selesai' => now()->subDays(14),
                        'keterangan' => 'Pipa tersumbat sampah plastik, sudah dibersihkan.',
                        'foto_bukti' => 'demo/penyelesaian-2.jpg',
                        'ulasan' => [
                            'rating' => 4,
                            'komentar' => 'Terima kasih pak Budi, aliran air sudah normal kembali.',
                        ],
                    ]
                ],
                'lat' => $baseLat + 0.015,
                'lng' => $baseLng - 0.005,
            ],
            // 6. Gratis, Selesai Via Email (Selesai tanpa petugas)
            [
                'judul' => 'Tanya soal jadwal gilir air',
                'user_id' => $nur?->id,
                'wilayah_id' => $mekarsari?->id,
                'kategori_laporan_id' => $airKeruh?->id,
                'alamat' => 'Perum Mekarsari Blok B4',
                'deskripsi' => 'Mengapa air sering mati setiap jam 5 sore? Apakah ada jadwal gilir?',
                'status' => 'selesai',
                'tanggal_lapor' => now()->subDays(5),
                'catatan_admin' => 'Telah dijelaskan via email dan sistem bahwa saat ini sedang ada pemeliharaan pompa berkala pada jam 17:00. Tidak perlu perbaikan di lokasi.',
                'pembayaran_status' => 'Lunas',
                'metode_pembayaran' => null,
                'penugasan' => null,
                'lat' => $baseLat - 0.005,
                'lng' => $baseLng + 0.015,
            ],
            // 7. Berbayar, Menunggu Konfirmasi (Dikerjakan tapi nunggu user approve)
            [
                'judul' => 'Pesan tangki air',
                'user_id' => $andi?->id,
                'wilayah_id' => $sukamaju?->id,
                'kategori_laporan_id' => $tangkiAir?->id,
                'alamat' => 'Jalan Melati No. 7, Sukamaju',
                'deskripsi' => 'Air mati 3 hari, mohon kirim tangki air darurat.',
                'status' => 'menunggu_konfirmasi',
                'tanggal_lapor' => now()->subDays(1),
                'pembayaran_status' => 'Terverifikasi',
                'metode_pembayaran' => 'Transfer Bank',
                'penugasan' => [
                    'petugas_id' => $siti?->id,
                    'status_tugas' => 'Menunggu Konfirmasi',
                    'tanggal_penugasan' => now()->subDays(1),
                    'penyelesaian' => [
                        'tanggal_selesai' => now()->subHours(2),
                        'keterangan' => 'Tangki air telah dikirim dan diisikan ke tandon rumah.',
                        'foto_bukti' => 'demo/tangki.jpg'
                    ]
                ],
                'lat' => $baseLat + 0.025,
                'lng' => $baseLng + 0.02,
            ],
            // 8. Berbayar, Pembayaran Ditolak (Belum dibayar/Assign)
            [
                'judul' => 'Ganti Pipa Bocor',
                'user_id' => $dewi?->id,
                'wilayah_id' => $cisadak?->id,
                'kategori_laporan_id' => $pipaBocor?->id,
                'alamat' => 'Gang Anggrek RT 02',
                'deskripsi' => 'Mohon segera diperbaiki pipa yang bocor.',
                'status' => 'pending',
                'tanggal_lapor' => now()->subDays(2),
                'pembayaran_status' => 'Ditolak',
                'metode_pembayaran' => 'Transfer Bank',
                'penugasan' => null,
                'lat' => $baseLat - 0.025,
                'lng' => $baseLng - 0.01,
            ],
            // 9. Berbayar, Laporan Ditolak (oleh admin)
            [
                'judul' => 'Pipa Bocor (Prank)',
                'user_id' => $nur?->id,
                'wilayah_id' => $mekarsari?->id,
                'kategori_laporan_id' => $pipaBocor?->id,
                'alamat' => 'Jalan Palsu',
                'deskripsi' => 'Laporan palsu tidak ada pipa di sini.',
                'status' => 'ditolak',
                'tanggal_lapor' => now()->subDays(20),
                'catatan_admin' => 'Alamat tidak ditemukan, nomor telepon tidak bisa dihubungi.',
                'pembayaran_status' => 'Menunggu',
                'metode_pembayaran' => null,
                'penugasan' => null,
                'lat' => $baseLat + 0.03,
                'lng' => $baseLng - 0.025,
            ],
        ];

        DB::beginTransaction();
        try {
            Laporan::query()->delete();

            foreach ($laporans as $item) {
                $pembayaranStatus = $item['pembayaran_status'];
                $metodePembayaran = $item['metode_pembayaran'];
                $penugasanData = $item['penugasan'];
                $catatanAdmin = $item['catatan_admin'] ?? null;
                $lat = $item['lat'];
                $lng = $item['lng'];

                unset($item['pembayaran_status'], $item['metode_pembayaran'], $item['penugasan'], $item['catatan_admin'], $item['lat'], $item['lng']);

                $laporan = Laporan::create(array_merge($item, [
                    'foto' => null,
                    'catatan_admin' => $catatanAdmin,
                ]));

                // Insert ke tabel map_lokasi
                DB::table('map_lokasi')->insert([
                    'laporan_id' => $laporan->id,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $harga = $laporan->kategoriLaporan?->tarif ?? 0;
                
                if ($harga == 0) {
                    $pembayaranStatus = 'Lunas';
                }

                Pembayaran::create([
                    'laporan_id' => $laporan->id,
                    'user_id' => $laporan->user_id,
                    'harga' => $harga,
                    'metode_pembayaran' => $metodePembayaran,
                    'status_pembayaran' => $pembayaranStatus,
                ]);

                if ($penugasanData) {
                    $penugasan = Penugasan::create([
                        'laporan_id' => $laporan->id,
                        'user_id' => $penugasanData['petugas_id'],
                        'tanggal_penugasan' => $penugasanData['tanggal_penugasan'],
                        'status_tugas' => $penugasanData['status_tugas'],
                    ]);

                    if (isset($penugasanData['penyelesaian']) && $penugasanData['penyelesaian']) {
                        DB::table('penyelesaian_tugas')->insert([
                            'penugasan_id' => $penugasan->id,
                            'foto_bukti' => $penugasanData['penyelesaian']['foto_bukti'],
                            'tanggal_selesai' => $penugasanData['penyelesaian']['tanggal_selesai'],
                            'keterangan' => $penugasanData['penyelesaian']['keterangan'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        if (isset($penugasanData['penyelesaian']['ulasan'])) {
                            DB::table('ulasan')->insert([
                                'penugasan_id' => $penugasan->id,
                                'laporan_id' => $laporan->id,
                                'user_id' => $laporan->user_id,
                                'rating' => $penugasanData['penyelesaian']['ulasan']['rating'],
                                'komentar' => $penugasanData['penyelesaian']['ulasan']['komentar'],
                                'tanggal_ulasan' => now()->subDays(1),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            // Insert TestimoniPublik agar masuk ke antrean Admin (status pending)
                            $user = User::find($laporan->user_id);
                            DB::table('testimoni_publik')->insert([
                                'nama' => $user->name ?? 'User Warga',
                                'email' => $user->email ?? 'warga@example.com',
                                'rating' => $penugasanData['penyelesaian']['ulasan']['rating'],
                                'pesan' => $penugasanData['penyelesaian']['ulasan']['komentar'],
                                'status' => 'pending',
                                'editable_until' => now()->addMinutes(5),
                                'session_token' => 'seeder_token_' . $laporan->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
