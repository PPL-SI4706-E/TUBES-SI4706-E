@extends('layouts.admin')
@section('title', 'Testimoni Publik')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <p class="text-sky-600 uppercase tracking-[0.2em]" style="font-size:0.7rem;font-weight:700">Moderasi Landing Page</p>
                <h1 class="text-slate-900 mt-1" style="font-size:1.7rem;font-weight:800">Testimoni Publik</h1>
                <p class="text-slate-500 mt-2" style="font-size:0.9rem">Tinjau pesan dari pengunjung, lalu setujui atau tolak sebelum tampil di landing page TirtaBantu.</p>
            </div>
            <a href="{{ route('home') }}#testimoni" class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2.5 rounded-xl transition-colors shadow-sm" style="font-size:0.85rem;font-weight:600">
                Lihat Landing Page
            </a>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-amber-100 p-5 shadow-sm">
                <p class="text-amber-600" style="font-size:0.8rem;font-weight:700">Pending</p>
                <p class="text-slate-900 mt-2" style="font-size:2rem;font-weight:800">{{ $summary['pending'] }}</p>
                <p class="text-slate-500 mt-1" style="font-size:0.8rem">Menunggu validasi admin</p>
            </div>
            <div class="bg-white rounded-2xl border border-emerald-100 p-5 shadow-sm">
                <p class="text-emerald-600" style="font-size:0.8rem;font-weight:700">Approved</p>
                <p class="text-slate-900 mt-2" style="font-size:2rem;font-weight:800">{{ $summary['approved'] }}</p>
                <p class="text-slate-500 mt-1" style="font-size:0.8rem">Sudah tampil di landing page</p>
            </div>
            <div class="bg-white rounded-2xl border border-rose-100 p-5 shadow-sm">
                <p class="text-rose-600" style="font-size:0.8rem;font-weight:700">Rejected</p>
                <p class="text-slate-900 mt-2" style="font-size:2rem;font-weight:800">{{ $summary['rejected'] }}</p>
                <p class="text-slate-500 mt-1" style="font-size:0.8rem">Disimpan sebagai arsip admin</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-slate-500" style="font-size:0.75rem">
                            <th class="px-5 py-4 font-semibold">Pengunjung</th>
                            <th class="px-5 py-4 font-semibold">Pesan</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold">Dikirim</th>
                            <th class="px-5 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($testimoni as $item)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="text-slate-900" style="font-size:0.9rem;font-weight:700">{{ $item->nama }}</p>
                                    <p class="text-slate-500 mt-1" style="font-size:0.78rem">{{ $item->email ?: 'Email tidak diisi' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-slate-600 max-w-xl" style="font-size:0.85rem;line-height:1.7">{{ $item->pesan }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex px-3 py-1 rounded-full {{ $item->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($item->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}" style="font-size:0.74rem;font-weight:700">
                                        {{ strtoupper($item->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-500" style="font-size:0.8rem">
                                    {{ $item->created_at?->format('d M Y H:i') }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2 flex-wrap">
                                        <form method="POST" action="{{ route('admin.testimoni.approve', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3.5 py-2 rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors" style="font-size:0.78rem;font-weight:700">
                                                Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.testimoni.reject', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3.5 py-2 rounded-lg border border-rose-100 bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors" style="font-size:0.78rem;font-weight:700">
                                                Tolak
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.testimoni.pending', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3.5 py-2 rounded-lg border border-amber-100 bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors" style="font-size:0.78rem;font-weight:700">
                                                Pendingkan
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.testimoni.destroy', $item) }}" onsubmit="return confirm('Hapus testimoni ini secara permanen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3.5 py-2 rounded-lg border border-red-100 bg-white text-red-600 hover:bg-red-50 transition-colors" style="font-size:0.78rem;font-weight:700">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center">
                                    <p class="text-slate-900" style="font-size:1rem;font-weight:700">Belum ada testimoni masuk</p>
                                    <p class="text-slate-500 mt-2" style="font-size:0.84rem">Saat pengunjung mengirim pesan dari landing page, daftar ini akan terisi otomatis.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $testimoni->links() }}
            </div>
        </div>
    </div>
@endsection
