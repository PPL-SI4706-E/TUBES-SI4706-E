@extends('layouts.admin')

@section('title', 'Manajemen Pembayaran')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Verifikasi Pembayaran</h1>
            <p class="text-slate-500 text-sm">Kelola dan validasi bukti transfer biaya perbaikan dari pelanggan</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center gap-2 text-slate-400 mb-3">
                <i data-lucide="receipt" class="w-4 h-4 uppercase"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Transaksi</span>
            </div>
            <div class="text-3xl font-black text-slate-700 leading-none">{{ $stats['total_transaksi'] }}</div>
        </div>
        
        <div class="bg-[#fff5f5] p-5 rounded-2xl border border-red-50 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center gap-2 text-[#e53e3e] mb-3">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest opacity-80 text-red-400">Belum Dibayar</span>
            </div>
            <div class="text-2xl font-black text-[#9b2c2c] leading-none">Rp {{ number_format($stats['belum_dibayar'], 0, ',', '.') }}</div>
        </div>

        <div class="bg-[#fffaf0] p-5 rounded-2xl border border-amber-50 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center gap-2 text-amber-500 mb-3">
                <i data-lucide="clock" class="w-4 h-4"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest opacity-80 text-amber-500/70">Menunggu Verifikasi</span>
            </div>
            <div class="text-3xl font-black text-[#975a16] leading-none">{{ $stats['menunggu_verif'] }}</div>
        </div>

        <div class="bg-[#f0fff4] p-5 rounded-2xl border border-emerald-50 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center gap-2 text-emerald-500 mb-3">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest opacity-80 text-emerald-600/70">Sudah Lunas</span>
            </div>
            <div class="text-2xl font-black text-[#22543d] leading-none">Rp {{ number_format($stats['sudah_lunas'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div x-data="{ openImage: false, currentSrc: '' }" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-700">Daftar Transaksi Masuk</h2>
            <div class="flex gap-2">
                <button class="p-2 hover:bg-slate-50 rounded-lg text-slate-400 transition-colors">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                </button>
                <button class="p-2 hover:bg-slate-50 rounded-lg text-slate-400 transition-colors">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="px-6 py-4">Informasi Laporan</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4 text-right">Nominal</th>
                        <th class="px-6 py-4">Metode & Bukti</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pembayarans as $p)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.laporan.show', $p->laporan->id) }}" class="flex flex-col group/link">
                                <span class="text-xs font-bold text-sky-600 mb-0.5">#{{ $p->laporan->id ?? '-' }}</span>
                                <span class="text-sm font-bold text-slate-700 truncate max-w-[200px] group-hover/link:text-sky-600 transition-colors">{{ $p->laporan->judul ?? 'N/A' }}</span>
                                <span class="text-[10px] text-slate-400 italic">Dibuat: {{ $p->created_at->format('d/m/Y H:i') }}</span>
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500">
                                    {{ substr($p->user->name ?? '?', 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700 leading-none">{{ $p->user->name ?? 'N/A' }}</span>
                                    <span class="text-[10px] text-slate-400">Warga</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right tabular-nums">
                            @if($p->harga == 0)
                                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">GRATIS</span>
                            @else
                                <span class="text-sm font-black text-slate-800">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($p->bukti_transaksi)
                                <button type="button" @click="currentSrc = '{{ asset('storage/bukti_pembayaran/' . $p->bukti_transaksi) }}'; openImage = true" 
                                    class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 hover:ring-2 ring-sky-500 transition-all shrink-0 cursor-pointer">
                                    <img src="{{ asset('storage/bukti_pembayaran/' . $p->bukti_transaksi) }}" class="w-full h-full object-cover">
                                </button>
                                @else
                                <div class="w-10 h-10 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center">
                                    <i data-lucide="{{ $p->harga == 0 ? 'check-circle' : 'image-off' }}" class="w-4 h-4 {{ $p->harga == 0 ? 'text-emerald-400' : 'text-slate-300' }}"></i>
                                </div>
                                @endif
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-slate-600">{{ $p->metode_pembayaran ?? '-' }}</span>
                                    @if($p->bukti_transaksi)
                                        <span class="text-[9px] text-sky-500 font-medium">Lihat Bukti</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusClass = match($p->status_pembayaran) {
                                    'Lunas' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                    'Terverifikasi' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    'Ditolak' => 'bg-red-50 text-red-600 border-red-100',
                                    default => 'bg-slate-50 text-slate-600 border-slate-100',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full border {{ $statusClass }} text-[10px] font-bold uppercase whitespace-nowrap">
                                {{ $p->status_pembayaran === 'Terverifikasi' ? 'Butuh Verifikasi' : $p->status_pembayaran }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2 text-slate-300">
                                @if($p->status_pembayaran === 'Terverifikasi')
                                    <form action="{{ route('admin.pembayaran.verify', $p->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="Lunas">
                                        <button type="submit" class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-lg transition-all" title="Terima Pembayaran">
                                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.pembayaran.verify', $p->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="Ditolak">
                                        <button type="submit" class="p-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg transition-all" title="Tolak">
                                            <i data-lucide="x-circle" class="w-5 h-5"></i>
                                        </button>
                                    </form>
                                @elseif($p->status_pembayaran === 'Lunas')
                                    <div class="flex items-center gap-1 text-emerald-500">
                                        <i data-lucide="check-check" class="w-4 h-4"></i>
                                        <span class="text-[10px] font-bold italic">Tuntas</span>
                                    </div>
                                @elseif($p->status_pembayaran === 'Ditolak')
                                    <div class="flex items-center gap-1 text-red-400">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                        <span class="text-[10px] italic">Ditolak</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1 text-slate-300">
                                        <i data-lucide="clock" class="w-4 h-4"></i>
                                        <span class="text-[10px] italic whitespace-nowrap">Belum Bayar</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <i data-lucide="inbox" class="w-12 h-12 text-slate-200"></i>
                                <p class="text-slate-400 text-sm">Belum ada transaksi di database.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="openImage" 
             class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
             x-transition x-cloak @keydown.escape.window="openImage = false">
            <div @click.away="openImage = false" class="relative max-w-4xl w-full flex flex-col items-center">
                <button @click="openImage = false" class="absolute -top-12 right-0 text-white hover:text-red-400 transition-colors bg-white/10 p-2 rounded-full">
                    <i data-lucide="x" class="w-8 h-8"></i>
                </button>
                <img :src="currentSrc" class="w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl border-4 border-white/20">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@endsection
