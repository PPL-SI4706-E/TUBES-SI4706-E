@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kinerja Petugas Lapangan</h1>
            <p class="text-sm text-gray-500 mt-1">Laporan produktivitas dan kualitas layanan setiap petugas</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700 transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </button>
            <button class="inline-flex items-center px-4 py-2 bg-rose-500 text-white text-sm font-medium rounded-md hover:bg-rose-600 transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Export PDF
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Left: Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Grafik Kinerja
            </h3>
            <div class="h-80">
                <canvas id="kinerjaChart"></canvas>
            </div>
        </div>

        <!-- Right: Top 3 Petugas Cards Stacked -->
        <div class="space-y-4 max-h-[25.5rem] overflow-y-auto pr-2" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">
            @forelse($topPetugas as $index => $petugas)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg border border-blue-100">
                            {{ strtoupper(substr($petugas->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">{{ $petugas->name }}</h3>
                            <p class="text-xs text-gray-400">Petugas Lapangan</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 text-amber-500 font-bold text-sm bg-amber-50 px-2 py-1 rounded-md">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <span>{{ number_format($petugas->rata_rata_rating ?? $petugas->average_rating ?? 0, 1) }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-emerald-50 rounded-xl py-2 px-1 text-center border border-emerald-100">
                        <p class="text-xl font-bold text-emerald-700">{{ $petugas->tugas_selesai_count ?? 0 }}</p>
                        <p class="text-[11px] font-medium text-emerald-600 mt-1">Selesai</p>
                    </div>
                    <div class="bg-blue-50 rounded-xl py-2 px-1 text-center border border-blue-100">
                        <p class="text-xl font-bold text-blue-700">{{ $petugas->tugas_aktif_count ?? 0 }}</p>
                        <p class="text-[11px] font-medium text-blue-600 mt-1">Aktif</p>
                    </div>
                    <div class="bg-amber-50 rounded-xl py-2 px-1 text-center border border-amber-100">
                        <p class="text-xl font-bold text-amber-700">{{ number_format($petugas->rata_rata_rating ?? $petugas->average_rating ?? 0, 1) }}</p>
                        <p class="text-[11px] font-medium text-amber-600 mt-1">Avg Rating</p>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
                Belum ada data petugas
            </div>
            @endforelse
        </div>
    </div>

    <!-- All Petugas Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-700">Daftar Kinerja Keseluruhan</h2>
            <div class="text-xs font-medium text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200">
                Menampilkan {{ $petugasList->count() }} Petugas
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-white border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => ($sortBy === 'name' && $sortDir === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center group transition-colors hover:text-blue-600">
                                Nama Petugas
                                @if($sortBy === 'name')
                                    <svg class="w-4 h-4 ml-1 {{ $sortDir === 'desc' ? 'rotate-180' : '' }} text-blue-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="w-4 h-4 ml-1 opacity-0 group-hover:opacity-100 text-gray-400 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-4 font-semibold">Email</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'tugas_selesai_count', 'sort_dir' => ($sortBy === 'tugas_selesai_count' && $sortDir === 'desc') ? 'asc' : 'desc']) }}" class="flex items-center justify-center group transition-colors hover:text-blue-600">
                                Tugas Selesai
                                @if($sortBy === 'tugas_selesai_count')
                                    <svg class="w-4 h-4 ml-1 {{ $sortDir === 'desc' ? 'rotate-180' : '' }} text-blue-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="w-4 h-4 ml-1 opacity-0 group-hover:opacity-100 text-gray-400 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'rata_rata_rating', 'sort_dir' => ($sortBy === 'rata_rata_rating' && $sortDir === 'desc') ? 'asc' : 'desc']) }}" class="flex items-center justify-center group transition-colors hover:text-blue-600">
                                Rating
                                @if($sortBy === 'rata_rata_rating')
                                    <svg class="w-4 h-4 ml-1 {{ $sortDir === 'desc' ? 'rotate-180' : '' }} text-blue-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="w-4 h-4 ml-1 opacity-0 group-hover:opacity-100 text-gray-400 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @endif
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($petugasList as $petugas)
                        <tr class="bg-white border-b hover:bg-blue-50/40 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs border border-blue-200 shadow-sm">
                                        {{ strtoupper(substr($petugas->name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ $petugas->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">{{ $petugas->email }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 text-emerald-700 font-bold text-sm border border-emerald-100 group-hover:bg-emerald-100 group-hover:border-emerald-200 transition-colors">
                                    {{ $petugas->tugas_selesai_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5 bg-amber-50 inline-flex px-3 py-1 rounded-full border border-amber-100 group-hover:bg-amber-100 group-hover:border-amber-200 transition-colors">
                                    <span class="font-bold text-amber-700">{{ number_format($petugas->rata_rata_rating ?? $petugas->average_rating ?? 0, 1) }}</span>
                                    <svg class="w-3.5 h-3.5 text-amber-500 pb-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center space-y-4">
                                    <div class="p-4 bg-gray-50 rounded-full">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    </div>
                                    <p class="font-medium">Belum ada data kinerja petugas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctxKinerja = document.getElementById('kinerjaChart');
    if (!ctxKinerja) return;

    // Ambil data petugas (maksimal 5 untuk grafik agar tidak terlalu padat)
    const petugasData = @json($topPetugas->take(5)->values());
    
    const labels = petugasData.map(p => p.name.split(' ')[0]);
    const dataSelesai = petugasData.map(p => p.tugas_selesai_count);
    const dataAktif = petugasData.map(p => p.tugas_aktif_count || 0);

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
                    label: 'Aktif',
                    data: dataAktif,
                    backgroundColor: '#3b82f6', // blue-500
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
                    callbacks: {
                        title: function(context) {
                            return petugasData[context[0].dataIndex].name;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#64748b'
                    },
                    grid: {
                        color: '#f1f5f9',
                        drawBorder: false,
                    },
                    border: { display: false }
                },
                x: {
                    ticks: {
                        color: '#64748b'
                    },
                    grid: {
                        display: false,
                        drawBorder: false,
                    },
                    border: { display: false }
                }
            }
        }
    });
});
</script>
@endpush
