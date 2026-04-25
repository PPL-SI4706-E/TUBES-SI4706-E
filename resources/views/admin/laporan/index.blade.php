@extends('layouts.admin')
@section('title', 'Kelola Laporan')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Kelola Laporan</h1>
        <p class="text-slate-500 text-sm mt-1">Validasi, tinjau kebutuhan turun lapangan, dan kelola seluruh laporan</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="p-4 border-b border-slate-200 bg-slate-50 flex items-center gap-4">
            <div class="relative flex-1 max-w-md flex items-center gap-2">
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ID, alamat, deskripsi..." class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                </div>
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Cari</button>
                @if(request('search'))
                    <a href="{{ route('admin.laporan.index') }}" class="text-slate-500 hover:text-slate-700 text-sm px-2">Clear</a>
                @endif
            </div>
            <!-- More filters can be added here -->
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap">ID</th>
                        <th class="px-6 py-4">Pelapor</th>
                        <th class="px-6 py-4">Alamat Rumah</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4 whitespace-nowrap">Tanggal</th>
                        <th class="px-6 py-4 whitespace-nowrap">Turun?</th>
                        <th class="px-6 py-4 whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($laporans as $laporan)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-sky-600 whitespace-nowrap">
                            #{{ str_pad($laporan->id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $laporan->user->name ?? 'Anonim' }}</div>
                            <div class="text-xs text-slate-500">{{ $laporan->user->phone ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-start gap-2">
                                <i data-lucide="map-pin" class="w-4 h-4 text-sky-500 mt-0.5 shrink-0"></i>
                                <div>
                                    <div class="text-slate-800 line-clamp-1">{{ $laporan->alamat }}</div>
                                    <div class="text-xs text-slate-500">{{ $laporan->wilayah->nama ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 text-slate-700">
                                <span class="text-base leading-none">{{ $laporan->kategoriLaporan->icon ?? '💧' }}</span>
                                <span class="font-medium">{{ $laporan->kategoriLaporan->nama_kategori ?? 'Umum' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                            {{ $laporan->created_at->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($laporan->jenis_penanganan == 'lapangan')
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-sky-50 text-sky-700 text-xs font-medium border border-sky-100">
                                    <i data-lucide="send" class="w-3 h-3"></i> Ya
                                </span>
                            @elseif($laporan->jenis_penanganan == 'virtual')
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-medium border border-slate-200">
                                    Tidak
                                </span>
                            @else
                                <span class="text-slate-400 text-xs italic">Belum</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($laporan->status === 'pending')
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Menunggu Validasi</span>
                            @elseif($laporan->status === 'diterima')
                                <span class="px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">Tervalidasi</span>
                            @elseif($laporan->status === 'selesai')
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Selesai</span>
                            @elseif($laporan->status === 'ditolak')
                                <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Ditolak</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">{{ ucfirst($laporan->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('admin.laporan.show', $laporan->id) }}" class="inline-flex items-center justify-center p-2 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-sky-600 transition-colors shadow-sm" title="Lihat Detail">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                            <i data-lucide="inbox" class="w-12 h-12 mx-auto text-slate-300 mb-3"></i>
                            <p class="text-lg font-medium text-slate-600">Belum ada laporan</p>
                            <p class="text-sm">Laporan dari masyarakat akan muncul di sini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination area (if any) -->
        <div class="p-4 border-t border-slate-200 bg-slate-50 text-sm text-slate-500 flex justify-between items-center">
            <span>Menampilkan total {{ count($laporans) }} data</span>
        </div>
    </div>
</div>
@endsection
