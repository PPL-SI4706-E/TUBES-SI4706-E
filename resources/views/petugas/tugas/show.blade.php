@extends('layouts.petugas')
@section('title', 'Detail Tugas #' . $penugasan->laporan->id)

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    $laporan    = $penugasan->laporan;
    $lokasi     = $laporan->mapLokasi;
    $statusOrder = ['Ditugaskan', 'Menuju Lokasi', 'Sedang Dikerjakan', 'Menunggu Konfirmasi', 'Selesai'];
    $currentIdx = array_search($penugasan->status_tugas, $statusOrder);
    $badgeColor = match($penugasan->status_tugas) {
        'Menuju Lokasi'       => 'bg-amber-100 text-amber-700 border-amber-200',
        'Sedang Dikerjakan'   => 'bg-blue-100 text-blue-700 border-blue-200',
        'Menunggu Konfirmasi' => 'bg-purple-100 text-purple-700 border-purple-200',
        'Selesai'             => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        default               => 'bg-slate-100 text-slate-600 border-slate-200',
    };
    $nextStatuses = array_filter($statusOrder, fn($s, $i) => $i > $currentIdx && !in_array($s, ['Menunggu Konfirmasi', 'Selesai']), ARRAY_FILTER_USE_BOTH);
    $udahSelesai  = $penugasan->status_tugas === 'Selesai';
    $bisaUpload   = !$penugasan->penyelesaian; // tampil selama belum ada bukti
    $rating       = optional($penugasan->ulasan)->rating ?? 0;
@endphp

{{-- Breadcrumb --}}
<div class="mb-5">
    <a href="{{ route('petugas.tugas.index') }}"
       class="inline-flex items-center gap-1.5 text-sky-600 hover:text-sky-800 text-sm font-medium transition-colors mb-3">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Tugas
    </a>
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-sky-900" style="font-size:1.4rem;font-weight:700">
            Detail Tugas <span class="text-sky-500">#{{ $laporan->id }}</span>
        </h1>
        <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-bold {{ $badgeColor }}">
            {{ $penugasan->status_tugas }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ═══ LEFT ═══ --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Progress Bar --}}
        <div class="bg-white rounded-2xl border border-sky-100 shadow-sm p-5">
            <p class="text-slate-500 mb-4" style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Progress Status Pekerjaan</p>
            <div class="flex items-start gap-0">
                @foreach($statusOrder as $i => $s)
                    @php $done = $i <= $currentIdx; $active = $i === $currentIdx; @endphp
                    @if($i < count($statusOrder) - 1)
                        <div class="flex flex-col items-center" style="flex:1">
                            <div class="w-full flex items-center">
                                <div class="w-3 h-3 rounded-full shrink-0 border-2
                                    {{ $done ? 'bg-sky-500 border-sky-500' : 'bg-white border-slate-300' }}
                                    {{ $active ? 'ring-2 ring-sky-200 ring-offset-1' : '' }}"></div>
                                <div class="h-1 flex-1 rounded {{ $i < $currentIdx ? 'bg-sky-500' : 'bg-slate-200' }}"></div>
                            </div>
                            <span class="mt-1.5 text-center leading-tight px-1
                                {{ $active ? 'text-sky-700 font-semibold' : 'text-slate-400' }}"
                                  style="font-size:0.68rem;min-width:60px">{{ $s }}</span>
                        </div>
                    @else
                        <div class="flex flex-col items-center" style="min-width:40px">
                            <div class="w-3 h-3 rounded-full border-2 {{ $done ? 'bg-sky-500 border-sky-500' : 'bg-white border-slate-300' }}
                                {{ $active ? 'ring-2 ring-sky-200 ring-offset-1' : '' }}"></div>
                            <span class="mt-1.5 text-center {{ $active ? 'text-sky-700 font-semibold' : 'text-slate-400' }}"
                                  style="font-size:0.68rem">{{ $s }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Info Pelanggan --}}
        <div class="bg-white rounded-2xl border border-sky-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100">
                <p class="text-slate-500" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Informasi Pelanggan</p>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-sky-100 rounded-full flex items-center justify-center text-sky-700 font-bold shrink-0">
                        {{ strtoupper(substr($laporan->user->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-800" style="font-size:0.95rem">{{ $laporan->user->name ?? 'Anonim' }}</p>
                        <p class="text-slate-500" style="font-size:0.78rem">{{ $laporan->user->email ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 pt-1">
                    <i data-lucide="phone" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <a href="tel:{{ $laporan->user->phone }}"
                       class="text-sky-600 hover:underline" style="font-size:0.88rem">
                        {{ $laporan->user->phone ?? '-' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Alamat & Peta --}}
        <div class="bg-white rounded-2xl border border-sky-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="home" class="w-4 h-4 text-sky-600"></i>
                <p class="text-sky-700 font-semibold" style="font-size:0.9rem">Alamat Rumah Lengkap</p>
            </div>
            <div class="p-5">
                <p class="text-slate-800 font-medium" style="font-size:0.9rem">{{ $laporan->alamat }}</p>
                @if($laporan->wilayah)
                    <p class="text-slate-500 mt-0.5" style="font-size:0.82rem">{{ $laporan->wilayah->nama_wilayah }}</p>
                @endif
                @if($lokasi)
                    <p class="text-slate-400 flex items-center gap-1 mt-2" style="font-size:0.75rem">
                        <i data-lucide="navigation" class="w-3 h-3"></i>
                        {{ $lokasi->latitude }}, {{ $lokasi->longitude }}
                    </p>
                @endif

                @if($lokasi)
                    {{-- Map --}}
                    <div id="tugas-map" class="w-full rounded-xl border border-slate-200 overflow-hidden mt-4" style="height:260px"></div>

                    {{-- Google Maps Button --}}
                    <a href="https://maps.google.com/?q={{ $lokasi->latitude }},{{ $lokasi->longitude }}"
                       target="_blank"
                       class="mt-3 w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-xl transition-colors shadow-sm"
                       style="font-size:0.9rem">
                        <i data-lucide="navigation" class="w-4 h-4"></i> Buka di Google Maps
                    </a>

                    <script>
                        setTimeout(function () {
                            var map = L.map('tugas-map').setView([{{ $lokasi->latitude }}, {{ $lokasi->longitude }}], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap', maxZoom: 19
                            }).addTo(map);

                            var icon = L.divIcon({
                                html: '<div style="width:18px;height:18px;border-radius:50%;background:#ef4444;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.35)"></div>',
                                className: '', iconSize: [18, 18], iconAnchor: [9, 9]
                            });
                            L.marker([{{ $lokasi->latitude }}, {{ $lokasi->longitude }}], {icon})
                                .addTo(map)
                                .bindPopup('<b>#{{ $laporan->id }}</b><br>{{ addslashes($laporan->alamat) }}')
                                .openPopup();

                            setTimeout(() => map.invalidateSize(), 400);
                        }, 200);
                    </script>
                @else
                    <div class="mt-4 bg-slate-50 rounded-xl p-4 text-center border border-slate-200">
                        <i data-lucide="map-off" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                        <p class="text-slate-400 text-sm">Koordinat lokasi tidak tersedia.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Deskripsi & Catatan Admin --}}
        <div class="bg-white rounded-2xl border border-sky-100 shadow-sm p-5">
            <p class="text-slate-500 mb-2" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Deskripsi Masalah:</p>
            <p class="text-slate-700 leading-relaxed" style="font-size:0.9rem">{{ $laporan->deskripsi }}</p>

            @if($laporan->catatan_admin)
                <div class="mt-4 bg-sky-50 rounded-xl p-4 border border-sky-100">
                    <p class="text-sky-700 font-semibold mb-1" style="font-size:0.82rem">Catatan Admin:</p>
                    <p class="text-sky-800" style="font-size:0.88rem">{{ $laporan->catatan_admin }}</p>
                </div>
            @endif
        </div>

        {{-- Upload Bukti (hanya saat Sedang Dikerjakan & belum upload) --}}
        @if($bisaUpload)
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm overflow-hidden" x-data="buktiUpload()">
            <div class="px-5 py-3.5 bg-emerald-50 border-b border-emerald-100 flex items-center gap-2">
                <i data-lucide="upload-cloud" class="w-4 h-4 text-emerald-600"></i>
                <p class="text-emerald-800 font-semibold" style="font-size:0.9rem">Upload Bukti Penyelesaian</p>
            </div>
            <form action="{{ route('petugas.tugas.bukti', $penugasan->id) }}" method="POST"
                  enctype="multipart/form-data" class="p-5">
                @csrf

                {{-- Preview --}}
                <div class="mb-4">
                    <div class="relative border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer
                                hover:border-emerald-400 hover:bg-emerald-50/30 transition-colors"
                         @click="$refs.fotoInput.click()">
                        <template x-if="!preview">
                            <div>
                                <i data-lucide="image-plus" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-slate-400 text-sm">Klik untuk pilih foto bukti</p>
                                <p class="text-slate-300 text-xs mt-1">JPG, JPEG, PNG · Maks 5MB</p>
                            </div>
                        </template>
                        <template x-if="preview">
                            <img :src="preview" class="max-h-48 mx-auto rounded-lg object-contain" alt="Preview">
                        </template>
                    </div>
                    <input type="file" name="foto_bukti" accept="image/jpg,image/jpeg,image/png"
                           class="hidden" x-ref="fotoInput" @change="handleFile($event)" id="foto_bukti">
                    @error('foto_bukti')
                        <p class="text-red-500 mt-1" style="font-size:0.78rem">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Keterangan --}}
                <div class="mb-4">
                    <label class="block text-slate-600 mb-1.5" style="font-size:0.83rem;font-weight:600">
                        Catatan Perbaikan <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <textarea name="keterangan" rows="3"
                              placeholder="Jelaskan perbaikan yang telah dilakukan..."
                              class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 resize-none">{{ old('keterangan') }}</textarea>
                </div>

                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-3 rounded-xl transition-colors shadow-sm"
                        style="font-size:0.9rem">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Selesaikan &amp; Upload Bukti
                </button>
            </form>
        </div>
        @endif

        {{-- Bukti yang sudah diupload --}}
        @if($penugasan->penyelesaian)
        <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 bg-emerald-50 border-b border-emerald-100 flex items-center gap-2">
                <i data-lucide="image" class="w-4 h-4 text-emerald-600"></i>
                <p class="text-emerald-800 font-semibold" style="font-size:0.9rem">Bukti Penyelesaian</p>
            </div>
            <div class="p-5">
                <img src="{{ asset('storage/' . $penugasan->penyelesaian->foto_bukti) }}"
                     alt="Bukti Penyelesaian"
                     class="w-full rounded-xl object-contain max-h-72 border border-slate-200 bg-slate-50">
                @if($penugasan->penyelesaian->keterangan)
                    <div class="mt-3 bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <p class="text-slate-500 text-xs font-semibold mb-1">Catatan Perbaikan:</p>
                        <p class="text-slate-700 text-sm">{{ $penugasan->penyelesaian->keterangan }}</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Rating dari warga (jika selesai atau menunggu konfirmasi dan sudah ada ulasan) --}}
        @if($penugasan->ulasan)
        <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5">
            <p class="text-slate-500 mb-3" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Rating dari Warga</p>
            <div class="flex items-center gap-3">
                <div class="flex gap-0.5">
                    @for($s = 1; $s <= 5; $s++)
                        <span style="font-size:1.4rem;color:{{ $s <= $rating ? '#f59e0b' : '#d1d5db' }}">★</span>
                    @endfor
                </div>
                <span class="text-amber-700 font-bold text-lg">{{ $rating }}/5</span>
            </div>
            @if(optional($penugasan->ulasan)->komentar ?? false)
                <p class="text-slate-600 text-sm mt-3 italic">"{{ $penugasan->ulasan->komentar }}"</p>
            @endif
        </div>
        @elseif($penugasan->status_tugas === 'Menunggu Konfirmasi')
        <div class="bg-purple-50 rounded-2xl border border-purple-200 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="clock" class="w-4 h-4 text-purple-500"></i>
                <p class="text-purple-700 font-semibold" style="font-size:0.85rem">Menunggu Konfirmasi Warga</p>
            </div>
            <p class="text-purple-600" style="font-size:0.8rem">Bukti telah dikirim. Menunggu warga mengkonfirmasi dan memberikan rating.</p>
        </div>
        @endif

    </div>

    {{-- ═══ RIGHT ═══ --}}
    <div class="space-y-5">

        {{-- Info Tugas --}}
        <div class="bg-white rounded-2xl border border-sky-100 shadow-sm p-5">
            <p class="text-slate-500 mb-4" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Info Tugas</p>
            <div class="space-y-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0">
                        <i data-lucide="hash" class="w-3.5 h-3.5 text-slate-400"></i>
                    </div>
                    <div>
                        <p class="text-slate-400" style="font-size:0.7rem">Laporan</p>
                        <p class="font-bold text-sky-700" style="font-size:0.88rem">#{{ $laporan->id }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                    </div>
                    <div>
                        <p class="text-slate-400" style="font-size:0.7rem">Tanggal Penugasan</p>
                        <p class="font-semibold text-slate-700" style="font-size:0.85rem">
                            {{ \Carbon\Carbon::parse($penugasan->tanggal_penugasan)->format('d M Y') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0 text-base">
                        {{ $laporan->kategoriLaporan->icon ?? '📋' }}
                    </div>
                    <div>
                        <p class="text-slate-400" style="font-size:0.7rem">Kategori</p>
                        <p class="font-semibold text-slate-700" style="font-size:0.85rem">{{ $laporan->kategoriLaporan->nama_kategori ?? '-' }}</p>
                    </div>
                </div>
                @if($penugasan->catatan_admin)
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-slate-400 mb-1" style="font-size:0.7rem">Catatan dari Admin</p>
                    <p class="text-slate-700 text-sm leading-relaxed">{{ $penugasan->catatan_admin }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Update Status --}}
        @if(!$udahSelesai && count($nextStatuses) > 0)
        <div class="bg-white rounded-2xl border border-sky-100 shadow-sm p-5">
            <p class="text-slate-500 mb-4" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Update Status</p>
            <form action="{{ route('petugas.tugas.status', $penugasan->id) }}" method="POST" class="space-y-2">
                @csrf
                @foreach($nextStatuses as $ns)
                    <button type="submit" name="status_tugas" value="{{ $ns }}"
                            onclick="return confirm('Ubah status ke \"{{ $ns }}\"?')"
                            class="w-full flex items-center gap-2 px-4 py-2.5 border-2 border-sky-200 bg-sky-50 hover:bg-sky-100 text-sky-700 font-medium rounded-xl transition-colors text-sm">
                        <i data-lucide="arrow-right-circle" class="w-4 h-4 shrink-0"></i>
                        {{ $ns }}
                    </button>
                @endforeach
            </form>
            @error('status_tugas')
                <p class="text-red-500 mt-2" style="font-size:0.78rem">{{ $message }}</p>
            @enderror
        </div>
        @endif

    </div>
</div>

<script>
    lucide.createIcons();

    function buktiUpload() {
        return {
            preview: null,
            handleFile(event) {
                const file = event.target.files[0];
                if (!file) return;
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    Swal.fire({ icon:'error', title:'Format Tidak Valid', text:'Gunakan JPG, JPEG, atau PNG.', confirmButtonColor:'#0284c7' });
                    event.target.value = ''; return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({ icon:'error', title:'File Terlalu Besar', text:'Ukuran foto maksimal 5MB.', confirmButtonColor:'#0284c7' });
                    event.target.value = ''; return;
                }
                const reader = new FileReader();
                reader.onload = e => this.preview = e.target.result;
                reader.readAsDataURL(file);
            }
        };
    }

    @if(session('success'))
        Swal.fire({ icon:'success', title:'Berhasil!', text:'{{ session("success") }}',
            timer:3000, showConfirmButton:false, toast:true, position:'top-end' });
    @endif
    @if(session('error'))
        Swal.fire({ icon:'error', title:'Gagal!', text:'{{ session("error") }}', confirmButtonColor:'#0284c7' });
    @endif
</script>
@endsection
