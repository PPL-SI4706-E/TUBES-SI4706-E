@extends('layouts.admin')
@section('title', 'Pengumuman')

@section('content')
@php
    $kategoriMeta = [
        'darurat' => ['label' => 'DARURAT', 'class' => 'bg-red-100 text-red-600'],
        'jadwal' => ['label' => 'JADWAL', 'class' => 'bg-blue-100 text-blue-600'],
        'informasi' => ['label' => 'INFORMASI', 'class' => 'bg-emerald-100 text-emerald-600'],
    ];
@endphp

<div
    x-data="{
        showForm: false,
        editing: null,
        form: {
            judul: '',
            isi: '',
            kategori: 'informasi',
            is_penting: false,
            tanggal_post: '{{ now()->format('Y-m-d') }}'
        }
    }"
>
    <div class="flex items-start justify-between gap-4 mb-8">
        <div>
            <h1 class="text-sky-900 mb-1" style="font-size:1.8rem;font-weight:800">Pengumuman</h1>
            <p class="text-slate-500" style="font-size:0.95rem">Kelola info gangguan distribusi air dan pengumuman publik</p>
        </div>
        <button
            @click="showForm = true; editing = null; form = { judul: '', isi: '', kategori: 'informasi', is_penting: false, tanggal_post: '{{ now()->format('Y-m-d') }}' }"
            class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-3 rounded-2xl flex items-center gap-2 transition-colors shadow-sm"
            style="font-size:0.95rem;font-weight:700"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Pengumuman
        </button>
    </div>

    <div x-show="showForm" x-transition class="bg-white rounded-2xl p-6 border border-sky-100 shadow-sm mb-6">
        <h3 class="text-sky-900 mb-5" style="font-size:1.05rem;font-weight:700" x-text="editing ? 'Edit Pengumuman' : 'Buat Pengumuman Baru'"></h3>
        <form :action="editing ? '{{ url('admin/pengumuman') }}/' + editing : '{{ route('admin.pengumuman.store') }}'" method="POST">
            @csrf
            <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

            <div class="grid lg:grid-cols-2 gap-5">
                <div class="lg:col-span-2">
                    <label class="text-slate-700 mb-1.5 block" style="font-size:0.82rem;font-weight:700">Judul Pengumuman</label>
                    <input name="judul" x-model="form.judul" class="w-full px-4 py-3 border border-sky-200 rounded-xl bg-sky-50/40 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.9rem" required>
                </div>

                <div>
                    <label class="text-slate-700 mb-1.5 block" style="font-size:0.82rem;font-weight:700">Kategori</label>
                    <select name="kategori" x-model="form.kategori" class="w-full px-4 py-3 border border-sky-200 rounded-xl bg-sky-50/40 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.9rem" required>
                        <option value="darurat">Darurat</option>
                        <option value="jadwal">Jadwal</option>
                        <option value="informasi">Informasi</option>
                    </select>
                </div>

                <div>
                    <label class="text-slate-700 mb-1.5 block" style="font-size:0.82rem;font-weight:700">Tanggal Posting</label>
                    <input type="date" name="tanggal_post" x-model="form.tanggal_post" class="w-full px-4 py-3 border border-sky-200 rounded-xl bg-sky-50/40 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.9rem" required>
                </div>

                <div class="lg:col-span-2">
                    <label class="text-slate-700 mb-1.5 block" style="font-size:0.82rem;font-weight:700">Isi Pengumuman</label>
                    <textarea name="isi" x-model="form.isi" class="w-full px-4 py-3 border border-sky-200 rounded-xl bg-sky-50/40 focus:outline-none focus:ring-2 focus:ring-sky-300 h-36 resize-none" style="font-size:0.9rem" required></textarea>
                </div>

                <div class="lg:col-span-2">
                    <label class="inline-flex items-center gap-3 rounded-xl bg-amber-50 border border-amber-100 px-4 py-3 cursor-pointer">
                        <input type="checkbox" name="is_penting" value="1" x-model="form.is_penting" class="rounded border-amber-300 text-amber-500 focus:ring-amber-300">
                        <span class="text-amber-700" style="font-size:0.88rem;font-weight:700">Tandai sebagai pengumuman penting</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl transition-colors" style="font-size:0.9rem;font-weight:700">Simpan</button>
                <button type="button" @click="showForm = false" class="border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl hover:bg-slate-50 transition-colors" style="font-size:0.9rem;font-weight:700">Batal</button>
            </div>
        </form>
    </div>

    <div class="space-y-5">
        @forelse($pengumumanList as $item)
            @php
                $meta = $kategoriMeta[$item->kategori] ?? $kategoriMeta['informasi'];
            @endphp
            <article class="bg-white rounded-[22px] border shadow-sm p-6 transition-all {{ $item->is_penting ? 'border-amber-200' : 'border-sky-100' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 11l18-5-5 18-2-7-7-2z"/></svg>
                            </div>
                            <span class="{{ $meta['class'] }} px-3 py-1 rounded-full" style="font-size:0.8rem;font-weight:800">{{ $meta['label'] }}</span>
                            @if($item->is_penting)
                                <span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full inline-flex items-center gap-1.5" style="font-size:0.8rem;font-weight:800">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                                    PENTING
                                </span>
                            @endif
                            <span class="text-slate-400" style="font-size:0.95rem;font-weight:600">{{ optional($item->tanggal_post)->format('Y-m-d') }}</span>
                        </div>

                        <h2 class="text-sky-800 mb-3" style="font-size:1.05rem;font-weight:800">{{ $item->judul }}</h2>
                        <p class="text-slate-600 max-w-5xl" style="font-size:0.95rem;line-height:1.75">{{ $item->isi }}</p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            @click="showForm = true; editing = {{ $item->id }}; form = { judul: @js($item->judul), isi: @js($item->isi), kategori: '{{ $item->kategori }}', is_penting: {{ $item->is_penting ? 'true' : 'false' }}, tanggal_post: '{{ optional($item->tanggal_post)->format('Y-m-d') }}' }"
                            class="text-sky-500 hover:bg-sky-50 p-2 rounded-lg"
                            title="Edit pengumuman"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form action="{{ route('admin.pengumuman.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin hapus pengumuman ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:bg-red-50 p-2 rounded-lg" title="Hapus pengumuman">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-sky-200 p-10 text-center text-slate-500" style="font-size:0.9rem">
                Belum ada pengumuman yang dibuat.
            </div>
        @endforelse
    </div>
</div>
@endsection
