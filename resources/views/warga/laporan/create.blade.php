@extends('layouts.warga')
@section('title', 'Buat Laporan')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<div x-data="laporanForm()" x-cloak>

    {{-- Success Screen --}}
    <template x-if="submitted">
        <div class="flex items-center justify-center min-h-[60vh]">
            <div class="text-center max-w-md">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="text-sky-900 mb-2" style="font-size:1.5rem;font-weight:700">Laporan Terkirim!</h2>
                <p class="text-slate-500 mb-2" style="font-size:0.9rem">Laporan Anda telah diterima dan menunggu validasi dari Admin.</p>
                <p class="text-slate-400 mb-6" style="font-size:0.8rem">Admin akan meninjau dan memutuskan apakah perlu penanganan langsung di lokasi. Pantau status di halaman Riwayat Laporan.</p>
                <a href="{{ route('warga.laporan.index') }}" class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-2.5 rounded-xl transition-colors inline-block" style="font-size:0.9rem">Lihat Riwayat Laporan</a>
            </div>
        </div>
    </template>

    {{-- Form --}}
    <template x-if="!submitted">
        <div>
            <h1 class="text-sky-900 mb-1" style="font-size:1.5rem;font-weight:700">Buat Laporan</h1>
            <p class="text-slate-500 mb-6" style="font-size:0.85rem">Laporkan masalah infrastruktur air di rumah Anda dengan detail lengkap</p>

            <form action="{{ route('warga.laporan.store') }}" method="POST" enctype="multipart/form-data" class="w-full space-y-6" @submit="handleSubmit">
                @csrf

                {{-- Jenis Masalah --}}
                <div class="bg-white rounded-xl p-6 border border-sky-100 shadow-sm">
                    <h3 class="text-sky-800 mb-4 flex items-center gap-2" style="font-size:1rem;font-weight:600">Jenis Masalah</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                        @foreach($kategoris as $k)
                            <button type="button"
                                @click="form.kategori_laporan_id = '{{ $k->id }}'"
                                :class="form.kategori_laporan_id === '{{ $k->id }}' ? 'border-sky-500 bg-sky-50 shadow-sm' : 'border-sky-100 hover:border-sky-200'"
                                class="p-4 rounded-xl border-2 text-left transition-all hover:shadow-md">
                                <span style="font-size:1.5rem">{{ $k->icon ?? '📋' }}</span>
                                <p class="text-sky-800 mt-2" style="font-size:0.83rem;font-weight:600">{{ $k->nama_kategori }}</p>
                                <p class="text-slate-400 mt-0.5" style="font-size:0.72rem">{{ $k->deskripsi }}</p>
                                <div class="mt-2 pt-2 border-t" :class="form.kategori_laporan_id === '{{ $k->id }}' ? 'border-sky-200' : 'border-sky-50'">
                                    @if($k->tarif == 0)
                                        <span class="text-emerald-600" style="font-size:0.75rem;font-weight:700">GRATIS</span>
                                    @else
                                        <span class="text-sky-600" style="font-size:0.75rem;font-weight:600">Rp {{ number_format($k->tarif, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="kategori_laporan_id" x-model="form.kategori_laporan_id">
                    @error('kategori_laporan_id') <p class="text-red-500 mt-2" style="font-size:0.8rem">{{ $message }}</p> @enderror
                </div>

                {{-- Estimasi Biaya --}}
                <template x-if="form.kategori_laporan_id">
                    <div>
                        @foreach($kategoris as $k)
                            <div x-show="form.kategori_laporan_id === '{{ $k->id }}'"
                                 class="rounded-xl p-5 border-2 {{ $k->tarif == 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-sky-50 border-sky-200' }}">
                                <div class="flex items-center gap-3 mb-2">
                                    <svg class="w-5 h-5 {{ $k->tarif == 0 ? 'text-emerald-600' : 'text-sky-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    <h3 class="{{ $k->tarif == 0 ? 'text-emerald-800' : 'text-sky-800' }}" style="font-size:0.95rem;font-weight:600">Estimasi Biaya Layanan</h3>
                                </div>
                                <div class="flex items-baseline gap-2 mb-2">
                                    @if($k->tarif == 0)
                                        <span class="text-emerald-700" style="font-size:1.5rem;font-weight:800">GRATIS</span>
                                    @else
                                        <span class="text-sky-500" style="font-size:0.85rem">Mulai dari Rp</span>
                                        <span class="text-sky-800" style="font-size:1.5rem;font-weight:800">{{ number_format($k->tarif, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                                <p class="{{ $k->tarif == 0 ? 'text-emerald-700' : 'text-sky-700' }}" style="font-size:0.8rem;line-height:1.5">{{ $k->deskripsi }}</p>
                                <p class="text-slate-400 mt-2" style="font-size:0.72rem">* Biaya final ditentukan setelah validasi admin. Tarif sudah termasuk jasa petugas.</p>
                            </div>
                        @endforeach
                    </div>
                </template>

                {{-- Alamat Rumah --}}
                <div class="bg-white rounded-xl p-6 border border-sky-100 shadow-sm">
                    <h3 class="text-sky-800 mb-4 flex items-center gap-2" style="font-size:1rem;font-weight:600">
                        <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Alamat Rumah
                    </h3>
                    <div class="grid lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2">
                            <label class="text-sky-800 mb-1 block" style="font-size:0.83rem">Alamat Lengkap (Nama Jalan) *</label>
                            <input name="alamat" x-model="form.alamat" placeholder="Contoh: Jl. Merdeka No. 12" class="w-full px-3 py-2.5 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" required>
                            @error('alamat') <p class="text-red-500 mt-1" style="font-size:0.78rem">{{ $message }}</p> @enderror
                        </div>

                        <div class="lg:col-span-1">
                            <label class="text-sky-800 mb-1 block" style="font-size:0.83rem">Wilayah / Kecamatan *</label>
                            <select name="wilayah_id" x-model="form.wilayah_id" class="w-full px-3 py-2.5 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300" style="font-size:0.85rem" required>
                                <option value="">-- Pilih Wilayah --</option>
                                @foreach($wilayahs as $w)
                                    <option value="{{ $w->id }}">{{ $w->nama_wilayah }}</option>
                                @endforeach
                            </select>
                            @error('wilayah_id') <p class="text-red-500 mt-1" style="font-size:0.78rem">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Pin Lokasi di Peta --}}
                <div class="bg-white rounded-xl p-6 border border-sky-100 shadow-sm">
                    <h3 class="text-sky-800 mb-2 flex items-center gap-2" style="font-size:1rem;font-weight:600">
                        <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Pin Lokasi di Peta
                    </h3>
                    <p class="text-slate-400 mb-3" style="font-size:0.8rem">Klik pada peta untuk menandai lokasi rumah Anda, atau gunakan GPS otomatis.</p>
                    <button type="button" @click="getLocation()" class="mb-3 bg-sky-100 hover:bg-sky-200 text-sky-700 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors" style="font-size:0.83rem">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Gunakan Lokasi GPS Saya
                    </button>

                    <div id="map" class="w-full h-[300px] rounded-xl border-2 border-sky-200 z-10 relative"></div>

                    <input type="hidden" name="latitude" x-model="form.lat">
                    <input type="hidden" name="longitude" x-model="form.lng">

                    <template x-if="mapReady">
                        <div class="mt-3 flex items-center gap-2 bg-emerald-50 text-emerald-700 rounded-lg p-2.5" style="font-size:0.8rem">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Koordinat: <span x-text="parseFloat(form.lat).toFixed(6)"></span>, <span x-text="parseFloat(form.lng).toFixed(6)"></span>
                        </div>
                    </template>

                    @error('latitude') <p class="text-red-500 mt-2" style="font-size:0.78rem">Tandai lokasi di peta terlebih dahulu.</p> @enderror
                </div>

                {{-- Detail Masalah --}}
                <div class="bg-white rounded-xl p-6 border border-sky-100 shadow-sm">
                    <h3 class="text-sky-800 mb-4" style="font-size:1rem;font-weight:600">Detail Masalah</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sky-800 mb-1.5 block" style="font-size:0.83rem">Deskripsi Masalah *</label>
                            <textarea name="deskripsi" x-model="form.deskripsi" placeholder="Jelaskan masalah secara detail: apa yang terjadi, sudah berapa lama, bagian mana yang bermasalah..." class="w-full px-3 py-2.5 border border-sky-200 rounded-lg bg-sky-50/50 focus:outline-none focus:ring-2 focus:ring-sky-300 h-32 resize-none" style="font-size:0.85rem" required></textarea>
                            @error('deskripsi') <p class="text-red-500 mt-1" style="font-size:0.78rem">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sky-800 mb-1.5 block" style="font-size:0.83rem">Foto Bukti (opsional)</label>
                            <label class="border-2 border-dashed border-sky-200 rounded-xl p-6 flex flex-col items-center justify-center cursor-pointer hover:bg-sky-50 transition-colors">
                                <svg class="w-8 h-8 text-sky-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span class="text-sky-600" style="font-size:0.83rem" x-text="fotoName || 'Klik untuk upload foto kondisi masalah'"></span>
                                <span class="text-slate-400 mt-1" style="font-size:0.72rem">Maks. 5MB (JPG, PNG)</span>
                                <input type="file" name="foto" accept="image/*" class="hidden" @change="fotoName = $event.target.files[0]?.name">
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white py-3.5 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-lg shadow-sky-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Kirim Laporan
                </button>
            </form>
        </div>
    </template>
</div>

<script>
function laporanForm() {
    return {
        submitted: false,
        mapReady: false,
        fotoName: '',
        form: {
            kategori_laporan_id: '{{ old("kategori_laporan_id", "") }}',
            wilayah_id: '{{ old("wilayah_id", "") }}',
            alamat: '{{ old("alamat", "") }}',
            deskripsi: '{{ old("deskripsi", "") }}',
            lat: {{ old('latitude', -6.9175) }},
            lng: {{ old('longitude', 107.6191) }},
        },
        map: null,
        marker: null,
        init() {
            this.$nextTick(() => {
                if(document.getElementById('map')) {
                    this.initMap();
                }
            });
        },
        initMap() {
            this.map = L.map('map').setView([this.form.lat, this.form.lng], 9);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                maxZoom: 19
            }).addTo(this.map);

            this.marker = L.marker([this.form.lat, this.form.lng], { draggable: true }).addTo(this.map);

            this.marker.on('dragend', () => {
                let pos = this.marker.getLatLng();
                this.form.lat = pos.lat;
                this.form.lng = pos.lng;
                this.mapReady = true;
            });

            this.map.on('click', (e) => {
                this.marker.setLatLng(e.latlng);
                this.form.lat = e.latlng.lat;
                this.form.lng = e.latlng.lng;
                this.mapReady = true;
            });

            setTimeout(() => this.map.invalidateSize(), 500);
        },
        getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.form.lat = pos.coords.latitude;
                        this.form.lng = pos.coords.longitude;
                        this.marker.setLatLng([this.form.lat, this.form.lng]);
                        this.map.setView([this.form.lat, this.form.lng], 15);
                        this.mapReady = true;
                    },
                    (error) => {
                        let errMsg = "Gagal mendapatkan lokasi.\n";
                        if(error.code == 1) errMsg += "Penyebab: Akses lokasi DITOLAK oleh browser atau pengaturan Windows Anda.";
                        else if(error.code == 2) errMsg += "Penyebab: Sinyal lokasi tidak tersedia di perangkat ini (Position Unavailable).";
                        else if(error.code == 3) errMsg += "Penyebab: Waktu permintaan lokasi habis (Timeout).";
                        alert(errMsg + "\n\nSilakan pin manual di peta untuk sementara waktu.");
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }
        },
        handleSubmit(e) {
            // Let the native form submit handle it
        }
    }
}
</script>
@endsection
