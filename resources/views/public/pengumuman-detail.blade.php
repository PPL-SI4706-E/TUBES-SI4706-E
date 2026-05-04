@extends('layouts.app')
@section('title', $pengumuman?->judul ?? 'Detail Pengumuman')

@section('content')
<div class="min-h-screen bg-sky-50/60 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <a href="{{ route('home') }}#pengumuman" class="inline-flex items-center gap-2 text-sky-700 hover:text-sky-900 mb-6" style="font-size:0.85rem;font-weight:600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Beranda
        </a>

        <article class="bg-white rounded-2xl border border-sky-100 shadow-sm p-6 md:p-8">
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <span class="bg-sky-100 text-sky-700 px-3 py-1 rounded-full" style="font-size:0.75rem;font-weight:700">Pengumuman Publik</span>
                <span class="text-slate-400" style="font-size:0.8rem">{{ optional($pengumuman?->tanggal_post)->format('d M Y') ?? optional($pengumuman?->created_at)->format('d M Y') }}</span>
            </div>

            <h1 class="text-sky-900 mb-3" style="font-size:2rem;font-weight:800;line-height:1.2">{{ $pengumuman?->judul }}</h1>
            <p class="text-slate-400 mb-6" style="font-size:0.82rem">Dipublikasikan oleh {{ $pengumuman?->user?->name ?? 'Admin TirtaBantu' }}</p>

            <div class="text-slate-600 whitespace-pre-line" style="font-size:0.95rem;line-height:1.85">{{ $pengumuman?->isi }}</div>
        </article>
    </div>
</div>
@endsection
