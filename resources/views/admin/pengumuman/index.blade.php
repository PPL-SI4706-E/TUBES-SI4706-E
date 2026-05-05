@extends('layouts.admin')
@section('title', 'Pengumuman')

@section('content')
    @php
        $kategoriStyles = [
            'darurat' => ['badge' => 'bg-red-100 text-red-600', 'border' => 'border-amber-200', 'label' => 'DARURAT'],
            'jadwal' => ['badge' => 'bg-blue-100 text-blue-600', 'border' => 'border-amber-200', 'label' => 'JADWAL'],
            'gangguan' => ['badge' => 'bg-amber-100 text-amber-600', 'border' => 'border-amber-200', 'label' => 'GANGGUAN'],
            'info' => ['badge' => 'bg-emerald-100 text-emerald-600', 'border' => 'border-slate-200', 'label' => 'INFORMASI'],
        ];
    @endphp

    <div class="space-y-7"
         x-data="{
            modalOpen: {{ $errors->any() ? 'true' : 'false' }},
            mode: 'create',
            form: {
                id: null,
                judul: @js(old('judul', '')),
                isi: @js(old('isi', '')),
                kategori: @js(old('kategori', 'info')),
                tanggal_post: @js(old('tanggal_post', now()->toDateString())),
                is_penting: {{ old('is_penting') ? 'true' : 'false' }}
            }
         }">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <p class="text-sky-600 uppercase tracking-[0.18em]" style="font-size:0.7rem;font-weight:800">Kelola Landing Page</p>
                <h1 class="text-sky-900 mt-2" style="font-size:2.25rem;font-weight:800;line-height:1.1">Pengumuman</h1>
                <p class="text-slate-500 mt-2" style="font-size:1rem">Kelola info gangguan distribusi air dan pengumuman publik</p>
            </div>
            <button type="button"
                    @click="modalOpen = true; mode = 'create'; form = { id: null, judul: '', isi: '', kategori: 'info', tanggal_post: '{{ now()->toDateString() }}', is_penting: false }"
                    class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-2xl transition-colors shadow-sm self-start"
                    style="font-size:0.92rem;font-weight:700">
                <span style="font-size:1.1rem">+</span>
                Buat Pengumuman
            </button>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl px-5 py-4">
                <p style="font-size:0.88rem;font-weight:800">Pengumuman belum berhasil disimpan.</p>
                <ul class="mt-2 space-y-1" style="font-size:0.82rem;line-height:1.6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-5">
            @forelse($pengumuman as $item)
                @php $style = $kategoriStyles[$item->kategori] ?? $kategoriStyles['info']; @endphp
                <article class="bg-white rounded-[28px] border {{ $style['border'] }} p-6 md:p-7 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <span class="text-sky-500 bg-sky-50 rounded-full p-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882L9.586 7.296A2 2 0 018.172 7.88L5.05 8.659A1 1 0 004 9.629V14.37a1 1 0 001.05.97l3.121.78a2 2 0 011.415.585L11 18.118m0-12.236V18.12m0-12.236l5.657-1.414A1 1 0 0118 5.44v13.12a1 1 0 01-1.343.948L11 18.118"/></svg>
                                </span>
                                <span class="px-3.5 py-1 rounded-full {{ $style['badge'] }}" style="font-size:0.78rem;font-weight:800">{{ $style['label'] }}</span>
                                @if($item->is_penting)
                                    <span class="px-3.5 py-1 rounded-full bg-amber-100 text-amber-700" style="font-size:0.78rem;font-weight:800">PENTING</span>
                                @endif
                                <span class="text-slate-400" style="font-size:0.9rem;font-weight:600">{{ optional($item->tanggal_post)->format('Y-m-d') }}</span>
                            </div>
                            <h2 class="text-sky-800" style="font-size:1.28rem;font-weight:800;line-height:1.35">{{ $item->judul }}</h2>
                            <p class="text-slate-600 mt-4 max-w-5xl" style="font-size:0.95rem;line-height:1.8">{{ $item->isi }}</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 pt-1">
                            <button type="button"
                                    @click="modalOpen = true; mode = 'edit'; form = { id: {{ $item->id }}, judul: @js($item->judul), isi: @js($item->isi), kategori: @js($item->kategori), tanggal_post: '{{ optional($item->tanggal_post)->format('Y-m-d') }}', is_penting: {{ $item->is_penting ? 'true' : 'false' }} }"
                                    class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 hover:bg-sky-100 hover:text-sky-700 transition-colors flex items-center justify-center"
                                    title="Edit pengumuman">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('admin.pengumuman.destroy', $item) }}" onsubmit="return confirm('Hapus pengumuman ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-700 transition-colors flex items-center justify-center" title="Hapus pengumuman">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-3xl border border-dashed border-sky-200 p-10 text-center">
                    <p class="text-sky-900" style="font-size:1.15rem;font-weight:800">Belum ada pengumuman</p>
                    <p class="text-slate-500 mt-2" style="font-size:0.9rem">Klik tombol <strong>Buat Pengumuman</strong> untuk menambahkan info publik pertama.</p>
                </div>
            @endforelse
        </div>

        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/55 backdrop-blur-sm p-4">
            <div @click.outside="modalOpen = false" class="w-full max-w-2xl bg-white rounded-[30px] shadow-2xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                    <div>
                        <h2 class="text-sky-900" style="font-size:1.25rem;font-weight:800" x-text="mode === 'create' ? 'Buat Pengumuman' : 'Edit Pengumuman'"></h2>
                        <p class="text-slate-500 mt-1" style="font-size:0.84rem">Kelola informasi yang akan tampil di landing page TirtaBantu.</p>
                    </div>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="mode === 'create' ? '{{ route('admin.pengumuman.store') }}' : '{{ url('/admin/pengumuman') }}/' + form.id" class="px-6 py-6 space-y-5">
                    @csrf
                    <template x-if="mode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-slate-700 mb-2" style="font-size:0.82rem;font-weight:700">Judul</label>
                        <input type="text" name="judul" x-model="form.judul" class="w-full rounded-2xl border border-sky-100 px-4 py-3.5 focus:border-sky-400 focus:ring-sky-400" placeholder="Masukkan judul pengumuman">
                        @error('judul')
                            <p class="text-red-500 mt-2" style="font-size:0.78rem">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="md:col-span-1">
                            <label class="block text-slate-700 mb-2" style="font-size:0.82rem;font-weight:700">Kategori</label>
                            <select name="kategori" x-model="form.kategori" class="w-full rounded-2xl border border-sky-100 px-4 py-3.5 focus:border-sky-400 focus:ring-sky-400">
                                <option value="darurat">Darurat</option>
                                <option value="jadwal">Jadwal</option>
                                <option value="gangguan">Gangguan</option>
                                <option value="info">Informasi</option>
                            </select>
                            @error('kategori')
                                <p class="text-red-500 mt-2" style="font-size:0.78rem">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-slate-700 mb-2" style="font-size:0.82rem;font-weight:700">Tanggal Post</label>
                            <input type="date" name="tanggal_post" x-model="form.tanggal_post" class="w-full rounded-2xl border border-sky-100 px-4 py-3.5 focus:border-sky-400 focus:ring-sky-400">
                            @error('tanggal_post')
                                <p class="text-red-500 mt-2" style="font-size:0.78rem">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-slate-700 mb-2" style="font-size:0.82rem;font-weight:700">Prioritas</label>
                            <label class="flex items-center gap-3 rounded-2xl border border-sky-100 px-4 py-3.5">
                                <input type="checkbox" name="is_penting" value="1" x-model="form.is_penting" class="rounded border-sky-300 text-sky-600 focus:ring-sky-500">
                                <span class="text-slate-600" style="font-size:0.84rem">Tandai sebagai penting</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-700 mb-2" style="font-size:0.82rem;font-weight:700">Isi Pengumuman</label>
                        <textarea name="isi" rows="6" x-model="form.isi" class="w-full rounded-2xl border border-sky-100 px-4 py-3.5 focus:border-sky-400 focus:ring-sky-400" placeholder="Tulis isi pengumuman untuk landing page..."></textarea>
                        @error('isi')
                            <p class="text-red-500 mt-2" style="font-size:0.78rem">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="modalOpen = false" class="px-5 py-3 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors" style="font-weight:700">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-3 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white transition-colors shadow-sm" style="font-weight:700">
                            Simpan Pengumuman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
