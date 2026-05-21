@extends('layouts.admin')
@section('title', 'Dashboard Admin')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div>
    <h1 class="text-sky-900 mb-1" style="font-size: 1.5rem; font-weight: 700;">Dashboard Admin</h1>
    <p class="text-slate-500 mb-6" style="font-size: 0.85rem;">Ringkasan sistem pelaporan dan distribusi air bersih</p>

    @if($stats['total'] == 0)
    {{-- Empty State --}}
    <div class="flex flex-col items-center justify-center py-16 px-4 bg-white rounded-2xl border border-sky-100 shadow-sm text-center">
        <div class="w-16 h-16 bg-sky-50 rounded-2xl flex items-center justify-center text-sky-500 mb-4 animate-bounce">
            <i data-lucide="bar-chart-3" class="w-8 h-8"></i>
        </div>
        <h2 class="text-sky-900 text-xl font-bold mb-2">Data statistik tidak tersedia</h2>
        <p class="text-slate-500 max-w-md mb-6" style="font-size: 0.9rem;">Belum ada laporan yang masuk ke dalam sistem untuk dianalisis.</p>
        <button onclick="window.location.reload();" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-semibold shadow-sm transition-all text-sm">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            Muat Ulang Halaman
        </button>
    </div>
    @else


    {{-- Highlight Cards --}}
    <div class="grid lg:grid-cols-3 gap-4 mb-6">
        {{-- Omset Pembayaran Card --}}
        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow-md flex flex-col justify-between hover:scale-[1.01] transition-transform duration-200">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                    <span style="font-size: 0.85rem; font-weight: 500;">Total Pendapatan (Lunas)</span>
                </div>
                <p style="font-size: 2rem; font-weight: 700;">Rp {{ number_format($stats['pendapatan'], 0, ',', '.') }}</p>
            </div>
            <p class="text-emerald-100 mt-3" style="font-size: 0.8rem;">{{ $stats['belum_bayar'] }} pembayaran belum lunas</p>
        </div>

        {{-- Rasio Penyelesaian Card --}}
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl p-5 text-white shadow-md flex flex-col justify-between hover:scale-[1.01] transition-transform duration-200">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="percent" class="w-5 h-5"></i>
                    <span style="font-size: 0.85rem; font-weight: 500;">Rasio Penyelesaian Laporan</span>
                </div>
                <p style="font-size: 2rem; font-weight: 700;">{{ $stats['rasio_penyelesaian'] }}%</p>
            </div>
            <div class="mt-3">
                <div class="w-full bg-indigo-700/50 rounded-full h-2 overflow-hidden">
                    <div class="bg-white h-full rounded-full transition-all duration-500" style="width: {{ $stats['rasio_penyelesaian'] }}%"></div>
                </div>
                <p class="text-indigo-100 mt-1.5" style="font-size: 0.75rem;">{{ $stats['selesai'] }} dari {{ $stats['total'] }} laporan selesai</p>
            </div>
        </div>

        {{-- Laporan Perlu Turun Lapangan Card --}}
        <div class="bg-gradient-to-r from-sky-500 to-sky-600 rounded-xl p-5 text-white shadow-md flex flex-col justify-between hover:scale-[1.01] transition-transform duration-200">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                    <span style="font-size: 0.85rem; font-weight: 500;">Laporan Perlu Tindakan</span>
                </div>
                <p style="font-size: 2rem; font-weight: 700;">{{ $stats['perlu_lapangan'] }}</p>
            </div>
            <p class="text-sky-100 mt-3" style="font-size: 0.8rem;">Visualisasi data sebaran titik di menu Peta</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-xl p-6 border border-sky-100 shadow-sm">
            <h3 class="text-sky-800 mb-4" style="font-size: 1rem; font-weight: 600;">Tren Laporan Masuk (6 Bulan Terakhir)</h3>
            <div class="relative w-full h-[280px]">
                <canvas id="laporanChart"></canvas>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 border border-sky-100 shadow-sm flex flex-col">
            <h3 class="text-sky-800 mb-4" style="font-size: 1rem; font-weight: 600;">Distribusi Status</h3>
            <div class="flex-1 flex items-center justify-center relative w-full" style="min-height: 250px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent Reports & Top 3 Wilayah --}}
    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-xl p-6 border border-sky-100 shadow-sm">
            <h3 class="text-sky-800 mb-4" style="font-size: 1rem; font-weight: 600;">Laporan Terbaru</h3>
            <div class="overflow-x-auto">
                <table class="w-full" style="font-size: 0.83rem;">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-sky-100">
                            <th class="pb-3 pr-4 font-semibold text-sky-900">ID</th>
                            <th class="pb-3 pr-4 font-semibold text-sky-900">Alamat</th>
                            <th class="pb-3 pr-4 font-semibold text-sky-900">Kategori</th>
                            <th class="pb-3 pr-4 font-semibold text-sky-900">Tanggal</th>
                            <th class="pb-3 font-semibold text-sky-900 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentReports as $l)
                            <tr class="border-b border-sky-50 hover:bg-sky-50/30 transition-colors">
                                <td class="py-3 pr-4 text-sky-600 font-bold">#{{ $l->id }}</td>
                                <td class="py-3 pr-4 text-slate-700">
                                    <div class="flex items-center gap-1.5 min-w-[150px]">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-sky-400 shrink-0"></i>
                                        <span class="truncate max-w-[200px]">{{ $l->alamat }}</span>
                                    </div>
                                </td>
                                <td class="py-3 pr-4 text-slate-600">
                                    <span class="flex items-center gap-2">
                                        <span>{{ $l->kategoriLaporan->icon ?? '📋' }}</span>
                                        <span>{{ $l->kategoriLaporan->nama_kategori ?? 'Umum' }}</span>
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-slate-500">{{ \Carbon\Carbon::parse($l->tanggal_lapor)->format('d M Y') }}</td>
                                <td class="py-3 text-center">
                                    @php
                                        $statusStyles = [
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            'diterima' => 'bg-blue-100 text-blue-700',
                                            'dikerjakan' => 'bg-violet-100 text-violet-700',
                                            'menunggu_konfirmasi' => 'bg-cyan-100 text-cyan-700',
                                            'selesai' => 'bg-emerald-100 text-emerald-700',
                                            'ditolak' => 'bg-red-100 text-red-700',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Menunggu Validasi',
                                            'diterima' => 'Divalidasi',
                                            'dikerjakan' => 'Sedang Dikerjakan',
                                            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                                            'selesai' => 'Selesai',
                                            'ditolak' => 'Ditolak',
                                        ];
                                    @endphp
                                    <span class="{{ $statusStyles[$l->status] ?? 'bg-slate-100 text-slate-600' }} px-2.5 py-1 rounded-full inline-block font-semibold whitespace-nowrap" style="font-size: 0.72rem;">
                                        {{ $statusLabels[$l->status] ?? $l->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('admin.laporan.index') }}" class="text-sky-600 hover:text-sky-800 font-semibold" style="font-size: 0.85rem;">Lihat Semua Laporan &rarr;</a>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 border border-sky-100 shadow-sm flex flex-col">
            <h3 class="text-sky-800 mb-4" style="font-size: 1rem; font-weight: 600;">Persebaran Wilayah</h3>
            <div class="flex-1 space-y-5 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                @foreach($persebaranWilayah as $tw)
                    @php 
                        $maxTotal = $persebaranWilayah->first()->total ?? 1;
                        $percentage = $maxTotal > 0 ? round(($tw->total / $maxTotal) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-slate-700 font-medium" style="font-size: 0.85rem;">
                                {{ $tw->wilayah->nama_wilayah ?? 'Tidak Diketahui' }}
                            </span>
                            <span class="text-slate-500 font-semibold" style="font-size: 0.8rem;">
                                {{ $tw->total }} Laporan
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-gradient-to-r from-sky-400 to-sky-600 h-full rounded-full transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
                @if($persebaranWilayah->isEmpty())
                    <p class="text-slate-500 text-sm text-center py-4">Belum ada data laporan masuk.</p>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($stats['total'] > 0)
        // Setup Chart Context and Gradients
        const ctxLaporan = document.getElementById('laporanChart').getContext('2d');
        
        const gradientTotal = ctxLaporan.createLinearGradient(0, 0, 0, 300);
        gradientTotal.addColorStop(0, 'rgba(14, 165, 233, 0.4)');   // sky-500 semi-transparent
        gradientTotal.addColorStop(1, 'rgba(14, 165, 233, 0.0)');   // sky-500 transparent

        // Laporan Chart (6 Bulan Terakhir)
        new Chart(ctxLaporan, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($monthlyData, 'label')) !!},
                datasets: [
                    { 
                        label: 'Total Laporan', 
                        data: {!! json_encode(array_column($monthlyData, 'total')) !!}, 
                        backgroundColor: gradientTotal,
                        borderColor: '#0284c7', // sky-600
                        borderWidth: 2,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0ea5e9',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4 // Smooth curves
                    }
                ]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: { 
                    x: {
                        grid: { display: false }
                    },
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: { borderDash: [4, 4] }
                    } 
                },
                plugins: {
                    tooltip: {
                        enabled: true,
                        intersect: false,
                        mode: 'index',
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#0f172a',
                        bodyColor: '#334155',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        boxPadding: 4,
                        usePointStyle: true,
                    },
                    legend: {
                        display: false // Hide legend since there's only one dataset
                    }
                }
            }
        });
        // Status Chart
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_column($statusDistribusi, 'name')) !!},
                datasets: [{
                    data: {!! json_encode(array_column($statusDistribusi, 'value')) !!},
                    backgroundColor: {!! json_encode(array_column($statusDistribusi, 'color')) !!},
                    borderWidth: 0
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } }
            }
        });

        @endif
        
        lucide.createIcons();
    });
</script>
@endsection
