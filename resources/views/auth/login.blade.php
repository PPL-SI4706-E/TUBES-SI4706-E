@extends('layouts.app')
@section('title', 'Masuk')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-sky-100 via-sky-50 to-white flex items-center justify-center p-4">
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
            <h2 class="text-sky-900 mb-1 text-center" style="font-size:1.35rem;font-weight:700">Masuk ke Sistem</h2>
            <p class="text-slate-500 text-center mb-6" style="font-size:0.85rem">Silakan login dengan akun Anda</p>

            @if(session('success'))
                <div class="bg-emerald-50 text-emerald-600 rounded-lg p-3 mb-4 flex items-center gap-2" style="font-size:0.85rem">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 text-red-600 rounded-lg p-3 mb-4 flex items-center gap-2" style="font-size:0.85rem">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Email</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="email@example.com"
                               class="w-full pl-10 pr-4 py-2.5 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:border-sky-400"
                               style="font-size:0.85rem">
                    </div>
                </div>
                <div>
                    <label class="text-sky-800 mb-1 block" style="font-size:0.85rem">Password</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <input type="password" name="password" required
                               placeholder="Masukkan password"
                               class="w-full pl-10 pr-4 py-2.5 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:border-sky-400"
                               style="font-size:0.85rem">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-sky-300 text-sky-600 focus:ring-sky-300">
                    <label for="remember" class="text-slate-600" style="font-size:0.85rem">Ingat saya</label>
                </div>
                <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white py-3 rounded-xl transition-colors shadow-lg shadow-sky-200" style="font-size:0.9rem;font-weight:600">
                    Masuk
                </button>
            </form>
        </div>

        {{-- Demo accounts --}}
        <div class="mt-6 bg-white/80 rounded-xl p-5 border border-sky-100">
            <p class="text-sky-800 mb-3" style="font-size:0.8rem;font-weight:600">Akun Demo:</p>
            <div class="space-y-2">
                @php
                    $demo = [
                        ['role'=>'Admin',      'email'=>'admin@tirtabantu.id', 'color'=>'bg-purple-100 text-purple-700'],
                        ['role'=>'Petugas',    'email'=>'budi@tirtabantu.id',  'color'=>'bg-emerald-100 text-emerald-700'],
                        ['role'=>'Masyarakat', 'email'=>'andi@gmail.com',      'color'=>'bg-amber-100 text-amber-700'],
                    ];
                @endphp
                @foreach($demo as $d)
                    <div class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-sky-50 transition-colors">
                        <span class="{{ $d['color'] }} px-2 py-0.5 rounded" style="font-size:0.7rem;font-weight:600">{{ $d['role'] }}</span>
                        <span class="text-slate-600" style="font-size:0.8rem">{{ $d['email'] }}</span>
                    </div>
                @endforeach
                <p class="pt-2 px-3 text-slate-400" style="font-size:0.75rem">
                    Password semua akun: <code class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-mono">password</code>
                </p>
            </div>
        </div>

        <p class="text-center text-slate-500 mt-6" style="font-size:0.85rem">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-sky-600 hover:text-sky-700" style="font-weight:600">Daftar sekarang</a>
        </p>

    </div>
</div>
@endsection
