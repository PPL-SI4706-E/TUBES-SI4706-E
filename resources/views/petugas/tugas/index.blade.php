@extends('layouts.petugas')
@section('title', 'Daftar Tugas')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

@php
    $statusBadge = [
        'Ditugaskan' => 'bg-sky-100 text-sky-700',
        'Menuju Lokasi' => 'bg-blue-100 text-blue-700',
        'Sedang Dikerjakan' => 'bg-violet-100 text-violet-700',
        'Menunggu Konfirmasi' => 'bg-orange-100 text-orange-700',
        'Selesai' => 'bg-emerald-100 text-emerald-700',
    ];
    $statusLabel = [
        'Ditugaskan' => 'Ditugaskan',
        'Menuju Lokasi' => 'Menuju Lokasi',
        'Sedang Dikerjakan' => 'Sedang Dikerjakan',
        'Menunggu Konfirmasi' => 'Menunggu Konfirmasi Warga',
        'Selesai' => 'Selesai',
    ];
    $steps = ['Ditugaskan', 'Menuju Lokasi', 'Sedang Dikerjakan', 'Menunggu Konfirmasi', 'Selesai'];
    $taskIcons = [
        'Air Keruh / Berbau' => 'droplets',
        'Pipa Bocor' => 'wrench',
        'Pipa Tersumbat' => 'ban',
        'Sambungan Baru' => 'house',
    ];
@endphp

<div class="mx-auto max-w-[1080px]">
    <div class="mb-8">
        <h1 class="text-[2rem] font-bold tracking-tight text-sky-900 sm:text-[2.15rem]">Daftar Tugas</h1>
        <p class="mt-2 text-[15px] text-slate-500">Laporan yang ditugaskan kepada Anda beserta lokasi rumah pelanggan</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-[26px] border border-sky-100 bg-white px-6 py-5 shadow-sm shadow-sky-100/50">
            <p class="text-[15px] text-slate-500">Tugas Aktif</p>
            <p class="mt-2 text-[2.15rem] font-bold leading-none text-sky-700">{{ $tugasAktif->count() }}</p>
        </div>
        <div class="rounded-[26px] border border-emerald-100 bg-emerald-50/80 px-6 py-5 shadow-sm shadow-emerald-100/50">
            <p class="text-[15px] text-slate-500">Telah Selesai</p>
            <p class="mt-2 text-[2.15rem] font-bold leading-none text-emerald-700">{{ $riwayatSelesai->count() }}</p>
        </div>
    </div>

    <section class="mt-8">
        <h2 class="text-[1.95rem] font-bold text-sky-900">Tugas Aktif</h2>

        <div class="mt-4 space-y-5">
            @forelse($tugasAktif as $penugasan)
                @php
                    $laporan = $penugasan->laporan;
                    $lokasi = $laporan?->mapLokasi;
                    $currentStep = array_search($penugasan->status_tugas, $steps, true);
                    $currentStep = $currentStep === false ? 0 : $currentStep;
                    $statusText = $statusLabel[$penugasan->status_tugas] ?? $penugasan->status_tugas;
                    $rating = $laporan?->ulasan?->rating;
                @endphp

                <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="overflow-hidden rounded-[28px] border border-sky-100 bg-white shadow-sm shadow-sky-100/50">
                    <button type="button" @click="open = !open" class="w-full px-5 py-5 text-left sm:px-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="text-[15px] font-bold text-sky-700 sm:text-[16px]">#{{ $laporan?->id }}</span>
                                    <i data-lucide="{{ $taskIcons[$laporan?->kategoriLaporan?->nama_kategori ?? ''] ?? 'droplets' }}" class="h-6 w-6 text-sky-400"></i>
                                    <span class="text-[1.05rem] font-semibold text-sky-900 sm:text-[1.15rem]">{{ $laporan?->kategoriLaporan?->nama_kategori ?? 'Laporan' }}</span>
                                    <span class="rounded-full px-3 py-1 text-[12px] font-semibold {{ $statusBadge[$penugasan->status_tugas] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $statusText }}
                                    </span>
                                </div>

                                <div class="mt-3 flex items-center gap-2 text-[14px] text-slate-600">
                                    <i data-lucide="map-pin" class="h-4 w-4 shrink-0 text-sky-500"></i>
                                    <span class="truncate">{{ $laporan?->alamat ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-[13px] text-slate-400 lg:pt-1">
                                <span>{{ optional($penugasan->tanggal_penugasan)->format('Y-m-d') }}</span>
                                <i data-lucide="chevron-down" class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </div>
                        </div>

                        <div class="mt-5">
                            <div class="grid grid-cols-5 gap-1.5">
                                @foreach($steps as $index => $step)
                                    <div class="h-[6px] rounded-full {{ $index <= $currentStep ? 'bg-sky-500' : 'bg-sky-100' }}"></div>
                                @endforeach
                            </div>
                            <div class="mt-2 grid grid-cols-5 text-center text-[10px] leading-tight text-slate-400 sm:text-[11px]">
                                @foreach($steps as $step)
                                    <span>{{ $statusLabel[$step] ?? $step }}</span>
                                @endforeach
                            </div>
                        </div>
                    </button>

                    <div x-show="open" x-transition.opacity class="border-t border-slate-100 px-4 py-5 sm:px-5">
                        <div class="space-y-4 rounded-[24px] bg-[#f4faff] p-4 sm:p-5">
                            <div class="rounded-[22px] bg-[#eaf5ff] px-4 py-4">
                                <p class="text-[14px] font-semibold text-sky-700">Info Pelanggan</p>
                                <div class="mt-3 flex items-center gap-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-200 text-[15px] font-bold text-sky-700">
                                        {{ strtoupper(substr($laporan?->user?->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-[15px] font-semibold text-slate-800">{{ $laporan?->user?->name ?? 'Pelanggan' }}</p>
                                        <div class="mt-1 flex items-center gap-1.5 text-[12px] text-slate-500">
                                            <i data-lucide="phone" class="h-3.5 w-3.5"></i>
                                            <span>{{ $laporan?->user?->phone ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[22px] bg-[#eaf5ff] px-4 py-4">
                                <div class="flex items-center gap-2 text-sky-700">
                                    <i data-lucide="house" class="h-4 w-4"></i>
                                    <p class="text-[14px] font-semibold">Alamat Rumah Lengkap</p>
                                </div>
                                <p class="mt-3 text-[15px] leading-7 text-slate-800">{{ $laporan?->alamat ?? '-' }}</p>
                                <p class="mt-1 text-[13px] text-slate-500">{{ $laporan?->wilayah?->nama_wilayah ?? '-' }}</p>
                                @if($lokasi)
                                    <p class="mt-2 text-[12px] text-sky-400">{{ number_format((float) $lokasi->latitude, 6) }}, {{ number_format((float) $lokasi->longitude, 6) }}</p>
                                @endif
                            </div>

                            @if($lokasi)
                                <div>
                                    <div id="map-{{ $penugasan->id }}" class="h-[260px] w-full rounded-[22px] border border-sky-100 sm:h-[290px]"></div>
                                    <script>
                                        setTimeout(function () {
                                            const mapElement = document.getElementById('map-{{ $penugasan->id }}');
                                            if (!mapElement || typeof L === 'undefined') {
                                                return;
                                            }

                                            if (!mapElement.dataset.ready) {
                                                mapElement.dataset.ready = 'true';
                                                const map = L.map('map-{{ $penugasan->id }}').setView([{{ $lokasi->latitude }}, {{ $lokasi->longitude }}], 15);
                                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                    attribution: '&copy; OpenStreetMap'
                                                }).addTo(map);
                                                L.marker([{{ $lokasi->latitude }}, {{ $lokasi->longitude }}]).addTo(map);
                                                mapElement._leafletMap = map;
                                            }

                                            setTimeout(() => {
                                                if (mapElement._leafletMap) {
                                                    mapElement._leafletMap.invalidateSize();
                                                }
                                            }, 250);
                                        }, 120);
                                    </script>

                                    <a href="https://www.google.com/maps?q={{ $lokasi->latitude }},{{ $lokasi->longitude }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="mt-4 flex h-12 w-full items-center justify-center gap-2 rounded-[18px] bg-blue-600 px-4 text-[15px] font-semibold text-white hover:bg-blue-700">
                                        <i data-lucide="navigation" class="h-4 w-4"></i>
                                        Buka di Google Maps
                                    </a>
                                </div>
                            @endif

                            <div>
                                <p class="text-[14px] font-semibold text-slate-700">Deskripsi Masalah:</p>
                                <div class="mt-2 rounded-[18px] bg-[#eaf5ff] px-4 py-4 text-[14px] leading-7 text-slate-700">
                                    {{ $laporan?->deskripsi ?? '-' }}
                                </div>
                            </div>

                            <div class="rounded-[18px] border border-blue-200 bg-blue-50 px-4 py-4">
                                <p class="text-[14px] font-semibold text-blue-700">Catatan Admin:</p>
                                <p class="mt-2 text-[14px] leading-7 text-blue-800">{{ $laporan?->catatan_admin ?: 'Belum ada catatan tambahan dari admin.' }}</p>
                            </div>

                            <div class="flex flex-wrap gap-2.5">
                                @foreach($steps as $step)
                                    <form action="{{ route('petugas.tugas.status', $penugasan) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status_tugas" value="{{ $step }}">
                                        <button type="submit"
                                                class="rounded-full px-4 py-2 text-[12px] font-semibold transition {{ $penugasan->status_tugas === $step ? 'bg-sky-700 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-sky-300 hover:text-sky-700' }}">
                                            {{ $statusLabel[$step] ?? $step }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>

                            @if($penugasan->status_tugas === 'Sedang Dikerjakan')
                                <form action="{{ route('petugas.tugas.bukti', $penugasan) }}" method="POST" enctype="multipart/form-data" class="rounded-[20px] border border-emerald-200 bg-white px-4 py-4">
                                    @csrf
                                    <label class="block text-[13px] font-semibold text-slate-600">Upload bukti penyelesaian</label>
                                    <input type="file" name="foto_bukti" class="mt-3 block w-full rounded-xl border border-slate-200 px-3 py-3 text-sm text-slate-600">
                                    <label class="mt-4 block text-[13px] font-semibold text-slate-600">Catatan perbaikan</label>
                                    <textarea name="keterangan" rows="3" class="mt-2 block w-full rounded-xl border border-slate-200 px-3 py-3 text-sm text-slate-600" placeholder="Tambahkan ringkasan perbaikan di lapangan...">{{ $penugasan->penyelesaian?->keterangan }}</textarea>
                                    <button type="submit" class="mt-4 flex h-12 w-full items-center justify-center gap-2 rounded-[18px] bg-emerald-500 px-4 text-[15px] font-semibold text-white hover:bg-emerald-600">
                                        <i data-lucide="check-circle" class="h-4 w-4"></i>
                                        Selesaikan & Upload Bukti
                                    </button>
                                </form>
                            @elseif($penugasan->status_tugas === 'Menunggu Konfirmasi')
                                <div class="rounded-[18px] border border-orange-200 bg-orange-50 px-4 py-4 text-[14px] text-orange-700">
                                    Tugas ini sedang menunggu konfirmasi warga. Bukti sudah diunggah dan laporan belum ditutup.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-[28px] border border-dashed border-sky-200 bg-white px-6 py-10 text-center text-sm text-slate-400">
                    Belum ada tugas aktif.
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-10">
        <h2 class="text-[1.95rem] font-bold text-sky-900">Riwayat Selesai</h2>

        <div class="mt-4 space-y-4">
            @forelse($riwayatSelesai as $penugasan)
                @php
                    $riwayatLaporan = $penugasan->laporan;
                    $catatanSelesai = $penugasan->penyelesaian?->keterangan ?: 'Tugas telah selesai ditangani.';
                    $rating = $riwayatLaporan?->ulasan?->rating;
                @endphp
                <div class="rounded-[26px] border border-emerald-100 bg-white px-5 py-5 shadow-sm shadow-emerald-100/40">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex min-w-0 gap-3">
                            <div class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-500">
                                <i data-lucide="check-circle-2" class="h-5 w-5"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[15px] font-semibold text-slate-800">#{{ $riwayatLaporan?->id }} - {{ $riwayatLaporan?->kategoriLaporan?->nama_kategori ?? 'Laporan' }}</p>
                                <p class="mt-1 truncate text-[13px] text-slate-500">{{ $riwayatLaporan?->alamat ?? '-' }}</p>
                                <p class="mt-3 text-[13px] leading-6 text-slate-500">Catatan: {{ $catatanSelesai }}</p>
                            </div>
                        </div>

                        <div class="shrink-0 text-left sm:text-right">
                            @if($rating)
                                <div class="mb-2 flex justify-start gap-1 text-amber-400 sm:justify-end">
                                    @for($i = 0; $i < 5; $i++)
                                        <i data-lucide="star" class="h-3.5 w-3.5 {{ $i < $rating ? 'fill-current' : '' }}"></i>
                                    @endfor
                                </div>
                            @endif
                            <p class="text-[13px] text-slate-400">{{ optional($penugasan->updated_at)->format('Y-m-d') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-[28px] border border-dashed border-emerald-200 bg-white px-6 py-10 text-center text-sm text-slate-400">
                    Belum ada riwayat tugas selesai.
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
