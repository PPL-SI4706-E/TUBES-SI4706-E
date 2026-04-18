@extends('layouts.admin')
@section('title', 'Master Kategori Laporan')

@section('content')
<div x-data="{ showForm: false, editing: null, form: { nama_kategori: '', deskripsi: '', tarif: 0, icon: '📋' } }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-sky-900" style="font-size:1.5rem;font-weight:700">Master Kategori Laporan</h1>
            <p class="text-slate-500" style="font-size:0.85rem">Kelola jenis-jenis laporan masalah air</p>
        </div>
        <button @click="showForm = true; editing = null; form = { nama_kategori: '', deskripsi: '', tarif: 0, icon: '📋' }"
            class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors" style="font-size:0.85rem">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah
        </button>
    </div>

    {{-- Add/Edit Form --}}
    <div x-show="showForm" x-transition class="bg-white rounded-xl p-5 border border-sky-100 shadow-sm mb-6">
        <h3 class="text-sky-800 mb-4" style="font-size:1rem;font-weight:600" x-text="editing ? 'Edit Kategori' : 'Tambah Kategori'"></h3>
        <form :action="editing ? '{{ url('admin/master-kategori') }}/' + editing : '{{ route('admin.master-kategori.store') }}'" method="POST">
            @csrf
            <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
            <div class="space-y-4">
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Nama Kategori</label>
                    <input name="nama_kategori" x-model="form.nama_kategori" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" required>
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Deskripsi</label>
                    <textarea name="deskripsi" x-model="form.deskripsi" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300 h-20 resize-none" style="font-size:0.85rem"></textarea>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Tarif (Rp)</label>
                        <input type="number" name="tarif" x-model="form.tarif" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" placeholder="0 = Gratis">
                    </div>
                    <div>
                        <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Ikon (Emoji)</label>
                        <input name="icon" x-model="form.icon" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" placeholder="🔧">
                    </div>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-lg transition-colors" style="font-size:0.85rem">Simpan</button>
                <button type="button" @click="showForm = false" class="border border-sky-200 text-sky-700 px-5 py-2 rounded-lg hover:bg-sky-50 transition-colors" style="font-size:0.85rem">Batal</button>
            </div>
        </form>
    </div>

    {{-- Card Grid --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($kategoris as $k)
            <div class="bg-white rounded-xl p-5 border border-sky-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center mb-3" style="font-size:1.3rem">
                        {{ $k->icon ?? '📋' }}
                    </div>
                    <div class="flex gap-1">
                        <button @click="showForm = true; editing = {{ $k->id }}; form = { nama_kategori: '{{ addslashes($k->nama_kategori) }}', deskripsi: '{{ addslashes($k->deskripsi) }}', tarif: {{ $k->tarif }}, icon: '{{ $k->icon ?? '📋' }}' }"
                            class="text-sky-600 hover:bg-sky-100 p-1.5 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form action="{{ route('admin.master-kategori.destroy', $k) }}" method="POST" onsubmit="return confirm('Yakin hapus kategori ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:bg-red-100 p-1.5 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                <h3 class="text-sky-800 mb-1" style="font-size:0.95rem;font-weight:600">{{ $k->nama_kategori }}</h3>
                <p class="text-slate-500 mb-3" style="font-size:0.8rem">{{ $k->deskripsi }}</p>
                <div class="rounded-lg p-2.5 {{ $k->tarif == 0 ? 'bg-emerald-50' : 'bg-sky-50' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500" style="font-size:0.75rem">Tarif</span>
                        @if($k->tarif == 0)
                            <span class="text-emerald-700" style="font-size:0.85rem;font-weight:700">GRATIS</span>
                        @else
                            <span class="text-sky-800" style="font-size:0.85rem;font-weight:700">Rp {{ number_format($k->tarif, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
