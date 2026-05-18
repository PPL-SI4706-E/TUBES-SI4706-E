@extends('layouts.admin')
@section('title', 'Kelola Laporan')

@section('content')
<div class="mb-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-sky-900">Kelola Laporan</h1>
        <p class="text-slate-500 text-sm mt-0.5">Validasi, tinjau kebutuhan turun lapangan, dan kelola seluruh laporan</p>
    </div>

</div>

{{-- Search + Filter Bar --}}
<form method="GET" action="{{ route('admin.laporan.index') }}"
      class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-5 flex flex-col sm:flex-row gap-3">

    {{-- Search --}}
    <div class="relative flex-1 min-w-0">
        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
            <i data-lucide="search" class="w-4 h-4"></i>
        </span>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari ID, alamat, deskripsi..."
               class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition">
    </div>

    {{-- Status --}}
    <select name="status" onchange="this.form.submit()"
            class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition min-w-[160px]">
        <option value="">Semua Status</option>
        <option value="pending"    {{ request('status') === 'pending'    ? 'selected' : '' }}>Menunggu Validasi</option>
        <option value="diterima"   {{ request('status') === 'diterima'   ? 'selected' : '' }}>Diterima</option>
        <option value="ditolak"    {{ request('status') === 'ditolak'    ? 'selected' : '' }}>Ditolak</option>
        <option value="dikerjakan" {{ request('status') === 'dikerjakan' ? 'selected' : '' }}>Sedang Dikerjakan</option>
        <option value="selesai"    {{ request('status') === 'selesai'    ? 'selected' : '' }}>Selesai</option>
    </select>

    {{-- Kategori --}}
    <select name="kategori" onchange="this.form.submit()"
            class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition min-w-[160px]">
        <option value="">Semua Kategori</option>
        @foreach($kategoris as $kat)
            <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>
                {{ $kat->nama_kategori }}
            </option>
        @endforeach
    </select>

    {{-- Turun Lapangan --}}
    <select name="turun" onchange="this.form.submit()"
            class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition min-w-[160px]">
        <option value="">Turun Lapangan</option>
        <option value="ya"    {{ request('turun') === 'ya'    ? 'selected' : '' }}>Ya</option>
        <option value="tidak" {{ request('turun') === 'tidak' ? 'selected' : '' }}>Tidak</option>
    </select>

    <button type="submit"
            class="px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-lg hover:bg-sky-700 transition-colors flex items-center gap-2 shrink-0">
        <i data-lucide="filter" class="w-4 h-4"></i> Filter
    </button>
    @if(request('search') || request('status') || request('kategori') || request('turun'))
        <a href="{{ route('admin.laporan.index') }}"
           class="px-4 py-2 bg-slate-100 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-200 transition-colors flex items-center gap-2 shrink-0">
            <i data-lucide="x" class="w-4 h-4"></i> Reset
        </a>
    @endif
</form>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[1000px]">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wider">
                    <th class="px-4 py-3.5 font-semibold">ID</th>
                    <th class="px-4 py-3.5 font-semibold">Pelapor</th>
                    <th class="px-4 py-3.5 font-semibold">Alamat Rumah</th>
                    <th class="px-4 py-3.5 font-semibold">Kategori</th>
                    <th class="px-4 py-3.5 font-semibold">Tanggal</th>
                    <th class="px-4 py-3.5 font-semibold text-center">Turun?</th>
                    <th class="px-4 py-3.5 font-semibold">Status</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($laporans as $laporan)
                    @php
                        $turunLapangan = in_array($laporan->status, ['diterima', 'dikerjakan', 'selesai'])
                                         && $laporan->penugasan !== null;
                        $badgeMap = [
                            'pending'    => ['bg-amber-100 text-amber-800 border-amber-200',   'Menunggu Validasi'],
                            'diterima'   => ['bg-blue-100 text-blue-800 border-blue-200',       'Diterima'],
                            'ditolak'    => ['bg-red-100 text-red-800 border-red-200',          'Ditolak'],
                            'dikerjakan' => ['bg-cyan-100 text-cyan-800 border-cyan-200',       'Sedang Dikerjakan'],
                            'selesai'    => ['bg-emerald-100 text-emerald-800 border-emerald-200','Selesai'],
                        ];
                        [$cls, $label] = $badgeMap[$laporan->status] ?? ['bg-slate-100 text-slate-600 border-slate-200', $laporan->status];

                        // Payment status check
                        $bayarLunas = optional($laporan->pembayaran)->status_pembayaran === 'Lunas';
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">

                        {{-- ID --}}
                        <td class="px-4 py-3.5">
                            <span class="text-sm font-bold text-sky-700">#{{ $laporan->id }}</span>
                        </td>

                        {{-- Pelapor --}}
                        <td class="px-4 py-3.5">
                            <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $laporan->user->name ?? 'Anonim' }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $laporan->user->phone ?? '-' }}</p>
                        </td>

                        {{-- Alamat --}}
                        <td class="px-4 py-3.5 max-w-[200px]">
                            <div class="flex items-start gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3 text-slate-400 shrink-0 mt-0.5"></i>
                                <div>
                                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ $laporan->alamat }}</p>
                                    @if($laporan->wilayah)
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            Kec. {{ $laporan->wilayah->nama_wilayah }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Kategori --}}
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <span class="text-base leading-none">{{ $laporan->kategoriLaporan->icon ?? '📋' }}</span>
                                <span class="text-sm text-slate-700">{{ $laporan->kategoriLaporan->nama_kategori ?? 'Lainnya' }}</span>
                            </div>
                        </td>

                        {{-- Tanggal --}}
                        <td class="px-4 py-3.5 text-sm text-slate-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($laporan->tanggal_lapor)->format('Y-m-d') }}
                        </td>

                        {{-- Turun? --}}
                        <td class="px-4 py-3.5 text-center">
                            @if($turunLapangan)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-sky-50 text-sky-700 text-xs font-semibold rounded-full border border-sky-200">
                                    <i data-lucide="navigation" class="w-3 h-3"></i> Ya
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">Belum</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-semibold {{ $cls }}">
                                {{ $label }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3.5">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Detail --}}
                                <a href="{{ route('admin.laporan.show', $laporan->id) }}"
                                   title="Lihat Detail"
                                   class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-700 transition-colors shadow-sm">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>

                                {{-- Validasi cepat (hanya pending + sudah Lunas) --}}
                                @if($laporan->status === 'pending')
                                    @if($bayarLunas)
                                        <a href="{{ route('admin.laporan.show', $laporan->id) }}"
                                           title="Validasi Laporan"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors shadow-sm">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                        </a>
                                    @else
                                        <span title="Pembayaran belum diverifikasi — tidak dapat divalidasi"
                                              class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300 cursor-not-allowed">
                                            <i data-lucide="lock" class="w-4 h-4"></i>
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <i data-lucide="inbox" class="w-10 h-10 mx-auto text-slate-300 mb-3"></i>
                            <p class="text-slate-400 text-sm">
                                @if(request('search') || request('status') || request('kategori') || request('turun'))
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
