@extends('layouts.app')

@section('title', $pengumuman->judul)

@section('content')
@php
    $kategoriLabel = [
        'darurat' => 'DARURAT',
        'jadwal' => 'JADWAL',
        'informasi' => 'INFORMASI',
    ];

    $kategoriStyle = [
        'darurat' => 'bg-red-100 text-red-700',
        'jadwal' => 'bg-blue-100 text-blue-700',
        'informasi' => 'bg-emerald-100 text-emerald-700',
    ];
@endphp

<div class="min-h-screen bg-slate-50 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <a href="{{ route('home') }}#pengumuman" class="inline-flex items-center gap-2 text-sm text-sky-700 hover:text-sky-900 mb-6">
            <span aria-hidden="true">←</span>
            Kembali ke pengumuman
        </a>

        <article class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $kategoriStyle[$pengumuman->category] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $kategoriLabel[$pengumuman->category] ?? strtoupper($pengumuman->category) }}
                </span>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $pengumuman->priority === 'penting' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ strtoupper($pengumuman->priority) }}
                </span>
                <span class="text-sm text-slate-400">{{ optional($pengumuman->tanggal_post)->format('Y-m-d') }}</span>
            </div>

            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-4">{{ $pengumuman->judul }}</h1>
            <p class="text-slate-700 leading-7 whitespace-pre-line">{{ $pengumuman->isi }}</p>
        </article>
    </div>
</div>
@endsection
