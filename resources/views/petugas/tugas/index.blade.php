@extends('layouts.petugas')
@section('title', 'Daftar Tugas')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div x-data="{}">

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-sky-900 mb-0.5" style="font-size:1.5rem;font-weight:700">Daftar Tugas</h1>
        <p class="text-slate-500" style="font-size:0.85rem">Laporan yang ditugaskan kepada Anda beserta lokasi rumah pelanggan</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-4 mb-7">
        <div class="bg-white rounded-2xl border border-sky-100 shadow-sm px-6 py-5">
            <p class="text-slate-500 mb-1" style="font-size:0.78rem;font-weight:500">Tugas Aktif</p>
            <p class="text-sky-700" style="font-size:2rem;font-weight:700">{{ $tugas_aktif->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm px-6 py-5">
            <p class="text-slate-500 mb-1" style="font-size:0.78rem;font-weight:500">Telah Selesai</p>
            <p class="text-emerald-600" style="font-size:2rem;font-weight:700">{{ $riwayat->count() }}</p>
        </div>
    </div>

    {{-- TUGAS AKTIF --}}
    <h2 class="text-sky-900 mb-4" style="font-size:1.1rem;font-weight:700">Tugas Aktif</h2>

    @forelse($tugas_aktif as $t)
        @php
            $statusOrder = ['Ditugaskan', 'Menuju Lokasi', 'Sedang Dikerjakan', 'Menunggu Konfirmasi', 'Selesai'];
            $currentIdx  = array_search($t->status_tugas, $statusOrder);
            $badgeColor  = match($t->status_tugas) {
                'Menuju Lokasi'       => 'bg-amber-100 text-amber-700 border-amber-200',
                'Sedang Dikerjakan'   => 'bg-blue-100 text-blue-700 border-blue-200',
                'Menunggu Konfirmasi' => 'bg-purple-100 text-purple-700 border-purple-200',
                default               => 'bg-slate-100 text-slate-600 border-slate-200',
            };
        @endphp
        <div class="bg-white rounded-2xl border border-sky-100 shadow-sm mb-4 overflow-hidden">

            {{-- Card Header --}}
            <div class="px-5 py-4 flex flex-wrap items-center gap-3 border-b border-slate-100">
                <span class="text-sky-700 font-bold" style="font-size:0.95rem">#{{ $t->laporan->id }}</span>
                <div class="flex items-center gap-1.5">
                    <span style="font-size:1rem">{{ $t->laporan->kategoriLaporan->icon ?? '📋' }}</span>
                    <span class="font-semibold text-slate-700" style="font-size:0.88rem">{{ $t->laporan->kategoriLaporan->nama_kategori ?? 'Lainnya' }}</span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-xs font-semibold {{ $badgeColor }}">
                    {{ $t->status_tugas }}
                </span>
                <span class="ml-auto text-slate-400" style="font-size:0.78rem">{{ \Carbon\Carbon::parse($t->tanggal_penugasan)->format('Y-m-d') }}</span>
                <a href="{{ route('petugas.tugas.show', $t->id) }}"
                   class="text-slate-400 hover:text-sky-600 transition-colors">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>

            {{-- Alamat --}}
            <div class="px-5 py-3 flex items-center gap-1.5 border-b border-slate-100">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-sky-500 shrink-0"></i>
                <span class="text-slate-600" style="font-size:0.83rem">{{ $t->laporan->alamat }}</span>
            </div>

            {{-- Progress Bar --}}
            <div class="px-5 py-4">
                <div class="flex items-center gap-0" style="font-size:0.68rem;color:#94a3b8">
                    @foreach($statusOrder as $i => $s)
                        @if($i < count($statusOrder) - 1)
                            <div class="flex flex-col items-center" style="flex:1">
                                <div class="w-full flex items-center">
                                    <div class="w-2.5 h-2.5 rounded-full shrink-0 border-2
                                        {{ $i <= $currentIdx ? 'bg-sky-500 border-sky-500' : 'bg-white border-slate-300' }}"></div>
                                    <div class="h-1 flex-1 rounded
                                        {{ $i < $currentIdx ? 'bg-sky-500' : 'bg-slate-200' }}"></div>
                                </div>
                                <span class="mt-1 text-center leading-tight" style="font-size:0.65rem;min-width:60px">{{ $s }}</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center" style="min-width:40px">
                                <div class="w-2.5 h-2.5 rounded-full border-2
                                    {{ $i <= $currentIdx ? 'bg-sky-500 border-sky-500' : 'bg-white border-slate-300' }}"></div>
                                <span class="mt-1 text-center" style="font-size:0.65rem">{{ $s }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Quick Action --}}
            <div class="px-5 py-3 border-t border-slate-100 flex justify-end">
                <a href="{{ route('petugas.tugas.show', $t->id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-medium transition-colors">
                    <i data-lucide="eye" class="w-4 h-4"></i> Lihat Detail
                </a>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-12 text-center mb-6">
            <i data-lucide="inbox" class="w-10 h-10 mx-auto text-slate-300 mb-3"></i>
            <p class="text-slate-400 text-sm">Tidak ada tugas aktif saat ini.</p>
        </div>
    @endforelse

    {{-- RIWAYAT SELESAI --}}
    <h2 class="text-sky-900 mt-6 mb-4" style="font-size:1.1rem;font-weight:700">Riwayat Selesai</h2>

    @forelse($riwayat as $r)
        @php
            $rating = optional($r->ulasan)->rating ?? 0;
        @endphp
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-3 px-5 py-4">
            <div class="flex items-start gap-3">
                {{-- Icon --}}
                <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-4.5 h-4.5"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-0.5">
                        <span class="text-sky-700 font-bold" style="font-size:0.88rem">#{{ $r->laporan->id }}</span>
                        <span class="text-slate-400">-</span>
                        <span style="font-size:0.85rem">{{ $r->laporan->kategoriLaporan->icon ?? '📋' }}</span>
                        <span class="font-semibold text-slate-700" style="font-size:0.85rem">{{ $r->laporan->kategoriLaporan->nama_kategori ?? 'Lainnya' }}</span>
                        {{-- Stars --}}
                        @if($rating > 0)
                            <div class="ml-auto flex items-center gap-0.5">
                                @for($s = 1; $s <= 5; $s++)
                                    <span style="color:{{ $s <= $rating ? '#f59e0b' : '#d1d5db' }};font-size:0.9rem">★</span>
                                @endfor
                            </div>
                        @endif
                    </div>
                    <p class="text-slate-500" style="font-size:0.78rem">
                        <i data-lucide="map-pin" class="w-3 h-3 inline -mt-0.5 mr-0.5"></i>
                        {{ $r->laporan->alamat }}
                    </p>
                    @if(optional($r->penyelesaian)->keterangan)
                        <p class="text-slate-500 mt-1 line-clamp-1" style="font-size:0.78rem">
                            Catatan: {{ $r->penyelesaian->keterangan }}
                        </p>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    <p class="text-slate-400" style="font-size:0.75rem">
                        {{ optional($r->penyelesaian?->tanggal_selesai ? \Carbon\Carbon::parse($r->penyelesaian->tanggal_selesai) : null)?->format('Y-m-d') ?? '-' }}
                    </p>
                    <a href="{{ route('petugas.tugas.show', $r->id) }}"
                       class="inline-flex items-center gap-1 text-sky-600 hover:underline mt-1" style="font-size:0.75rem">
                        Detail <i data-lucide="chevron-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-10 text-center">
            <i data-lucide="clock" class="w-10 h-10 mx-auto text-slate-300 mb-3"></i>
            <p class="text-slate-400 text-sm">Belum ada riwayat tugas selesai.</p>
        </div>
    @endforelse

</div>

<script>
    lucide.createIcons();

    @if(session('success'))
        Swal.fire({
            icon: 'success', title: 'Berhasil!',
            text: '{{ session("success") }}',
            timer: 3000, showConfirmButton: false,
            toast: true, position: 'top-end',
        });
    @endif
    @if(session('error'))
        Swal.fire({
            icon: 'error', title: 'Gagal!',
            text: '{{ session("error") }}',
            confirmButtonColor: '#0284c7',
        });
    @endif
</script>
@endsection
