@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
@php
    // Mock data from Figma reference (github.com/rizkymavnsyh/Tirtabantu/src/app/data/mockData.ts)
    $pengumumanList = [
        ['id'=>1,'judul'=>'DARURAT: Pemadaman Air Wilayah Cianjur','isi'=>'Sehubungan dengan perbaikan pipa utama distribusi, aliran air PDAM di wilayah Kec. Cianjur akan dihentikan sementara pada tanggal 16-17 Maret 2026 pukul 08.00-17.00 WIB. Posko air darurat tersedia di Balai Desa Sukamaju. Mohon warga mempersiapkan cadangan air.','tgl_posting'=>'2026-03-14','penting'=>true,'kategori'=>'darurat'],
        ['id'=>2,'judul'=>'Jadwal Pengiriman Tangki Air Darurat - Mekarjaya','isi'=>'Bagi warga Desa Mekarjaya yang terdampak kekeringan, pengiriman tangki air darurat akan dilakukan setiap hari Senin dan Kamis pukul 08.00 WIB di Balai Desa. Silakan bawa wadah masing-masing, maksimal 2 jerigen per KK.','tgl_posting'=>'2026-03-12','penting'=>true,'kategori'=>'jadwal'],
        ['id'=>3,'judul'=>'Info Tarif Baru Sambungan Air 2026','isi'=>'Mulai April 2026, tarif pemasangan sambungan air baru menjadi Rp 1.500.000,- (sudah termasuk meteran dan pipa 10 meter). Pendaftaran bisa melalui aplikasi TirtaBantu atau kantor PDAM.','tgl_posting'=>'2026-03-10','penting'=>false,'kategori'=>'info'],
        ['id'=>4,'judul'=>'Himbauan Hemat Air Musim Kemarau','isi'=>'Memasuki musim kemarau 2026, kami menghimbau seluruh warga untuk menghemat penggunaan air. Tips: tutup keran saat menyikat gigi, gunakan air bekas cucian untuk menyiram tanaman, periksa kebocoran pipa secara berkala.','tgl_posting'=>'2026-03-08','penting'=>false,'kategori'=>'info'],
        ['id'=>5,'judul'=>'Gangguan Air Wilayah Cibadak','isi'=>'Terjadi kerusakan pipa distribusi utama di Kec. Cibadak. Tim teknis sedang melakukan perbaikan. Estimasi air kembali normal dalam 24-48 jam. Kami mohon maaf atas ketidaknyamanan ini.','tgl_posting'=>'2026-03-06','penting'=>true,'kategori'=>'gangguan'],
    ];

    $kategoriIkon = [
        'darurat'  => 'bg-red-100 text-red-600',
        'gangguan' => 'bg-amber-100 text-amber-600',
        'jadwal'   => 'bg-blue-100 text-blue-600',
        'info'     => 'bg-emerald-100 text-emerald-600',
    ];
    $kategoriLabel = [
        'darurat'  => 'DARURAT',
        'gangguan' => 'GANGGUAN',
        'jadwal'   => 'JADWAL',
        'info'     => 'INFORMASI',
    ];

    $kategoriList = [
        ['id'=>1,'nama'=>'Pipa Bocor',             'deskripsi'=>'Laporan kebocoran pipa distribusi air di area rumah',          'icon'=>'🔧','tarif'=>50000, 'keterangan_tarif'=>'Biaya jasa perbaikan ringan. Jika butuh material tambahan, dikenakan biaya material sesuai kebutuhan.'],
        ['id'=>2,'nama'=>'Air Keruh / Berbau',     'deskripsi'=>'Laporan kualitas air yang keruh, berbau, atau berubah warna',  'icon'=>'💧','tarif'=>0,     'keterangan_tarif'=>'GRATIS — Pengecekan kualitas air adalah layanan dasar yang disubsidi pemerintah.'],
        ['id'=>3,'nama'=>'Permintaan Tangki Air',  'deskripsi'=>'Permintaan pasokan air darurat via tangki ke rumah',           'icon'=>'🚛','tarif'=>75000, 'keterangan_tarif'=>'Biaya operasional pengiriman per tangki (5.000 liter). Gratis untuk daerah bencana/darurat.'],
        ['id'=>5,'nama'=>'Pipa Tersumbat',         'deskripsi'=>'Laporan pipa yang tersumbat atau aliran air kecil/mati',       'icon'=>'🚫','tarif'=>35000, 'keterangan_tarif'=>'Biaya jasa pembersihan dan pemeriksaan pipa. Sudah termasuk alat kerja.'],
        ['id'=>6,'nama'=>'Sambungan Baru',         'deskripsi'=>'Permohonan pemasangan sambungan air baru ke rumah',            'icon'=>'🏠','tarif'=>250000,'keterangan_tarif'=>'Biaya survey + pemasangan awal (DP). Total biaya tergantung jarak pipa, dibayar bertahap.'],
    ];

    $landingPengumuman = isset($dbPengumuman) && $dbPengumuman->isNotEmpty()
        ? $dbPengumuman->map(fn ($item) => [
            'id' => $item->id,
            'judul' => $item->judul,
            'isi' => $item->isi,
            'tgl_posting' => optional($item->tanggal_post)->format('Y-m-d'),
            'penting' => (bool) $item->is_penting,
            'kategori' => $item->kategori,
        ])->all()
        : $pengumumanList;

    $featured = collect($landingPengumuman)->firstWhere('kategori', 'darurat')
        ?? collect($landingPengumuman)->firstWhere('penting', true)
        ?? collect($landingPengumuman)->first();
    $otherPengumuman = collect($landingPengumuman)
        ->reject(fn ($item) => $featured && $item['id'] === $featured['id'])
        ->values()
        ->all();

    $fiturList = [
        ['icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','title'=>'Pelaporan Detail','desc'=>'Buat laporan dengan alamat rumah lengkap, koordinat GPS, dan foto bukti. Admin tahu persis lokasinya.','color'=>'bg-blue-100 text-blue-600'],
        ['icon'=>'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7','title'=>'Peta Lokasi Real-time','desc'=>'Lihat posisi laporan di peta interaktif. Petugas bisa navigasi langsung ke lokasi rumah.','color'=>'bg-emerald-100 text-emerald-600'],
        ['icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z','title'=>'Validasi Admin','desc'=>'Admin meninjau dan memutuskan apakah perlu turun lapangan atau bisa diselesaikan secara remote.','color'=>'bg-violet-100 text-violet-600'],
        ['icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z','title'=>'Penugasan Petugas','desc'=>'Sistem dispatch otomatis: admin assign petugas, petugas update progress di lapangan.','color'=>'bg-amber-100 text-amber-600'],
        ['icon'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z','title'=>'Konfirmasi & Feedback','desc'=>'Pelanggan wajib konfirmasi apakah perbaikan sudah benar. Bisa kasih feedback jika belum selesai.','color'=>'bg-cyan-100 text-cyan-600'],
        ['icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z','title'=>'Pembayaran Online','desc'=>'Bayar biaya perbaikan langsung via transfer. Upload bukti bayar, admin verifikasi.','color'=>'bg-pink-100 text-pink-600'],
        ['icon'=>'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z','title'=>'Rating & Ulasan','desc'=>'Beri rating bintang dan ulasan setelah pekerjaan selesai untuk evaluasi kinerja petugas.','color'=>'bg-orange-100 text-orange-600'],
        ['icon'=>'M12 2.69l5.66 5.66a8 8 0 11-11.31 0z','title'=>'Dashboard Analytics','desc'=>'Visualisasi data laporan per bulan, kinerja petugas, dan rekapitulasi pendapatan.','color'=>'bg-sky-100 text-sky-600'],
        ['icon'=>'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z','title'=>'Pengumuman Publik','desc'=>'Info gangguan distribusi air massal, jadwal pemadaman, dan himbauan bisa dibaca tanpa login.','color'=>'bg-red-100 text-red-600'],
    ];

    $alurList = [
        ['step'=>'1','title'=>'Masyarakat Melapor','desc'=>'Buat tiket laporan dengan foto, alamat rumah lengkap, dan pin lokasi di peta. Sistem mencatat koordinat otomatis.','color'=>'bg-sky-600'],
        ['step'=>'2','title'=>'Admin Memvalidasi','desc'=>'Admin memeriksa laporan: apakah perlu turun ke lapangan atau bisa ditangani secara remote (misalnya reset jaringan, info area terdampak). Jika ditolak, ada catatan alasan.','color'=>'bg-blue-600'],
        ['step'=>'3','title'=>'Penugasan Petugas','desc'=>'Jika perlu turun lapangan, admin menugaskan petugas. Petugas melihat detail masalah, alamat, dan navigasi ke lokasi via peta.','color'=>'bg-violet-600'],
        ['step'=>'4','title'=>'Petugas Mengerjakan','desc'=>'Petugas update status: Menuju Lokasi → Sedang Dikerjakan → Selesai. Upload foto bukti penyelesaian.','color'=>'bg-emerald-600'],
        ['step'=>'5','title'=>'Pelanggan Konfirmasi','desc'=>'Pelanggan menerima notifikasi dan diminta mengonfirmasi: SELESAI atau BELUM SELESAI. Jika belum, wajib isi alasan/feedback agar ditindaklanjuti.','color'=>'bg-amber-600'],
        ['step'=>'6','title'=>'Pembayaran & Rating','desc'=>'Jika ada biaya, pelanggan bayar via transfer dan upload bukti. Setelah lunas, pelanggan bisa memberi rating bintang dan ulasan.','color'=>'bg-pink-600'],
    ];
@endphp

<div class="min-h-screen bg-gradient-to-b from-sky-50 to-white">

    {{-- ── Navbar ──────────────────────────────────────────────── --}}
    <header class="bg-white/80 backdrop-blur sticky top-0 z-20 border-b border-sky-100">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-sky-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/></svg>
                </div>
                <span class="text-sky-800" style="font-size:1.2rem;font-weight:700">TirtaBantu</span>
            </div>
            <nav class="hidden md:flex items-center gap-6" style="font-size:0.875rem">
                <a href="#pengumuman" class="text-sky-700 hover:text-sky-900 transition-colors">Pengumuman</a>
                <a href="#tarif"      class="text-sky-700 hover:text-sky-900 transition-colors">Tarif Layanan</a>
                <a href="#fitur"      class="text-sky-700 hover:text-sky-900 transition-colors">Fitur</a>
                <a href="#alur"       class="text-sky-700 hover:text-sky-900 transition-colors">Alur Pelaporan</a>
                <a href="#testimoni"  class="text-sky-700 hover:text-sky-900 transition-colors">Testimoni</a>
                <a href="#kontak"     class="text-sky-700 hover:text-sky-900 transition-colors">Kontak</a>
            </nav>
            @auth
                @php
                    $loggedUrl = auth()->user()->isAdmin()
                        ? route('admin.dashboard')
                        : (auth()->user()->isPetugas() ? route('petugas.tugas.index') : route('warga.laporan.index'));
                    $loggedLabel = auth()->user()->isAdmin() ? 'Dashboard' : (auth()->user()->isPetugas() ? 'Tugas Saya' : 'Laporan Saya');
                @endphp
                <a href="{{ $loggedUrl }}" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-lg transition-colors shadow-sm" style="font-size:0.875rem">{{ $loggedLabel }}</a>
            @else
                <a href="{{ route('register') }}" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-lg transition-colors shadow-sm" style="font-size:0.875rem">Masuk</a>
            @endauth
        </div>
    </header>

    {{-- ── Hero ───────────────────────────────────────────────── --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-sky-600 via-sky-700 to-sky-800 text-white">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="absolute top-10 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-sky-300 rounded-full blur-3xl"></div>
        </div>
        <div class="max-w-6xl mx-auto px-4 py-20 md:py-28 grid md:grid-cols-2 gap-10 items-center relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 bg-white/15 backdrop-blur rounded-full px-4 py-1.5 mb-6 border border-white/20" style="font-size:0.8rem">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/></svg>
                    SDG 6 - Air Bersih dan Sanitasi Layak
                </div>
                <h1 class="mb-4" style="font-size:2.75rem;font-weight:800;line-height:1.1">TirtaBantu</h1>
                <p class="text-sky-100 mb-2" style="font-size:1.1rem;font-weight:500">Sistem Informasi Manajemen Pelaporan &amp; Distribusi Air Bersih</p>
                <p class="text-sky-200/80 mb-8" style="font-size:0.9rem;line-height:1.7">Laporkan masalah infrastruktur air langsung dari rumah Anda. Pantau perbaikan secara real-time dengan peta lokasi. Bayar biaya layanan secara online. Bersama wujudkan akses air bersih untuk semua.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="bg-white text-sky-700 hover:bg-sky-50 px-7 py-3 rounded-xl flex items-center gap-2 transition-colors shadow-lg" style="font-weight:600">
                        Mulai Lapor
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="#fitur" class="border border-white/30 text-white hover:bg-white/10 px-7 py-3 rounded-xl transition-colors backdrop-blur">Pelajari Fitur</a>
                </div>
                <div class="flex gap-6 mt-10 pt-8 border-t border-white/15">
                    @foreach([['150+','Laporan Ditangani'],['95%','Tingkat Penyelesaian'],['3','Petugas Aktif']] as $s)
                        <div>
                            <p style="font-size:1.5rem;font-weight:700">{{ $s[0] }}</p>
                            <p class="text-sky-300" style="font-size:0.75rem">{{ $s[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="hidden md:block">
                <img src="https://images.unsplash.com/photo-1574718944703-2857f03a82b6?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080"
                     alt="Air Bersih"
                     class="rounded-2xl shadow-2xl w-full h-96 object-cover border-4 border-white/20">
            </div>
        </div>
    </section>

    {{-- ── Pengumuman ─────────────────────────────────────────── --}}
    <section id="pengumuman" class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-2 bg-sky-100 text-sky-700 rounded-full px-4 py-1.5 mb-3" style="font-size:0.8rem;font-weight:600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                    Info Terkini
                </div>
                <h2 class="text-sky-900" style="font-size:1.75rem;font-weight:700">Pengumuman &amp; Info Gangguan</h2>
                <p class="text-slate-500 mt-2" style="font-size:0.9rem">Informasi terbaru seputar distribusi air di wilayah Anda</p>
            </div>

            @if($featured)
                <div class="mb-6">
                    <div class="bg-gradient-to-r from-red-50 to-amber-50 border-2 border-red-200 rounded-2xl p-6 md:p-8">
                        <div class="flex items-center gap-3 mb-3">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full" style="font-size:0.75rem;font-weight:700">{{ $kategoriLabel[$featured['kategori']] }}</span>
                            <span class="text-slate-400" style="font-size:0.8rem">{{ $featured['tgl_posting'] }}</span>
                        </div>
                        <h3 class="text-red-800 mb-2" style="font-size:1.15rem;font-weight:700">{{ $featured['judul'] }}</h3>
                        <p class="text-slate-700" style="font-size:0.9rem;line-height:1.7">{{ $featured['isi'] }}</p>
                    </div>
                </div>
            @endif

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($otherPengumuman as $p)
                    <div class="bg-white border border-sky-100 rounded-xl p-5 hover:shadow-lg transition-all hover:-translate-y-0.5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="{{ $kategoriIkon[$p['kategori']] }} px-2.5 py-0.5 rounded-full" style="font-size:0.7rem;font-weight:700">{{ $kategoriLabel[$p['kategori']] }}</span>
                            <span class="text-slate-400 ml-auto" style="font-size:0.75rem">{{ $p['tgl_posting'] }}</span>
                        </div>
                        <h3 class="text-sky-800 mb-2" style="font-size:0.95rem;font-weight:600">{{ $p['judul'] }}</h3>
                        <p class="text-slate-600" style="font-size:0.83rem;line-height:1.6">{{ \Illuminate\Support\Str::limit($p['isi'], 120) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Tarif Layanan ──────────────────────────────────────── --}}
    <section id="tarif" class="py-16 bg-sky-50/50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-700 rounded-full px-4 py-1.5 mb-3" style="font-size:0.8rem;font-weight:600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Transparan &amp; Terjangkau
                </div>
                <h2 class="text-sky-900" style="font-size:1.75rem;font-weight:700">Tarif Layanan TirtaBantu</h2>
                <p class="text-slate-500 mt-2 max-w-2xl mx-auto" style="font-size:0.9rem">Kami berkomitmen menyediakan layanan air bersih yang terjangkau untuk semua lapisan masyarakat. Berikut tarif resmi per jenis layanan — tanpa biaya tersembunyi.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
                @foreach($kategoriList as $k)
                    <div class="bg-white rounded-2xl border-2 shadow-sm hover:shadow-lg transition-all hover:-translate-y-0.5 overflow-hidden {{ $k['tarif'] === 0 ? 'border-emerald-300' : 'border-sky-100' }}">
                        <div class="px-6 py-4 {{ $k['tarif'] === 0 ? 'bg-gradient-to-r from-emerald-50 to-emerald-100' : 'bg-gradient-to-r from-sky-50 to-sky-100' }}">
                            <div class="flex items-center gap-3 mb-2">
                                <span style="font-size:1.5rem">{{ $k['icon'] }}</span>
                                <h3 class="text-sky-800" style="font-size:1rem;font-weight:700">{{ $k['nama'] }}</h3>
                            </div>
                            @if($k['tarif'] === 0)
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-700" style="font-size:1.75rem;font-weight:800">GRATIS</span>
                                    <span class="bg-emerald-200 text-emerald-800 px-2.5 py-0.5 rounded-full" style="font-size:0.68rem;font-weight:700">DISUBSIDI</span>
                                </div>
                            @else
                                <div class="flex items-baseline gap-1">
                                    <span class="text-sky-500" style="font-size:0.85rem">Rp</span>
                                    <span class="text-sky-800" style="font-size:1.75rem;font-weight:800">{{ number_format($k['tarif'], 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="px-6 py-4">
                            <p class="text-slate-600 mb-3" style="font-size:0.83rem;line-height:1.6">{{ $k['deskripsi'] }}</p>
                            <div class="rounded-lg p-3 {{ $k['tarif'] === 0 ? 'bg-emerald-50' : 'bg-sky-50' }}">
                                <p class="{{ $k['tarif'] === 0 ? 'text-emerald-700' : 'text-sky-700' }}" style="font-size:0.78rem;line-height:1.5">{{ $k['keterangan_tarif'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-gradient-to-r from-sky-600 to-sky-700 rounded-2xl p-6 md:p-8 text-white">
                <div class="grid md:grid-cols-3 gap-6 items-center">
                    <div class="md:col-span-2">
                        <h3 class="mb-2" style="font-size:1.15rem;font-weight:700">Komitmen Kami: Layanan Terjangkau untuk Semua</h3>
                        <p class="text-sky-100" style="font-size:0.85rem;line-height:1.7">Tarif TirtaBantu dirancang serendah mungkin karena tujuan utama kami adalah <strong>membantu masyarakat</strong> mendapatkan akses air bersih. Pengecekan kualitas air <strong>gratis</strong>, perbaikan ringan mulai <strong>Rp 35.000</strong>. Untuk daerah bencana/darurat, layanan tangki air <strong>tidak dikenakan biaya</strong>.</p>
                        <div class="flex flex-wrap gap-3 mt-4">
                            <div class="bg-white/15 backdrop-blur rounded-lg px-4 py-2 border border-white/20">
                                <p class="text-sky-200" style="font-size:0.68rem">Mulai dari</p>
                                <p style="font-size:1.1rem;font-weight:700">Rp 0</p>
                            </div>
                            <div class="bg-white/15 backdrop-blur rounded-lg px-4 py-2 border border-white/20">
                                <p class="text-sky-200" style="font-size:0.68rem">Perbaikan ringan</p>
                                <p style="font-size:1.1rem;font-weight:700">Rp 35.000</p>
                            </div>
                            <div class="bg-white/15 backdrop-blur rounded-lg px-4 py-2 border border-white/20">
                                <p class="text-sky-200" style="font-size:0.68rem">Bisa bayar</p>
                                <p style="font-size:1.1rem;font-weight:700">Bertahap</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="bg-white/10 rounded-xl p-5 backdrop-blur border border-white/20">
                            <svg class="w-10 h-10 text-emerald-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <p style="font-size:0.85rem;font-weight:600">Tanpa Biaya Tersembunyi</p>
                            <p class="text-sky-200 mt-1" style="font-size:0.78rem">Semua tarif sudah termasuk jasa petugas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Fitur ──────────────────────────────────────────────── --}}
    <section id="fitur" class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-10">
                <h2 class="text-sky-900" style="font-size:1.75rem;font-weight:700">Fitur Lengkap TirtaBantu</h2>
                <p class="text-slate-500 mt-2" style="font-size:0.9rem">Solusi terintegrasi dari pelaporan hingga pembayaran</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($fiturList as $f)
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-sky-100 hover:shadow-md transition-all hover:-translate-y-0.5">
                        <div class="w-11 h-11 {{ $f['color'] }} rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['icon'] }}"/></svg>
                        </div>
                        <h3 class="text-sky-800 mb-2" style="font-size:0.95rem;font-weight:600">{{ $f['title'] }}</h3>
                        <p class="text-slate-500" style="font-size:0.83rem;line-height:1.6">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Alur Pelaporan ─────────────────────────────────────── --}}
    <section id="alur" class="py-16 bg-sky-50/50">
        <div class="max-w-5xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-sky-900" style="font-size:1.75rem;font-weight:700">Alur Pelaporan TirtaBantu</h2>
                <p class="text-slate-500 mt-2" style="font-size:0.9rem">Proses transparan dari laporan hingga konfirmasi selesai</p>
            </div>
            <div class="space-y-0">
                @foreach($alurList as $i => $s)
                    @php $isLast = $i === count($alurList) - 1; @endphp
                    <div class="flex gap-5">
                        <div class="flex flex-col items-center">
                            <div class="w-11 h-11 {{ $s['color'] }} text-white rounded-full flex items-center justify-center shrink-0 shadow-md" style="font-size:1rem;font-weight:700">{{ $s['step'] }}</div>
                            @if(!$isLast)<div class="w-0.5 flex-1 bg-sky-200 my-1"></div>@endif
                        </div>
                        <div class="{{ $isLast ? '' : 'pb-8' }}">
                            <h3 class="text-sky-800 mb-1" style="font-size:1rem;font-weight:600">{{ $s['title'] }}</h3>
                            <p class="text-slate-500" style="font-size:0.85rem;line-height:1.6">{{ $s['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Tentang & Kontak ───────────────────────────────────── --}}
    @php
        $canManageOwnTestimoni = $activeTestimoni?->isEditableUntil() ?? false;
        $remainingWindowLabel = null;

        if ($canManageOwnTestimoni) {
            $remainingSeconds = max(0, now()->diffInSeconds($activeTestimoni->created_at->copy()->addMinutes(5), false));
            $remainingMinutes = intdiv($remainingSeconds, 60);
            $remainingRemainderSeconds = $remainingSeconds % 60;
            $remainingWindowLabel = sprintf('%02d:%02d', $remainingMinutes, $remainingRemainderSeconds);
        }
    @endphp

    <section id="testimoni" class="py-16 bg-slate-50/80">
        <div class="max-w-6xl mx-auto px-4">
            <div class="mb-10 max-w-3xl">
                <div class="inline-flex items-center gap-2 bg-white text-sky-700 rounded-full px-4 py-1.5 mb-4 border border-sky-100 shadow-sm" style="font-size:0.82rem;font-weight:700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-4 4v-4z"/></svg>
                    Buku Tamu / Testimoni Publik
                </div>
                <h2 class="text-sky-900" style="font-size:2.4rem;font-weight:800;line-height:1.15">Cerita warga tentang layanan TirtaBantu</h2>
                <p class="text-slate-500 mt-4" style="font-size:1rem;line-height:1.75">Pengunjung dapat meninggalkan pesan, saran, atau kesan tanpa login. Testimoni hanya tampil di landing page setelah divalidasi admin, dan pengirim masih bisa mengedit atau menarik kembali pesan selama 5 menit dalam sesi browser yang sama.</p>
            </div>

            <div class="grid lg:grid-cols-[1.1fr,0.9fr] gap-8 items-start">
                <div>
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <h3 class="text-sky-900" style="font-size:1.2rem;font-weight:800">Testimoni tervalidasi</h3>
                            <p class="text-slate-500 mt-2 max-w-2xl" style="font-size:0.88rem;line-height:1.7">Pesan `pending` dan `rejected` tidak tampil ke publik. Yang terlihat di sini hanya testimoni yang sudah lolos validasi admin.</p>
                        </div>
                        <div class="bg-white border border-sky-100 rounded-2xl px-4 py-3 shadow-sm">
                            <p class="text-slate-400" style="font-size:0.74rem;font-weight:700">TOTAL TAMPIL</p>
                            <p class="text-sky-900 mt-1" style="font-size:1.4rem;font-weight:800">{{ $approvedTestimonials->count() }}</p>
                        </div>
                    </div>

                    @if($errors->has('testimoni'))
                        <div class="mt-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3" style="font-size:0.85rem">
                            {{ $errors->first('testimoni') }}
                        </div>
                    @endif

                    @if($activeTestimoni)
                        <div class="mt-6 bg-white border border-sky-100 rounded-2xl p-5 shadow-sm">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="px-3 py-1 rounded-full {{ $activeTestimoni->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($activeTestimoni->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}" style="font-size:0.75rem;font-weight:700">
                                    {{ strtoupper($activeTestimoni->status) }}
                                </span>
                                <span class="text-slate-400" style="font-size:0.78rem">Terakhir dikirim {{ $activeTestimoni->created_at?->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-700 mb-3" style="font-size:0.85rem;line-height:1.7">
                                @if($activeTestimoni->status === 'pending')
                                    Testimoni Anda sedang menunggu validasi admin dan belum tampil di landing page.
                                @elseif($activeTestimoni->status === 'approved')
                                    Testimoni Anda sudah disetujui dan tampil di landing page.
                                @else
                                    Testimoni Anda ditolak admin dan disembunyikan dari landing page.
                                @endif
                            </p>
                            <div class="bg-sky-50 rounded-xl p-4">
                                <p class="text-sky-900" style="font-size:0.88rem;font-weight:600">{{ $activeTestimoni->nama }}</p>
                                <p class="text-slate-500 mt-1" style="font-size:0.82rem;line-height:1.6">{{ $activeTestimoni->pesan }}</p>
                            </div>
                            <div class="mt-3 text-slate-500" style="font-size:0.78rem">
                                @if($canManageOwnTestimoni)
                                    Anda masih bisa edit atau hapus testimoni ini selama <strong>{{ $remainingWindowLabel }}</strong> lagi.
                                @else
                                    Jendela edit dan hapus 5 menit untuk testimoni ini sudah berakhir.
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="mt-8">
                        @if($approvedTestimonials->isNotEmpty())
                            <div x-data="{
                                    active: 0,
                                    total: {{ $approvedTestimonials->count() }},
                                    maxIndex: Math.max({{ $approvedTestimonials->count() }} - 2, 0),
                                    next() { this.active = this.active >= this.maxIndex ? 0 : this.active + 1; },
                                    prev() { this.active = this.active <= 0 ? this.maxIndex : this.active - 1; }
                                }"
                                x-init="if (total > 2) { setInterval(() => next(), 4500) }"
                                class="relative">
                                <div class="overflow-hidden px-2 md:px-3">
                                    <div class="flex transition-transform duration-500 ease-out gap-4" :style="`transform: translateX(-${active * 50}%);`">
                                        @foreach($approvedTestimonials as $testimonial)
                                            <div class="w-full md:w-[calc(50%-0.5rem)] shrink-0">
                                                <article class="bg-white rounded-3xl border border-sky-100 p-5 shadow-sm min-h-[155px]">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div>
                                                            <h3 class="text-sky-900" style="font-size:0.95rem;font-weight:800">{{ $testimonial->nama }}</h3>
                                                            <p class="text-slate-400 mt-1" style="font-size:0.76rem">{{ optional($testimonial->validated_at ?? $testimonial->created_at)->format('d M Y H:i') }}</p>
                                                        </div>
                                                        <div class="inline-flex items-center bg-emerald-100 text-emerald-700 rounded-full px-3 py-1.5 shrink-0" style="font-size:0.76rem;font-weight:700">
                                                            Tervalidasi
                                                        </div>
                                                    </div>

                                                    <p class="text-slate-600 mt-5" style="font-size:0.9rem;line-height:1.7">{{ $testimonial->pesan }}</p>
                                                </article>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                @if($approvedTestimonials->count() > 2)
                                    <button type="button"
                                            @click="prev()"
                                            class="absolute left-0 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-slate-200/90 text-slate-500 hover:bg-sky-100 hover:text-sky-700 transition-colors shadow-sm">
                                        <span class="sr-only">Testimoni sebelumnya</span>
                                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>

                                    <button type="button"
                                            @click="next()"
                                            class="absolute right-0 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-slate-200/90 text-slate-500 hover:bg-sky-100 hover:text-sky-700 transition-colors shadow-sm">
                                        <span class="sr-only">Geser testimoni</span>
                                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                @endif

                                @if($approvedTestimonials->count() > 1)
                                    <div class="flex items-center justify-center gap-2 mt-5">
                                        @for($index = 0; $index <= max($approvedTestimonials->count() - 2, 0); $index++)
                                            <button type="button"
                                                    @click="active = {{ $index }}"
                                                    :class="active === {{ $index }} ? 'bg-sky-600 w-8' : 'bg-sky-200 w-3'"
                                                    class="h-3 rounded-full transition-all duration-300">
                                                <span class="sr-only">Posisi slider {{ $index + 1 }}</span>
                                            </button>
                                        @endfor
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="bg-white border border-dashed border-sky-200 rounded-2xl p-8 text-center">
                                <p class="text-sky-900" style="font-size:1rem;font-weight:700">Belum ada testimoni yang ditampilkan</p>
                                <p class="text-slate-500 mt-2" style="font-size:0.85rem">Jadilah pengunjung pertama yang meninggalkan kesan untuk TirtaBantu.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-sky-100 shadow-xl p-6 md:p-7">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-8 8l2.5-2.5H19a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2v3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sky-900" style="font-size:1.1rem;font-weight:700">{{ $activeTestimoni ? 'Kelola testimoni Anda' : 'Tinggalkan testimoni' }}</h3>
                            <p class="text-slate-500" style="font-size:0.82rem">Isi nama, email opsional, dan pesan. Setelah dikirim, testimoni akan menunggu validasi admin.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mb-5">
                        <div class="rounded-2xl bg-sky-50 px-3 py-3 border border-sky-100">
                            <p class="text-sky-700" style="font-size:0.72rem;font-weight:800">NAMA</p>
                            <p class="text-slate-500 mt-1" style="font-size:0.77rem">Wajib</p>
                        </div>
                        <div class="rounded-2xl bg-sky-50 px-3 py-3 border border-sky-100">
                            <p class="text-sky-700" style="font-size:0.72rem;font-weight:800">EMAIL</p>
                            <p class="text-slate-500 mt-1" style="font-size:0.77rem">Opsional</p>
                        </div>
                        <div class="rounded-2xl bg-sky-50 px-3 py-3 border border-sky-100">
                            <p class="text-sky-700" style="font-size:0.72rem;font-weight:800">PESAN</p>
                            <p class="text-slate-500 mt-1" style="font-size:0.77rem">Wajib</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ $activeTestimoni && $canManageOwnTestimoni ? route('testimoni.update', $activeTestimoni) : route('testimoni.store') }}" class="space-y-4">
                        @csrf
                        @if($activeTestimoni && $canManageOwnTestimoni)
                            @method('PUT')
                        @endif

                        <div>
                            <label for="nama" class="block text-slate-700 mb-1.5" style="font-size:0.82rem;font-weight:600">Nama</label>
                            <input id="nama" name="nama" type="text" value="{{ old('nama', $activeTestimoni?->nama) }}" class="w-full rounded-xl border border-sky-100 focus:border-sky-400 focus:ring-sky-400 px-4 py-3" placeholder="Masukkan nama Anda">
                            @error('nama')<p class="text-red-500 mt-1" style="font-size:0.76rem">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="email" class="block text-slate-700 mb-1.5" style="font-size:0.82rem;font-weight:600">Email (opsional)</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $activeTestimoni?->email) }}" class="w-full rounded-xl border border-sky-100 focus:border-sky-400 focus:ring-sky-400 px-4 py-3" placeholder="nama@email.com">
                            @error('email')<p class="text-red-500 mt-1" style="font-size:0.76rem">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="pesan" class="block text-slate-700 mb-1.5" style="font-size:0.82rem;font-weight:600">Pesan Testimoni</label>
                            <textarea id="pesan" name="pesan" rows="5" class="w-full rounded-xl border border-sky-100 focus:border-sky-400 focus:ring-sky-400 px-4 py-3" placeholder="Ceritakan pengalaman, saran, atau kesan Anda tentang TirtaBantu...">{{ old('pesan', $activeTestimoni?->pesan) }}</textarea>
                            @error('pesan')<p class="text-red-500 mt-1" style="font-size:0.76rem">{{ $message }}</p>@enderror
                        </div>

                        <div class="bg-sky-50 rounded-2xl p-4">
                            <div class="space-y-2 text-slate-600" style="font-size:0.8rem;line-height:1.6">
                                <p><strong>Create:</strong> testimoni baru masuk sebagai <strong>pending</strong>.</p>
                                <p><strong>Read:</strong> testimoni tampil setelah admin memvalidasi.</p>
                                <p><strong>Update/Delete:</strong> pengirim bisa edit atau tarik kembali pesan selama 5 menit.</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="flex-1 min-w-[180px] bg-sky-600 hover:bg-sky-700 text-white rounded-xl px-5 py-3 transition-colors shadow-md" style="font-weight:600">
                                {{ $activeTestimoni && $canManageOwnTestimoni ? 'Perbarui Testimoni' : 'Kirim Testimoni' }}
                            </button>

                            @if($activeTestimoni && $canManageOwnTestimoni)
                                <button type="submit" form="delete-testimoni-form" class="flex-1 min-w-[180px] bg-white border border-red-200 text-red-600 hover:bg-red-50 rounded-xl px-5 py-3 transition-colors" style="font-weight:600">
                                    Tarik Kembali
                                </button>
                            @endif
                        </div>
                    </form>

                    @if($activeTestimoni && $canManageOwnTestimoni)
                        <form id="delete-testimoni-form" method="POST" action="{{ route('testimoni.destroy', $activeTestimoni) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section id="kontak" class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white rounded-2xl p-8 border border-sky-100 shadow-sm">
                    <h3 class="text-sky-900 mb-4" style="font-size:1.25rem;font-weight:700">Tentang TirtaBantu</h3>
                    <p class="text-slate-600 mb-4" style="font-size:0.9rem;line-height:1.7">TirtaBantu adalah aplikasi web manajemen pelaporan dan distribusi air bersih yang dikembangkan untuk mendukung <strong>SDG Poin 6</strong> - Air Bersih dan Sanitasi Layak.</p>
                    <p class="text-slate-600 mb-4" style="font-size:0.9rem;line-height:1.7">Platform ini menjembatani masyarakat, petugas lapangan, dan pengelola PDAM dengan sistem yang transparan, terukur, dan terdokumentasi.</p>
                    <div class="bg-sky-50 rounded-xl p-4">
                        <p class="text-sky-800 mb-2" style="font-size:0.85rem;font-weight:600">Wilayah Layanan Aktif:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['Kec. Cianjur','Kec. Cibadak','Kec. Sumedang','Kec. Cipanas','Kec. Bandung Selatan'] as $w)
                                <span class="bg-sky-100 text-sky-700 px-3 py-1 rounded-full" style="font-size:0.75rem">{{ $w }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-8 border border-sky-100 shadow-sm">
                    <h3 class="text-sky-900 mb-4" style="font-size:1.25rem;font-weight:700">Hubungi Kami</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-4 p-4 bg-sky-50 rounded-xl">
                            <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sky-800" style="font-size:0.85rem;font-weight:600">Kantor PDAM TirtaBantu</p>
                                <p class="text-slate-500" style="font-size:0.83rem">Jl. Raya Cianjur No. 100, Kec. Cianjur, Jawa Barat 43211</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 bg-sky-50 rounded-xl">
                            <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <p class="text-sky-800" style="font-size:0.85rem;font-weight:600">Telepon &amp; WhatsApp</p>
                                <p class="text-slate-500" style="font-size:0.83rem">(0263) 123-456 | WA: 0812-3456-7890</p>
                                <p class="text-slate-400" style="font-size:0.75rem">Senin - Jumat, 08.00 - 16.00 WIB</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 bg-sky-50 rounded-xl">
                            <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sky-800" style="font-size:0.85rem;font-weight:600">Email</p>
                                <p class="text-slate-500" style="font-size:0.83rem">cs@tirtabantu.id</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('register') }}" class="w-full bg-sky-600 hover:bg-sky-700 text-white py-3 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-md">
                            Masuk ke Sistem
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Footer ─────────────────────────────────────────────── --}}
    <footer class="bg-sky-900 text-white/70 py-10">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-sky-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/></svg>
                        <span class="text-white" style="font-weight:700">TirtaBantu</span>
                    </div>
                    <p style="font-size:0.8rem;line-height:1.6">Sistem Informasi Manajemen Pelaporan dan Distribusi Air Bersih untuk masyarakat Indonesia.</p>
                </div>
                <div>
                    <p class="text-white mb-3" style="font-size:0.85rem;font-weight:600">Layanan</p>
                    <ul class="space-y-1.5" style="font-size:0.8rem">
                        <li>Laporan Pipa Bocor</li>
                        <li>Permintaan Tangki Air</li>
                        <li>Cek Kualitas Air</li>
                        <li>Sambungan Baru</li>
                    </ul>
                </div>
                <div>
                    <p class="text-white mb-3" style="font-size:0.85rem;font-weight:600">Tautan</p>
                    <ul class="space-y-1.5" style="font-size:0.8rem">
                        <li><a href="#pengumuman" class="hover:text-white transition-colors">Pengumuman</a></li>
                        <li><a href="#fitur"      class="hover:text-white transition-colors">Fitur</a></li>
                        <li><a href="#alur"       class="hover:text-white transition-colors">Alur Pelaporan</a></li>
                        <li><a href="#testimoni"  class="hover:text-white transition-colors">Testimoni</a></li>
                        <li><a href="#kontak"     class="hover:text-white transition-colors">Kontak</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 pt-6 text-center">
                <p style="font-size:0.75rem">&copy; {{ date('Y') }} TirtaBantu. Mendukung SDG 6 - Air Bersih dan Sanitasi Layak untuk Semua.</p>
            </div>
        </div>
    </footer>

</div>
@endsection
