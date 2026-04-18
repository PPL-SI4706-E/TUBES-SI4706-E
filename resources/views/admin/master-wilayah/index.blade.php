@extends('layouts.admin')
@section('title', 'Master Wilayah')

@section('content')
<div x-data="{
    showForm: false,
    editMode: false,
    form: { id: null, nama_wilayah: '', tipe: 'desa', kode_wilayah: '' },
    openAdd() {
        this.editMode = false;
        this.form = { id: null, nama_wilayah: '', tipe: 'desa', kode_wilayah: '' };
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
            <h1 class="text-sky-900" style="font-size:1.5rem;font-weight:700">Master Wilayah</h1>
            <p class="text-slate-500" style="font-size:0.85rem">Kelola data wilayah layanan (kecamatan / desa / kelurahan)</p>
        </div>
        <button @click="openAdd()"
                class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
                style="font-size:0.85rem">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah
        </button>
    </div>

    {{-- Summary --}}
    @php
        $totalKec  = \App\Models\Wilayah::where('tipe','kecamatan')->count();
        $totalDesa = \App\Models\Wilayah::where('tipe','desa')->count();
        $totalKel  = \App\Models\Wilayah::where('tipe','kelurahan')->count();
    @endphp
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 text-center">
            <svg class="w-5 h-5 text-blue-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5"/></svg>
            <p class="text-blue-700" style="font-size:1.5rem;font-weight:700">{{ $totalKec }}</p>
            <p class="text-blue-500" style="font-size:0.78rem">Kecamatan</p>
        </div>
        <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100 text-center">
            <svg class="w-5 h-5 text-emerald-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-emerald-700" style="font-size:1.5rem;font-weight:700">{{ $totalDesa }}</p>
            <p class="text-emerald-500" style="font-size:0.78rem">Desa</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 text-center">
            <svg class="w-5 h-5 text-purple-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="text-purple-700" style="font-size:1.5rem;font-weight:700">{{ $totalKel }}</p>
            <p class="text-purple-500" style="font-size:0.78rem">Kelurahan</p>
        </div>
    </div>

    {{-- Form --}}
    <div x-show="showForm" x-cloak x-transition
         class="bg-white rounded-xl p-5 border border-sky-100 shadow-sm mb-6">
        <h3 class="text-sky-800 mb-4" style="font-size:1rem;font-weight:600"
            x-text="editMode ? 'Edit Wilayah' : 'Tambah Wilayah'"></h3>

        <form method="POST"
              :action="editMode ? `/admin/master-wilayah/${form.id}` : '{{ route('admin.master-wilayah.store') }}'"
              class="space-y-4">
            @csrf
            <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

            <div class="grid sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Nama Wilayah <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_wilayah" x-model="form.nama_wilayah" required
                           placeholder="cth. Desa Sukamaju"
                           class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                           style="font-size:0.85rem">
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Tipe <span class="text-red-500">*</span></label>
                    <select name="tipe" x-model="form.tipe" required
                            class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                            style="font-size:0.85rem">
                        <option value="desa">Desa</option>
                        <option value="kelurahan">Kelurahan</option>
                        <option value="kecamatan">Kecamatan</option>
                    </select>
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Kode Wilayah</label>
                    <input type="text" name="kode_wilayah" x-model="form.kode_wilayah"
                           placeholder="cth. DSM-001"
                           class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                           style="font-size:0.85rem">
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-lg transition-colors"
                        style="font-size:0.85rem">Simpan</button>
                <button type="button" @click="close()"
                        class="border border-sky-200 text-sky-700 px-5 py-2 rounded-lg hover:bg-sky-50 transition-colors"
                        style="font-size:0.85rem">Batal</button>
            </div>
        </form>
    </div>

    {{-- Grid --}}
    @if($wilayahs->isEmpty())
        <div class="bg-white rounded-xl p-12 border border-sky-100 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            </svg>
            <p style="font-size:0.85rem">Belum ada wilayah. Klik <strong>Tambah</strong> untuk mulai.</p>
        </div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($wilayahs as $w)
                @php
                    $tipeStyle = [
                        'kecamatan' => 'bg-blue-100 text-blue-700',
                        'desa'      => 'bg-emerald-100 text-emerald-700',
                        'kelurahan' => 'bg-purple-100 text-purple-700',
                    ][$w->tipe] ?? 'bg-slate-100 text-slate-700';
                @endphp
                <div class="bg-white rounded-xl p-5 border border-sky-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="flex gap-1">
                            <button @click="openEdit({
                                        id: {{ $w->id }},
                                        nama_wilayah: '{{ addslashes($w->nama_wilayah) }}',
                                        tipe: '{{ $w->tipe }}',
                                        kode_wilayah: '{{ $w->kode_wilayah ?? '' }}'
                                    })"
                                    class="text-sky-600 hover:bg-sky-100 p-1.5 rounded-lg" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('admin.master-wilayah.destroy', $w) }}"
                                  onsubmit="return confirm('Hapus wilayah {{ addslashes($w->nama_wilayah) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:bg-red-100 p-1.5 rounded-lg" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <h3 class="text-sky-800 mb-1" style="font-size:0.95rem;font-weight:600">{{ $w->nama_wilayah }}</h3>
                    <p class="text-slate-500 mb-3" style="font-size:0.8rem">{{ $w->kode_wilayah ?? '—' }}</p>

                    <div class="flex items-center justify-between">
                        <span class="{{ $tipeStyle }} px-2.5 py-1 rounded-full" style="font-size:0.7rem;font-weight:600">
                            {{ ucfirst($w->tipe) }}
                        </span>
                        <span class="text-slate-400" style="font-size:0.72rem">{{ $w->laporans_count }} laporan</span>
                    </div>
                </div>
            @endforeach
        </div>

        @if(method_exists($wilayahs, 'hasPages') && $wilayahs->hasPages())
            <div class="mt-6">{{ $wilayahs->links() }}</div>
        @endif
    @endif

</div>
@endsection
