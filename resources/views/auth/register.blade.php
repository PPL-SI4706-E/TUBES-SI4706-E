@extends('layouts.app')
@section('title', 'Daftar Akun')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-sky-100 via-sky-50 to-white flex items-center justify-center p-4 py-12">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <svg class="w-10 h-10 text-sky-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/>
                </svg>
                <span class="text-sky-800" style="font-size:2rem;font-weight:800">TirtaBantu</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 border border-sky-100">
            <h2 class="text-sky-900 mb-1 text-center" style="font-size:1.35rem;font-weight:700">Buat Akun Baru</h2>
            <p class="text-slate-500 text-center mb-6" style="font-size:0.85rem">Daftarkan diri untuk mulai melapor</p>

            @if($errors->any())
                <div class="bg-red-50 text-red-600 rounded-lg p-3 mb-4 space-y-0.5" style="font-size:0.85rem">
                    @foreach($errors->all() as $error)
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Nama Lengkap <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nama lengkap Anda"
                               class="w-full pl-10 pr-4 py-2.5 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:border-sky-400" style="font-size:0.85rem">
                    </div>
                </div>

                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Email <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com"
                               class="w-full pl-10 pr-4 py-2.5 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:border-sky-400" style="font-size:0.85rem">
                    </div>
                </div>

                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Nomor Telepon</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx"
                               class="w-full pl-10 pr-4 py-2.5 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:border-sky-400" style="font-size:0.85rem">
                    </div>
                </div>

                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Wilayah Tempat Tinggal</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-sky-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <select name="wilayah_id" class="w-full pl-10 pr-4 py-2.5 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:border-sky-400" style="font-size:0.85rem">
                            <option value="">-- Pilih Wilayah (opsional) --</option>
                            @foreach($wilayahs as $w)
                                <option value="{{ $w->id }}" {{ old('wilayah_id') == $w->id ? 'selected' : '' }}>{{ $w->nama_wilayah }} ({{ ucfirst($w->tipe) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter"
                               class="w-full pl-10 pr-4 py-2.5 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:border-sky-400" style="font-size:0.85rem">
                    </div>
                </div>

                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <input type="password" name="password_confirmation" required placeholder="Ulangi password"
                               class="w-full pl-10 pr-4 py-2.5 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:border-sky-400" style="font-size:0.85rem">
                    </div>
                </div>

                <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white py-3 rounded-xl transition-colors shadow-lg shadow-sky-200" style="font-size:0.9rem;font-weight:600">
                    Buat Akun
                </button>
            </form>
        </div>

        <p class="text-center text-slate-500 mt-6" style="font-size:0.85rem">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-sky-600 hover:text-sky-700" style="font-weight:600">Masuk di sini</a>
        </p>

    </div>
</div>
@endsection
