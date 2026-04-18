<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Warga') | TirtaBantu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 antialiased" x-data="{ sidebarOpen: true }">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <aside class="bg-gradient-to-b from-sky-800 via-sky-800 to-sky-900 text-white flex flex-col shrink-0 transition-all duration-200"
           :class="sidebarOpen ? 'w-64' : 'w-0 overflow-hidden'">

        <div class="p-5 border-b border-white/10">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-9 h-9 bg-sky-400/20 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-sky-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/></svg>
                </div>
                <span class="text-white tracking-wide" style="font-size:1.2rem;font-weight:700">TirtaBantu</span>
            </div>
            <div class="bg-white/10 rounded-lg p-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-sky-400/30 rounded-full flex items-center justify-center text-white shrink-0"
                         style="font-weight:600;font-size:0.85rem">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-white/90 truncate" style="font-size:0.85rem;font-weight:500">{{ auth()->user()->name }}</p>
                        <p class="text-sky-300 mt-0.5" style="font-size:0.7rem">Masyarakat</p>
                    </div>
                </div>
            </div>
        </div>

        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
            <p class="px-3 pt-2 pb-1 text-sky-400/60 uppercase tracking-wider" style="font-size:0.65rem;font-weight:600">Menu</p>

            @php
                $navItems = [
                    ['route' => 'warga.laporan.create', 'label' => 'Buat Laporan',   'match' => 'warga.laporan.create', 'icon' => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'warga.laporan.index',  'label' => 'Riwayat Laporan','match' => 'warga.laporan.index',  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'warga.pembayaran.index','label' => 'Pembayaran',     'match' => 'warga.pembayaran.*',   'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                ];
            @endphp

            @foreach($navItems as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $active ? 'bg-white/15 text-white shadow-sm' : 'text-white/60 hover:bg-white/5 hover:text-white/90' }}"
                   style="font-size:0.85rem">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="p-3 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/60 hover:bg-red-500/20 hover:text-red-300 w-full transition-all"
                        style="font-size:0.85rem">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">

        <header class="bg-white border-b border-sky-100 h-14 flex items-center justify-between px-6 shrink-0">
            <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-sky-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center gap-3">
                <span class="text-slate-600" style="font-size:0.85rem">{{ auth()->user()->name }}</span>
                <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center text-sky-700"
                     style="font-weight:600;font-size:0.85rem">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-red-600 hover:bg-red-50 px-2.5 py-1.5 rounded-md border border-slate-200 hover:border-red-200 transition-colors"
                            title="Logout (testing)">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-2" style="font-size:0.85rem">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center gap-2" style="font-size:0.85rem">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>