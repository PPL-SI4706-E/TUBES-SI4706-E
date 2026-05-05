@extends('layouts.warga')
@section('title', 'Riwayat Laporan')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<div>
    <h1 class="text-sky-900 mb-1" style="font-size:1.5rem;font-weight:700">Riwayat Laporan</h1>
    <p class="text-slate-500 mb-6" style="font-size:0.85rem">Pantau status laporan Anda. Konfirmasi penyelesaian dan berikan feedback.</p>

    {{-- Summary Cards --}}
    @php
        $total = $laporans->count();
        $menunggu = $laporans->whereIn('status', ['pending', 'diterima'])->count();
        $dikerjakan = $laporans->whereIn('status', ['dikerjakan'])->count();
        $selesai = $laporans->where('status', 'selesai')->count();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @foreach([
            ['label' => 'Total', 'value' => $total, 'color' => 'bg-sky-50 text-sky-700'],
            ['label' => 'Menunggu', 'value' => $menunggu, 'color' => 'bg-amber-50 text-amber-700'],
            ['label' => 'Dikerjakan', 'value' => $dikerjakan, 'color' => 'bg-cyan-50 text-cyan-700'],
            ['label' => 'Selesai', 'value' => $selesai, 'color' => 'bg-emerald-50 text-emerald-700'],
        ] as $s)
            <div class="{{ $s['color'] }} rounded-xl p-4 border border-sky-100">
                <p class="opacity-70" style="font-size:0.78rem">{{ $s['label'] }}</p>
                <p style="font-size:1.5rem;font-weight:700">{{ $s['value'] }}</p>
            </div>
        @endforeach
    </div>

    @if($laporans->isEmpty())
        <div class="bg-white rounded-xl p-12 text-center border border-sky-100">
            <svg class="w-12 h-12 text-sky-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-slate-500" style="font-size:0.9rem">Belum ada laporan. <a href="{{ route('warga.laporan.create') }}" class="text-sky-600 underline font-semibold">Buat laporan pertama</a></p>
        </div>
    @endif

    {{-- Laporan List --}}
    <div class="space-y-4" x-data="{ expandedId: null, ulasanModalOpen: false, selectedLaporanId: null }">
        @foreach($laporans as $l)
            @php
                $kategori = $l->kategoriLaporan;
                $mapLokasi = $l->mapLokasi;
                $statusColors = [
                    'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                    'diterima' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'ditolak' => 'bg-red-100 text-red-700 border-red-200',
                    'dikerjakan' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
                    'selesai' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                ];
                $statusLabels = [
                    'pending' => 'Menunggu Validasi',
                    'diterima' => 'Divalidasi',
                    'ditolak' => 'Ditolak',
                    'dikerjakan' => 'Sedang Dikerjakan',
                    'selesai' => 'Selesai',
                ];
                $statusSteps = ['pending', 'diterima', 'dikerjakan', 'selesai'];
                $stepIdx = array_search($l->status, $statusSteps);
                if($stepIdx === false) $stepIdx = -1;
            @endphp

            <div class="bg-white rounded-xl border shadow-sm overflow-hidden transition-all {{ $l->status === 'dikerjakan' ? 'border-cyan-300 ring-1 ring-cyan-200' : 'border-sky-100' }}">
                {{-- Header --}}
                <button @click="expandedId = expandedId === {{ $l->id }} ? null : {{ $l->id }}"
                    class="w-full p-5 flex items-center justify-between text-left hover:bg-sky-50/30 transition-colors">
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <div class="shrink-0">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-sky-100" style="font-size:1.2rem">
                                {{ $kategori->icon ?? '📋' }}
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sky-600" style="font-weight:700;font-size:0.83rem">#{{ $l->id }}</span>
                                <span class="text-sky-800" style="font-weight:600;font-size:0.9rem">{{ $kategori->nama_kategori ?? 'Laporan' }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <svg class="w-3 h-3 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <p class="text-slate-500 truncate" style="font-size:0.78rem">{{ $l->alamat }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="hidden sm:flex items-center gap-1 text-slate-400" style="font-size:0.78rem">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $l->tanggal_lapor ? \Carbon\Carbon::parse($l->tanggal_lapor)->format('d M Y') : '-' }}
                            </div>
                            <span class="px-2.5 py-1 rounded-full border {{ $statusColors[$l->status] ?? 'bg-slate-100 text-slate-600' }}" style="font-size:0.75rem;font-weight:600">
                                {{ $statusLabels[$l->status] ?? $l->status }}
                            </span>
                            <svg x-show="expandedId !== {{ $l->id }}" class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            <svg x-show="expandedId === {{ $l->id }}" class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </div>
                    </div>
                </button>

                {{-- Expanded Content --}}
                <div x-show="expandedId === {{ $l->id }}" x-transition class="px-5 pb-5 border-t border-sky-50">
                    {{-- Address details --}}
                    <div class="bg-sky-50 rounded-xl p-4 mt-4">
                        <p class="text-sky-700 mb-2 flex items-center gap-2" style="font-size:0.85rem;font-weight:600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Detail Alamat Rumah
                        </p>
                        <div class="text-slate-700" style="font-size:0.83rem">
                            <span class="text-slate-400">Alamat: </span>{{ $l->alamat }}
                        </div>
                        @if($mapLokasi)
                            <p class="text-slate-400 mt-2 flex items-center gap-1" style="font-size:0.75rem">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                {{ number_format($mapLokasi->latitude, 6) }}, {{ number_format($mapLokasi->longitude, 6) }}
                            </p>
                        @endif
                    </div>

                    {{-- Map --}}
                    @if($mapLokasi)
                        <div class="mt-3">
                            <div id="map-{{ $l->id }}" class="w-full h-[180px] rounded-xl border border-sky-200 z-10"
                                 x-init="$nextTick(() => { 
                                     $watch('expandedId', value => {
                                         if(value === {{ $l->id }}) {
                                             setTimeout(() => {
                                                 let el = document.getElementById('map-{{ $l->id }}');
                                                 if(el && !el._leaflet_id) {
                                                     let m = L.map(el).setView([{{ $mapLokasi->latitude }}, {{ $mapLokasi->longitude }}], 15);
                                                     L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                                         attribution: '&copy; CARTO',
                                                         maxZoom: 19
                                                     }).addTo(m);
                                                     L.marker([{{ $mapLokasi->latitude }}, {{ $mapLokasi->longitude }}]).addTo(m);
                                                     m.invalidateSize();
                                                 }
                                             }, 300);
                                         }
                                     });
                                 })">
                            </div>
                        </div>
                    @endif

                    {{-- Description --}}
                    <div class="mt-4" style="font-size:0.85rem">
                        <p class="text-slate-500 mb-1" style="font-weight:600">Deskripsi Masalah:</p>
                        <p class="text-slate-800 bg-sky-50 p-3 rounded-lg">{{ $l->deskripsi }}</p>
                    </div>

                    {{-- Catatan Admin --}}
                    @if($l->catatan_admin)
                        <div class="mt-3 bg-blue-50 rounded-xl p-3 border border-blue-100" style="font-size:0.83rem">
                            <p class="text-blue-700 mb-1 flex items-center gap-2" style="font-weight:600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Catatan Admin
                            </p>
                            <p class="text-slate-700">{{ $l->catatan_admin }}</p>
                        </div>
                    @endif

                    {{-- Progress Bar --}}
                    @if($l->status !== 'ditolak')
                        <div class="mt-4">
                            <p class="text-slate-500 mb-2" style="font-size:0.83rem;font-weight:600">Progress:</p>
                            <div class="flex items-center gap-1">
                                @foreach($statusSteps as $i => $step)
                                    <div class="flex-1">
                                        <div class="h-2 rounded-full {{ $i <= $stepIdx ? 'bg-sky-500' : 'bg-sky-100' }}"></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-between mt-1">
                                @foreach(['Validasi', 'Divalidasi', 'Dikerjakan', 'Selesai'] as $label)
                                    <span class="text-slate-400" style="font-size:0.58rem">{{ $label }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Rejected info --}}
                    @if($l->status === 'ditolak')
                        <div class="mt-4 bg-red-50 rounded-xl p-4 border border-red-100" style="font-size:0.85rem">
                            <p class="text-red-700 mb-1 flex items-center gap-2" style="font-weight:600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Laporan Ditolak
                            </p>
                            <p class="text-slate-700">{{ $l->catatan_admin ?? 'Admin menolak laporan ini.' }}</p>
                        </div>
                    @endif

                    {{-- Foto Bukti --}}
                    @if($l->foto)
                        <div class="mt-4">
                            <p class="text-slate-500 mb-2" style="font-size:0.83rem;font-weight:600">Foto Bukti:</p>
                            <img src="{{ asset('storage/' . $l->foto) }}" alt="Foto laporan" class="rounded-xl border border-sky-100 max-h-60 object-cover">
                        </div>
                    @endif

                    {{-- Feedback / Ulasan --}}
                    @if($l->status === 'selesai')
                        @php
                            $ulasan = \App\Models\Ulasan::where('laporan_id', $l->id)->first();
                        @endphp
                        <div class="mt-5 border-t border-sky-100 pt-4">
                            @if($ulasan)
                                <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="flex text-amber-400">
                                            @for($i=1; $i<=5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $ulasan->rating ? 'text-amber-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                        </div>
                                        <span class="text-emerald-700" style="font-size:0.83rem;font-weight:600">Feedback Tersimpan</span>
                                    </div>
                                    @if($ulasan->komentar)
                                        <p class="text-slate-700 italic" style="font-size:0.85rem">"{{ $ulasan->komentar }}"</p>
                                    @endif
                                </div>
                            @else
                                <button type="button" @click="selectedLaporanId = {{ $l->id }}; ulasanModalOpen = true" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                    Beri Ulasan Pelayanan
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- Ulasan Modal --}}
        <div x-show="ulasanModalOpen" style="display: none" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="ulasanModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" aria-hidden="true" @click="ulasanModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="ulasanModalOpen" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form :action="`/warga/laporan/${selectedLaporanId}/ulasan`" method="POST" x-data="{ rating: 5, hoverRating: 0 }">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Beri Ulasan Pelayanan</h3>
                            <p class="text-sm text-slate-500 mt-2">Bagaimana pengalaman Anda dengan layanan perbaikan kami?</p>
                            
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Rating</label>
                                <div class="flex items-center gap-1">
                                    <template x-for="i in 5">
                                        <button type="button" 
                                                @mouseenter="hoverRating = i" 
                                                @mouseleave="hoverRating = 0" 
                                                @click="rating = i" 
                                                class="focus:outline-none">
                                            <svg class="w-8 h-8 transition-colors" 
                                                 :class="i <= (hoverRating || rating) ? 'text-amber-400' : 'text-slate-300'" 
                                                 fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                                <input type="hidden" name="rating" x-model="rating">
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Komentar (Opsional)</label>
                                <textarea name="komentar" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:border-sky-500 focus:outline-none text-sm" placeholder="Ceritakan pengalaman Anda..."></textarea>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Kirim Ulasan
                            </button>
                            <button type="button" @click="ulasanModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
