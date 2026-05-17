<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Daftar Tugas') | TirtaBantu Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-[#eaf4fb] text-slate-800 antialiased">
    @php
        $petugasAuth = auth()->user();
        $notifItems = $notifications ?? collect();
        $notifUnread = $unreadNotificationsCount ?? $notifItems->where('is_read', false)->count();
    @endphp

    <div class="min-h-screen lg:flex" x-data="{ notifOpen: false }">
        <aside class="relative flex w-full flex-col bg-gradient-to-b from-[#0f6897] via-[#0d5f8d] to-[#0b567f] px-5 py-5 text-white lg:min-h-screen lg:w-[260px] lg:shrink-0">
            <div class="mb-7 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10">
                    <i data-lucide="droplets" class="h-5 w-5 text-sky-200"></i>
                </div>
                <div>
                    <p class="text-[1.95rem] font-bold leading-none">TirtaBantu</p>
                </div>
            </div>

            <div class="mb-10 rounded-2xl bg-white/10 p-4 shadow-sm ring-1 ring-white/5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-sky-300/25 text-base font-bold">
                        {{ strtoupper(substr($petugasAuth->name ?? 'B', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-[15px] font-semibold leading-tight">{{ $petugasAuth->name ?? 'Budi Hartono' }}</p>
                        <p class="mt-1 text-xs text-sky-200">Petugas Lapangan</p>
                    </div>
                </div>
            </div>

            <nav class="space-y-2">
                <p class="px-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-sky-300/70">Menu</p>
                <a href="{{ route('petugas.tugas.index') }}"
                   class="flex items-center gap-3 rounded-2xl px-4 py-3 text-[15px] font-medium {{ request()->routeIs('petugas.tugas.*') || request()->routeIs('petugas.daftar-tugas') ? 'bg-white/15 text-white shadow-sm ring-1 ring-white/5' : 'text-white/75 hover:bg-white/10' }}">
                    <i data-lucide="clipboard-list" class="h-5 w-5"></i>
                    Daftar Tugas
                </a>
            </nav>

            <div class="mt-auto space-y-2 border-t border-white/10 pt-6">
                <a href="{{ route('petugas.profile') }}"
                   class="flex items-center gap-3 rounded-2xl px-4 py-3 text-[15px] text-white/75 hover:bg-white/10">
                    <i data-lucide="user-round" class="h-5 w-5"></i>
                    Profil Saya
                </a>
                <div class="relative" @click.outside="notifOpen = false">
                    <button type="button"
                            @click="notifOpen = !notifOpen"
                            :class="notifOpen ? 'bg-white/15 text-white shadow-sm' : 'text-white/75 hover:bg-white/10'"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 text-[15px] transition">
                        <span class="flex items-center gap-3">
                            <i data-lucide="bell" class="h-5 w-5"></i>
                            Notifikasi
                        </span>
                        @if($notifUnread > 0)
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-red-500 px-2 text-xs font-bold text-white">
                                {{ $notifUnread }}
                            </span>
                        @endif
                    </button>

                    <div x-cloak
                         x-show="notifOpen"
                         x-transition.opacity.scale.origin.left
                         class="fixed bottom-24 left-4 right-4 z-40 rounded-3xl border border-slate-200 bg-white text-slate-800 shadow-2xl shadow-slate-900/15 sm:left-6 sm:right-auto sm:w-[340px] lg:absolute lg:bottom-[-8px] lg:left-[calc(100%+14px)] lg:right-auto">
                        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4">
                            <div>
                                <p class="text-lg font-semibold text-slate-800">Notifikasi Sistem</p>
                            </div>
                            <button type="button" class="text-sm font-medium text-sky-600">Tandai semua dibaca</button>
                        </div>
                        <div class="max-h-[320px] space-y-3 overflow-y-auto px-4 py-4">
                            @forelse($notifItems as $notif)
                                <div class="flex gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-3 py-3">
                                    <div class="mt-1 flex h-9 w-9 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                                        <i data-lucide="clipboard-check" class="h-4 w-4"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="font-semibold text-slate-800">{{ $notif->title ?? 'Notifikasi' }}</p>
                                            @if(! $notif->is_read)
                                                <span class="mt-1 h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notif->message }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ $notif->created_at?->diffForHumans() ?? 'baru saja' }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-400">
                                    Belum ada notifikasi baru.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-[15px] text-white/75 hover:bg-red-500/20 hover:text-red-100">
                        <i data-lucide="log-out" class="h-5 w-5"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 px-5 py-6 sm:px-6 lg:px-10 lg:py-9">
            @if(session('success'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
