@extends('layouts.admin')
@section('title', 'Dashboard Admin')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div>
    <h1 class="text-sky-900 mb-1" style="font-size: 1.5rem; font-weight: 700;">Dashboard Admin</h1>
    <p class="text-slate-500 mb-6" style="font-size: 0.85rem;">Ringkasan sistem pelaporan dan distribusi air bersih</p>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @php
            $cards = [
                ['label' => 'Total Laporan', 'value' => $stats['total'], 'icon' => 'file-text', 'color' => 'bg-sky-500', 'bgLight' => 'bg-sky-50', 'textColor' => 'text-sky-700'],
                ['label' => 'Menunggu Validasi', 'value' => $stats['pending'], 'icon' => 'clock', 'color' => 'bg-amber-500', 'bgLight' => 'bg-amber-50', 'textColor' => 'text-amber-700'],
                ['label' => 'Sedang Diproses', 'value' => $stats['diproses'], 'icon' => 'trending-up', 'color' => 'bg-violet-500', 'bgLight' => 'bg-violet-50', 'textColor' => 'text-violet-700'],
                ['label' => 'Selesai', 'value' => $stats['selesai'], 'icon' => 'check-circle', 'color' => 'bg-emerald-500', 'bgLight' => 'bg-emerald-50', 'textColor' => 'text-emerald-700'],
                ['label' => 'Menunggu Konfirmasi', 'value' => $stats['konfirmasi'], 'icon' => 'alert-triangle', 'color' => 'bg-cyan-500', 'bgLight' => 'bg-cyan-50', 'textColor' => 'text-cyan-700'],
                ['label' => 'Ditolak', 'value' => $stats['ditolak'], 'icon' => 'x-circle', 'color' => 'bg-red-500', 'bgLight' => 'bg-red-50', 'textColor' => 'text-red-700'],
            ];
        @endphp

        @foreach($cards as $c)
            <div class="{{ $c['bgLight'] }} rounded-xl p-4 border border-sky-100 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="{{ $c['color'] }} w-9 h-9 rounded-lg flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="{{ $c['icon'] }}" class="w-4 h-4 text-white"></i>
                    </div>
                    <p class="text-slate-500" style="font-size: 0.78rem;">{{ $c['label'] }}</p>
                </div>
                <p class="{{ $c['textColor'] }}" style="font-size: 1.75rem; font-weight: 700;">{{ $c['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Revenue & Field Work Cards --}}
    <div class="grid lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow-md">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
                <span style="font-size: 0.85rem;">Total Pendapatan (Lunas)</span>
            </div>
            <p style="font-size: 2rem; font-weight: 700;">Rp {{ number_format($stats['pendapatan'], 0, ',', '.') }}</p>
            <p class="text-emerald-100 mt-1" style="font-size: 0.8rem;">{{ $stats['belum_bayar'] }} pembayaran belum lunas</p>
        </div>
        <div class="bg-gradient-to-r from-sky-500 to-sky-600 rounded-xl p-5 text-white shadow-md">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="map-pin" class="w-5 h-5"></i>
                <span style="font-size: 0.85rem;">Laporan Perlu Turun Lapangan</span>
            </div>
            <p style="font-size: 2rem; font-weight: 700;">{{ $stats['perlu_lapangan'] }}</p>
            <p class="text-sky-100 mt-1" style="font-size: 0.8rem;">Visualisasi data sebaran titik di menu Peta</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl p-6 border border-sky-100 shadow-sm">
            <h3 class="text-sky-800 mb-4" style="font-size: 1rem; font-weight: 600;">Trend Laporan (3 Bulan Terakhir)</h3>
            <canvas id="laporanChart" height="200"></canvas>
        </div>

        <div class="bg-white rounded-xl p-6 border border-sky-100 shadow-sm">
            <h3 class="text-sky-800 mb-4" style="font-size: 1rem; font-weight: 600;">Distribusi Status</h3>
            <div class="max-w-[250px] mx-auto">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent Reports --}}
    <div class="bg-white rounded-xl p-6 border border-sky-100 shadow-sm mb-6">
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
                                        'selesai' => 'bg-emerald-100 text-emerald-700',
                                        'ditolak' => 'bg-red-100 text-red-700',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Menunggu Validasi',
                                        'diterima' => 'Divalidasi',
                                        'dikerjakan' => 'Sedang Dikerjakan',
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Laporan Chart
        new Chart(document.getElementById('laporanChart'), {
            type: 'bar',
            data: {
                labels: ['Feb', 'Mar', 'Apr'],
                datasets: [
                    { label: 'Total', data: [8, 12, {{ $stats['total'] }}], backgroundColor: '#0284c7', borderRadius: 4 },
                    { label: 'Selesai', data: [6, 3, {{ $stats['selesai'] }}], backgroundColor: '#10b981', borderRadius: 4 }
                ]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
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
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } }
            }
        });
        
        lucide.createIcons();
    });
</script>
@endsection
