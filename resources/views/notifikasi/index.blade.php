@php
    // Deteksi layout berdasarkan role pengguna
    $role   = auth()->user()->role;
    $layout = match($role) {
        'admin'      => 'layouts.admin',
        'petugas'    => 'layouts.petugas',
        default      => 'layouts.warga',
    };
@endphp

@extends($layout)

@section('title', 'Notifikasi')

@section('content')
<div class="w-full pb-16"
     x-data="notifikasiApp()"
     x-init="fetchNotifications()"
     id="notifikasi-page">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- ── Header Section ─────────────────────────────────────────────────── --}}
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Notifikasi Sistem</h1>
                <p class="text-slate-500 text-sm mt-1">Pusat informasi dan pembaruan aktivitas Anda.</p>
            </div>
            <div class="flex items-center gap-3">
                <button
                    @click="markAllAsRead()"
                    :disabled="unreadCount === 0 || loading"
                    class="flex items-center gap-2 px-4 py-2 rounded-full transition-all duration-300 font-medium text-sm
                           disabled:opacity-50 disabled:cursor-not-allowed bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-sky-500"></i>
                    <span class="hidden sm:inline">Tandai Dibaca</span>
                </button>
                <button
                    @click="clearAll()"
                    :disabled="notifications.length === 0 || loading"
                    class="flex items-center gap-2 px-4 py-2 rounded-full transition-all duration-300 font-medium text-sm
                           disabled:opacity-50 disabled:cursor-not-allowed bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200">
                    <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                    <span class="hidden sm:inline">Bersihkan</span>
                </button>
            </div>
        </div>

        {{-- ── Main Panel ─────────────────────────────────────────────────────── --}}

        {{-- Filter & Search Toolbar --}}
        <div class="p-4 sm:p-6 sm:pb-5 border-b border-slate-100/80 flex flex-col sm:flex-row sm:items-center justify-between gap-5 bg-white/50">

            {{-- Segmented Control Tabs --}}
            <div class="flex bg-slate-100/80 p-1.5 rounded-2xl w-full sm:w-auto gap-1 shadow-inner">
                <template x-for="tab in tabs" :key="tab.id">
                    <button
                        @click="activeTab = tab.id; fetchNotifications()"
                        :class="activeTab === tab.id
                            ? 'bg-white text-sky-700 shadow-md ring-1 ring-slate-900/5'
                            : 'text-slate-500 hover:text-slate-800 hover:bg-slate-200/50'"
                        class="relative flex-1 sm:flex-none px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 flex items-center justify-center gap-2">
                        <span x-text="tab.label"></span>
                        
                        <template x-if="tab.id === 'belum_dibaca' && unreadCount > 0">
                            <span class="absolute -top-1 -right-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                                <span x-text="unreadCount"></span>
                            </span>
                        </template>
                    </button>
                </template>
            </div>

            {{-- Search Bar --}}
            <div class="relative w-full sm:w-72 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-sky-500 transition-colors">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input
                    type="text"
                    placeholder="Cari notifikasi..."
                    x-model="searchQuery"
                    @input.debounce.400ms="fetchNotifications()"
                    class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition-all font-medium text-sm text-slate-700 outline-none placeholder:text-slate-400"/>
            </div>
        </div>

        {{-- Notification List Container --}}
        <div class="relative min-h-[400px]">

            {{-- Loading State --}}
            <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm z-10" x-transition>
                <div class="w-16 h-16 relative flex items-center justify-center mb-4">
                    <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-sky-500 rounded-full border-t-transparent animate-spin"></div>
                    <i data-lucide="bell" class="w-6 h-6 text-sky-500 animate-pulse"></i>
                </div>
                <span class="text-sm font-bold text-slate-500 tracking-widest uppercase">Memuat Data...</span>
            </div>

            {{-- Empty State --}}
            <div x-show="!loading && notifications.length === 0"
                 class="absolute inset-0 flex flex-col items-center justify-center text-center p-8" x-transition>
                <div class="mb-3">
                    <i data-lucide="inbox" class="w-10 h-10 text-slate-300 mx-auto"></i>
                </div>
                <p class="text-slate-500 text-sm font-medium" x-text="emptyStateMessage"></p>
            </div>

            {{-- Notification Items --}}
            <div class="p-4 sm:p-6 space-y-3 bg-slate-50/50">
                <template x-for="notif in notifications" :key="notif.id">
                    <div
                        :class="!notif.read ? 'bg-sky-50 border-sky-200 shadow hover:shadow-md hover:border-sky-300 hover:bg-sky-100' : 'bg-white border-slate-200 shadow-sm hover:shadow-md hover:border-slate-300 hover:bg-slate-50'"
                        class="relative p-4 sm:p-5 rounded-2xl transition-all duration-300 flex gap-4 group border"
                        x-show="!loading"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0">

                        {{-- Unread Dot Indicator --}}
                        <div x-show="!notif.read" class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-8 bg-sky-500 rounded-r-full shadow-[0_0_8px_rgba(14,165,233,0.6)]"></div>

                        {{-- Icon Badge --}}
                        <div class="shrink-0 pt-0.5">
                            <div :class="!notif.read ? 'bg-white shadow-md border-sky-100 ring-4 ring-sky-50' : 'bg-slate-50 border-slate-200 text-slate-400'"
                                 class="w-12 h-12 rounded-2xl flex items-center justify-center border transition-all duration-300 group-hover:scale-110">
                                
                                <template x-if="notif.type === 'success' || notif.title.includes('Berhasil') || notif.title.includes('Selesai')">
                                    <i data-lucide="check-circle" class="w-6 h-6 text-emerald-500"></i>
                                </template>
                                <template x-if="notif.type === 'error' || notif.title.includes('Buruk') || notif.title.includes('Gagal')">
                                    <i data-lucide="alert-triangle" class="w-6 h-6 text-red-500"></i>
                                </template>
                                <template x-if="notif.type === 'new_task' || notif.title.includes('Tugas')">
                                    <i data-lucide="briefcase" class="w-6 h-6 text-indigo-500"></i>
                                </template>
                                <template x-if="notif.title.includes('Testimoni')">
                                    <i data-lucide="message-square-quote" class="w-6 h-6 text-amber-500"></i>
                                </template>
                                <template x-if="!['success','error','new_task'].includes(notif.type) && !notif.title.includes('Berhasil') && !notif.title.includes('Buruk') && !notif.title.includes('Gagal') && !notif.title.includes('Tugas') && !notif.title.includes('Testimoni')">
                                    <i data-lucide="bell" class="w-6 h-6 text-sky-500"></i>
                                </template>
                            </div>
                        </div>

                        {{-- Text Content --}}
                        <div class="flex-1 min-w-0 pr-16 sm:pr-24">
                            <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between mb-1.5 gap-1">
                                <h4 :class="!notif.read ? 'font-bold text-slate-900' : 'font-semibold text-slate-700'"
                                    class="text-base truncate group-hover:text-sky-700 transition-colors" x-text="notif.title"></h4>
                                <span :class="!notif.read ? 'text-sky-600 font-bold' : 'text-slate-400 font-medium'"
                                      class="text-[11px] uppercase tracking-widest whitespace-nowrap" x-text="relativeTime(notif.created_at)"></span>
                            </div>
                            <p :class="!notif.read ? 'text-slate-700 font-medium' : 'text-slate-500'"
                               class="text-sm leading-relaxed mb-3" x-text="notif.message"></p>
                            
                            <a x-show="notif.link"
                               :href="notif.link"
                               @click="if (!notif.read) markAsRead(notif.id)"
                               class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-sky-600 hover:text-sky-800 bg-sky-50 hover:bg-sky-100 px-3 py-1.5 rounded-lg transition-colors">
                                Lihat Detail
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="absolute right-4 top-4 flex flex-col sm:flex-row items-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200">
                            <button
                                x-show="!notif.read"
                                @click.stop="markAsRead(notif.id)"
                                title="Tandai dibaca"
                                class="p-2 rounded-xl text-sky-600 bg-white hover:bg-sky-50 border border-slate-200 hover:border-sky-200 shadow-sm transition-all hover:scale-110">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </button>
                            <button
                                @click.stop="deleteNotif(notif.id)"
                                title="Hapus"
                                class="p-2 rounded-xl text-red-500 bg-white hover:bg-red-50 border border-slate-200 hover:border-red-200 shadow-sm transition-all hover:scale-110">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
function notifikasiApp() {
    return {
        notifications: [],
        unreadCount:   {{ $unreadCount }},
        loading:       false,
        activeTab:     'semua',
        searchQuery:   '',
        tabs: [
            { id: 'semua',        label: 'Semua' },
            { id: 'belum_dibaca', label: 'Belum Dibaca' },
            { id: 'sudah_dibaca', label: 'Sudah Dibaca' },
        ],

        get emptyStateMessage() {
            if (this.searchQuery)
                return 'Tidak ada notifikasi yang cocok dengan kata kunci pencarian Anda.';
            if (this.activeTab === 'belum_dibaca')
                return 'Semua notifikasi sudah dibaca. ✓';
            if (this.activeTab === 'sudah_dibaca')
                return 'Belum ada notifikasi yang pernah dibaca.';
            return 'Anda belum memiliki notifikasi. Kami akan memberi tahu Anda jika ada aktivitas baru.';
        },

        /** Helper: format relative time tanpa library */
        relativeTime(isoString) {
            if (!isoString) return '';
            const diff = (Date.now() - new Date(isoString)) / 1000;
            if (diff < 60)   return 'Baru saja';
            if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
            if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
            return Math.floor(diff / 86400) + ' hari lalu';
        },

        /** Helper: headers fetch dengan CSRF */
        _headers(method = 'GET') {
            const h = { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
            if (method !== 'GET') h['Content-Type'] = 'application/json';
            return h;
        },

        /** [Read] Ambil list notifikasi dari API */
        async fetchNotifications() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.activeTab === 'belum_dibaca') params.set('filter', 'unread');
                if (this.activeTab === 'sudah_dibaca') params.set('filter', 'read');
                if (this.searchQuery) params.set('search', this.searchQuery);

                const res  = await fetch('/api/notifications?' + params.toString(), { headers: this._headers() });
                const json = await res.json();
                this.notifications = json.notifications ?? [];
                this.unreadCount   = json.unread_count ?? 0;

                // Re-init lucide icons inside dynamically rendered template
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            } catch (e) {
                console.error('Gagal memuat notifikasi:', e);
            } finally {
                this.loading = false;
            }
        },

        /** [Update] Tandai satu notifikasi dibaca */
        async markAsRead(id) {
            try {
                const res  = await fetch('/api/notifications/' + id + '/read', {
                    method: 'PATCH', headers: this._headers('PATCH'),
                });
                const json = await res.json();
                const notif = this.notifications.find(n => n.id === id);
                if (notif) notif.read = true;
                this.unreadCount = json.unread_count ?? Math.max(0, this.unreadCount - 1);

                // Jika tab aktif "belum_dibaca" → hapus dari list
                if (this.activeTab === 'belum_dibaca') {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }
            } catch (e) { console.error(e); }
        },

        /** [Update Massal] Tandai semua dibaca */
        async markAllAsRead() {
            try {
                await fetch('/api/notifications/read-all', {
                    method: 'POST', headers: this._headers('POST'),
                });
                this.notifications.forEach(n => n.read = true);
                this.unreadCount = 0;
                if (this.activeTab === 'belum_dibaca') this.notifications = [];
            } catch (e) { console.error(e); }
        },

        /** [Delete] Hapus satu notifikasi */
        async deleteNotif(id) {
            try {
                const res  = await fetch('/api/notifications/' + id, {
                    method: 'DELETE', headers: this._headers('DELETE'),
                });
                const json = await res.json();
                this.notifications = this.notifications.filter(n => n.id !== id);
                this.unreadCount   = json.unread_count ?? this.unreadCount;
            } catch (e) { console.error(e); }
        },

        /** [Delete Massal] Bersihkan semua */
        async clearAll() {
            if (!confirm('Apakah Anda yakin ingin menghapus seluruh riwayat notifikasi?')) return;
            try {
                await fetch('/api/notifications/clear-all', {
                    method: 'DELETE', headers: this._headers('DELETE'),
                });
                this.notifications = [];
                this.unreadCount   = 0;
            } catch (e) { console.error(e); }
        },
    };
}

// Re-init lucide after Alpine finishes first render
document.addEventListener('alpine:initialized', () => {
    if (window.lucide) lucide.createIcons();
});
</script>
@endpush
