@extends('layouts.admin')
@section('title', 'Kelola Laporan')

@section('content')
@php
    $hasFilter = request()->filled('keyword')
        || request()->filled('status_bayar')
        || request()->filled('bulan_awal')
        || request()->filled('bulan_akhir')
        || request()->filled('wilayah_id')
        || request()->filled('kategori_id');

    // Build export URL with active filters
    $exportParams = array_filter([
        'keyword'      => request('keyword'),
        'status_bayar' => request('status_bayar'),
        'bulan_awal'   => request('bulan_awal'),
        'bulan_akhir'  => request('bulan_akhir'),
        'wilayah_id'   => request('wilayah_id'),
        'kategori_id'  => request('kategori_id'),
    ]);

    $exportExcelUrl = route('admin.laporan.export.excel', $exportParams);
    $exportPdfUrl   = route('admin.laporan.export.pdf',   $exportParams);
@endphp

{{-- Flash error untuk export kosong --}}
@if(session('error'))
    <div class="mb-4 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 shadow-sm">
        <i data-lucide="alert-circle" class="h-4 w-4 shrink-0"></i>
        {{ session('error') }}
    </div>
@endif

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-sky-900">Kelola Laporan</h1>
        <p class="mt-1 text-sm text-slate-500">Validasi, tinjau kebutuhan turun lapangan, dan kelola seluruh laporan</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ $exportExcelUrl }}"
           id="btn-export-excel"
           class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-emerald-700">
            <i data-lucide="file-spreadsheet" class="h-4 w-4"></i>
            Export Excel
        </a>
        <a href="{{ $exportPdfUrl }}"
           id="btn-export-pdf"
           class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-700">
            <i data-lucide="file-text" class="h-4 w-4"></i>
            Export PDF
        </a>
        <a href="{{ route('admin.laporan.peta') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-sky-700">
            <i data-lucide="map" class="h-4 w-4"></i>
            Lihat Peta
        </a>
    </div>
</div>

@include('admin.laporan._filter')

@error('bulan_akhir')
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
        {{ $message }}
    </div>
@enderror

@if($hasFilter)
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
            {{ $laporans->total() }} hasil ditemukan
        </span>
        @if(request('keyword'))
            <span class="inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                <i data-lucide="search" class="h-3 w-3"></i>
                {{ request('keyword') }}
            </span>
        @endif
    </div>
@endif

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[980px] border-collapse text-left">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-3.5 font-semibold">Nomor</th>
                    <th class="px-5 py-3.5 font-semibold">Pelapor</th>
                    <th class="px-5 py-3.5 font-semibold">Kategori &amp; Alamat</th>
                    <th class="px-5 py-3.5 font-semibold">Tanggal Lapor</th>
                    <th class="px-5 py-3.5 font-semibold">Status Laporan</th>
                    <th class="px-5 py-3.5 font-semibold">Pembayaran</th>
                    <th class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($laporans as $laporan)
                    <tr class="transition-colors hover:bg-slate-50/70">
                        <td class="px-5 py-4">
                            <span class="text-sm font-bold text-sky-700">#{{ $laporan->id }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700">
                                    {{ strtoupper(substr($laporan->user->name ?? 'A', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold leading-tight text-slate-800">{{ $laporan->user->name ?? 'Anonim' }}</p>
                                    <p class="text-xs text-slate-400">{{ $laporan->wilayah->nama_wilayah ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="max-w-[280px] px-5 py-4">
                            <div class="mb-0.5 flex items-center gap-1.5">
                                <span class="text-sm font-semibold text-slate-700">{{ $laporan->kategoriLaporan->nama_kategori ?? 'Lainnya' }}</span>
                            </div>
                            <p class="truncate text-xs text-slate-400" title="{{ $laporan->alamat }}">
                                <i data-lucide="map-pin" class="-mt-0.5 mr-0.5 inline-block h-3 w-3"></i>
                                {{ $laporan->alamat ?: '-' }}
                            </p>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500">
                            {{ optional($laporan->tanggal_lapor)->format('d M Y') ?? '-' }}<br>
                            <span class="text-xs text-slate-400">{{ optional($laporan->tanggal_lapor)->format('H:i') ?? '' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $badgeMap = [
                                    'pending' => ['bg-amber-100 text-amber-800 border-amber-200', 'Menunggu'],
                                    'diterima' => ['bg-blue-100 text-blue-800 border-blue-200', 'Diterima'],
                                    'ditolak' => ['bg-red-100 text-red-800 border-red-200', 'Ditolak'],
                                    'dikerjakan' => ['bg-cyan-100 text-cyan-800 border-cyan-200', 'Dikerjakan'],
                                    'selesai' => ['bg-emerald-100 text-emerald-800 border-emerald-200', 'Selesai'],
                                ];
                                [$statusClass, $statusLabel] = $badgeMap[$laporan->status] ?? ['bg-slate-100 text-slate-700 border-slate-200', $laporan->status];
                            @endphp
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $paymentStatus = $laporan->pembayaran->status_pembayaran ?? null;
                                $paymentMap = [
                                    'Lunas' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Lunas'],
                                    'Terverifikasi' => ['bg-amber-50 text-amber-700 border-amber-200', 'Menunggu Verifikasi'],
                                    'Menunggu' => ['bg-slate-50 text-slate-600 border-slate-200', 'Belum Lunas'],
                                    'Ditolak' => ['bg-red-50 text-red-700 border-red-200', 'Ditolak'],
                                    'Kadaluarsa' => ['bg-red-50 text-red-700 border-red-200', 'Kadaluarsa'],
                                ];
                                [$paymentClass, $paymentLabel] = $paymentMap[$paymentStatus] ?? ['bg-slate-50 text-slate-600 border-slate-200', 'Belum Lunas'];
                            @endphp
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $paymentClass }}">
                                {{ $paymentLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.laporan.show', $laporan->id) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:border-sky-400 hover:bg-sky-50 hover:text-sky-700">
                                <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <i data-lucide="inbox" class="mx-auto mb-3 h-10 w-10 text-slate-300"></i>
                            <p class="text-sm text-slate-400">
                                @if(request('keyword'))
                                    Laporan tidak ditemukan.
                                @elseif($hasFilter)
                                    Tidak ada laporan yang sesuai dengan filter yang dipilih.
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

    @if($laporans->hasPages())
        <div class="border-t border-slate-100 bg-slate-50/40 px-5 py-4">
            {{ $laporans->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
