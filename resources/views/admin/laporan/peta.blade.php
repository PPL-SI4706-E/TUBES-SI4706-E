@extends('layouts.admin')
@section('title', 'Peta Laporan')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>

<div x-data="petaLaporan()" x-init="initMap()">
    <h1 class="text-sky-900 mb-1" style="font-size: 1.5rem; font-weight: 700;">Peta Laporan</h1>
    <p class="text-slate-500 mb-4" style="font-size: 0.85rem;">Visualisasi lokasi seluruh laporan di peta interaktif</p>

    <!-- Filter -->
    <div class="bg-white rounded-xl p-4 border border-sky-100 shadow-sm mb-4">
        <div class="flex items-center gap-3 flex-wrap">
            <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <select x-model="filterStatus" @change="updateFilters()" class="border border-sky-200 rounded-lg px-3 py-2 bg-sky-50/50 text-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size: 0.83rem;">
                <option value="semua">Semua Status</option>
                <template x-for="status in availableStatuses" :key="status.value">
                    <option :value="status.value" x-text="status.label"></option>
                </template>
            </select>
            <div class="flex items-center gap-3 ml-auto" style="font-size: 0.78rem;">
                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-500 rounded-full inline-block"></span> Aktif/Menunggu</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-500 rounded-full inline-block"></span> Selesai</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-blue-500 rounded-full inline-block"></span> Lainnya</span>
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="bg-white p-2 rounded-2xl shadow-sm border border-sky-100 mb-6">
        <div id="map" class="w-full h-[500px] rounded-xl border border-sky-200 z-10 relative"></div>
    </div>

    <!-- List below map -->
    <div class="mt-4 bg-white rounded-xl border border-sky-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-sky-100 bg-sky-50/30">
            <p class="text-sky-800" style="font-size: 0.9rem; font-weight: 600;">
                <span x-text="filteredReports.length"></span> Laporan Ditampilkan
            </p>
        </div>
        <div class="divide-y divide-sky-50 overflow-y-auto max-h-[400px]">
            <template x-for="report in filteredReports" :key="report.id">
                <div class="p-4 hover:bg-sky-50/30 transition-colors cursor-pointer" @click="zoomToReport(report)">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-sky-600 shrink-0" style="font-weight: 600; font-size: 0.83rem;">#<span x-text="report.id"></span></span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <p class="text-slate-700 truncate" style="font-size: 0.83rem;" x-text="report.alamat"></p>
                                </div>
                                <p class="text-slate-400" style="font-size: 0.75rem;">
                                    <span x-text="report.kategori_laporan ? report.kategori_laporan.icon + ' ' + report.kategori_laporan.nama_kategori : '📋 Umum'"></span> | 
                                    <span x-text="report.user ? report.user.name : 'Anonim'"></span> | 
                                    <span x-text="report.map_lokasi ? parseFloat(report.map_lokasi.latitude).toFixed(4) + ', ' + parseFloat(report.map_lokasi.longitude).toFixed(4) : 'Lokasi tidak tersedia'"></span>
                                </p>
                            </div>
                        </div>
                        <span :class="getStatusBadgeClass(report.status)" class="px-2.5 py-1 rounded-full whitespace-nowrap" style="font-size: 0.75rem; font-weight: 600;" x-text="getStatusLabel(report.status)"></span>
                    </div>
                </div>
            </template>
            <template x-if="filteredReports.length === 0">
                <div class="p-8 text-center text-slate-400 italic">
                    Tidak ada laporan untuk status ini.
                </div>
            </template>
        </div>
    </div>
</div>

<script>
window.adminMapInstance = null;
window.adminMarkersLayer = [];

function petaLaporan() {
    return {
        filterStatus: 'semua',
        allReports: @json($laporans),
        filteredReports: [],
        availableStatuses: [
            { value: 'pending', label: 'Menunggu Validasi' },
            { value: 'diterima', label: 'Divalidasi' },
            { value: 'dikerjakan', label: 'Sedang Dikerjakan' },
            { value: 'selesai', label: 'Selesai' },
            { value: 'ditolak', label: 'Ditolak' }
        ],
        initMap() {
            this.filteredReports = this.allReports;
            
            setTimeout(() => {
                if (window.adminMapInstance) {
                    window.adminMapInstance.off();
                    window.adminMapInstance.remove();
                }
                
                const mapContainer = document.getElementById('map');
                if (mapContainer) {
                    mapContainer._leaflet_id = null;
                }
                
                window.adminMapInstance = L.map('map').setView([-6.9175, 107.6191], 9);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(window.adminMapInstance);

                this.renderMarkers();
                
                if (window.adminMarkersLayer.length > 0) {
                    const group = new L.featureGroup(window.adminMarkersLayer);
                    window.adminMapInstance.fitBounds(group.getBounds(), { padding: [50, 50] });
                }
                
                window.adminMapInstance.invalidateSize();
            }, 100);
        },
        renderMarkers() {
            if (!window.adminMapInstance) return;
            
            // Clear existing markers
            window.adminMarkersLayer.forEach(m => window.adminMapInstance.removeLayer(m));
            window.adminMarkersLayer = [];

            this.filteredReports.forEach(report => {
                if (report.map_lokasi && report.map_lokasi.latitude && report.map_lokasi.longitude) {
                    const lat = parseFloat(report.map_lokasi.latitude);
                    const lng = parseFloat(report.map_lokasi.longitude);
                    
                    // Prevent Leaflet crash if coordinates are invalid
                    if (isNaN(lat) || isNaN(lng)) return;
                    
                    let markerColor = 'blue';
                    if (report.status === 'pending') markerColor = 'red';
                    else if (report.status === 'selesai') markerColor = 'green';
                    else if (report.status === 'ditolak') markerColor = 'gray';
                    else if (report.status === 'dikerjakan') markerColor = 'red'; // Active

                    const iconHtml = `<div style="background-color: ${markerColor}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.3);"></div>`;
                    const customIcon = L.divIcon({
                        html: iconHtml,
                        className: 'custom-div-icon',
                        iconSize: [12, 12],
                        iconAnchor: [6, 6]
                    });

                    const popupContent = `
                        <div class="p-2 min-w-[200px]">
                            <div class="font-bold text-sky-800 mb-1">
                                ${report.kategori_laporan ? (report.kategori_laporan.icon || '📋') + ' ' + report.kategori_laporan.nama_kategori : '📋 Laporan'}
                            </div>
                            <div class="text-[10px] font-bold text-slate-500 mb-2 uppercase">${this.getStatusLabel(report.status)}</div>
                            <div class="text-xs text-slate-700 mb-3">${report.alamat}</div>
                            <div class="flex justify-between items-center mt-2 pt-2 border-t border-slate-100">
                                <a href="/admin/laporan/${report.id}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">Lihat Detail &rarr;</a>
                            </div>
                        </div>
                    `;

                    try {
                        const marker = L.marker([lat, lng], { icon: customIcon }).addTo(window.adminMapInstance).bindPopup(popupContent);
                        window.adminMarkersLayer.push(marker);
                    } catch (e) {
                        console.error("Error adding marker for report", report.id, e);
                    }
                }
            });
        },
        updateFilters() {
            if (this.filterStatus === 'semua') {
                this.filteredReports = this.allReports;
            } else {
                this.filteredReports = this.allReports.filter(r => r.status === this.filterStatus);
            }
            this.renderMarkers();
            
            if (window.adminMarkersLayer.length > 0 && window.adminMapInstance) {
                const group = new L.featureGroup(window.adminMarkersLayer);
                window.adminMapInstance.fitBounds(group.getBounds(), { padding: [50, 50], maxZoom: 15 });
            }
        },
        zoomToReport(report) {
            if (report.map_lokasi && window.adminMapInstance) {
                const lat = parseFloat(report.map_lokasi.latitude);
                const lng = parseFloat(report.map_lokasi.longitude);
                
                if (isNaN(lat) || isNaN(lng)) return;
                
                window.adminMapInstance.setView([lat, lng], 16);
                
                // Find and open marker popup
                const marker = window.adminMarkersLayer.find(m => {
                    const pos = m.getLatLng();
                    // using small epsilon for float comparison
                    return Math.abs(pos.lat - lat) < 0.0001 && Math.abs(pos.lng - lng) < 0.0001;
                });
                if (marker) marker.openPopup();
            }
        },
        getStatusLabel(status) {
            const found = this.availableStatuses.find(s => s.value === status);
            return found ? found.label : status;
        },
        getStatusBadgeClass(status) {
            const styles = {
                'pending': 'bg-red-100 text-red-700',
                'diterima': 'bg-blue-100 text-blue-700',
                'dikerjakan': 'bg-orange-100 text-orange-700',
                'selesai': 'bg-green-100 text-green-700',
                'ditolak': 'bg-slate-100 text-slate-700'
            };
            return styles[status] || 'bg-sky-100 text-sky-700';
        }
    }
}
</script>

<style>
.custom-div-icon {
    background: none;
    border: none;
}
</style>
@endsection
