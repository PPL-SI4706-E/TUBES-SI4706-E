@extends('layouts.petugas')

@section('title', 'Dashboard Kinerja')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-sky-800 to-sky-900 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold mb-2">Halo, {{ $petugas->name }}! 👋</h1>
                <p class="text-sky-100 max-w-xl">Selamat datang di Dashboard Kinerja Anda. Berikut adalah ringkasan produktivitas dan kepuasan masyarakat terhadap layanan yang Anda berikan.</p>
            </div>
            <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md px-6 py-4 rounded-xl border border-white/20">
                <div class="text-right">
                    <p class="text-sky-200 text-sm font-medium mb-1">Status Anda Saat Ini</p>
                    <div class="flex items-center justify-end gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $statusBadge['dot'] }}"></span>
                        <span class="font-bold text-lg text-white">{{ $statusBadge['text'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card: Tugas Selesai -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-start gap-4 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                <i data-lucide="check-circle-2" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Tugas Selesai</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-3xl font-bold text-slate-800">{{ $tugasSelesai }}</h3>
                    <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Laporan</span>
                </div>
            </div>
        </div>

        <!-- Card: Tugas Diproses -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-start gap-4 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 border border-sky-100">
                <i data-lucide="loader" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Sedang Diproses</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-3xl font-bold text-slate-800">{{ $tugasDiproses }}</h3>
                    <span class="text-xs font-semibold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-full">Laporan</span>
                </div>
            </div>
        </div>

        <!-- Card: Rata-Rata Rating -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-start gap-4 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0 border border-amber-100">
                <i data-lucide="star" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Rata-Rata Rating</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-3xl font-bold text-slate-800">{{ number_format($rataRataRating, 1) }}</h3>
                    <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Bintang</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Ulasan Terbaru -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i data-lucide="message-square-heart" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Ulasan & Apresiasi Warga</h2>
                    <p class="text-sm text-slate-500">Feedback terbaru dari laporan yang Anda tangani</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            @if($ulasanTerbaru->isEmpty())
                <div class="text-center py-12 px-4 bg-slate-50 rounded-xl border border-slate-100 border-dashed">
                    <i data-lucide="star-off" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                    <h3 class="text-lg font-medium text-slate-800 mb-1">Belum ada ulasan</h3>
                    <p class="text-slate-500 text-sm">Selesaikan tugas dan dapatkan ulasan bintang dari masyarakat!</p>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($ulasanTerbaru as $tugas)
                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 hover:border-indigo-200 transition-colors relative overflow-hidden group">
                            <!-- Dekorasi background -->
                            <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-indigo-100 to-white rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
                            
                            <div class="relative z-10">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-1">{{ $tugas->laporan->kategoriLaporan->nama_kategori }}</p>
                                        <p class="font-semibold text-slate-800">{{ $tugas->laporan->judul }}</p>
                                    </div>
                                    <div class="flex bg-white px-2.5 py-1 rounded-lg border border-slate-200 shadow-sm gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i data-lucide="star" class="w-3.5 h-3.5 {{ $i <= $tugas->ulasan->rating ? 'text-amber-400 fill-amber-400' : 'text-slate-200' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                
                                @if($tugas->ulasan->komentar)
                                    <div class="mt-4 bg-white/80 p-4 rounded-lg border border-slate-100">
                                        <p class="text-sm text-slate-600 italic">"{{ $tugas->ulasan->komentar }}"</p>
                                    </div>
                                @endif
                                
                                <div class="mt-4 flex items-center justify-between text-xs text-slate-400">
                                    <span>Tugas selesai pada {{ \Carbon\Carbon::parse($tugas->penyelesaian->tanggal_selesai)->format('d M Y') }}</span>
                                    <a href="{{ route('petugas.tugas.show', $tugas->id) }}" class="text-indigo-600 font-semibold hover:underline flex items-center gap-1">
                                        Lihat Laporan <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
