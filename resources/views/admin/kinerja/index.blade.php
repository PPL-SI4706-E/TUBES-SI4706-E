@extends('layouts.admin')

@section('title', 'Kinerja Petugas')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Kinerja Petugas Lapangan</h1>
        <p class="text-slate-500 mt-1 text-sm">Laporan produktivitas dan kualitas layanan setiap petugas</p>
    </div>
</div>

@if($petugasList->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex flex-col items-center justify-center p-12 text-center">
            <div class="w-24 h-24 bg-sky-50 rounded-full flex items-center justify-center mb-4 text-sky-500">
                <i data-lucide="bar-chart-2" class="w-10 h-10"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-1">Belum ada data kinerja petugas</h3>
            <p class="text-slate-500 text-sm">Saat ini belum ada petugas lapangan yang terdaftar di dalam sistem.</p>
        </div>
    </div>
@else
    <!-- Dashboard Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Charts Section -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col" x-data="{ activeTab: 'pekerjaan' }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-6 h-6 text-sky-500"></i>
                    <h2 class="text-lg font-bold text-slate-800">Analitik Kinerja Petugas</h2>
                </div>
                
                <!-- Tab Navigation -->
                <div class="flex items-center p-1 bg-slate-100 rounded-lg">
                    <button @click="activeTab = 'pekerjaan'" 
                            class="px-4 py-1.5 text-sm font-semibold rounded-md transition-all"
                            :class="activeTab === 'pekerjaan' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        Jumlah Pekerjaan
                    </button>
                    <button @click="activeTab = 'rating'" 
                            class="px-4 py-1.5 text-sm font-semibold rounded-md transition-all"
                            :class="activeTab === 'rating' ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        Rata-Rata Rating
                    </button>
                </div>
            </div>
            
            <div class="relative w-full min-h-[300px]">
                <!-- Chart Jumlah Pekerjaan -->
                <div :class="activeTab === 'pekerjaan' ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'" class="absolute inset-0 transition-opacity duration-300">
                    <canvas id="kinerjaChart"></canvas>
                </div>

                <!-- Chart Rata-Rata Rating -->
                <div :class="activeTab === 'rating' ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'" class="absolute inset-0 transition-opacity duration-300">
                    <canvas id="ratingChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Performers Section -->
        <div class="flex flex-col gap-4 max-h-[420px] overflow-y-auto pr-2 custom-scrollbar">
            @foreach($topPetugas as $index => $petugas)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-sky-200 transition-colors">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center font-bold text-lg shadow-sm border border-sky-200">
                                {{ substr($petugas->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-800">{{ $petugas->name }}</h3>
                                <p class="text-xs text-slate-500">Petugas Lapangan</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <i data-lucide="award" class="w-6 h-6 {{ $index === 0 ? 'text-amber-500' : ($index === 1 ? 'text-slate-400' : 'text-amber-700') }} mb-1"></i>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-emerald-50 rounded-lg p-2.5 flex flex-col items-center justify-center border border-emerald-100 shadow-sm">
                            <span class="text-xl font-bold text-emerald-700">{{ $petugas->tugas_selesai_count }}</span>
                            <span class="text-[10px] uppercase font-semibold text-emerald-600 mt-1">Selesai</span>
                        </div>
                        <div class="bg-sky-50 rounded-lg p-2.5 flex flex-col items-center justify-center border border-sky-100 shadow-sm">
                            <span class="text-xl font-bold text-sky-700">{{ $petugas->tugas_aktif_count }}</span>
                            <span class="text-[10px] uppercase font-semibold text-sky-600 mt-1">Diproses</span>
                        </div>
                        <div class="bg-amber-50 rounded-lg p-2.5 flex flex-col items-center justify-center border border-amber-100 shadow-sm">
                            <div class="flex items-center gap-1">
                                <span class="text-xl font-bold text-amber-700">{{ number_format($petugas->rata_rata_rating, 1) }}</span>
                            </div>
                            <span class="text-[10px] uppercase font-semibold text-amber-600 mt-1 flex items-center gap-1">Avg Rating</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-semibold tracking-wider">
                        <th class="px-6 py-4">Nama Petugas</th>
                        <th class="px-6 py-4">
                            @php
                                $nextDirTugas = ($sortBy === 'tugas_selesai_count' && $sortDir === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('admin.kinerja.index', ['sort_by' => 'tugas_selesai_count', 'sort_dir' => $nextDirTugas]) }}" class="flex items-center gap-1 hover:text-sky-600 transition-colors">
                                Jumlah Tugas Selesai
                                @if($sortBy === 'tugas_selesai_count')
                                    <i data-lucide="{{ $sortDir === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3.5 h-3.5"></i>
                                @else
                                    <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4">
                            @php
                                $nextDirRating = ($sortBy === 'rata_rata_rating' && $sortDir === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('admin.kinerja.index', ['sort_by' => 'rata_rata_rating', 'sort_dir' => $nextDirRating]) }}" class="flex items-center gap-1 hover:text-sky-600 transition-colors">
                                Rata-rata Rating
                                @if($sortBy === 'rata_rata_rating')
                                    <i data-lucide="{{ $sortDir === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3.5 h-3.5"></i>
                                @else
                                    <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4">Aktivitas Terkini</th>
                        <th class="px-6 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($petugasList as $petugas)
                        <tr class="hover:bg-slate-50/50 transition-colors border-b border-slate-100">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center font-bold text-sm border border-sky-200">
                                        {{ substr($petugas->name, 0, 1) }}
                                    </div>
                                    <div class="font-medium text-slate-800">{{ $petugas->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-medium">
                                {{ $petugas->tugas_selesai_count }} Tugas
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <span class="font-semibold text-slate-700">{{ number_format($petugas->rata_rata_rating, 1) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $lastTask = $petugas->penugasanSebagaiPetugas->first();
                                    $daysSinceLastTask = $lastTask ? \Carbon\Carbon::parse($lastTask->tanggal_penugasan)->diffInDays(now()) : null;
                                    
                                    if ($petugas->tugas_aktif_count > 0) {
                                        $statusBadge = ['text' => 'Aktif Bertugas', 'bg' => 'bg-emerald-50', 'textCol' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'];
                                    } elseif ($lastTask && $daysSinceLastTask <= 3) {
                                        $statusBadge = ['text' => 'Standby', 'bg' => 'bg-amber-50', 'textCol' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'];
                                    } else {
                                        $statusBadge = ['text' => 'Lama Tidak Aktif', 'bg' => 'bg-rose-50', 'textCol' => 'text-rose-700', 'border' => 'border-rose-200', 'dot' => 'bg-rose-500'];
                                    }
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusBadge['bg'] }} {{ $statusBadge['textCol'] }} border {{ $statusBadge['border'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusBadge['dot'] }}"></span> {{ $statusBadge['text'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right" x-data>
                                <button @click="$dispatch('open-modal', {{ $petugas->id }})" class="text-sky-600 hover:text-white hover:bg-sky-500 text-xs font-bold px-4 py-2 bg-sky-50 border border-sky-100 rounded-lg transition-all shadow-sm">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @foreach($petugasList as $petugas)
        <div x-data="{ open: false, id: {{ $petugas->id }} }" 
             @open-modal.window="if($event.detail == id) open = true" 
             x-show="open" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50" 
             x-cloak>
            <div @click.away="open = false" class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[80vh] overflow-y-auto">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Riwayat Penugasan: {{ $petugas->name }}</h3>
                    <button @click="open = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x"></i></button>
                </div>
                <div class="p-6">
                    @if($petugas->penugasanSebagaiPetugas->isEmpty())
                        <p class="text-sm text-slate-500">Belum ada riwayat penugasan.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($petugas->penugasanSebagaiPetugas as $tugas)
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-semibold">{{ $tugas->laporan->judul ?? 'Laporan #' . $tugas->laporan_id }}</span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $tugas->status_tugas === 'Selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $tugas->status_tugas }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-2"><i data-lucide="calendar" class="w-3 h-3 inline"></i> {{ \Carbon\Carbon::parse($tugas->tanggal_penugasan)->format('d M Y') }}</p>
                                    @if($tugas->ulasan)
                                        <div class="mt-2 pt-2 border-t border-slate-200">
                                            <div class="flex items-center gap-1 mb-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="text-xs" style="color:{{ $i <= $tugas->ulasan->rating ? '#f59e0b' : '#d1d5db' }}">★</span>
                                                @endfor
                                            </div>
                                            @if($tugas->ulasan->komentar)
                                                <p class="text-xs text-slate-600 italic leading-relaxed">"{{ $tugas->ulasan->komentar }}"</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctxKinerja = document.getElementById('kinerjaChart');
    const ctxRating = document.getElementById('ratingChart');
    if (!ctxKinerja || !ctxRating) return;

    const petugasData = @json($topPetugas->take(5)->values());
    
    const labels = petugasData.map(p => p.name.split(' ')[0]);
    const dataSelesai = petugasData.map(p => p.tugas_selesai_count);
    const dataAktif = petugasData.map(p => p.tugas_aktif_count);
    const dataRating = petugasData.map(p => p.rata_rata_rating);

    new Chart(ctxKinerja, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Selesai',
                    data: dataSelesai,
                    backgroundColor: '#10b981', // emerald-500
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Diproses',
                    data: dataAktif,
                    backgroundColor: '#0ea5e9', // sky-500
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        padding: 20,
                        font: {
                            family: "'Inter', sans-serif",
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#1e293b',
                    bodyColor: '#475569',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true,
                    titleFont: {
                        size: 13,
                        family: "'Inter', sans-serif"
                    },
                    bodyFont: {
                        size: 12,
                        family: "'Inter', sans-serif"
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            family: "'Inter', sans-serif",
                            size: 11
                        },
                        color: '#64748b'
                    },
                    grid: {
                        color: '#f1f5f9',
                        drawBorder: false,
                    },
                    border: {
                        display: false
                    }
                },
                x: {
                    ticks: {
                        font: {
                            family: "'Inter', sans-serif",
                            size: 11
                        },
                        color: '#64748b'
                    },
                    grid: {
                        display: false,
                        drawBorder: false,
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });

    new Chart(ctxRating, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Rata-rata Rating',
                    data: dataRating,
                    borderColor: '#f59e0b', // amber-500
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#f59e0b',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' Bintang';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
});
</script>
@endpush
