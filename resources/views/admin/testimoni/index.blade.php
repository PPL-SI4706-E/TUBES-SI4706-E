@extends('layouts.admin')

@section('title', 'Testimoni Publik')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Kelola Testimoni Publik</h1>
                <p class="text-sm text-slate-500 mt-1">Validasi pesan pengunjung sebelum ditampilkan di landing page TirtaBantu.</p>
            </div>
            <div class="bg-sky-50 border border-sky-100 rounded-xl px-4 py-3 text-sm text-sky-700">
                Pending: <span class="font-semibold">{{ $pendingTestimonials->count() }}</span>
            </div>
        </div>

        <div class="grid xl:grid-cols-3 gap-6">
            <section class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-900">Menunggu Validasi</h2>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($pendingTestimonials as $testimoni)
                        <article class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-base font-semibold text-slate-900">{{ $testimoni->nama }}</h3>
                                        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold uppercase tracking-wide">
                                            {{ $testimoni->status }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-500 mb-3">
                                        {{ $testimoni->email ?: 'Email tidak diisi' }} • {{ $testimoni->created_at->format('d M Y H:i') }}
                                    </p>
                                    <p class="text-sm leading-7 text-slate-700">{{ $testimoni->pesan }}</p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-3">
                                <form method="POST" action="{{ route('admin.testimoni.approve', $testimoni) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                                        Setujui
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.testimoni.reject', $testimoni) }}" class="flex-1 min-w-[240px]">
                                    @csrf
                                    @method('PATCH')
                                    <div class="flex gap-2">
                                        <input
                                            type="text"
                                            name="catatan_admin"
                                            placeholder="Alasan penolakan (opsional)"
                                            class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none"
                                        >
                                        <button type="submit" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium">
                                            Tolak
                                        </button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('admin.testimoni.destroy', $testimoni) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-medium border border-rose-200">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="p-6 text-sm text-slate-500">Belum ada testimoni yang menunggu validasi.</div>
                    @endforelse
                </div>
            </section>

            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-900">Riwayat Tinjauan</h2>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($reviewedTestimonials as $testimoni)
                        <article class="p-5">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <p class="font-semibold text-slate-900">{{ $testimoni->nama }}</p>
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide {{ $testimoni->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $testimoni->status }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 leading-6">{{ \Illuminate\Support\Str::limit($testimoni->pesan, 120) }}</p>
                            @if($testimoni->catatan_admin)
                                <p class="text-xs text-slate-400 mt-2">Catatan: {{ $testimoni->catatan_admin }}</p>
                            @endif
                        </article>
                    @empty
                        <div class="p-6 text-sm text-slate-500">Belum ada riwayat validasi testimoni.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
