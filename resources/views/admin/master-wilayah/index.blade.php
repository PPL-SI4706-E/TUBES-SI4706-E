@extends('layouts.admin')
@section('title', 'Master Wilayah')

@section('content')
<div x-data="{ showForm: false, editing: null, form: { nama_wilayah: '', tipe: 'kecamatan', kode_wilayah: '' } }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-sky-900" style="font-size:1.5rem;font-weight:700">Master Wilayah</h1>
            <p class="text-slate-500" style="font-size:0.85rem">Kelola data wilayah / area layanan distribusi air</p>
        </div>
        <button @click="showForm = true; editing = null; form = { nama_wilayah: '', tipe: 'kecamatan', kode_wilayah: '' }"
            class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors" style="font-size:0.85rem">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Wilayah
        </button>
    </div>

    {{-- Summary --}}
    @php
        $kecCount = $wilayahs->where('tipe', 'kecamatan')->count();
        $desaCount = $wilayahs->where('tipe', 'desa')->count();
        $kelCount = $wilayahs->where('tipe', 'kelurahan')->count();
    @endphp
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-sky-50 rounded-xl p-4 border border-sky-100 text-center">
            <svg class="w-5 h-5 text-sky-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-sky-700" style="font-size:1.5rem;font-weight:700">{{ $kecCount }}</p>
            <p class="text-sky-500" style="font-size:0.78rem">Kecamatan</p>
        </div>
        <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100 text-center">
            <svg class="w-5 h-5 text-emerald-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <p class="text-emerald-700" style="font-size:1.5rem;font-weight:700">{{ $desaCount }}</p>
            <p class="text-emerald-500" style="font-size:0.78rem">Desa</p>
        </div>
        <div class="bg-violet-50 rounded-xl p-4 border border-violet-100 text-center">
            <svg class="w-5 h-5 text-violet-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-violet-700" style="font-size:1.5rem;font-weight:700">{{ $kelCount }}</p>
            <p class="text-violet-500" style="font-size:0.78rem">Kelurahan</p>
        </div>
    </div>

    {{-- Add/Edit Form --}}
    <div x-show="showForm" x-transition class="bg-white rounded-xl p-5 border border-sky-100 shadow-sm mb-6">
        <h3 class="text-sky-800 mb-4" style="font-size:1rem;font-weight:600" x-text="editing ? 'Edit Wilayah' : 'Tambah Wilayah'"></h3>
        <form :action="editing ? '{{ url('admin/master-wilayah') }}/' + editing : '{{ route('admin.master-wilayah.store') }}'" method="POST">
            @csrf
            <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Nama Wilayah *</label>
                    <input name="nama_wilayah" x-model="form.nama_wilayah" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" required placeholder="Contoh: Cianjur">
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Tipe *</label>
                    <select name="tipe" x-model="form.tipe" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" required>
                        <option value="kecamatan">Kecamatan</option>
                        <option value="desa">Desa</option>
                        <option value="kelurahan">Kelurahan</option>
                    </select>
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Kode Wilayah</label>
                    <input name="kode_wilayah" x-model="form.kode_wilayah" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" placeholder="Contoh: 3201">
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-lg transition-colors" style="font-size:0.85rem">Simpan</button>
                <button type="button" @click="showForm = false" class="border border-sky-200 text-sky-700 px-5 py-2 rounded-lg hover:bg-sky-50 transition-colors" style="font-size:0.85rem">Batal</button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-sky-100 shadow-sm overflow-x-auto">
        <table class="w-full" style="font-size:0.85rem">
            <thead>
                <tr class="text-left text-slate-500 border-b border-sky-100 bg-sky-50/50">
                    <th class="p-4">ID</th>
                    <th class="p-4">Nama Wilayah</th>
                    <th class="p-4">Tipe</th>
                    <th class="p-4">Kode</th>
                    <th class="p-4">Jml. Laporan</th>
                    <th class="p-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wilayahs as $w)
                    @php
                        $tipeBadge = match($w->tipe) {
                            'kecamatan' => 'bg-sky-100 text-sky-700',
                            'desa' => 'bg-emerald-100 text-emerald-700',
                            'kelurahan' => 'bg-violet-100 text-violet-700',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <tr class="border-b border-sky-50 hover:bg-sky-50/30">
                        <td class="p-4 text-sky-600" style="font-weight:600">{{ $w->id }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-sky-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                </div>
                                <span class="text-slate-800 font-medium">{{ $w->nama_wilayah }}</span>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="{{ $tipeBadge }} px-2.5 py-1 rounded-full" style="font-size:0.75rem;font-weight:600">{{ ucfirst($w->tipe) }}</span>
                        </td>
                        <td class="p-4 text-slate-500">{{ $w->kode_wilayah ?? '-' }}</td>
                        <td class="p-4">
                            <span class="bg-sky-50 text-sky-700 px-2.5 py-1 rounded-full" style="font-size:0.75rem;font-weight:600">{{ $w->laporans_count ?? 0 }}</span>
                        </td>
                        <td class="p-4 flex gap-2">
                            <button @click="showForm = true; editing = {{ $w->id }}; form = { nama_wilayah: '{{ addslashes($w->nama_wilayah) }}', tipe: '{{ $w->tipe }}', kode_wilayah: '{{ $w->kode_wilayah }}' }"
                                class="text-sky-600 hover:bg-sky-100 p-1.5 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form action="{{ route('admin.master-wilayah.destroy', $w) }}" method="POST" onsubmit="return confirm('Yakin hapus wilayah ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:bg-red-100 p-1.5 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $wilayahs->links() }}
    </div>
</div>
@endsection
