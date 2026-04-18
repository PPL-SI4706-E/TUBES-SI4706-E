@extends('layouts.admin')
@section('title', 'Master Kategori')

@section('content')
<div x-data="{
    showForm: false,
    editMode: false,
    form: { id: null, nama_kategori: '', deskripsi: '', tarif: 0, icon: 'droplet', is_active: true },
    openAdd() {
        this.editMode = false;
        this.form = { id: null, nama_kategori: '', deskripsi: '', tarif: 0, icon: 'droplet', is_active: true };
        this.showForm = true;
    },
    openEdit(item) {
        this.editMode = true;
        this.form = { ...item };
        this.showForm = true;
    },
    close() { this.showForm = false; }
}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-sky-900" style="font-size:1.5rem;font-weight:700">Master Kategori Laporan</h1>
            <p class="text-slate-500" style="font-size:0.85rem">Kelola jenis-jenis laporan masalah air</p>
        </div>
        <button @click="openAdd()"
                class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
                style="font-size:0.85rem">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah
        </button>
    </div>

    {{-- Form (collapsible) --}}
    <div x-show="showForm" x-cloak x-transition
         class="bg-white rounded-xl p-5 border border-sky-100 shadow-sm mb-6">
        <h3 class="text-sky-800 mb-4" style="font-size:1rem;font-weight:600"
            x-text="editMode ? 'Edit Kategori' : 'Tambah Kategori'"></h3>

        <form method="POST"
              :action="editMode ? `/admin/master-kategori/${form.id}` : '{{ route('admin.master-kategori.store') }}'"
              class="space-y-4">
            @csrf
            <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

            <div>
                <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Nama Kategori</label>
                <input type="text" name="nama_kategori" x-model="form.nama_kategori" required
                       class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                       style="font-size:0.85rem" placeholder="cth. Pipa Bocor">
            </div>

            <div>
                <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Deskripsi</label>
                <textarea name="deskripsi" x-model="form.deskripsi" rows="2"
                          class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300 h-20 resize-none"
                          style="font-size:0.85rem" placeholder="Penjelasan singkat"></textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Tarif (Rp)</label>
                    <input type="number" name="tarif" x-model="form.tarif" min="0" step="1000"
                           class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                           style="font-size:0.85rem" placeholder="0 = Gratis">
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Ikon</label>
                    <select name="icon" x-model="form.icon"
                            class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                            style="font-size:0.85rem">
                        <option value="droplet">💧 Tetesan</option>
                        <option value="beaker">🧪 Kualitas Air</option>
                        <option value="truck">🚛 Tangki</option>
                        <option value="chart-bar">📊 Meteran</option>
                        <option value="x-circle">⛔ Tersumbat</option>
                        <option value="wrench">🔧 Perbaikan</option>
                    </select>
                </div>
            </div>

            <div x-show="editMode" class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="kat_active" value="1" x-model="form.is_active"
                       class="rounded border-sky-300 text-sky-600 focus:ring-sky-300">
                <label for="kat_active" class="text-slate-600" style="font-size:0.85rem">Kategori aktif</label>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="submit"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-lg transition-colors"
                        style="font-size:0.85rem">Simpan</button>
                <button type="button" @click="close()"
                        class="border border-sky-200 text-sky-700 px-5 py-2 rounded-lg hover:bg-sky-50 transition-colors"
                        style="font-size:0.85rem">Batal</button>
            </div>
        </form>
    </div>

    {{-- Cards Grid --}}
    @if($kategoris->isEmpty())
        <div class="bg-white rounded-xl p-12 border border-sky-100 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <p style="font-size:0.85rem">Belum ada kategori. Klik <strong>Tambah</strong> untuk mulai.</p>
        </div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $iconMap = [
                    'droplet' => '💧',
                    'beaker' => '🧪',
                    'truck' => '🚛',
                    'chart-bar' => '📊',
                    'x-circle' => '⛔',
                    'wrench' => '🔧',
                ];
            @endphp
            @foreach($kategoris as $k)
            <div class="bg-white rounded-xl p-5 border border-sky-100 shadow-sm hover:shadow-md transition-shadow {{ ! $k->is_active ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center mb-3" style="font-size:1.3rem">
                        {{ $iconMap[$k->icon] ?? '💧' }}
                    </div>
                    <div class="flex gap-1">
                        <button @click="openEdit({
                                    id: {{ $k->id }},
                                    nama_kategori: '{{ addslashes($k->nama_kategori) }}',
                                    deskripsi: '{{ addslashes($k->deskripsi ?? '') }}',
                                    tarif: {{ (int) $k->tarif }},
                                    icon: '{{ $k->icon }}',
                                    is_active: {{ $k->is_active ? 'true' : 'false' }}
                                })"
                                class="text-sky-600 hover:bg-sky-100 p-1.5 rounded-lg" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.master-kategori.destroy', $k) }}"
                              onsubmit="return confirm('Hapus kategori {{ addslashes($k->nama_kategori) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:bg-red-100 p-1.5 rounded-lg" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <h3 class="text-sky-800 mb-1" style="font-size:0.95rem;font-weight:600">{{ $k->nama_kategori }}</h3>
                <p class="text-slate-500 mb-3 line-clamp-2" style="font-size:0.8rem">{{ $k->deskripsi ?? '—' }}</p>

                <div class="rounded-lg p-2.5 {{ (int) $k->tarif === 0 ? 'bg-emerald-50' : 'bg-sky-50' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500" style="font-size:0.75rem">Tarif</span>
                        @if((int) $k->tarif === 0)
                            <span class="text-emerald-700" style="font-size:0.85rem;font-weight:700">GRATIS</span>
                        @else
                            <span class="text-sky-800" style="font-size:0.85rem;font-weight:700">{{ $k->formatted_tarif }}</span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <span class="text-slate-400" style="font-size:0.72rem">{{ $k->laporans_count }} laporan</span>
                    @if(! $k->is_active)
                        <span class="text-slate-400 italic" style="font-size:0.72rem">Nonaktif</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
