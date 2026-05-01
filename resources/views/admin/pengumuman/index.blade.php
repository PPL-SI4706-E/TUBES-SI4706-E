@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
@php
    $categoryStyles = [
        'darurat' => 'bg-red-100 text-red-700',
        'jadwal' => 'bg-blue-100 text-blue-700',
        'informasi' => 'bg-emerald-100 text-emerald-700',
    ];

    $categoryLabels = [
        'darurat' => 'DARURAT',
        'jadwal' => 'JADWAL',
        'informasi' => 'INFORMASI',
    ];

    $priorityStyles = [
        'penting' => 'bg-amber-100 text-amber-700',
        'normal' => 'bg-slate-100 text-slate-600',
    ];

    $priorityLabels = [
        'penting' => 'PENTING',
        'normal' => 'NORMAL',
    ];
@endphp

<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pengumuman</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola pengumuman yang ditampilkan kepada masyarakat.</p>
        </div>
        <a href="{{ route('admin.pengumuman.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-white hover:bg-sky-700 shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Buat Pengumuman
        </a>
    </div>

    @forelse($pengumuman as $item)
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $categoryStyles[$item->category] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ $categoryLabels[$item->category] ?? strtoupper($item->category) }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $priorityStyles[$item->priority] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ $priorityLabels[$item->priority] ?? strtoupper($item->priority) }}
                        </span>
                        <span class="text-sm text-slate-400">{{ optional($item->tanggal_post)->format('Y-m-d') }}</span>
                        @unless($item->is_published)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-200 text-slate-700">DRAFT</span>
                        @endunless
                    </div>

                    <h2 class="text-lg font-bold text-slate-900 mb-2">{{ $item->judul }}</h2>
                    <p class="text-sm text-slate-600 leading-6" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                        {{ $item->isi }}
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('admin.pengumuman.edit', $item) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-sky-50 hover:text-sky-700" title="Edit">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </a>
                    <form method="POST" action="{{ route('admin.pengumuman.destroy', $item) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-red-50 hover:text-red-600" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center">
            <i data-lucide="megaphone-off" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
            <h2 class="text-lg font-semibold text-slate-700">Belum ada pengumuman</h2>
            <p class="text-sm text-slate-500 mt-1">Mulai dengan membuat pengumuman pertama untuk masyarakat.</p>
        </div>
    @endforelse

    <div>
        {{ $pengumuman->links() }}
    </div>
</div>
@endsection
