@extends('layouts.admin')
@section('title', 'Peta Laporan')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<div x-data="petaLaporan()" x-cloak>

    <div class="mb-5">
        <h1 class="text-2xl font-bold text-sky-900">Peta Laporan</h1>
        <p class="text-slate-500 text-sm mt-1">Visualisasi lokasi laporan berdasarkan status penanganan.</p>
    </div>

    {{-- Filter & Legend Bar --}}
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm mb-4 flex flex-wrap items-center gap-4">

        {{-- Filter Dropdown --}}
        <div class="flex items-center gap-2">
            <i data-lucide="filter" class="w-4 h-4 text-slate-400"></i>
            <select x-model="filterStatus" @change="updateFilters()"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition min-w-[180px]">
                <option value="semua">Semua Status</option>
                <template x-for="s in availableStatuses" :key="s.value">
                    <option :value="s.value" x-text="s.label"></option>
                </template>
            </select>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-3 ml-auto text-xs font-medium text-slate-600">
            <span class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded-full bg-amber-400 border-2 border-white shadow-sm inline-block"></span> Menunggu
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded-full bg-blue-500 border-2 border-white shadow-sm inline-block"></span> Diterima
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded-full bg-cyan-500 border-2 border-white shadow-sm inline-block"></span> Dikerjakan
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white shadow-sm inline-block"></span> Selesai
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded-full bg-red-500 border-2 border-white shadow-sm inline-block"></span> Ditolak
            </span>
        </div>

        {{-- Count badge --}}
        <span class="px-3 py-1.5 bg-sky-50 text-sky-700 text-xs font-semibold rounded-full border border-sky-200">
            <span x-text="filteredReports.length"></span> titik ditampilkan
        </span>
    </div>

    {{-- Map --}}
    <div class="bg-white p-2 rounded-2xl shadow-sm border border-slate-200 mb-5">
        <div id="map" class="w-full h-[500px] rounded-xl border border-slate-200 z-10 relative"></div>
    </div>

    {{-- List --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <p class="text-sm font-semibold text-slate-700">
                Daftar Titik Laporan
            </p>
            <span class="text-xs text-slate-400">Klik baris untuk zoom ke peta</span>
        </div>

        <div class="divide-y divide-slate-100 overflow-y-auto max-h-[360px]">
            <template x-for="report in filteredReports" :key="report.id">
                <div class="px-5 py-3.5 hover:bg-slate-50 transition-colors cursor-pointer flex items-center gap-4"
                     @click="zoomToReport(report)">

                    {{-- Status dot --}}
                    <div class="w-3 h-3 rounded-full shrink-0 border-2 border-white shadow"
                         :style="`background:${getStatusColor(report.status)}`"></div>

                    {{-- ID --}}
                    <span class="text-sm font-bold text-sky-700 shrink-0">#<span x-text="report.id"></span></span>

                    {{-- Kategori + Alamat --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-slate-800 font-medium truncate"
                           x-text="(report.kategori_laporan ? report.kategori_laporan.icon + ' ' + report.kategori_laporan.nama_kategori : '📋 Umum')"></p>
                        <p class="text-xs text-slate-400 truncate" x-text="report.alamat"></p>
                    </div>

                    {{-- Pelapor --}}
                    <p class="text-xs text-slate-500 shrink-0 hidden sm:block"
                       x-text="report.user ? report.user.name : 'Anonim'"></p>

                    {{-- Badge --}}
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold shrink-0"
                          :class="getStatusBadgeClass(report.status)"
                          x-text="getStatusLabel(report.status)"></span>
                </div>
            </template>

            <template x-if="filteredReports.length === 0">
                <div class="p-10 text-center text-slate-400 text-sm italic">
                    Tidak ada laporan untuk status yang dipilih.
                </div>
            </template>
        </div>
    </div>
</div>

<script>
window.adminMapInstance = null;
window.adminMarkersLayer = [];

function petaLaporan() {
    let leafletMap = null;
    let leafletMarkers = [];

    return {
        filterStatus: 'semua',
        allReports: @json($laporans),
        filteredReports: [],

        availableStatuses: [
            { value: 'pending',    label: 'Menunggu Validasi' },
            { value: 'diterima',   label: 'Diterima' },
            { value: 'dikerjakan', label: 'Dikerjakan' },
            { value: 'selesai',    label: 'Selesai' },
            { value: 'ditolak',    label: 'Ditolak' },
        ],

        // Warna per status
        statusColors: {
            pending:    '#F59E0B',
            diterima:   '#3B82F6',
            dikerjakan: '#06B6D4',
            selesai:    '#10B981',
            ditolak:    '#EF4444',
        },

        getStatusColor(status) {
            return this.statusColors[status] || '#64748B';
        },

        getStatusLabel(status) {
            const found = this.availableStatuses.find(s => s.value === status);
            return found ? found.label : status;
        },

        getStatusBadgeClass(status) {
            const map = {
                pending:    'bg-amber-100 text-amber-700',
                diterima:   'bg-blue-100 text-blue-700',
                dikerjakan: 'bg-cyan-100 text-cyan-700',
                selesai:    'bg-emerald-100 text-emerald-700',
                ditolak:    'bg-red-100 text-red-700',
            };
            return map[status] || 'bg-slate-100 text-slate-600';
        },

        init() {
            this.filteredReports = this.allReports;

            this.$nextTick(() => {
                if (document.getElementById('map')) {
                    this.initMap();
                }
            });
        },

        initMap() {
            setTimeout(() => {
                if (leafletMap) {
                    leafletMap.off();
                    leafletMap.remove();
                }
                
                const mapContainer = document.getElementById('map');
                if (mapContainer) {
                    mapContainer._leaflet_id = null;
                }
                
                leafletMap = L.map('map').setView([-6.9175, 107.6191], 9);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                    maxZoom: 19
                }).addTo(leafletMap);

                this.renderMarkers();
                
                if (leafletMarkers.length > 0) {
                    const group = new L.featureGroup(leafletMarkers);
                    leafletMap.fitBounds(group.getBounds(), { padding: [60, 60], maxZoom: 14 });
                }
                
                setTimeout(() => {
                    if (leafletMap) leafletMap.invalidateSize();
                }, 100);
            }, 100);
        },

        renderMarkers() {
            if (!leafletMap) return;
            
            // Hapus marker lama
            leafletMarkers.forEach(m => leafletMap.removeLayer(m));
            leafletMarkers = [];

            this.filteredReports.forEach(report => {
                const lokasi = report.map_lokasi || report.mapLokasi;
                if (!lokasi) return;

                const lat = parseFloat(lokasi.latitude);
                const lng = parseFloat(lokasi.longitude);
                
                // Prevent Leaflet crash if coordinates are invalid
                if (isNaN(lat) || isNaN(lng)) return;
                
                const color = this.getStatusColor(report.status);
                const label = this.getStatusLabel(report.status);

                // Marker lingkaran berwarna dengan border putih
                const iconHtml = `
                    <div style="
                        width:16px; height:16px; border-radius:50%;
                        background:${color}; border:3px solid white;
                        box-shadow:0 2px 6px rgba(0,0,0,.35);
                    "></div>`;

                const icon = L.divIcon({
                    html: iconHtml,
                    className: '',
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                });

                // Popup lengkap
                const kategoriText = report.kategori_laporan
                    ? (report.kategori_laporan.icon || '📋') + ' ' + report.kategori_laporan.nama_kategori
                    : '📋 Laporan';

                const popup = `
                    <div style="min-width:200px; font-family:inherit;">
                        <div style="font-weight:700; font-size:0.85rem; color:#0c4a6e; margin-bottom:4px;">
                            ${kategoriText}
                        </div>
                        <div style="display:inline-block; padding:2px 8px; border-radius:999px;
                                    background:${color}22; color:${color};
                                    font-size:0.7rem; font-weight:700; margin-bottom:6px;">
                            ${label}
                        </div>
                        <div style="font-size:0.78rem; color:#475569; margin-bottom:4px;">
                            📍 ${report.alamat}
                        </div>
                        <div style="font-size:0.72rem; color:#94a3b8; margin-bottom:8px;">
                            👤 ${report.user ? report.user.name : 'Anonim'}
                        </div>
                        <a href="/admin/laporan/${report.id}"
                           style="font-size:0.78rem; font-weight:600; color:#0284c7; text-decoration:none;">
                           Lihat Detail &rarr;
                        </a>
                    </div>`;

                try {
                    const marker = L.marker([lat, lng], { icon })
                        .addTo(leafletMap)
                        .bindPopup(popup, { maxWidth: 240 });

                    leafletMarkers.push(marker);
                } catch (e) {
                    console.error("Error adding marker for report", report.id, e);
                }
            });
        },

        updateFilters() {
            this.filteredReports = this.filterStatus === 'semua'
                ? this.allReports
                : this.allReports.filter(r => r.status === this.filterStatus);
            
            this.renderMarkers();
            
            if (leafletMarkers.length > 0 && leafletMap) {
                const group = new L.featureGroup(leafletMarkers);
                leafletMap.fitBounds(group.getBounds(), { padding: [60, 60], maxZoom: 15 });
            }
        },

        zoomToReport(report) {
            const lokasi = report.map_lokasi || report.mapLokasi;
            if (!lokasi || !leafletMap) return;
            const lat = parseFloat(lokasi.latitude);
            const lng = parseFloat(lokasi.longitude);
            
            if (isNaN(lat) || isNaN(lng)) return;
            
            leafletMap.setView([lat, lng], 16, { animate: true });
            
            const marker = leafletMarkers.find(m => {
                const pos = m.getLatLng();
                // using small epsilon for float comparison
                return Math.abs(pos.lat - lat) < 0.0001 && Math.abs(pos.lng - lng) < 0.0001;
            });
            if (marker) marker.openPopup();
        },
    };
}
</script>

<style>
/* Pastikan popup Leaflet terlihat rapi */
.leaflet-popup-content-wrapper {
    border-radius: 12px !important;
    box-shadow: 0 4px 20px rgba(0,0,0,.15) !important;
    border: 1px solid #e2e8f0 !important;
}
.leaflet-popup-tip {
    background: white !important;
}
</style>
@endsection
