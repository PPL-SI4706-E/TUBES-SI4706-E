<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Petugas') | TirtaBantu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#eaf5ff] text-slate-800 antialiased" x-data="{ sidebarOpen: false, notificationOpen: false }" x-init="sidebarOpen = window.innerWidth >= 1024">
    @php
        $user = auth()->user();
        $profileName = $petugas['name'] ?? $user?->name ?? 'Budi Hartono';
        $profileRole = $petugas['role'] ?? 'Petugas Lapangan';
        $profileInitial = $petugas['initial'] ?? strtoupper(substr($profileName, 0, 1));
        $profileAvatar = $petugas['avatar_url'] ?? $user?->avatar_url;
        $notifCount = $notificationCount ?? 1;
        $navItems = [
            ['route' => 'petugas.tugas.index', 'label' => 'Daftar Tugas', 'icon' => 'clipboard-list', 'match' => ['petugas.tugas.*', 'petugas.daftar-tugas']],
        ];
        $bottomNavItems = [
            ['route' => 'petugas.profile', 'label' => 'Profil Saya', 'icon' => 'user-round', 'match' => ['petugas.profile']],
        ];
    @endphp

    <div class="min-h-screen lg:flex">
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-30 bg-slate-950/40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-[19rem] max-w-[82vw] flex-col bg-gradient-to-b from-[#0f5d8b] via-[#0f5b87] to-[#08496f] text-white shadow-2xl shadow-slate-900/15 transition-transform duration-300 lg:static lg:w-80 lg:max-w-none lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="border-b border-white/10 px-6 pb-6 pt-8">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-400/15">
                        <i data-lucide="droplets" class="h-6 w-6 text-sky-200"></i>
                    </div>
                    <span class="text-3xl font-bold tracking-tight">TirtaBantu</span>
                </div>

                <a
                    href="{{ route('petugas.profile') }}"
                    class="mt-6 block rounded-[1.4rem] bg-white/12 p-4 shadow-lg shadow-slate-950/10 ring-1 ring-white/10 transition hover:bg-white/15"
                >
                    <div class="flex items-center gap-4">
                        @if ($profileAvatar)
                            <img
                                src="{{ $profileAvatar }}"
                                alt="Foto Profil"
                                class="h-14 w-14 rounded-full object-cover ring-2 ring-white/15"
                            >
                        @else
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-sky-400/35 text-xl font-semibold text-white">
                                {{ $profileInitial }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="truncate text-xl font-semibold text-white">{{ $profileName }}</p>
                            <p class="mt-1 text-sm text-sky-200">{{ $profileRole }}</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="flex flex-1 flex-col">
                <nav class="px-4 py-6">
                    <p class="px-3 pb-3 text-xs font-semibold uppercase tracking-[0.24em] text-sky-300/70">Menu</p>
                    <div class="space-y-2">
                        @foreach ($navItems as $item)
                            @php
                                $matchPatterns = $item['match'] ?? [];
                                $active = collect((array) $matchPatterns)->contains(fn ($pattern) => request()->routeIs($pattern));
                                $baseClass = $active
                                    ? 'bg-white/14 text-white shadow-lg shadow-slate-950/10'
                                    : 'text-sky-100/70 hover:bg-white/8 hover:text-white';
                            @endphp

                            @if ($item['route'])
                                <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ $baseClass }}">
                                    <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5 shrink-0"></i>
                                    <span class="text-lg font-medium">{{ $item['label'] }}</span>
                                    @isset($item['badge'])
                                        <span class="ml-auto flex h-6 min-w-6 items-center justify-center rounded-full bg-rose-500 px-1.5 text-xs font-semibold text-white">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endisset
                                </a>
                            @else
                                <div class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sky-100/75">
                                    <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5 shrink-0"></i>
                                    <span class="text-lg font-medium">{{ $item['label'] }}</span>
                                    @isset($item['badge'])
                                        <span class="ml-auto flex h-6 min-w-6 items-center justify-center rounded-full bg-rose-500 px-1.5 text-xs font-semibold text-white">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endisset
                                </div>
                            @endif
                        @endforeach
                    </div>
                </nav>

                <div class="mt-auto border-t border-white/10 px-4 py-5">
                    <div class="space-y-2 pb-4">
                        @foreach ($bottomNavItems as $item)
                            @php
                                $matchPatterns = $item['match'] ?? [];
                                $active = collect((array) $matchPatterns)->contains(fn ($pattern) => request()->routeIs($pattern));
                                $baseClass = $active
                                    ? 'bg-white/14 text-white shadow-lg shadow-slate-950/10'
                                    : 'text-sky-100/70 hover:bg-white/8 hover:text-white';
                            @endphp

                            <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ $baseClass }}">
                                <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5 shrink-0"></i>
                                <span class="text-lg font-medium">{{ $item['label'] }}</span>
                                @isset($item['badge'])
                                    <span class="ml-auto flex h-6 min-w-6 items-center justify-center rounded-full bg-rose-500 px-1.5 text-xs font-semibold text-white">
                                        {{ $item['badge'] }}
                                    </span>
                                @endisset
                            </a>
                        @endforeach

                        <div class="relative">
                            <button
                                type="button"
                                @click="notificationOpen = !notificationOpen"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sky-100/70 transition hover:bg-white/8 hover:text-white"
                            >
                                <i data-lucide="bell" class="h-5 w-5 shrink-0"></i>
                                <span class="text-lg font-medium">Notifikasi</span>
                                <span class="ml-auto flex h-6 min-w-6 items-center justify-center rounded-full bg-rose-500 px-1.5 text-xs font-semibold text-white">
                                    {{ $notifCount }}
                                </span>
                            </button>

                            <div
                                x-show="notificationOpen"
                                x-transition.opacity
                                @click.outside="notificationOpen = false"
                                class="absolute bottom-16 left-full z-50 ml-4 w-[20rem] rounded-[1.4rem] bg-white text-slate-800 shadow-[0_18px_40px_-20px_rgba(15,23,42,0.45)] ring-1 ring-slate-100"
                                style="display: none;"
                            >
                                <div class="border-b border-slate-100 px-5 py-4">
                                    <h3 class="text-xl font-semibold text-slate-800">Notifikasi Sistem</h3>
                                </div>

                                <a
                                    href="{{ route('petugas.daftar-tugas') }}"
                                    @click="notificationOpen = false"
                                    class="block px-5 py-4 transition hover:bg-slate-50"
                                >
                                    <div class="flex gap-3">
                                        <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sky-600">
                                            <i data-lucide="clipboard-list" class="h-5 w-5"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <p class="text-xl font-semibold text-slate-800">Tugas Baru</p>
                                                <span class="whitespace-nowrap text-xs text-slate-400">1 menit yang lalu</span>
                                            </div>
                                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                                Anda ditugaskan ke Laporan #1006 (Pipa Tersumbat) di Jl. Raya Sumedang.
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sky-100/75 transition hover:bg-white/8 hover:text-white">
                            <i data-lucide="log-out" class="h-5 w-5 shrink-0"></i>
                            <span class="text-lg font-medium">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col">
            <header class="sticky top-0 z-20 flex items-center justify-between border-b border-sky-100/80 bg-[#eaf5ff]/90 px-5 py-4 backdrop-blur lg:px-10">
                <button @click="sidebarOpen = !sidebarOpen" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-slate-600 shadow-sm ring-1 ring-sky-100 transition hover:text-sky-700 lg:hidden">
                    <i data-lucide="menu" class="h-5 w-5"></i>
                </button>
                <div class="hidden lg:block">
                    <p class="text-sm text-slate-500">Dashboard Petugas Lapangan</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-slate-700">{{ $profileName }}</p>
                        <p class="text-xs text-slate-500">{{ $profileRole }}</p>
                    </div>
                    @if ($profileAvatar)
                        <img
                            src="{{ $profileAvatar }}"
                            alt="Foto Profil"
                            class="h-10 w-10 rounded-full object-cover shadow-sm ring-1 ring-sky-100"
                        >
                    @else
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-sm font-semibold text-sky-700 shadow-sm ring-1 ring-sky-100">
                            {{ $profileInitial }}
                        </div>
                    @endif
                </div>
            </header>

            <main class="flex-1 px-5 py-6 lg:px-10 lg:py-8">
                @if(session('success'))
                    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
