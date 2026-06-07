@extends('layouts.warga')
@section('title', 'Detail Laporan #' . $laporan->id)

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<div class="mb-5">
    <a href="{{ route('warga.laporan.index') }}" class="inline-flex items-center gap-1.5 text-sky-600 hover:text-sky-800 text-sm font-medium transition-colors mb-3">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Laporan
    </a>
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-sky-900" style="font-size:1.4rem;font-weight:700">Detail Laporan <span class="text-sky-500">#{{ $laporan->id }}</span></h1>
        @php
            $badgeColor = match($laporan->status) {
                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                'dikerjakan' => 'bg-blue-100 text-blue-700 border-blue-200',
                'selesai' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'ditolak' => 'bg-red-100 text-red-700 border-red-200',
                default => 'bg-slate-100 text-slate-600 border-slate-200',
            };
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-bold {{ $badgeColor }}">
            {{ ucfirst($laporan->status) }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ═══ KIRI: INFO LAPORAN ═══ --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Detail Informasi Laporan --}}
        <div class="bg-white rounded-2xl border border-sky-100 shadow-sm p-5">
            <h2 class="text-sky-900 font-bold mb-4 flex items-center gap-2">
                <i data-lucide="file-text" class="w-5 h-5 text-sky-600"></i> Informasi Laporan
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Judul Laporan</p>
                    <p class="font-medium text-slate-800">{{ $laporan->judul }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Kategori Masalah</p>
                    <div class="flex items-center gap-1.5">
                        <span>{{ $laporan->kategoriLaporan->icon ?? '📋' }}</span>
                        <span class="font-medium text-slate-800">{{ $laporan->kategoriLaporan->nama_kategori ?? '-' }}</span>
                    </div>
                </div>
                <div>
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Tanggal Lapor</p>
                    <p class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($laporan->tanggal_lapor)->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Wilayah</p>
                    <p class="font-medium text-slate-800">{{ $laporan->wilayah->nama_wilayah ?? '-' }}</p>
                </div>
            </div>
            
            <div class="border-t border-slate-100 pt-4 mb-4">
                <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Deskripsi Laporan</p>
                <p class="text-slate-700 leading-relaxed text-sm">{{ $laporan->deskripsi }}</p>
            </div>

            @if($laporan->foto)
            <div class="border-t border-slate-100 pt-4">
                <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Foto Lampiran</p>
                <img src="{{ asset('storage/' . $laporan->foto) }}" class="rounded-xl border border-slate-200 max-h-60 object-contain bg-slate-50">
            </div>
            @endif
        </div>

        {{-- Bukti Penyelesaian dari Petugas --}}
        @if($laporan->penugasan && $laporan->penugasan->penyelesaian)
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 bg-emerald-50 border-b border-emerald-100 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                <h2 class="text-emerald-800 font-bold" style="font-size:1.05rem">Bukti Penyelesaian Petugas</h2>
            </div>
            <div class="p-5">
                <div class="flex flex-col sm:flex-row gap-5">
                    <img src="{{ asset('storage/' . $laporan->penugasan->penyelesaian->foto_bukti) }}"
                         class="w-full sm:w-1/2 rounded-xl object-contain max-h-64 border border-slate-200 bg-slate-50">
                    <div class="w-full sm:w-1/2 space-y-4">
                        <div>
                            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Tanggal Selesai</p>
                            <p class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($laporan->penugasan->penyelesaian->tanggal_selesai)->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Petugas Lapangan</p>
                            <p class="font-medium text-slate-800">{{ $laporan->penugasan->petugas->name ?? '-' }}</p>
                        </div>
                        @if($laporan->penugasan->penyelesaian->keterangan)
                        <div>
                            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Catatan Perbaikan</p>
                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-sm text-slate-700">
                                {{ $laporan->penugasan->penyelesaian->keterangan }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Riwayat Ulasan (jika sudah dikonfirmasi) --}}
        @if($laporan->ulasan)
        <div class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 bg-amber-50 border-b border-amber-100 flex items-center gap-2">
                <i data-lucide="star" class="w-5 h-5 text-amber-600"></i>
                <h2 class="text-amber-800 font-bold" style="font-size:1.05rem">Ulasan Anda</h2>
            </div>
            <div class="p-5">
                <div class="flex items-center gap-1 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <span style="font-size:1.5rem;color:{{ $i <= $laporan->ulasan->rating ? '#f59e0b' : '#d1d5db' }}">★</span>
                    @endfor
                    <span class="ml-2 font-bold text-amber-700 text-lg">{{ $laporan->ulasan->rating }}/5</span>
                </div>
                @if($laporan->ulasan->komentar)
                    <p class="text-slate-700 italic bg-white border border-slate-100 p-4 rounded-xl shadow-sm text-sm">"{{ $laporan->ulasan->komentar }}"</p>
                @endif
                <p class="text-slate-400 text-xs mt-3">Diberikan pada: {{ \Carbon\Carbon::parse($laporan->ulasan->tanggal_ulasan)->format('d M Y') }}</p>
            </div>
        </div>
        @endif

    </div>

    {{-- ═══ KANAN: STATUS & KONFIRMASI ═══ --}}
    <div class="space-y-5">
        
        {{-- Peta Lokasi --}}
        <div class="bg-white rounded-2xl border border-sky-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-sky-600"></i>
                <p class="text-sky-700 font-semibold" style="font-size:0.9rem">Lokasi Laporan</p>
            </div>
            <div class="p-4">
                <p class="text-sm text-slate-700 mb-3">{{ $laporan->alamat }}</p>
                @if($laporan->mapLokasi)
                    <div id="map" class="w-full h-48 rounded-xl border border-slate-200 relative z-0"></div>
                    <script>
                        setTimeout(function(){
                            var map = L.map('map').setView([{{ $laporan->mapLokasi->latitude }}, {{ $laporan->mapLokasi->longitude }}], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                            L.marker([{{ $laporan->mapLokasi->latitude }}, {{ $laporan->mapLokasi->longitude }}]).addTo(map);
                        }, 200);
                    </script>
                @endif
            </div>
        </div>

        {{-- Form Konfirmasi (Hanya muncul jika status laporan menunggu_konfirmasi) --}}
        @if($laporan->status === 'menunggu_konfirmasi')
        <div class="bg-white rounded-2xl border border-blue-200 shadow-sm overflow-hidden" x-data="{ rating: 0, hoverRating: 0 }">
            <div class="px-5 py-3.5 bg-blue-600 flex items-center gap-2">
                <i data-lucide="check-square" class="w-5 h-5 text-white"></i>
                <h2 class="text-white font-bold" style="font-size:1.05rem">Konfirmasi Penyelesaian</h2>
            </div>
            <form action="{{ route('warga.laporan.konfirmasi', $laporan->id) }}" method="POST" class="p-5">
                @csrf
                <p class="text-sm text-slate-600 mb-4 text-center">Silakan konfirmasi jika perbaikan telah benar-benar selesai dan berikan rating untuk kinerja petugas.</p>

                {{-- Star Rating --}}
                <div class="flex justify-center gap-1 mb-4">
                    <input type="hidden" name="rating" x-model="rating">
                    <template x-for="star in 5">
                        <button type="button" class="focus:outline-none transition-transform hover:scale-110"
                                @click="rating = star"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0">
                            <span x-text="'★'" style="font-size:2.5rem;line-height:1"
                                  :class="(hoverRating >= star || (!hoverRating && rating >= star)) ? 'text-amber-400 drop-shadow-sm' : 'text-slate-200'">
                            </span>
                        </button>
                    </template>
                </div>
                @error('rating')
                    <p class="text-red-500 text-center text-xs mb-3">{{ $message }}</p>
                @enderror

                {{-- Komentar --}}
                <div class="mb-5">
                    <label class="block text-slate-500 text-xs font-semibold uppercase mb-1.5">Ulasan / Komentar (Opsional)</label>
                    <textarea name="komentar" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Bagaimana hasil perbaikannya?"></textarea>
                    @error('komentar')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Action (Selesai / Revisi) --}}
                <div class="flex gap-3">
                    <button type="submit" name="action" value="revisi" class="w-1/3 flex justify-center items-center gap-2 bg-rose-100 hover:bg-rose-200 text-rose-700 font-semibold py-3 rounded-xl transition-colors shadow-sm text-sm" onclick="return confirm('Yakin ingin meminta revisi?')">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Revisi
                    </button>
                    <button type="submit" name="action" value="selesai" class="w-2/3 flex justify-center items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition-colors shadow-sm text-sm" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan laporan ini?')">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> Selesai
                    </button>
                </div>
            </form>
        </div>
        @elseif($laporan->status === 'selesai' && !$laporan->ulasan)
        <div class="bg-white rounded-2xl border border-amber-200 shadow-sm overflow-hidden" x-data="{ rating: 0, hoverRating: 0 }">
            <div class="px-5 py-3.5 bg-amber-500 flex items-center gap-2">
                <i data-lucide="star" class="w-5 h-5 text-white"></i>
                <h2 class="text-white font-bold" style="font-size:1.05rem">Beri Ulasan Pelayanan</h2>
            </div>
            <form action="{{ route('warga.ulasan.store', $laporan->id) }}" method="POST" class="p-5">
                @csrf
                <p class="text-sm text-slate-600 mb-4 text-center">Laporan sudah selesai, tapi Anda belum memberikan ulasan. Yuk, beritahu kami pengalaman Anda!</p>

                {{-- Star Rating --}}
                <div class="flex justify-center gap-1 mb-4">
                    <input type="hidden" name="rating" x-model="rating">
                    <template x-for="star in 5">
                        <button type="button" class="focus:outline-none transition-transform hover:scale-110"
                                @click="rating = star"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0">
                            <span x-text="'★'" style="font-size:2.5rem;line-height:1"
                                  :class="(hoverRating >= star || (!hoverRating && rating >= star)) ? 'text-amber-400 drop-shadow-sm' : 'text-slate-200'">
                            </span>
                        </button>
                    </template>
                </div>
                @error('rating')
                    <p class="text-red-500 text-center text-xs mb-3">{{ $message }}</p>
                @enderror

                {{-- Komentar --}}
                <div class="mb-5">
                    <label class="block text-slate-500 text-xs font-semibold uppercase mb-1.5">Komentar (Opsional)</label>
                    <textarea name="komentar" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none" placeholder="Bagaimana pelayanannya?"></textarea>
                </div>

                <button type="submit" class="w-full flex justify-center items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl transition-colors shadow-sm text-sm">
                    <i data-lucide="send" class="w-4 h-4"></i> Kirim Ulasan
                </button>
            </form>
        </div>
        @elseif($laporan->status === 'dikerjakan')
        <div class="bg-blue-50 rounded-2xl border border-blue-100 p-5 text-center">
            <i data-lucide="wrench" class="w-8 h-8 text-blue-400 mx-auto mb-2"></i>
            <p class="text-blue-800 font-medium text-sm">Laporan Sedang Dikerjakan</p>
            <p class="text-blue-600 text-xs mt-1">Menunggu petugas lapangan menyelesaikan pekerjaannya dan mengunggah bukti.</p>
        </div>
        @endif

    </div>
</div>

<script>
    lucide.createIcons();
    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session("success") }}', timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
    @endif
</script>
@endsection
