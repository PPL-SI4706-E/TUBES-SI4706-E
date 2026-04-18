@extends('layouts.admin')
@section('title', 'Manajemen Pengguna')

@section('content')
<div x-data="{
    modalOpen: false,
    editMode: false,
    form: { id: null, name: '', email: '', role: 'masyarakat', phone: '', wilayah_id: '', is_active: true },
    openAdd() {
        this.editMode = false;
        this.form = { id: null, name: '', email: '', role: 'masyarakat', phone: '', wilayah_id: '', is_active: true };
        this.modalOpen = true;
    },
    openEdit(user) {
        this.editMode = true;
        this.form = { ...user };
        this.modalOpen = true;
    }
}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-sky-900" style="font-size:1.5rem;font-weight:700">Manajemen Pengguna</h1>
            <p class="text-slate-500" style="font-size:0.85rem">Kelola data pengguna dan role akses</p>
        </div>
        <button @click="openAdd()"
                class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
                style="font-size:0.85rem">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </button>
    </div>

    {{-- Summary --}}
    @php
        $adminCount      = $users->where('role','admin')->count();
        $petugasCount    = $users->where('role','petugas')->count();
        $masyarakatCount = $users->where('role','masyarakat')->count();
        // Use total from paginator when available
        $totalAdmin      = \App\Models\User::where('role','admin')->count();
        $totalPetugas    = \App\Models\User::where('role','petugas')->count();
        $totalMasyarakat = \App\Models\User::where('role','masyarakat')->count();
    @endphp

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 text-center">
            <svg class="w-5 h-5 text-purple-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <p class="text-purple-700" style="font-size:1.5rem;font-weight:700">{{ $totalAdmin }}</p>
            <p class="text-purple-500" style="font-size:0.78rem">Admin</p>
        </div>
        <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100 text-center">
            <svg class="w-5 h-5 text-emerald-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
            <p class="text-emerald-700" style="font-size:1.5rem;font-weight:700">{{ $totalPetugas }}</p>
            <p class="text-emerald-500" style="font-size:0.78rem">Petugas</p>
        </div>
        <div class="bg-amber-50 rounded-xl p-4 border border-amber-100 text-center">
            <svg class="w-5 h-5 text-amber-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75M7 20H2v-2a4 4 0 013-3.87m4-12a4 4 0 100 7.75"/></svg>
            <p class="text-amber-700" style="font-size:1.5rem;font-weight:700">{{ $totalMasyarakat }}</p>
            <p class="text-amber-500" style="font-size:0.78rem">Masyarakat</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-sky-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" style="font-size:0.85rem">
                <thead>
                    <tr class="text-left text-slate-500 border-b border-sky-100 bg-sky-50/50">
                        <th class="p-4" style="font-weight:600">ID</th>
                        <th class="p-4" style="font-weight:600">Nama</th>
                        <th class="p-4" style="font-weight:600">Email</th>
                        <th class="p-4" style="font-weight:600">Telepon</th>
                        <th class="p-4" style="font-weight:600">Wilayah</th>
                        <th class="p-4" style="font-weight:600">Role</th>
                        <th class="p-4" style="font-weight:600">Status</th>
                        <th class="p-4" style="font-weight:600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $roleStyles = [
                                'admin'      => 'bg-purple-100 text-purple-700',
                                'petugas'    => 'bg-emerald-100 text-emerald-700',
                                'masyarakat' => 'bg-amber-100 text-amber-700',
                            ];
                        @endphp
                    <tr class="border-b border-sky-50 hover:bg-sky-50/30 transition-colors">
                        <td class="p-4 text-sky-600" style="font-weight:600">#{{ $user->id }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-sky-100 rounded-full flex items-center justify-center text-sky-700 shrink-0"
                                     style="font-weight:600;font-size:0.85rem">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="text-slate-800">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="p-4 text-slate-500">{{ $user->email }}</td>
                        <td class="p-4 text-slate-500">{{ $user->phone ?? '—' }}</td>
                        <td class="p-4 text-slate-500">{{ $user->wilayah?->nama_wilayah ?? '—' }}</td>
                        <td class="p-4">
                            <span class="{{ $roleStyles[$user->role] ?? 'bg-slate-100 text-slate-600' }} px-2.5 py-1 rounded-full"
                                  style="font-size:0.75rem;font-weight:600">
                                {{ $user->role_label }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-1 text-emerald-700" style="font-size:0.78rem;font-weight:500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-slate-400" style="font-size:0.78rem;font-weight:500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="flex gap-2">
                                <button @click="openEdit({
                                            id: {{ $user->id }},
                                            name: '{{ addslashes($user->name) }}',
                                            email: '{{ $user->email }}',
                                            role: '{{ $user->role }}',
                                            phone: '{{ $user->phone ?? '' }}',
                                            wilayah_id: '{{ $user->wilayah_id ?? '' }}',
                                            is_active: {{ $user->is_active ? 'true' : 'false' }}
                                        })"
                                        class="text-sky-600 hover:bg-sky-100 p-1.5 rounded-lg" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('Hapus pengguna {{ addslashes($user->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:bg-red-100 p-1.5 rounded-lg" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="p-10 text-center text-slate-400">Belum ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-sky-100 bg-sky-50/30">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Modal --}}
    <div x-show="modalOpen" x-cloak x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
         @keydown.escape.window="modalOpen = false">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg" @click.outside="modalOpen = false">

            <div class="flex items-center justify-between px-6 py-4 border-b border-sky-100">
                <h2 class="text-sky-900" style="font-size:1.05rem;font-weight:700"
                    x-text="editMode ? 'Edit Pengguna' : 'Tambah Pengguna'"></h2>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST"
                  :action="editMode ? `/admin/users/${form.id}` : '{{ route('admin.users.store') }}'"
                  class="px-6 py-5 space-y-4">
                @csrf
                <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" required
                               class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                               style="font-size:0.85rem">
                    </div>
                    <div class="col-span-2">
                        <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" x-model="form.email" required
                               class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                               style="font-size:0.85rem">
                    </div>
                    <div>
                        <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Role <span class="text-red-500">*</span></label>
                        <select name="role" x-model="form.role" required
                                class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                                style="font-size:0.85rem">
                            <option value="masyarakat">Masyarakat</option>
                            <option value="petugas">Petugas</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">No. Telepon</label>
                        <input type="text" name="phone" x-model="form.phone"
                               class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                               style="font-size:0.85rem">
                    </div>
                    <div class="col-span-2">
                        <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Wilayah Penugasan</label>
                        <select name="wilayah_id" x-model="form.wilayah_id"
                                class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                                style="font-size:0.85rem">
                            <option value="">-- Tidak ada --</option>
                            @foreach($wilayahs as $w)
                                <option value="{{ $w->id }}">{{ $w->nama_wilayah }} ({{ ucfirst($w->tipe) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="!editMode" class="col-span-2">
                        <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" :required="!editMode" placeholder="Minimal 6 karakter"
                               class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                               style="font-size:0.85rem">
                    </div>
                    <div x-show="editMode" class="col-span-2">
                        <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">
                            Password Baru <span class="text-slate-400" style="font-weight:400">(kosongkan jika tidak diubah)</span>
                        </label>
                        <input type="password" name="password" placeholder="Isi untuk ganti password"
                               class="w-full px-3 py-2 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300"
                               style="font-size:0.85rem">
                    </div>
                    <div x-show="editMode" class="col-span-2 flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="user_active" value="1" x-model="form.is_active"
                               class="rounded border-sky-300 text-sky-600 focus:ring-sky-300">
                        <label for="user_active" class="text-slate-600" style="font-size:0.85rem">Akun aktif</label>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit"
                            class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-lg transition-colors"
                            style="font-size:0.85rem">Simpan</button>
                    <button type="button" @click="modalOpen = false"
                            class="border border-sky-200 text-sky-700 px-5 py-2 rounded-lg hover:bg-sky-50 transition-colors"
                            style="font-size:0.85rem">Batal</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
