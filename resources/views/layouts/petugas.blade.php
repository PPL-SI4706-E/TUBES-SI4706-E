<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Petugas') | TirtaBantu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased" x-data="{ sidebarOpen: window.innerWidth >= 1024, ...notifBell() }" @resize.window="sidebarOpen = window.innerWidth >= 1024" x-init="fetchUnread()">

<div class="flex h-screen overflow-hidden relative">

    {{-- Mobile Overlay --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-slate-900/50 z-40 lg:hidden" x-transition.opacity x-cloak></div>

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-sky-800 via-sky-850 to-sky-900 text-white flex flex-col shrink-0 transition-transform duration-300 lg:static lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="p-5 border-b border-white/10">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-9 h-9 bg-sky-400/20 rounded-lg flex items-center justify-center shrink-0">
                    <i data-lucide="droplets" class="w-5 h-5 text-sky-300"></i>
                </div>
                <span class="text-white tracking-wide" style="font-size:1.2rem;font-weight:700">TirtaBantu</span>
            </div>
            <a href="{{ route('profile.show') }}" class="block bg-white/10 rounded-lg p-3 hover:bg-white/15 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-sky-400/30 rounded-full flex items-center justify-center text-white shrink-0"
                         style="font-weight:600;font-size:0.85rem">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-white/90 truncate" style="font-size:0.85rem;font-weight:500">{{ auth()->user()->name }}</p>
                        <p class="text-sky-300 mt-0.5" style="font-size:0.7rem">Petugas</p>
                    </div>
                </div>
            </a>
        </div>

        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
            <p class="px-3 pt-2 pb-1 text-sky-400/60 uppercase tracking-wider" style="font-size:0.65rem;font-weight:600">Menu</p>

            @php
                $navItems = [
                    ['route' => 'petugas.dashboard', 'label' => 'Dashboard', 'match' => 'petugas.dashboard', 'icon' => 'home'],
                    ['route' => 'petugas.tugas.index', 'label' => 'Daftar Tugas', 'match' => 'petugas.tugas.*', 'icon' => 'clipboard-list'],
                    ['route' => 'notifikasi.index', 'label' => 'Notifikasi', 'match' => 'notifikasi.*', 'icon' => 'bell'],
                ];
            @endphp

            @foreach($navItems as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg transition-all {{ $active ? 'bg-white/15 text-white shadow-sm' : 'text-white/60 hover:bg-white/5 hover:text-white/90' }}"
                   style="font-size:0.85rem">
                    <div class="flex items-center gap-3">
                        <i data-lucide="{{ $item['icon'] }}" class="w-[18px] h-[18px] shrink-0"></i>
                        {{ $item['label'] }}
                    </div>
                    @if($item['route'] === 'notifikasi.index')
                        <span x-cloak x-show="unreadCount > 0" x-text="unreadCount" class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="p-3 border-t border-white/10 space-y-0.5">
            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('profile.*') ? 'bg-white/15 text-white shadow-sm' : 'text-white/60 hover:bg-white/5 hover:text-white/90' }}"
               style="font-size:0.85rem">
                <i data-lucide="user-circle" class="w-[18px] h-[18px] shrink-0"></i>
                Profil Saya
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/60 hover:bg-red-500/20 hover:text-red-300 w-full transition-all"
                        style="font-size:0.85rem">
                    <i data-lucide="log-out" class="w-[18px] h-[18px]"></i>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <header class="bg-white border-b border-sky-100 h-14 flex items-center justify-between px-6 shrink-0">
            <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-sky-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center gap-3">
                <div class="relative" x-cloak>
                    <button id="petugas-notif-bell" @click="open = !open; if(open) fetchDropdown()" class="relative p-2 rounded-lg text-slate-500 hover:text-sky-700 hover:bg-sky-50 transition-colors">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 shadow" id="petugas-notif-badge"></span>
                    </button>
                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden z-50 origin-top-right" id="petugas-notif-dropdown">
                        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                            <a href="{{ route('notifikasi.index') }}" class="font-semibold text-slate-800 text-sm hover:text-sky-600 transition-colors">Notifikasi</a>
                            <div class="flex gap-3">
                                <button x-show="unreadCount > 0" @click="dropdownReadAll()" class="text-[11px] text-sky-600 hover:text-sky-700 font-medium flex items-center gap-1"><i data-lucide="check-check" class="w-3 h-3"></i> Tandai semua</button>
                                <button x-show="items.length > 0" @click="dropdownClearAll()" class="text-[11px] text-red-500 hover:text-red-600 font-medium flex items-center gap-1"><i data-lucide="trash-2" class="w-3 h-3"></i> Bersihkan</button>
                            </div>
                        </div>
                        <div class="max-h-[350px] overflow-y-auto divide-y divide-slate-50" id="petugas-notif-list">
                            <div x-show="loadingDrop" class="p-6 flex items-center justify-center text-slate-400 text-sm gap-2"><svg class="animate-spin w-4 h-4 text-sky-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Memuat...</div>
                            <div x-show="!loadingDrop && items.length === 0" class="p-8 text-center text-slate-400 text-sm flex flex-col items-center"><i data-lucide="bell-off" class="w-10 h-10 mb-2 text-slate-300"></i>Belum ada notifikasi baru</div>
                            <template x-for="notif in items" :key="notif.id">
                                <div :class="!notif.read ? 'bg-sky-50/30' : ''" class="relative px-4 py-3 flex gap-3 group hover:bg-slate-50 transition-colors">
                                    <div x-show="!notif.read" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-sky-500 rounded-r-sm"></div>
                                    <div :class="!notif.read ? 'bg-white shadow-sm border border-sky-100' : 'bg-slate-100'" class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 mt-0.5"><i :data-lucide="notif.type === 'new_task' ? 'wrench' : 'bell'" :class="notif.type === 'new_task' ? 'text-sky-500' : 'text-indigo-500'" class="w-4 h-4"></i></div>
                                    <div class="flex-1 min-w-0 cursor-pointer pr-6" @click="if(!notif.read) dropdownMarkRead(notif.id); if(notif.link) window.location.href = notif.link;">
                                        <p :class="!notif.read ? 'font-semibold text-slate-900' : 'font-medium text-slate-700'" class="text-sm leading-tight truncate" x-text="notif.title"></p>
                                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2" x-text="notif.message"></p>
                                        <span class="text-[10px] text-slate-400 block mt-1" x-text="relTime(notif.created_at)"></span>
                                    </div>
                                    <button @click.stop="dropdownDelete(notif.id)" class="absolute right-2 top-2 p-1 rounded text-slate-400 hover:text-red-500 hover:bg-red-50 opacity-0 group-hover:opacity-100 transition-all"><i data-lucide="x" class="w-4 h-4"></i></button>
                                </div>
                            </template>
                        </div>
                        <div class="p-3 border-t border-slate-100 bg-slate-50/60">
                            <a href="{{ route('notifikasi.index') }}" class="block w-full text-center text-sm font-medium text-sky-600 hover:text-sky-700 py-1 transition-colors">Lihat Semua Notifikasi →</a>
                        </div>
                    </div>
                </div>
                <span class="text-slate-600" style="font-size:0.85rem">{{ auth()->user()->name }}</span>
                <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center text-sky-700" style="font-weight:600;font-size:0.85rem">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-red-600 hover:bg-red-50 px-2.5 py-1.5 rounded-md border border-slate-200 hover:border-red-200 transition-colors" title="Logout">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
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

<script>
    lucide.createIcons();
</script>

<script>
function notifBell() {
    return {
        open: false, items: [], unreadCount: 0, loadingDrop: false,
        _h(m) { const t = document.querySelector('meta[name="csrf-token"]'); const h = {'Accept':'application/json'}; if(t) h['X-CSRF-TOKEN']=t.content; if(m && m!=='GET') h['Content-Type']='application/json'; return h; },
        relTime(iso) { if(!iso) return ''; const d=(Date.now()-new Date(iso))/1000; if(d<60) return 'Baru saja'; if(d<3600) return Math.floor(d/60)+' mnt lalu'; if(d<86400) return Math.floor(d/3600)+' jam lalu'; return Math.floor(d/86400)+' hari lalu'; },
        async fetchUnread() { try { const r=await fetch('/api/notifications?filter=unread',{headers:this._h()}); const j=await r.json(); this.unreadCount=j.unread_count??0; } catch(e){} },
        async fetchDropdown() { this.loadingDrop=true; try { const r=await fetch('/api/notifications',{headers:this._h()}); const j=await r.json(); this.items=(j.notifications??[]).slice(0,5); this.unreadCount=j.unread_count??0; this.$nextTick(()=>{if(window.lucide) lucide.createIcons();}); } catch(e){} finally{this.loadingDrop=false;} },
        async dropdownMarkRead(id) { try { const r=await fetch('/api/notifications/'+id+'/read',{method:'PATCH',headers:this._h('PATCH')}); const j=await r.json(); const n=this.items.find(i=>i.id===id); if(n) n.read=true; this.unreadCount=j.unread_count??Math.max(0,this.unreadCount-1); } catch(e){} },
        async dropdownReadAll() { try { await fetch('/api/notifications/read-all',{method:'POST',headers:this._h('POST')}); this.items.forEach(n=>n.read=true); this.unreadCount=0; } catch(e){} },
        async dropdownDelete(id) { try { const r=await fetch('/api/notifications/'+id,{method:'DELETE',headers:this._h('DELETE')}); const j=await r.json(); this.items=this.items.filter(i=>i.id!==id); this.unreadCount=j.unread_count??this.unreadCount; } catch(e){} },
        async dropdownClearAll() { if(!confirm('Hapus semua notifikasi?')) return; try { await fetch('/api/notifications/clear-all',{method:'DELETE',headers:this._h('DELETE')}); this.items=[]; this.unreadCount=0; } catch(e){} },
    };
}
</script>

@stack('scripts')
@yield('scripts')
</body>
</html>