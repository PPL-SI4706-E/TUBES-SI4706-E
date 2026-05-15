@extends('layouts.petugas')

@section('title', 'Daftar Tugas')

@section('content')
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >

    @php
        $taskIcons = [
            'Air Keruh / Berbau' => 'droplets',
            'Pipa Bocor' => 'wrench',
            'Pipa Tersumbat' => 'ban',
            'Sambungan Baru' => 'house',
        ];

        $statusActionLabels = [
            'Menuju Lokasi' => 'Ubah ke Menuju Lokasi',
            'Sedang Dikerjakan' => 'Ubah ke Sedang Dikerjakan',
        ];
    @endphp

    <section class="space-y-8">
        <div>
            <h1 class="text-4xl font-bold tracking-tight text-[#0d5a88]">Daftar Tugas</h1>
            <p class="mt-3 max-w-3xl text-lg text-slate-500">
                Laporan yang ditugaskan kepada Anda beserta lokasi rumah pelanggan
            </p>
        </div>

        <div class="grid gap-5 xl:grid-cols-2">
            <div class="rounded-[1.75rem] border border-sky-100 bg-white/70 p-6 shadow-sm shadow-sky-100/70">
                <p class="text-xl text-slate-500">Tugas Aktif</p>
                <p class="mt-4 text-6xl font-bold text-[#0b74b6]">{{ $summary['active'] }}</p>
            </div>
            <div class="rounded-[1.75rem] border border-emerald-100 bg-emerald-50/80 p-6 shadow-sm shadow-emerald-100/60">
                <p class="text-xl text-slate-500">Telah Selesai</p>
                <p class="mt-4 text-6xl font-bold text-emerald-700">{{ $summary['completed'] }}</p>
            </div>
        </div>

        <div class="space-y-4">
            <h2 class="text-2xl font-bold text-[#0d5a88]">Tugas Aktif</h2>

            @forelse ($activeTasks as $task)
                @php
                    $detailId = 'task-detail-' . $task['penugasan_id'];
                    $mapId = 'task-map-' . $task['penugasan_id'];
                    $shouldOpenModal = (string) $openUploadModalId === (string) $task['penugasan_id'];
                @endphp

                <article
                    x-data="taskCard({
                        detailId: '{{ $detailId }}',
                        mapId: '{{ $mapId }}',
                        lat: {{ $task['latitude'] }},
                        lng: {{ $task['longitude'] }},
                        autoOpenModal: {{ $shouldOpenModal ? 'true' : 'false' }}
                    })"
                    class="rounded-[1.8rem] border border-sky-100 bg-white p-6 shadow-[0_14px_30px_-22px_rgba(14,116,184,0.55)]"
                >
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="text-3xl font-bold text-[#0b74f0]">{{ $task['number'] }}</span>
                                <i data-lucide="{{ $taskIcons[$task['category']] ?? 'clipboard-list' }}" class="h-6 w-6 text-sky-400"></i>
                                <span class="text-3xl font-semibold text-[#0d4771]">{{ $task['category'] }}</span>
                                <span class="rounded-full px-4 py-2 text-sm font-semibold {{ $task['status_class'] }}">
                                    {{ $task['status'] }}
                                </span>
                            </div>
                            <div class="flex items-start gap-3 text-slate-500">
                                <i data-lucide="map-pin" class="mt-0.5 h-5 w-5 shrink-0 text-sky-500"></i>
                                <p class="text-lg">{{ $task['address'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-start">
                            <div class="text-lg font-semibold text-slate-400">{{ $task['date'] }}</div>
                            <button
                                type="button"
                                @click="toggleDetail"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-sky-50 text-sky-600 transition hover:bg-sky-100"
                            >
                                <i data-lucide="chevron-down" class="h-5 w-5 transition-transform" :class="detailOpen ? 'rotate-180' : ''"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mt-7">
                        <div class="grid grid-cols-5 gap-2">
                            @foreach ($progressSteps as $index => $step)
                                <div class="h-1.5 rounded-full {{ $index <= $task['progress_index'] ? 'bg-sky-500' : 'bg-sky-100' }}"></div>
                            @endforeach
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-slate-400 md:grid-cols-5">
                            @foreach ($progressSteps as $index => $step)
                                <p class="{{ $index === $task['progress_index'] ? 'font-semibold text-slate-600' : '' }}">
                                    {{ $step }}
                                </p>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="detailOpen" class="mt-7 border-t border-sky-100 pt-6" style="display: none;">
                        <div class="grid gap-4 xl:grid-cols-[1.1fr,0.9fr]">
                            <div class="space-y-4">
                                <div class="rounded-[1.5rem] bg-sky-50/80 p-4">
                                    <p class="flex items-center gap-2 text-lg font-semibold text-[#0d5a88]">
                                        <i data-lucide="user-round" class="h-5 w-5"></i>
                                        Info Pelanggan
                                    </p>
                                    <div class="mt-3 space-y-2 text-slate-600">
                                        <p><span class="font-semibold text-slate-700">Nama:</span> {{ $task['customer_name'] }}</p>
                                        <p><span class="font-semibold text-slate-700">Nomor HP:</span> {{ $task['customer_phone'] }}</p>
                                    </div>
                                </div>

                                <div class="rounded-[1.5rem] bg-sky-50/80 p-4">
                                    <p class="flex items-center gap-2 text-lg font-semibold text-[#0d5a88]">
                                        <i data-lucide="house" class="h-5 w-5"></i>
                                        Alamat Rumah Lengkap
                                    </p>
                                    <div class="mt-3 space-y-1 text-slate-600">
                                        @foreach ($task['full_address'] as $addressLine)
                                            @if ($addressLine !== '')
                                                <p>{{ $addressLine }}</p>
                                            @endif
                                        @endforeach
                                        <p class="flex items-center gap-2 text-sm text-slate-400">
                                            <i data-lucide="navigation" class="h-4 w-4"></i>
                                            Koordinat: {{ $task['coordinates'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[1.5rem] border border-sky-100 bg-white p-4 shadow-sm">
                                <p class="text-lg font-semibold text-[#0d5a88]">Peta Lokasi</p>
                                <div id="{{ $mapId }}" class="mt-4 h-64 rounded-[1.25rem] border border-sky-100"></div>
                                <a
                                    href="{{ $task['google_maps_url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#2563eb] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]"
                                >
                                    <i data-lucide="navigation" class="h-4 w-4"></i>
                                    Buka di Google Maps
                                </a>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4">
                            <div class="rounded-[1.5rem] bg-sky-50/80 p-4">
                                <p class="text-lg font-semibold text-[#0d5a88]">Deskripsi Masalah</p>
                                <p class="mt-3 text-slate-600">{{ $task['description'] }}</p>
                            </div>

                            <div class="rounded-[1.5rem] bg-sky-50/80 p-4">
                                <p class="text-lg font-semibold text-[#0d5a88]">Catatan Admin</p>
                                <p class="mt-3 text-slate-600">{{ $task['admin_note'] }}</p>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                            @if ($task['next_status'])
                                <form
                                    method="POST"
                                    action="{{ route('petugas.tugas.update-status', $task['penugasan_id']) }}"
                                    class="flex-1"
                                >
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $task['next_status'] }}">
                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-700"
                                    >
                                        <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                                        {{ $statusActionLabels[$task['next_status']] ?? ('Ubah ke ' . $task['next_status']) }}
                                    </button>
                                </form>
                            @endif

                            @if ($task['can_upload'])
                                <button
                                    type="button"
                                    @click="openModal"
                                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600"
                                >
                                    <i data-lucide="check-circle-2" class="h-4 w-4"></i>
                                    Selesaikan Tugas
                                </button>
                            @endif

                            @if ($task['is_waiting_confirmation'])
                                <div class="inline-flex flex-1 items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-700">
                                    Menunggu Konfirmasi Warga
                                </div>
                            @endif
                        </div>

                        <div
                            x-show="modalOpen"
                            x-transition.opacity
                            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/40 px-4 py-6"
                        >
                            <div
                                @click.outside="closeModal"
                                class="w-full max-w-xl rounded-[1.8rem] bg-white p-6 shadow-2xl"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-2xl font-bold text-[#0d5a88]">Upload Bukti Penyelesaian</h3>
                                        <p class="mt-1 text-sm text-slate-500">Unggah bukti perbaikan untuk {{ $task['number'] }}.</p>
                                    </div>
                                    <button type="button" @click="closeModal" class="rounded-full bg-slate-100 p-2 text-slate-500 transition hover:bg-slate-200">
                                        <i data-lucide="x" class="h-5 w-5"></i>
                                    </button>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('petugas.tugas.upload-bukti', $task['penugasan_id']) }}"
                                    enctype="multipart/form-data"
                                    class="mt-6 space-y-4"
                                >
                                    @csrf
                                    <input type="hidden" name="penugasan_id" value="{{ $task['penugasan_id'] }}">

                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-slate-700">Foto Bukti Perbaikan</label>
                                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 transition hover:border-sky-200 hover:bg-sky-50/60">
                                            <span class="inline-flex shrink-0 items-center rounded-xl bg-sky-100 px-4 py-2 font-semibold text-sky-700">
                                                Choose File
                                            </span>
                                            <span x-text="selectedProofName || 'No file chosen'" class="min-w-0 truncate text-slate-500"></span>
                                            <input
                                                type="file"
                                                name="foto_bukti"
                                                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                                class="hidden"
                                                @change="selectedProofName = $event.target.files[0] ? $event.target.files[0].name : ''"
                                            >
                                        </label>
                                        @if ((string) $openUploadModalId === (string) $task['penugasan_id'])
                                            @error('foto_bukti')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        @endif
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-slate-700">Catatan Perbaikan</label>
                                        <textarea
                                            name="catatan_perbaikan"
                                            rows="4"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                                            placeholder="Tulis ringkasan perbaikan yang sudah dilakukan..."
                                        >{{ (string) $openUploadModalId === (string) $task['penugasan_id'] ? old('catatan_perbaikan') : '' }}</textarea>
                                        @if ((string) $openUploadModalId === (string) $task['penugasan_id'])
                                            @error('catatan_perbaikan')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        @endif
                                    </div>

                                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                        <button
                                            type="button"
                                            @click="closeModal"
                                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                                        >
                                            Batal
                                        </button>
                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#2563eb] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]"
                                        >
                                            <i data-lucide="upload" class="h-4 w-4"></i>
                                            Kirim Bukti
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[1.8rem] border border-dashed border-sky-200 bg-white/70 px-6 py-10 text-center text-slate-500 shadow-sm">
                    Belum ada tugas aktif.
                </div>
            @endforelse
        </div>

        <div class="space-y-4">
            <h2 class="text-2xl font-bold text-[#0d5a88]">Riwayat Selesai</h2>

            <div class="space-y-4">
                @forelse ($completedTasks as $task)
                    <article class="rounded-[1.8rem] border border-emerald-200 bg-white px-5 py-5 shadow-[0_14px_30px_-24px_rgba(16,185,129,0.55)]">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-500 ring-1 ring-emerald-200">
                                    <i data-lucide="check" class="h-6 w-6"></i>
                                </div>

                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-2xl font-bold text-slate-700">{{ $task['number'] }}</span>
                                        <i data-lucide="{{ $taskIcons[$task['category']] ?? 'clipboard-check' }}" class="h-5 w-5 text-violet-300"></i>
                                        <span class="text-2xl font-semibold text-slate-700">{{ $task['category'] }}</span>
                                    </div>
                                    <p class="text-lg text-slate-400">{{ $task['address'] }}</p>
                                    <p class="text-lg text-slate-600">Catatan: {{ $task['repair_note'] ?? 'Belum ada catatan penyelesaian.' }}</p>
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-col items-start gap-2 text-left lg:items-end lg:text-right">
                                @if ($task['rating'])
                                    <div class="flex items-center gap-1 text-amber-400">
                                        @for ($star = 1; $star <= 5; $star++)
                                            <i data-lucide="star" class="h-4 w-4 {{ $star <= $task['rating'] ? 'fill-current' : '' }}"></i>
                                        @endfor
                                    </div>
                                @endif
                                <p class="text-lg text-slate-400">{{ $task['completed_date'] ?? $task['date'] }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.8rem] border border-dashed border-emerald-200 bg-white/70 px-6 py-10 text-center text-slate-500 shadow-sm">
                        Belum ada riwayat tugas selesai.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>
    <script>
        window.taskCard = function taskCard(config) {
            return {
                detailOpen: false,
                modalOpen: false,
                selectedProofName: '',
                mapInstance: null,
                mapReady: false,
                detailId: config.detailId,
                mapId: config.mapId,
                lat: config.lat,
                lng: config.lng,
                autoOpenModal: config.autoOpenModal,
                init() {
                    if (this.autoOpenModal) {
                        this.detailOpen = true;
                        this.modalOpen = true;
                        this.$nextTick(() => {
                            this.ensureMap();
                        });
                    }
                },
                toggleDetail() {
                    this.detailOpen = !this.detailOpen;
                    if (this.detailOpen) {
                        this.$nextTick(() => {
                            this.ensureMap();
                        });
                    }
                },
                openModal() {
                    this.detailOpen = true;
                    this.modalOpen = true;
                    this.$nextTick(() => {
                        this.ensureMap();
                    });
                },
                closeModal() {
                    this.modalOpen = false;
                    this.selectedProofName = '';
                },
                ensureMap() {
                    if (!window.L) {
                        return;
                    }

                    const mapElement = document.getElementById(this.mapId);

                    if (!mapElement) {
                        return;
                    }

                    if (!this.mapReady) {
                        this.mapInstance = L.map(this.mapId).setView([this.lat, this.lng], 16);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.mapInstance);

                        L.marker([this.lat, this.lng]).addTo(this.mapInstance);
                        this.mapReady = true;
                    }

                    setTimeout(() => {
                        this.mapInstance.invalidateSize();
                    }, 150);
                }
            };
        };
    </script>
@endsection
