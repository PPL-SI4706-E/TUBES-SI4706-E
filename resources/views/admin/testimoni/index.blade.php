@extends('layouts.admin')

@section('title', 'Manajemen Testimoni')

@section('content')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        {{-- Header Section --}}
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Manajemen Testimoni</h1>
                <p class="text-sm text-slate-500 mt-1">Daftar feedback dari laporan warga yang masuk ke sistem.</p>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-full px-4 py-2 text-sm text-slate-700">
                Total: <span class="font-bold">{{ $testimonials->count() }}</span>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold">ID</th>
                        <th class="px-6 py-4 font-semibold">Pengirim</th>
                        <th class="px-6 py-4 font-semibold text-center">Rating</th>
                        <th class="px-6 py-4 font-semibold w-1/3">Pesan Feedback</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-center">Tanggal</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($testimonials as $testimoni)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            {{-- ID --}}
                            <td class="px-6 py-4 font-medium text-slate-500">
                                #{{ $testimoni->id }}
                            </td>
                            
                            {{-- PENGIRIM --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $testimoni->nama }}</div>
                                <div class="text-slate-500 text-xs mt-0.5">{{ $testimoni->email ?: '-' }}</div>
                            </td>

                            {{-- RATING --}}
                            <td class="px-6 py-4 text-center">
                                @if($testimoni->rating)
                                    <div class="flex items-center justify-center text-amber-400">
                                        @for($i=1; $i<=5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $testimoni->rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs italic">Tanpa Rating</span>
                                @endif
                            </td>

                            {{-- PESAN --}}
                            <td class="px-6 py-4 text-slate-700">
                                {{ $testimoni->pesan }}
                                @if($testimoni->catatan_admin)
                                    <div class="mt-1 text-xs text-rose-500 italic">
                                        Catatan Penolakan: {{ $testimoni->catatan_admin }}
                                    </div>
                                @endif
                            </td>

                            {{-- STATUS --}}
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-amber-50 text-amber-600 border border-amber-200',
                                        'approved' => 'bg-emerald-50 text-emerald-600 border border-emerald-200',
                                        'rejected' => 'bg-rose-50 text-rose-600 border border-rose-200',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'MENUNGGU',
                                        'approved' => 'DISETUJUI',
                                        'rejected' => 'DITOLAK',
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider {{ $statusClasses[$testimoni->status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $statusLabels[$testimoni->status] ?? strtoupper($testimoni->status) }}
                                </span>
                            </td>

                            {{-- TANGGAL --}}
                            <td class="px-6 py-4 text-center text-slate-500 text-xs">
                                <div class="font-medium">{{ $testimoni->created_at->format('d/m/Y') }}</div>
                                <div>{{ $testimoni->created_at->format('H:i') }} WIB</div>
                            </td>

                            {{-- AKSI --}}
                            <td class="px-6 py-4 text-center align-middle">
                                @if($testimoni->status === 'pending')
                                    <div class="flex items-center justify-center gap-2">
                                        <form method="POST" action="{{ route('admin.testimoni.approve', $testimoni) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors" title="Setujui">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </form>
                                        <div x-data="{ openReject: false }" class="relative inline-block text-left">
                                            <button type="button" @click="openReject = !openReject" class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" title="Tolak">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                            <div x-show="openReject" @click.away="openReject = false" x-transition class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-10 p-3" style="display: none;">
                                                <form method="POST" action="{{ route('admin.testimoni.reject', $testimoni) }}">
                                                    @csrf @method('PATCH')
                                                    <textarea name="catatan_admin" class="w-full text-xs rounded-lg border-slate-200 p-2 focus:ring-rose-500 focus:border-rose-500" rows="2" placeholder="Alasan penolakan (opsional)"></textarea>
                                                    <button type="submit" class="mt-2 w-full bg-rose-500 text-white text-xs font-semibold py-1.5 rounded-lg hover:bg-rose-600">Tolak Testimoni</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Sudah dievaluasi</span>
                                    <form method="POST" action="{{ route('admin.testimoni.destroy', $testimoni) }}" class="inline-block ml-2" onsubmit="return confirm('Hapus testimoni ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-500 transition-colors" title="Hapus">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                Belum ada feedback yang masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
