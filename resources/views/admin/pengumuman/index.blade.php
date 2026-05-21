@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
@php
    $kategoriStyles = [
        'darurat' => ['badge' => 'bg-red-100 text-red-700', 'label' => 'DARURAT'],
        'gangguan' => ['badge' => 'bg-amber-100 text-amber-700', 'label' => 'GANGGUAN'],
        'jadwal' => ['badge' => 'bg-blue-100 text-blue-700', 'label' => 'JADWAL'],
        'info' => ['badge' => 'bg-emerald-100 text-emerald-700', 'label' => 'INFORMASI'],
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-sky-900">Pengumuman</h1>
            <p class="text-slate-500 mt-1">Kelola info gangguan distribusi air dan pengumuman publik</p>
        </div>
        <button type="button" onclick="document.getElementById('form-pengumuman').scrollIntoView({ behavior: 'smooth' })" class="inline-flex items-center gap-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white px-5 py-3 shadow-md text-sm font-semibold">
            <span class="text-lg leading-none">+</span>
            Buat Pengumuman
        </button>
    </div>

    <div id="form-pengumuman" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Form Pengumuman Baru</h2>
        <form method="POST" action="{{ route('admin.pengumuman.store') }}" class="grid lg:grid-cols-2 gap-4">
            @csrf
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Judul</label>
                <input type="text" name="judul" value="{{ old('judul') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-sky-500 focus:outline-none">
                @error('judul')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                <select name="kategori" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-sky-500 focus:outline-none">
                    @foreach(['darurat' => 'Darurat', 'gangguan' => 'Gangguan', 'jadwal' => 'Jadwal', 'info' => 'Informasi'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('kategori', 'info') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('kategori')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Posting</label>
                <input type="date" name="tanggal_post" value="{{ old('tanggal_post', now()->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-sky-500 focus:outline-none">
                @error('tanggal_post')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Isi Pengumuman</label>
                <textarea name="isi" rows="5" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-sky-500 focus:outline-none">{{ old('isi') }}</textarea>
                @error('isi')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="lg:col-span-2 flex items-center justify-between gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_penting" value="1" @checked(old('is_penting')) class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    Tandai sebagai pengumuman penting
                </label>
                <button type="submit" class="rounded-xl bg-sky-600 hover:bg-sky-700 text-white px-5 py-3 text-sm font-semibold">
                    Simpan Pengumuman
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($pengumuman as $item)
            @php
                $style = $kategoriStyles[$item->kategori] ?? $kategoriStyles['info'];
            @endphp
            <article class="bg-white border border-amber-200 rounded-3xl p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $style['badge'] }}">{{ $style['label'] }}</span>
                            @if($item->is_penting)
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold bg-amber-100 text-amber-700">PENTING</span>
                            @endif
                            <span class="text-sm text-slate-400">{{ optional($item->tanggal_post)->format('Y-m-d') }}</span>
                        </div>

                        <h3 class="text-2xl font-bold text-sky-800 mb-2">{{ $item->judul }}</h3>
                        <p class="text-slate-600 leading-8">{{ $item->isi }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <details class="group">
                            <summary class="list-none cursor-pointer rounded-lg border border-sky-200 px-3 py-2 text-sky-600 hover:bg-sky-50 text-sm font-medium">
                                Edit
                            </summary>
                            <div class="mt-3 w-[340px] max-w-[85vw] rounded-2xl border border-slate-200 bg-white p-4 shadow-xl">
                                <form method="POST" action="{{ route('admin.pengumuman.update', $item) }}" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="judul" value="{{ $item->judul }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <select name="kategori" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                        @foreach(['darurat' => 'Darurat', 'gangguan' => 'Gangguan', 'jadwal' => 'Jadwal', 'info' => 'Informasi'] as $value => $label)
                                            <option value="{{ $value }}" @selected($item->kategori === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="date" name="tanggal_post" value="{{ optional($item->tanggal_post)->format('Y-m-d') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <textarea name="isi" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $item->isi }}</textarea>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                        <input type="checkbox" name="is_penting" value="1" @checked($item->is_penting) class="rounded border-slate-300 text-sky-600">
                                        Tandai penting
                                    </label>
                                    <button type="submit" class="w-full rounded-lg bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 text-sm font-semibold">Simpan Perubahan</button>
                                </form>
                            </div>
                        </details>

                        <form method="POST" action="{{ route('admin.pengumuman.destroy', $item) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-rose-200 px-3 py-2 text-rose-600 hover:bg-rose-50 text-sm font-medium">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-8 text-center text-slate-500">
                Belum ada pengumuman. Buat pengumuman pertama agar tampil di beranda.
            </div>
        @endforelse
    </div>
</div>
@endsection
