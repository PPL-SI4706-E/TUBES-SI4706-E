@extends('layouts.admin')
@section('title', 'Detail Laporan #' . $laporan->id)

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

{{-- Breadcrumb --}}
<div class="mb-5">
    <a href="{{ route('admin.laporan.index') }}"
       class="inline-flex items-center gap-1.5 text-sky-600 hover:text-sky-800 text-sm font-medium transition-colors mb-3">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Laporan
    </a>
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-bold text-sky-900">Detail Laporan <span class="text-sky-500">#{{ $laporan->id }}</span></h1>
        @php
            $statusMap = [
                'pending'    => ['bg-amber-100 text-amber-800 border-amber-200', 'Menunggu Validasi'],
                'diterima'   => ['bg-blue-100 text-blue-800 border-blue-200',    'Diterima'],
                'ditolak'    => ['bg-red-100 text-red-800 border-red-200',        'Ditolak'],
                'dikerjakan' => ['bg-cyan-100 text-cyan-800 border-cyan-200',     'Dikerjakan'],
                'selesai'    => ['bg-emerald-100 text-emerald-800 border-emerald-200','Selesai'],
            ];
            [$sCls, $sLabel] = $statusMap[$laporan->status] ?? ['bg-slate-100 text-slate-700 border-slate-200', $laporan->status];
        @endphp
        <span class="px-3 py-1 rounded-full border text-xs font-bold {{ $sCls }}">{{ $sLabel }}</span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ═══════════ LEFT COLUMN ═══════════ --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Card Detail Laporan --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Header --}}
            <div class="p-6 border-b border-slate-100 flex items-start gap-4">
                <div class="w-12 h-12 bg-sky-50 rounded-xl flex items-center justify-center shrink-0 text-2xl border border-sky-100">
                    {{ $laporan->kategoriLaporan->icon ?? '📋' }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">{{ $laporan->judul ?? 'Laporan Masalah' }}</h2>
                    <p class="text-slate-500 text-sm mt-0.5">
                        Kategori: <span class="font-semibold text-slate-700">{{ $laporan->kategoriLaporan->nama_kategori ?? 'Lainnya' }}</span>
                    </p>
                </div>
            </div>

            <div class="p-6 space-y-6">

                {{-- Deskripsi --}}
                <div>
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Deskripsi Laporan
                    </h3>
                    <div class="bg-slate-50 p-4 rounded-lg text-slate-700 whitespace-pre-wrap text-sm leading-relaxed border border-slate-100">
                        {{ $laporan->deskripsi }}
                    </div>
                </div>

                {{-- Lokasi --}}
                <div>
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Lokasi &amp; Alamat
                    </h3>
                    <div class="bg-slate-50 p-4 rounded-lg flex items-start gap-3 border border-slate-100 mb-3">
                        <div class="w-6 h-6 rounded-full bg-white flex items-center justify-center shadow-sm shrink-0 text-sky-600 mt-0.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm text-slate-800">{{ $laporan->alamat }}</p>
                            <p class="text-xs text-slate-500 mt-1">
                                Wilayah: <span class="font-medium">{{ $laporan->wilayah->nama_wilayah ?? '-' }}</span>
                            </p>
                        </div>
                    </div>

                    @if($laporan->mapLokasi)
                        <div id="laporan-map" class="w-full h-[260px] rounded-xl border border-slate-200 shadow-sm z-10"></div>
                        <script>
                            setTimeout(function () {
                                if (typeof L !== 'undefined') {
                                    var lmap = L.map('laporan-map').setView(
                                        [{{ $laporan->mapLokasi->latitude }}, {{ $laporan->mapLokasi->longitude }}], 15
                                    );
                                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                        attribution: '&copy; CARTO', maxZoom: 19
                                    }).addTo(lmap);

                                    // Marker dengan warna sesuai status
                                    var color = {
                                        pending:    '#F59E0B',
                                        diterima:   '#3B82F6',
                                        ditolak:    '#EF4444',
                                        dikerjakan: '#06B6D4',
                                        selesai:    '#10B981'
                                    }['{{ $laporan->status }}'] || '#64748B';

                                    var iconHtml = `<div style="
                                        width:18px; height:18px; border-radius:50%;
                                        background:${color}; border:3px solid white;
                                        box-shadow:0 2px 6px rgba(0,0,0,.35);
                                    "></div>`;
                                    var customIcon = L.divIcon({
                                        html: iconHtml,
                                        className: '',
                                        iconSize: [18, 18],
                                        iconAnchor: [9, 9]
                                    });

                                    L.marker(
                                        [{{ $laporan->mapLokasi->latitude }}, {{ $laporan->mapLokasi->longitude }}],
                                        { icon: customIcon }
                                    ).addTo(lmap).bindPopup(
                                        '<b>#{{ $laporan->id }}</b><br>{{ addslashes($laporan->alamat) }}'
                                    ).openPopup();

                                    setTimeout(() => lmap.invalidateSize(), 500);
                                }
                            }, 100);
                        </script>
                    @endif
                </div>

                {{-- Foto --}}
                @if($laporan->foto)
                <div>
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <i data-lucide="camera" class="w-3.5 h-3.5"></i> Foto Bukti
                    </h3>
                    <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-50 flex items-center justify-center">
                        <img src="{{ asset('storage/' . $laporan->foto) }}" alt="Foto Laporan"
                             class="max-h-[380px] w-auto object-contain">
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ═══════════ RIGHT COLUMN ═══════════ --}}
    <div class="space-y-5">

        {{-- Info Pelapor --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                Informasi Pelapor
            </h3>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 bg-sky-100 rounded-full flex items-center justify-center text-sky-700 font-bold text-base border border-sky-200 shrink-0">
                    {{ strtoupper(substr($laporan->user->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-slate-800 text-sm">{{ $laporan->user->name ?? 'Anonim' }}</p>
                    <p class="text-xs text-slate-500">{{ $laporan->user->email ?? '-' }}</p>
                </div>
            </div>
            <div class="space-y-2.5 text-sm">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center border border-slate-100 text-slate-400 shrink-0">
                        <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400">No. Telepon</p>
                        <p class="font-medium text-slate-700 text-xs">{{ $laporan->user->phone ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center border border-slate-100 text-slate-400 shrink-0">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400">Waktu Lapor</p>
                        <p class="font-medium text-slate-700 text-xs">
                            {{ \Carbon\Carbon::parse($laporan->tanggal_lapor)->format('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── FORM VALIDASI (hanya saat status pending) ── --}}
        @if($laporan->status === 'pending')
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
             x-data="{ action: '{{ old('status', '') }}' }">

            {{-- Header --}}
            <div class="p-4 bg-amber-50 border-b border-amber-100 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-amber-900 text-sm">Validasi Laporan</h3>
                    <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                        Pilih salah satu tindak lanjut untuk laporan ini.
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.laporan.validasi', $laporan->id) }}" method="POST" class="p-5">
                @csrf

                {{-- 3 Pilihan Aksi --}}
                <div class="space-y-2 mb-4">

                    {{-- Opsi 1: Terima → Penanganan Lapangan --}}
                    <label class="cursor-pointer block relative">
                        <input type="radio" name="status" value="diterima" class="peer sr-only" x-model="action">
                        <div class="flex items-center gap-3 p-3 rounded-lg border-2 border-slate-200
                                    peer-checked:border-blue-500 peer-checked:bg-blue-50
                                    hover:bg-slate-50 transition-all">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0
                                        peer-checked:bg-blue-500"
                                 :class="action === 'diterima' ? 'bg-blue-500 text-white' : 'bg-slate-100 text-slate-400'">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-sm"
                                   :class="action === 'diterima' ? 'text-blue-800' : 'text-slate-700'">
                                    Terima — Penanganan Lapangan
                                </p>
                                <p class="text-xs text-slate-400">Petugas akan diturunkan ke lokasi.</p>
                            </div>
                        </div>
                    </label>

                    {{-- Opsi 2: Solusi Virtual --}}
                    <label class="cursor-pointer block relative">
                        <input type="radio" name="status" value="selesai" class="peer sr-only" x-model="action">
                        <div class="flex items-center gap-3 p-3 rounded-lg border-2 border-slate-200
                                    peer-checked:border-emerald-500 peer-checked:bg-emerald-50
                                    hover:bg-slate-50 transition-all">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                 :class="action === 'selesai' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400'">
                                <i data-lucide="monitor-check" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-sm"
                                   :class="action === 'selesai' ? 'text-emerald-800' : 'text-slate-700'">
                                    Solusi Virtual
                                </p>
                                <p class="text-xs text-slate-400">Diselesaikan tanpa menurunkan petugas lapangan.</p>
                            </div>
                        </div>
                    </label>

                    {{-- Opsi 3: Tolak --}}
                    <label class="cursor-pointer block relative">
                        <input type="radio" name="status" value="ditolak" class="peer sr-only" x-model="action">
                        <div class="flex items-center gap-3 p-3 rounded-lg border-2 border-slate-200
                                    peer-checked:border-red-500 peer-checked:bg-red-50
                                    hover:bg-slate-50 transition-all">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                 :class="action === 'ditolak' ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-400'">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-sm"
                                   :class="action === 'ditolak' ? 'text-red-800' : 'text-slate-700'">
                                    Tolak Laporan
                                </p>
                                <p class="text-xs text-slate-400">Laporan tidak dapat ditindaklanjuti.</p>
                            </div>
                        </div>
                    </label>
                </div>

                @error('status')
                    <p class="text-red-500 text-xs mb-3 font-medium -mt-1">{{ $message }}</p>
                @enderror

                {{-- Catatan — wajib untuk semua opsi --}}
                <div class="mb-4" x-show="action !== ''">
                    <label class="block text-xs font-semibold mb-1.5"
                           :class="{
                               'text-blue-700'   : action === 'diterima',
                               'text-emerald-700': action === 'selesai',
                               'text-red-700'    : action === 'ditolak',
                               'text-slate-700'  : action === ''
                           }">
                        <span x-show="action === 'diterima'">Catatan Penanganan <span class="text-red-500">*</span></span>
                        <span x-show="action === 'selesai'">Solusi / Penjelasan Virtual <span class="text-red-500">*</span></span>
                        <span x-show="action === 'ditolak'">Alasan Penolakan <span class="text-red-500">*</span></span>
                    </label>
                    <textarea name="catatan_admin" rows="3"
                              :placeholder="action === 'diterima' ? 'Tambahkan catatan untuk petugas…'
                                          : action === 'selesai' ? 'Jelaskan solusi yang diberikan secara virtual…'
                                          : 'Jelaskan alasan penolakan laporan ini…'"
                              class="w-full rounded-lg border-slate-300 shadow-sm text-sm p-3 focus:outline-none transition
                                     focus:ring-2"
                              :class="{
                                  'focus:border-blue-400 focus:ring-blue-100'   : action === 'diterima',
                                  'focus:border-emerald-400 focus:ring-emerald-100': action === 'selesai',
                                  'focus:border-red-400 focus:ring-red-100'     : action === 'ditolak'
                              }"
                              required>{{ old('catatan_admin') }}</textarea>
                    @error('catatan_admin')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full text-white font-medium py-2.5 rounded-lg transition-all flex items-center justify-center gap-2
                               disabled:opacity-40 disabled:cursor-not-allowed"
                        :class="{
                            'bg-blue-600 hover:bg-blue-700'     : action === 'diterima',
                            'bg-emerald-600 hover:bg-emerald-700': action === 'selesai',
                            'bg-red-600 hover:bg-red-700'       : action === 'ditolak',
                            'bg-slate-300'                      : action === ''
                        }"
                        :disabled="!action">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span x-show="action === 'diterima'">Terima &amp; Tugaskan Petugas</span>
                    <span x-show="action === 'selesai'">Tandai Selesai (Solusi Virtual)</span>
                    <span x-show="action === 'ditolak'">Tolak Laporan</span>
                    <span x-show="action === ''"       class="text-slate-500">Pilih Aksi Terlebih Dahulu</span>
                </button>
            </form>
        </div>

        {{-- ── STATUS BOX (bukan pending) ── --}}
        @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                Status Terkini
            </h3>

            @if($laporan->status === 'diterima')
                <div class="flex items-start gap-3 bg-blue-50 p-4 rounded-lg border border-blue-100 text-blue-700">
                    <i data-lucide="check-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-bold text-sm">Laporan Diterima</p>
                        <p class="text-xs text-blue-600/80 mt-1">Menunggu penugasan petugas lapangan.</p>
                        @if($laporan->catatan_admin)
                            <div class="mt-2 bg-white/70 p-2.5 rounded-lg border border-blue-100 text-xs text-blue-800">
                                <span class="font-semibold">Catatan:</span> {{ $laporan->catatan_admin }}
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($laporan->status === 'ditolak')
                <div class="flex items-start gap-3 bg-red-50 p-4 rounded-lg border border-red-100 text-red-700">
                    <i data-lucide="x-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-bold text-sm">Laporan Ditolak</p>
                        @if($laporan->catatan_admin)
                            <div class="mt-2 bg-white/70 p-2.5 rounded-lg border border-red-100 text-xs text-red-800">
                                <span class="font-semibold">Alasan:</span> {{ $laporan->catatan_admin }}
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($laporan->status === 'selesai')
                <div class="flex items-start gap-3 bg-emerald-50 p-4 rounded-lg border border-emerald-100 text-emerald-700">
                    <i data-lucide="monitor-check" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-bold text-sm">Selesai (Solusi Virtual)</p>
                        @if($laporan->catatan_admin)
                            <div class="mt-2 bg-white/70 p-2.5 rounded-lg border border-emerald-100 text-xs text-emerald-800">
                                <span class="font-semibold">Solusi:</span> {{ $laporan->catatan_admin }}
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($laporan->status === 'dikerjakan')
                <div class="flex items-start gap-3 bg-cyan-50 p-4 rounded-lg border border-cyan-100 text-cyan-700">
                    <i data-lucide="hammer" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-bold text-sm">Sedang Dikerjakan</p>
                        <p class="text-xs text-cyan-600/80 mt-1">Petugas lapangan sedang menangani laporan ini.</p>
                    </div>
                </div>
            @endif

            @if($laporan->status === 'diterima')
            <div class="mt-4 pt-4 border-t border-slate-100">
                <a href="#"
                   class="w-full flex items-center justify-center gap-2 bg-sky-100 text-sky-400 font-medium py-2 rounded-lg text-sm cursor-not-allowed select-none"
                   title="Fitur penugasan (PBI berikutnya)">
                    <i data-lucide="user-plus" class="w-4 h-4"></i> Tugaskan Petugas
                </a>
                <p class="text-[10px] text-slate-400 text-center mt-1.5">Tersedia pada PBI berikutnya.</p>
            </div>
            @endif
        </div>
        @endif

    </div>{{-- end right column --}}
</div>
@endsection
