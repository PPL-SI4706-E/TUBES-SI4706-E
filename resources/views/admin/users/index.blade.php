@extends('layouts.admin')
@section('title', 'Manajemen Pengguna')

@section('content')
<div x-data="{ showForm: false, editing: null, form: { name: '', email: '', password: '', role: 'masyarakat', phone: '', wilayah_id: '' } }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-sky-900" style="font-size:1.5rem;font-weight:700">Manajemen Pengguna</h1>
            <p class="text-slate-500" style="font-size:0.85rem">Kelola data pengguna dan role akses</p>
        </div>
        <button @click="showForm = true; editing = null; form = { name: '', email: '', password: '', role: 'masyarakat', phone: '', wilayah_id: '' }"
            class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors" style="font-size:0.85rem">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </button>
    </div>

    {{-- Summary --}}
    @php
        $adminCount = $users->where('role', 'admin')->count();
        $petugasCount = $users->where('role', 'petugas')->count();
        $masyarakatCount = $users->where('role', 'masyarakat')->count();
    @endphp
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 text-center">
            <svg class="w-5 h-5 text-purple-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <p class="text-purple-700" style="font-size:1.5rem;font-weight:700">{{ $adminCount }}</p>
            <p class="text-purple-500" style="font-size:0.78rem">Admin</p>
        </div>
        <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100 text-center">
            <svg class="w-5 h-5 text-emerald-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-emerald-700" style="font-size:1.5rem;font-weight:700">{{ $petugasCount }}</p>
            <p class="text-emerald-500" style="font-size:0.78rem">Petugas</p>
        </div>
        <div class="bg-amber-50 rounded-xl p-4 border border-amber-100 text-center">
            <svg class="w-5 h-5 text-amber-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <p class="text-amber-700" style="font-size:1.5rem;font-weight:700">{{ $masyarakatCount }}</p>
            <p class="text-amber-500" style="font-size:0.78rem">Masyarakat</p>
        </div>
    </div>

    {{-- Add/Edit Form --}}
    <div x-show="showForm" x-transition class="bg-white rounded-xl p-5 border border-sky-100 shadow-sm mb-6">
        <h3 class="text-sky-800 mb-4" style="font-size:1rem;font-weight:600" x-text="editing ? 'Edit Pengguna' : 'Tambah Pengguna'"></h3>
        <form :action="editing ? '{{ url('admin/users') }}/' + editing : '{{ route('admin.users.store') }}'" method="POST">
            @csrf
            <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Nama Lengkap *</label>
                    <input name="name" x-model="form.name" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" required>
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Email *</label>
                    <input type="email" name="email" x-model="form.email" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" required>
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Password <span x-show="editing" class="text-slate-400">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" x-model="form.password" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" :required="!editing">
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Role *</label>
                    <select name="role" x-model="form.role" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" required>
                        <option value="masyarakat">Masyarakat</option>
                        <option value="petugas">Petugas</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Telepon</label>
                    <input name="phone" x-model="form.phone" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Wilayah</label>
                    <select name="wilayah_id" x-model="form.wilayah_id" class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem">
                        <option value="">-- Tidak Ada --</option>
                        @foreach($wilayahs as $w)
                            <option value="{{ $w->id }}">{{ $w->nama_wilayah }}</option>
                        @endforeach
                    </select>
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
                    <th class="p-4">Nama</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Telepon</th>
                    <th class="p-4">Role</th>
                    <th class="p-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    @php
                        $roleBadge = match($u->role) {
                            'admin' => 'bg-purple-100 text-purple-700',
                            'petugas' => 'bg-emerald-100 text-emerald-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                        $roleLabel = match($u->role) {
                            'admin' => 'Admin',
                            'petugas' => 'Petugas',
                            default => 'Masyarakat',
                        };
                    @endphp
                    <tr class="border-b border-sky-50 hover:bg-sky-50/30">
                        <td class="p-4 text-sky-600" style="font-weight:600">{{ $u->id }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-sky-100 rounded-full flex items-center justify-center text-sky-700" style="font-weight:600;font-size:0.85rem">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <span class="text-slate-800">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="p-4 text-slate-500">{{ $u->email }}</td>
                        <td class="p-4 text-slate-500">{{ $u->phone ?? '-' }}</td>
                        <td class="p-4">
                            <span class="{{ $roleBadge }} px-2.5 py-1 rounded-full" style="font-size:0.75rem;font-weight:600">{{ $roleLabel }}</span>
                        </td>
                        <td class="p-4 flex gap-2">
                            <button @click="showForm = true; editing = {{ $u->id }}; form = { name: '{{ addslashes($u->name) }}', email: '{{ $u->email }}', password: '', role: '{{ $u->role }}', phone: '{{ $u->phone }}', wilayah_id: '{{ $u->wilayah_id }}' }"
                                class="text-sky-600 hover:bg-sky-100 p-1.5 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Yakin hapus pengguna ini?')">
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
        {{ $users->links() }}
    </div>
</div>
@endsection
