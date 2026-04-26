@extends('layouts.admin')
@section('title', 'Kelola Laporan')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-sky-900">Kelola Laporan</h1>
        <p class="text-slate-500 text-sm mt-1">Daftar laporan dari masyarakat — validasi dan pantau penanganan.</p>
    </div>
    <a href="{{ route('admin.laporan.peta') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-lg hover:bg-sky-700 transition-colors shadow-sm">
        <i data-lucide="map" class="w-4 h-4"></i> Lihat Peta
    </a>
</div>

{{-- Search + Filter Bar --}}
<form method="GET" action="{{ route('admin.laporan.index') }}"
      class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-5 flex flex-col sm:flex-row gap-3">

    {{-- Search Input --}}
    <div class="relative flex-1 min-w-0">
        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
            <i data-lucide="search" class="w-4 h-4"></i>
        </span>
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari ID, alamat, atau deskripsi…"
            class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition"
        >
    </div>

    {{-- Status Dropdown --}}
    <select name="status" onchange="this.form.submit()"
            class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 bg-white transition min-w-[180px]">
        <option value="">Semua Status</option>
        <option value="pending"    {{ request('status') === 'pending'    ? 'selected' : '' }}>Menunggu Validasi</option>
        <option value="diterima"   {{ request('status') === 'diterima'   ? 'selected' : '' }}>Diterima</option>
        <option value="ditolak"    {{ request('status') === 'ditolak'    ? 'selected' : '' }}>Ditolak</option>
        <option value="dikerjakan" {{ request('status') === 'dikerjakan' ? 'selected' : '' }}>Dikerjakan</option>
        <option value="selesai"    {{ request('status') === 'selesai'    ? 'selected' : '' }}>Selesai</option>
    </select>

    <button type="submit"
            class="px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-lg hover:bg-sky-700 transition-colors flex items-center gap-2 shrink-0">
        <i data-lucide="filter" class="w-4 h-4"></i> Filter
    </button>
    @if(request('search') || request('status'))
        <a href="{{ route('admin.laporan.index') }}"
           class="px-4 py-2 bg-slate-100 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-200 transition-colors flex items-center gap-2 shrink-0">
            <i data-lucide="x" class="w-4 h-4"></i> Reset
        </a>
    @endif
</form>

{{-- Active filter pills --}}
@if(request('search') || request('status'))
<div class="flex flex-wrap gap-2 mb-4">
    @if(request('search'))
        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-sky-50 text-sky-700 text-xs font-semibold rounded-full border border-sky-200">
            <i data-lucide="search" class="w-3 h-3"></i> "{{ request('search') }}"
        </span>
    @endif
    @if(request('status'))
        @php
            $statusLabels = [
                'pending'    => 'Menunggu Validasi',
                'diterima'   => 'Diterima',
                'ditolak'    => 'Ditolak',
                'dikerjakan' => 'Dikerjakan',
                'selesai'    => 'Selesai',
            ];
        @endphp
        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 text-xs font-semibold rounded-full border border-amber-200">
            <i data-lucide="tag" class="w-3 h-3"></i> {{ $statusLabels[request('status')] ?? request('status') }}
        </span>
    @endif
    <span class="inline-flex items-center px-3 py-1 bg-slate-50 text-slate-500 text-xs rounded-full border border-slate-200">
        {{ $laporans->total() }} hasil ditemukan
    </span>
</div>
@endif

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[900px]">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3.5 font-semibold">ID</th>
                    <th class="px-5 py-3.5 font-semibold">Pelapor</th>
                    <th class="px-5 py-3.5 font-semibold">Kategori &amp; Alamat</th>
                    <th class="px-5 py-3.5 font-semibold">Tanggal Lapor</th>
                    {{-- Status dropdown kanan atas Aksi --}}
                    <th class="px-5 py-3.5 font-semibold">
                        <div class="flex flex-col gap-0.5">
                            <span>Status</span>
                        </div>
                    </th>
                    <th class="px-5 py-3.5 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($laporans as $laporan)
                    <tr class="hover:bg-slate-50/60 transition-colors group">

                        {{-- ID --}}
                        <td class="px-5 py-4">
                            <span class="text-sm font-bold text-sky-700">#{{ $laporan->id }}</span>
                        </td>

                        {{-- Pelapor --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-700 font-bold text-sm flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($laporan->user->name ?? 'A', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $laporan->user->name ?? 'Anonim' }}</p>
                                    <p class="text-xs text-slate-400">{{ $laporan->wilayah->nama_wilayah ?? '-' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Kategori & Alamat --}}
                        <td class="px-5 py-4 max-w-[260px]">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <span class="text-base leading-none">{{ $laporan->kategoriLaporan->icon ?? '📋' }}</span>
                                <span class="text-sm font-semibold text-slate-700">{{ $laporan->kategoriLaporan->nama_kategori ?? 'Lainnya' }}</span>
                            </div>
                            <p class="text-xs text-slate-400 truncate" title="{{ $laporan->alamat }}">
                                <i data-lucide="map-pin" class="w-3 h-3 inline-block mr-0.5 -mt-0.5"></i>
                                {{ $laporan->alamat }}
                            </p>
                        </td>

                        {{-- Tanggal --}}
                        <td class="px-5 py-4 text-sm text-slate-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($laporan->tanggal_lapor)->format('d M Y') }}<br>
                            <span class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($laporan->tanggal_lapor)->format('H:i') }}</span>
                        </td>

                        {{-- Status Badge --}}
                        <td class="px-5 py-4">
                            @php
                                $badgeMap = [
                                    'pending'    => ['bg-amber-100 text-amber-800 border-amber-200', 'Menunggu'],
                                    'diterima'   => ['bg-blue-100 text-blue-800 border-blue-200',    'Diterima'],
                                    'ditolak'    => ['bg-red-100 text-red-800 border-red-200',        'Ditolak'],
                                    'dikerjakan' => ['bg-cyan-100 text-cyan-800 border-cyan-200',     'Dikerjakan'],
                                    'selesai'    => ['bg-emerald-100 text-emerald-800 border-emerald-200','Selesai'],
                                ];
                                [$cls, $label] = $badgeMap[$laporan->status] ?? ['bg-slate-100 text-slate-700 border-slate-200',$laporan->status];
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-semibold {{ $cls }}">
                                {{ $label }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.laporan.show', $laporan->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 text-slate-700 hover:bg-sky-50 hover:border-sky-400 hover:text-sky-700 rounded-lg text-sm font-medium transition-colors shadow-sm">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <i data-lucide="inbox" class="w-10 h-10 mx-auto text-slate-300 mb-3"></i>
                            <p class="text-slate-400 text-sm">
                                @if(request('search') || request('status'))
                                    Tidak ada laporan yang cocok dengan filter.
                                @else
                                    Belum ada data laporan.
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($laporans->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/40">
            {{ $laporans->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
