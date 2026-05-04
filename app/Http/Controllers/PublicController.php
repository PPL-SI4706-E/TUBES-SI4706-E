<?php

namespace App\Http\Controllers;

use App\Models\TestimoniPublik;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PublicController extends Controller
{
    public function home(Request $request)
    {
        $pengumumanList = collect([
            ['id'=>1,'judul'=>'DARURAT: Pemadaman Air Wilayah Cianjur','isi'=>'Sehubungan dengan perbaikan pipa utama distribusi, aliran air PDAM di wilayah Kec. Cianjur akan dihentikan sementara pada tanggal 16-17 Maret 2026 pukul 08.00-17.00 WIB. Posko air darurat tersedia di Balai Desa Sukamaju. Mohon warga mempersiapkan cadangan air.','tgl_posting'=>'2026-03-14','penting'=>true,'kategori'=>'darurat'],
            ['id'=>2,'judul'=>'Jadwal Pengiriman Tangki Air Darurat - Mekarjaya','isi'=>'Bagi warga Desa Mekarjaya yang terdampak kekeringan, pengiriman tangki air darurat akan dilakukan setiap hari Senin dan Kamis pukul 08.00 WIB di Balai Desa. Silakan bawa wadah masing-masing, maksimal 2 jerigen per KK.','tgl_posting'=>'2026-03-12','penting'=>true,'kategori'=>'jadwal'],
            ['id'=>3,'judul'=>'Info Tarif Baru Sambungan Air 2026','isi'=>'Mulai April 2026, tarif pemasangan sambungan air baru menjadi Rp 1.500.000,- (sudah termasuk meteran dan pipa 10 meter). Pendaftaran bisa melalui aplikasi TirtaBantu atau kantor PDAM.','tgl_posting'=>'2026-03-10','penting'=>false,'kategori'=>'info'],
            ['id'=>4,'judul'=>'Himbauan Hemat Air Musim Kemarau','isi'=>'Memasuki musim kemarau 2026, kami menghimbau seluruh warga untuk menghemat penggunaan air. Tips: tutup keran saat menyikat gigi, gunakan air bekas cucian untuk menyiram tanaman, periksa kebocoran pipa secara berkala.','tgl_posting'=>'2026-03-08','penting'=>false,'kategori'=>'info'],
            ['id'=>5,'judul'=>'Gangguan Air Wilayah Cibadak','isi'=>'Terjadi kerusakan pipa distribusi utama di Kec. Cibadak. Tim teknis sedang melakukan perbaikan. Estimasi air kembali normal dalam 24-48 jam. Kami mohon maaf atas ketidaknyamanan ini.','tgl_posting'=>'2026-03-06','penting'=>true,'kategori'=>'gangguan'],
        ]);
        $testimoniPublik = collect();
        $testimoniSaya = collect();

        if (Schema::hasTable('pengumuman')) {
            $dbPengumuman = Pengumuman::query()
                ->latest('tanggal_post')
                ->latest()
                ->take(5)
                ->get()
                ->map(function (Pengumuman $item, int $index) {
                    return [
                        'id' => $item->id,
                        'judul' => $item->judul,
                        'isi' => $item->isi,
                        'tgl_posting' => optional($item->tanggal_post)->format('Y-m-d') ?? optional($item->created_at)->format('Y-m-d'),
                        'penting' => (bool) $item->is_penting,
                        'kategori' => match ($item->kategori) {
                            'darurat' => 'darurat',
                            'jadwal' => 'jadwal',
                            default => 'info',
                        },
                    ];
                });

            if ($dbPengumuman->isNotEmpty()) {
                $pengumumanList = $dbPengumuman;
            }
        }

        if (Schema::hasTable('testimoni_publik')) {
            $testimoniPublik = TestimoniPublik::approved()
                ->latest('validated_at')
                ->take(6)
                ->get();

            $testimoniSaya = TestimoniPublik::query()
                ->latest()
                ->get()
                ->filter(fn (TestimoniPublik $testimoni) => $request->session()->has("testimoni_guest.{$testimoni->id}"))
                ->values();
        }

        return view('public.home', compact('testimoniPublik', 'testimoniSaya', 'pengumumanList'));
    }

    public function pengumumanDetail($id)
    {
        $pengumuman = Schema::hasTable('pengumuman')
            ? Pengumuman::with('user')->findOrFail($id)
            : null;

        return view('public.pengumuman-detail', compact('pengumuman'));
    }
}
