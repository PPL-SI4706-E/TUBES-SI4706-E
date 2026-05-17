@php
    $layoutMap = [
        'admin'      => 'layouts.admin',
        'petugas'    => 'layouts.petugas',
        'masyarakat' => 'layouts.warga',
    ];
@endphp

@extends($layoutMap[auth()->user()->role] ?? 'layouts.warga')
@section('title', 'Manajemen Profil')

@section('content')
{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div x-data="profilePage()" x-init="init()">
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-sky-900 mb-1" style="font-size:1.5rem;font-weight:700">Manajemen Profil</h1>
        <p class="text-slate-500" style="font-size:0.85rem">Kelola informasi pribadi dan keamanan akun Anda di sini.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ═══ LEFT: Informasi Pribadi (2/3 width) ═══ --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-sky-100 shadow-sm overflow-hidden">
                {{-- Card Header --}}
                <div class="px-6 py-4 border-b border-sky-50 flex items-center gap-3">
                    <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="user" class="w-4 h-4 text-sky-600"></i>
                    </div>
                    <h2 class="text-sky-900" style="font-size:1.1rem;font-weight:700">Informasi Pribadi</h2>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="p-6">
                    @csrf
                    @method('PATCH')

                    {{-- Avatar Section --}}
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 mb-8 pb-6 border-b border-slate-100">
                        {{-- Avatar Preview --}}
                        <div class="relative group">
                            <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-sky-100 shadow-md">
                                <img :src="avatarPreview"
                                     alt="Foto Profil"
                                     class="w-full h-full object-cover"
                                     id="avatar-preview-img">
                            </div>
                            <div class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer"
                                 @click="$refs.avatarInput.click()">
                                <i data-lucide="camera" class="w-6 h-6 text-white"></i>
                            </div>
                        </div>

                        <div>
                            <p class="text-sky-900" style="font-size:0.95rem;font-weight:600">Foto Profil</p>
                            <p class="text-slate-400 mt-0.5" style="font-size:0.78rem">JPG, JPEG, atau PNG. Maks 2MB.</p>
                            <button type="button"
                                    @click="$refs.avatarInput.click()"
                                    class="mt-2 text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-4 py-1.5 rounded-lg transition-colors"
                                    style="font-size:0.83rem;font-weight:600">
                                Pilih Foto Baru
                            </button>
                            <input type="file"
                                   name="avatar"
                                   accept="image/jpg,image/jpeg,image/png"
                                   class="hidden"
                                   x-ref="avatarInput"
                                   @change="previewAvatar($event)"
                                   id="avatar-input">
                        </div>

                        @error('avatar')
                            <p class="text-red-500 mt-1" style="font-size:0.78rem">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Name Field --}}
                    <div class="mb-5">
                        <label for="name" class="block text-slate-600 mb-1.5" style="font-size:0.83rem;font-weight:600">Nama Lengkap</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-red-300 bg-red-50' : 'border-slate-200' }} focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none transition-all"
                                   style="font-size:0.9rem"
                                   required>
                        </div>
                        @error('name')
                            <p class="text-red-500 mt-1" style="font-size:0.78rem">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email & Phone Row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-slate-600 mb-1.5" style="font-size:0.83rem;font-weight:600">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i data-lucide="mail" class="w-4 h-4 text-slate-400"></i>
                                </div>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $user->email) }}"
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-red-300 bg-red-50' : 'border-slate-200' }} focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none transition-all"
                                       style="font-size:0.9rem"
                                       required>
                            </div>
                            @error('email')
                                <p class="text-red-500 mt-1" style="font-size:0.78rem">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block text-slate-600 mb-1.5" style="font-size:0.83rem;font-weight:600">Nomor HP</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i data-lucide="phone" class="w-4 h-4 text-slate-400"></i>
                                </div>
                                <input type="text"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $user->phone) }}"
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('phone') ? 'border-red-300 bg-red-50' : 'border-slate-200' }} focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none transition-all"
                                       style="font-size:0.9rem"
                                       placeholder="08xxxxxxxxxx">
                            </div>
                            @error('phone')
                                <p class="text-red-500 mt-1" style="font-size:0.78rem">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>


                    {{-- Submit --}}
                    <div class="flex justify-end">
                        <button type="submit"
                                class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-2.5 rounded-xl transition-all shadow-sm hover:shadow-md flex items-center gap-2"
                                style="font-size:0.85rem;font-weight:600">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═══ RIGHT: Keamanan / Password (1/3 width) ═══ --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-sky-100 shadow-sm overflow-hidden">
                {{-- Card Header --}}
                <div class="px-6 py-4 border-b border-sky-50 flex items-center gap-3">
                    <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-4 h-4 text-sky-600"></i>
                    </div>
                    <h2 class="text-sky-900" style="font-size:1.1rem;font-weight:700">Keamanan</h2>
                </div>

                <form method="POST" action="{{ route('profile.password') }}" class="p-6">
                    @csrf
                    @method('PATCH')

                    {{-- Old Password --}}
                    <div class="mb-4">
                        <label for="old_password" class="block text-slate-600 mb-1.5" style="font-size:0.83rem;font-weight:600">Password Lama</label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <input :type="show ? 'text' : 'password'"
                                   id="old_password"
                                   name="old_password"
                                   class="w-full pl-10 pr-10 py-2.5 rounded-xl border {{ $errors->has('old_password') ? 'border-red-300 bg-red-50' : 'border-slate-200' }} focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none transition-all"
                                   style="font-size:0.9rem"
                                   placeholder="••••••••">
                            <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                <i x-show="!show" data-lucide="eye" class="w-4 h-4"></i>
                                <i x-show="show" data-lucide="eye-off" class="w-4 h-4"></i>
                            </button>
                        </div>
                        @error('old_password')
                            <p class="text-red-500 mt-1" style="font-size:0.78rem">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div class="mb-4">
                        <label for="password" class="block text-slate-600 mb-1.5" style="font-size:0.83rem;font-weight:600">Password Baru</label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <input :type="show ? 'text' : 'password'"
                                   id="password"
                                   name="password"
                                   class="w-full pl-10 pr-10 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-red-300 bg-red-50' : 'border-slate-200' }} focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none transition-all"
                                   style="font-size:0.9rem"
                                   placeholder="Minimal 8 karakter">
                            <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                <i x-show="!show" data-lucide="eye" class="w-4 h-4"></i>
                                <i x-show="show" data-lucide="eye-off" class="w-4 h-4"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 mt-1" style="font-size:0.78rem">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-slate-600 mb-1.5" style="font-size:0.83rem;font-weight:600">Konfirmasi Password Baru</label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <input :type="show ? 'text' : 'password'"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none transition-all"
                                   style="font-size:0.9rem"
                                   placeholder="Ulangi password baru">
                            <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                <i x-show="!show" data-lucide="eye" class="w-4 h-4"></i>
                                <i x-show="show" data-lucide="eye-off" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="w-full bg-slate-800 hover:bg-slate-900 text-white px-5 py-2.5 rounded-xl transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2"
                            style="font-size:0.85rem;font-weight:600">
                        <i data-lucide="key" class="w-4 h-4"></i>
                        Ganti Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function profilePage() {
    return {
        avatarPreview: '{{ $user->avatar_url }}',

        init() {
            // SweetAlert for success messages
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session("success") }}',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            @endif

            // Re-initialize Lucide icons after Alpine renders
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        },

        previewAvatar(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Client-side validation
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format Tidak Valid',
                    text: 'Gunakan format JPG, JPEG, atau PNG.',
                    confirmButtonColor: '#0284c7',
                });
                event.target.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran Terlalu Besar',
                    text: 'Ukuran foto maksimal 2MB.',
                    confirmButtonColor: '#0284c7',
                });
                event.target.value = '';
                return;
            }

            // Preview
            const reader = new FileReader();
            reader.onload = (e) => {
                this.avatarPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    };
}
</script>
@endsection
