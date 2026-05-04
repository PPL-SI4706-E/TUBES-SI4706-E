@extends('layouts.app')
@section('title', 'Daftar Akun')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-sky-100 via-sky-50 to-white flex items-center justify-center p-4 py-12">
        <div class="w-full max-w-md">

            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 group">
                    <div class="bg-white p-2 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-sky-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z" />
                        </svg>
                    </div>
                    <span class="text-sky-800" style="font-size:2rem;font-weight:800">TirtaBantu</span>
                </a>
            </div>

            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-sky-100 relative overflow-hidden"
                x-data="{ role: '{{ old('role', 'masyarakat') }}' }">

                {{-- Decorative elements --}}
                <div class="absolute top-0 right-0 w-32 h-32 bg-sky-50 rounded-bl-full -z-0 opacity-50"></div>

                <div class="relative z-10">
                    <h2 class="text-sky-900 mb-1 text-center" style="font-size:1.5rem;font-weight:800">Buat Akun Baru</h2>
                    <p class="text-slate-500 text-center mb-8" style="font-size:0.85rem">Daftarkan diri untuk mulai
                        menggunakan layanan</p>

                    @if($errors->any())
                        <div class="bg-red-50 text-red-600 rounded-2xl p-4 mb-6 space-y-1 border border-red-100 animate-shake"
                            style="font-size:0.85rem">
                            @foreach($errors->all() as $error)
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                                    </svg>
                                    <span>{{ $error }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="space-y-6">
                        @csrf

                        {{-- ── PILIH ROLE ────────────────────────────────────── --}}
                        <div>
                            <label class="text-sky-800 mb-3 block" style="font-size:0.85rem;font-weight:700">
                                Daftar Sebagai <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="role" value="masyarakat" x-model="role" class="peer sr-only"
                                        required>
                                    <div class="border-2 rounded-2xl p-3 text-center transition-all duration-300 transform peer-checked:scale-105
                                                            peer-checked:border-amber-500 peer-checked:bg-amber-50
                                                            peer-checked:shadow-lg peer-checked:shadow-amber-100
                                                            border-slate-100 bg-white hover:border-amber-200">
                                        <svg class="w-6 h-6 mx-auto mb-1 transition-colors"
                                            :class="role === 'masyarakat' ? 'text-amber-600' : 'text-slate-400'" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="block transition-colors"
                                            :class="role === 'masyarakat' ? 'text-amber-700' : 'text-slate-500'"
                                            style="font-size:0.75rem;font-weight:700">Masyarakat</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="role" value="admin" x-model="role" class="peer sr-only"
                                        required>
                                    <div class="border-2 rounded-2xl p-3 text-center transition-all duration-300 transform peer-checked:scale-105
                                                            peer-checked:border-purple-500 peer-checked:bg-purple-50
                                                            peer-checked:shadow-lg peer-checked:shadow-purple-100
                                                            border-slate-100 bg-white hover:border-purple-200">
                                        <svg class="w-6 h-6 mx-auto mb-1 transition-colors"
                                            :class="role === 'admin' ? 'text-purple-600' : 'text-slate-400'" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <span class="block transition-colors"
                                            :class="role === 'admin' ? 'text-purple-700' : 'text-slate-500'"
                                            style="font-size:0.75rem;font-weight:700">Admin</span>
                                    </div>
                                </label>
                            </div>
                            <p class="mt-2 text-slate-400 px-1" style="font-size:0.7rem;font-style:italic">
                                * Akun Petugas hanya dapat didaftarkan oleh Admin melalui Manajemen Pengguna.
                            </p>
                        </div>

                        {{-- ── NAMA ──────────────────────────────────────────── --}}
                        <div class="space-y-1.5">
                            <label class="text-sky-800 block px-1" style="font-size:0.85rem;font-weight:600">Nama Lengkap
                                <span class="text-red-500">*</span></label>
                            <div class="relative group">
                                <div
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-sky-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    placeholder="Masukkan nama lengkap"
                                    class="w-full pl-12 pr-4 py-3 border-2 border-slate-100 rounded-2xl bg-slate-50/30 focus:outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500 transition-all"
                                    style="font-size:0.9rem">
                            </div>
                        </div>

                        {{-- ── EMAIL ─────────────────────────────────────────── --}}
                        <div class="space-y-1.5">
                            <label class="text-sky-800 block px-1" style="font-size:0.85rem;font-weight:600">Alamat Email
                                <span class="text-red-500">*</span></label>
                            <div class="relative group">
                                <div
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-sky-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    placeholder="email@example.com"
                                    class="w-full pl-12 pr-4 py-3 border-2 border-slate-100 rounded-2xl bg-slate-50/30 focus:outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500 transition-all"
                                    style="font-size:0.9rem">
                            </div>
                        </div>

                        {{-- ── TELEPON ───────────────────────────────────────── --}}
                        <div class="space-y-1.5">
                            <label class="text-sky-800 block px-1" style="font-size:0.85rem;font-weight:600">Nomor
                                Telepon</label>
                            <div class="relative group">
                                <div
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-sky-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx"
                                    class="w-full pl-12 pr-4 py-3 border-2 border-slate-100 rounded-2xl bg-slate-50/30 focus:outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500 transition-all"
                                    style="font-size:0.9rem">
                            </div>
                        </div>

                        {{-- ── WILAYAH (hanya tampil untuk Masyarakat) ───────── --}}
                        <div class="space-y-1.5" x-show="role === 'masyarakat'"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0">
                            <label class="text-sky-800 block px-1" style="font-size:0.85rem;font-weight:600">Wilayah Tempat
                                Tinggal</label>
                            <div class="relative group">
                                <div
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-sky-600 transition-colors pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <select name="wilayah_id"
                                    class="w-full pl-12 pr-4 py-3 border-2 border-slate-100 rounded-2xl bg-slate-50/30 focus:outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500 transition-all appearance-none"
                                    style="font-size:0.9rem">
                                    <option value="">-- Pilih Wilayah (opsional) --</option>
                                    @foreach($wilayahs as $w)
                                        <option value="{{ $w->id }}" {{ old('wilayah_id') == $w->id ? 'selected' : '' }}>
                                            {{ $w->nama_wilayah }} ({{ ucfirst($w->tipe) }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- ── PASSWORD ──────────────────────────────────────── --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-sky-800 block px-1" style="font-size:0.85rem;font-weight:600">Password
                                    <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div
                                        class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-sky-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input type="password" name="password" required placeholder="Min. 6 karakter"
                                        class="w-full pl-12 pr-4 py-3 border-2 border-slate-100 rounded-2xl bg-slate-50/30 focus:outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500 transition-all"
                                        style="font-size:0.9rem">
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sky-800 block px-1" style="font-size:0.85rem;font-weight:600">Konfirmasi
                                    <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div
                                        class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-sky-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <input type="password" name="password_confirmation" required
                                        placeholder="Ulangi password"
                                        class="w-full pl-12 pr-4 py-3 border-2 border-slate-100 rounded-2xl bg-slate-50/30 focus:outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500 transition-all"
                                        style="font-size:0.9rem">
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full bg-sky-600 hover:bg-sky-700 text-white py-4 rounded-2xl transition-all shadow-xl shadow-sky-100 hover:shadow-sky-200 hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2"
                            style="font-size:1rem;font-weight:700">
                            <span>Daftar Akun Sekarang</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center text-slate-500 mt-8" style="font-size:0.85rem">
                Sudah punya akun?
                <a href="{{ route('login') }}"
                    class="text-sky-600 hover:text-sky-700 font-bold hover:underline transition-all">Masuk di sini</a>
            </p>

            <p class="text-center text-slate-400 mt-8" style="font-size:0.75rem">
                &copy; {{ date('Y') }} TirtaBantu. Hak Cipta Dilindungi.
            </p>

        </div>
    </div>

    <style>
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .animate-shake {
            animation: shake 0.3s ease-in-out;
        }
    </style>
@endsection