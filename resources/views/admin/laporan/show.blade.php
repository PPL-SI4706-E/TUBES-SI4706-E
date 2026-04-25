@extends('layouts.admin')
@section('title', 'Detail Laporan')

@section('content')
<div class="max-w-6xl mx-auto pb-10" x-data="{ showVirtualModal: false, showRejectModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.laporan.index') }}" class="w-10 h-10 rounded-full flex items-center justify-center bg-white shadow-sm border border-slate-200 hover:bg-slate-50 text-slate-600 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Laporan <span class="text-sky-600">#{{ str_pad($laporan->id, 4, '0', STR_PAD_LEFT) }}</span></h1>
                <p class="text-slate-500 text-sm mt-0.5"><i data-lucide="calendar" class="w-3.5 h-3.5 inline mr-1 relative -top-[1px]"></i>{{ $laporan->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
        
        <div>
            @if($laporan->status === 'pending')
                <span class="px-4 py-2 rounded-full bg-amber-50 text-amber-700 text-sm font-semibold border border-amber-200 shadow-sm flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Menunggu Validasi
                </span>
            @elseif($laporan->status === 'diterima')
                <span class="px-4 py-2 rounded-full bg-indigo-50 text-indigo-700 text-sm font-semibold border border-indigo-200 shadow-sm flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> Tervalidasi (Lapangan)
                </span>
            @elseif($laporan->status === 'selesai')
                <span class="px-4 py-2 rounded-full bg-emerald-50 text-emerald-700 text-sm font-semibold border border-emerald-200 shadow-sm flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Selesai (Virtual)
                </span>
            @elseif($laporan->status === 'ditolak')
                <span class="px-4 py-2 rounded-full bg-red-50 text-red-700 text-sm font-semibold border border-red-200 shadow-sm flex items-center gap-2">
                    <i data-lucide="x-circle" class="w-4 h-4"></i> Ditolak
                </span>
            @else
                <span class="px-4 py-2 rounded-full bg-slate-50 text-slate-700 text-sm font-semibold border border-slate-200 shadow-sm">{{ ucfirst($laporan->status) }}</span>
            @endif
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Kolom Kiri: Detail Utama -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="px-3 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wider border border-slate-200">
                            {{ $laporan->kategoriLaporan->icon ?? '💧' }} {{ $laporan->kategoriLaporan->nama_kategori ?? 'Umum' }}
                        </span>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800 mb-5">{{ $laporan->judul ?? 'Laporan Tanpa Judul' }}</h2>
                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed mb-8">
                        {!! nl2br(e($laporan->deskripsi)) !!}
                    </div>
                    
                    @if($laporan->foto)
                    <div>
                        <p class="text-sm font-semibold text-slate-800 mb-3">Lampiran Foto</p>
                        <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 relative group">
                            <img src="{{ asset('storage/' . $laporan->foto) }}" alt="Foto Laporan" class="w-full object-cover max-h-[500px]">
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Validation Info Box (if processed) -->
            @if($laporan->status !== 'pending')
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">Riwayat Validasi</h3>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                        <div>
                            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-1.5">Divalidasi Oleh</p>
                            <p class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i> {{ $laporan->validatedBy->name ?? 'Admin' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-1.5">Tanggal Validasi</p>
                            <p class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i> {{ $laporan->validated_at ? \Carbon\Carbon::parse($laporan->validated_at)->format('d M Y, H:i') : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-1.5">Metode</p>
                            <p class="text-sm font-semibold text-slate-800 capitalize flex items-center gap-2">
                                <i data-lucide="git-merge" class="w-3.5 h-3.5 text-slate-400"></i> Penanganan {{ $laporan->jenis_penanganan ?? '-' }}
                            </p>
                        </div>
                    </div>

                    @if($laporan->jenis_penanganan === 'virtual' && $laporan->solusi)
                    <div class="bg-emerald-50 rounded-xl p-5 border border-emerald-100">
                        <div class="flex items-center gap-2 text-emerald-800 font-bold mb-3">
                            <i data-lucide="message-square" class="w-4 h-4"></i> Solusi Virtual yang Diberikan
                        </div>
                        <p class="text-emerald-700 text-sm leading-relaxed">{{ $laporan->solusi }}</p>
                    </div>
                    @endif

                    @if($laporan->status === 'ditolak' && $laporan->alasan_penolakan)
                    <div class="bg-red-50 rounded-xl p-5 border border-red-100">
                        <div class="flex items-center gap-2 text-red-800 font-bold mb-3">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i> Alasan Penolakan
                        </div>
                        <p class="text-red-700 text-sm leading-relaxed">{{ $laporan->alasan_penolakan }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Kolom Kanan: Sidebar & Aksi -->
        <div class="space-y-6">
            <!-- Pelapor -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4"></i> Informasi Pelapor
                </h3>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-lg border border-sky-100 shrink-0">
                        {{ strtoupper(substr($laporan->user->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-800">{{ $laporan->user->name ?? 'Anonim' }}</p>
                        <p class="text-sm text-slate-500 mt-0.5">{{ $laporan->user->phone ?? 'Tidak ada nomor HP' }}</p>
                    </div>
                </div>
            </div>

            <!-- Lokasi -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4"></i> Lokasi Kejadian
                </h3>
                <div class="bg-slate-50 rounded-xl p-5 border border-slate-100">
                    <p class="text-sm font-bold text-slate-800 mb-2">{{ $laporan->wilayah->nama ?? 'Wilayah Tidak Diketahui' }}</p>
                    <p class="text-sm text-slate-600 leading-relaxed">{{ $laporan->alamat }}</p>
                </div>
            </div>

            <!-- Aksi Panel -->
            @if($laporan->status === 'pending')
            <div class="bg-white rounded-2xl border border-slate-200 shadow-lg shadow-sky-100/50 p-6 relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-sky-50 rounded-full -z-10 blur-2xl"></div>
                <h3 class="text-sm font-bold text-slate-800 mb-5 flex items-center gap-2">
                    <i data-lucide="check-square" class="w-4 h-4 text-sky-500"></i> Tindakan Validasi
                </h3>
                
                <div class="space-y-3">
                    <form action="{{ route('admin.laporan.approve-lapangan', $laporan->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-3.5 rounded-xl font-semibold transition-all shadow-md shadow-sky-200">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Tugaskan ke Lapangan
                        </button>
                    </form>

                    <button @click="showVirtualModal = true" type="button" class="w-full flex items-center justify-center gap-2 bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-200 hover:border-emerald-200 px-4 py-3.5 rounded-xl font-semibold transition-colors shadow-sm">
                        <i data-lucide="message-square" class="w-4 h-4 text-emerald-500"></i>
                        Beri Solusi Virtual
                    </button>

                    <button @click="showRejectModal = true" type="button" class="w-full flex items-center justify-center gap-2 bg-white hover:bg-red-50 text-slate-700 hover:text-red-700 border border-slate-200 hover:border-red-200 px-4 py-3.5 rounded-xl font-semibold transition-colors shadow-sm mt-6">
                        <i data-lucide="x-circle" class="w-4 h-4 text-red-500"></i>
                        Tolak Laporan
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Solusi Virtual -->
    <div x-show="showVirtualModal" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" x-show="showVirtualModal" x-transition.opacity></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg" @click.away="showVirtualModal = false" x-show="showVirtualModal" x-transition>
                    <form action="{{ route('admin.laporan.approve-virtual', $laporan->id) }}" method="POST">
                        @csrf
                        <div class="bg-white px-6 pb-6 pt-8 sm:p-8 sm:pb-6">
                            <div class="sm:flex sm:items-start gap-5">
                                <div class="mx-auto flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0">
                                    <i data-lucide="message-square" class="h-6 w-6 text-emerald-600"></i>
                                </div>
                                <div class="mt-4 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-xl font-bold leading-6 text-slate-900 mb-2" id="modal-title">Beri Solusi Virtual</h3>
                                    <p class="text-sm text-slate-500 mb-5 leading-relaxed">Berikan solusi atau panduan yang jelas. Laporan ini akan ditandai selesai tanpa menerjunkan petugas ke lapangan.</p>
                                    
                                    <textarea name="solusi" rows="5" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50 transition-colors" placeholder="Ketikkan solusi di sini..." required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:ml-3 sm:w-auto transition-colors">Selesaikan (Virtual)</button>
                            <button type="button" @click="showVirtualModal = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tolak Laporan -->
    <div x-show="showRejectModal" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" x-show="showRejectModal" x-transition.opacity></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg" @click.away="showRejectModal = false" x-show="showRejectModal" x-transition>
                    <form action="{{ route('admin.laporan.reject', $laporan->id) }}" method="POST">
                        @csrf
                        <div class="bg-white px-6 pb-6 pt-8 sm:p-8 sm:pb-6">
                            <div class="sm:flex sm:items-start gap-5">
                                <div class="mx-auto flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0">
                                    <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600"></i>
                                </div>
                                <div class="mt-4 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-xl font-bold leading-6 text-slate-900 mb-2" id="modal-title">Tolak Laporan</h3>
                                    <p class="text-sm text-slate-500 mb-5 leading-relaxed">Laporan akan ditolak permanen. Berikan alasan penolakan yang jelas agar pelapor dapat mengerti.</p>
                                    
                                    <textarea name="alasan_penolakan" rows="5" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-slate-50 transition-colors" placeholder="Ketikkan alasan penolakan..." required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-colors">Konfirmasi Tolak</button>
                            <button type="button" @click="showRejectModal = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
