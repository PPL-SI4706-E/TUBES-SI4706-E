@extends('layouts.app')
@section('title', 'Masuk')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-sky-100 via-sky-50 to-white flex items-center justify-center p-4">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 group">
                <div class="bg-white p-2 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-sky-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/>
                    </svg>
                </div>
                <span class="text-sky-800" style="font-size:2rem;font-weight:800">TirtaBantu</span>
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-2xl p-8 border border-sky-100 relative overflow-hidden" x-data="{ selectedRole: '{{ old('role', 'masyarakat') }}' }">
            {{-- Decorative elements --}}
            <div class="absolute top-0 right-0 w-32 h-32 bg-sky-50 rounded-bl-full -z-0 opacity-50"></div>
            
            <div class="relative z-10">
                <h2 class="text-sky-900 mb-1 text-center" style="font-size:1.5rem;font-weight:800">Selamat Datang</h2>
                <p class="text-slate-500 text-center mb-8" style="font-size:0.85rem">Pilih jenis akun dan masukkan detail login Anda</p>

                @if(session('success'))
                    <div class="bg-emerald-50 text-emerald-600 rounded-2xl p-4 mb-6 flex items-center gap-3 border border-emerald-100 animate-fade-in" style="font-size:0.85rem">
                        <div class="bg-emerald-500 text-white p-1 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-50 text-red-600 rounded-2xl p-4 mb-6 flex items-center gap-3 border border-red-100 animate-shake" style="font-size:0.85rem">
                        <div class="bg-red-500 text-white p-1 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    {{-- ── PILIH ROLE ────────────────────────────────────── --}}
                    <div>
                        <label class="text-sky-800 mb-3 block" style="font-size:0.85rem;font-weight:700">
                            Masuk Sebagai <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            @php
                                $roles = [
                                ['val'=>'masyarakat', 'label'=>'Masyarakat', 'color'=>'amber',   'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                                ['val'=>'petugas',    'label'=>'Petugas',    'color'=>'emerald', 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ['val'=>'admin',      'label'=>'Admin',      'color'=>'purple',  'icon'=>'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                            ];
                            @endphp
                            @foreach($roles as $r)
                                <label class="cursor-pointer">
                                    <input type="radio" name="role" value="{{ $r['val'] }}"
                                           x-model="selectedRole"
                                           class="peer sr-only" required>
                                    <div class="border-2 rounded-2xl p-3 text-center transition-all duration-300 transform peer-checked:scale-105
                                                peer-checked:border-{{ $r['color'] }}-500
                                                peer-checked:bg-{{ $r['color'] }}-50
                                                peer-checked:shadow-lg peer-checked:shadow-{{ $r['color'] }}-100
                                                border-slate-100 bg-white
                                                hover:border-{{ $r['color'] }}-200">
                                        <svg class="w-6 h-6 mx-auto mb-1 transition-colors duration-300" 
                                             :class="selectedRole === '{{ $r['val'] }}' ? 'text-{{ $r['color'] }}-600' : 'text-slate-400'"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $r['icon'] }}"/>
                                        </svg>
                                        <span class="block transition-colors duration-300" 
                                              :class="selectedRole === '{{ $r['val'] }}' ? 'text-{{ $r['color'] }}-700' : 'text-slate-500'"
                                              style="font-size:0.75rem;font-weight:700">{{ $r['label'] }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- ── EMAIL ─────────────────────────────────────────── --}}
                    <div class="space-y-1.5">
                        <label class="text-sky-800 block px-1" style="font-size:0.85rem;font-weight:600">Alamat Email</label>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 transition-colors group-focus-within:text-sky-600 text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                   placeholder="Masukkan email terdaftar"
                                   class="w-full pl-12 pr-4 py-3.5 border-2 border-slate-100 rounded-2xl bg-slate-50/30 focus:outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500 transition-all"
                                   style="font-size:0.9rem">
                        </div>
                    </div>

                    {{-- ── PASSWORD ──────────────────────────────────────── --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between px-1">
                            <label class="text-sky-800 block" style="font-size:0.85rem;font-weight:600">Password</label>
                        </div>
                        <div class="relative group" x-data="{ show: false }">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 transition-colors group-focus-within:text-sky-600 text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input :type="show ? 'text' : 'password'" name="password" required
                                   placeholder="••••••••"
                                   class="w-full pl-12 pr-12 py-3.5 border-2 border-slate-100 rounded-2xl bg-slate-50/30 focus:outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500 transition-all"
                                   style="font-size:0.9rem">
                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-sky-600 transition-colors">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88L4.573 4.574m14.853 14.854L15.12 14.121M13.875 5.175A10.05 10.05 0 0121 12c0 .453-.03.896-.089 1.328M15.12 10.121a3 3 0 00-4.242-4.242"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between px-1">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 transition-all">
                            <span class="text-slate-600 group-hover:text-sky-800 transition-colors" style="font-size:0.85rem">Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white py-4 rounded-2xl transition-all shadow-xl shadow-sky-100 hover:shadow-sky-200 hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2" style="font-size:1rem;font-weight:700">
                        <span>Masuk Sekarang</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Help center --}}
        <div class="mt-8 bg-white/40 backdrop-blur-sm rounded-2xl p-6 border border-white/50 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 bg-sky-500 rounded-full"></div>
                <p class="text-sky-900" style="font-size:0.85rem;font-weight:700">Pusat Bantuan Login</p>
            </div>
            
            <div class="grid grid-cols-1 gap-2">
                @php
                    $demo = [
                        ['role'=>'Admin',      'email'=>'admin@tirtabantu.id', 'color'=>'purple'],
                        ['role'=>'Petugas',    'email'=>'budi@tirtabantu.id',  'color'=>'emerald'],
                        ['role'=>'Warga',      'email'=>'andi@gmail.com',      'color'=>'amber'],
                    ];
                @endphp
                @foreach($demo as $d)
                    <div class="flex items-center justify-between bg-white/60 p-3 rounded-xl border border-white hover:bg-white transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-{{ $d['color'] }}-500"></div>
                            <div>
                                <p class="text-slate-400" style="font-size:0.7rem;font-weight:600">{{ $d['role'] }}</p>
                                <p class="text-slate-700" style="font-size:0.8rem">{{ $d['email'] }}</p>
                            </div>
                        </div>
                        <button type="button" @click="document.getElementsByName('email')[0].value='{{ $d['email'] }}'; document.getElementsByName('password')[0].value='password'; selectedRole='{{ strtolower($d['role'] === 'Warga' ? 'masyarakat' : $d['role']) }}'" class="opacity-0 group-hover:opacity-100 transition-opacity bg-sky-50 text-sky-600 p-1.5 rounded-lg hover:bg-sky-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        </button>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-sky-100/50 text-center">
                <p class="text-slate-500" style="font-size:0.8rem">
                    Belum punya akun? <a href="{{ route('register') }}" class="text-sky-600 font-bold hover:underline">Daftar Sekarang</a>
                </p>
            </div>
        </div>

        <p class="text-center text-slate-400 mt-8" style="font-size:0.75rem">
            &copy; {{ date('Y') }} TirtaBantu. Hak Cipta Dilindungi.
        </p>

    </div>
</div>

{{-- Tailwind Safelist for dynamic classes --}}
<div class="hidden">
    <div class="bg-amber-50 border-amber-500 text-amber-600 text-amber-700 shadow-amber-100 hover:border-amber-200"></div>
    <div class="bg-emerald-50 border-emerald-500 text-emerald-600 text-emerald-700 shadow-emerald-100 hover:border-emerald-200 text-emerald-500"></div>
    <div class="bg-purple-50 border-purple-500 text-purple-600 text-purple-700 shadow-purple-100 hover:border-purple-200 text-purple-500"></div>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease-out forwards;
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    .animate-shake {
        animation: shake 0.3s ease-in-out;
    }
</style>
@endsection