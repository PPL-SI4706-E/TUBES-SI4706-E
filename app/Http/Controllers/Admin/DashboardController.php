<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalLaporan = Laporan::count();
        $laporanSelesai = Laporan::where('status', 'selesai')->count();
        
        $stats = [
            'total' => $totalLaporan,
            'pending' => Laporan::where('status', 'pending')->count(),
            'diproses' => Laporan::whereIn('status', ['diterima', 'dikerjakan'])->count(),
            'selesai' => $laporanSelesai,
            'konfirmasi' => Laporan::where('status', 'menunggu_konfirmasi')->count(),
            'ditolak' => Laporan::where('status', 'ditolak')->count(),
            'pendapatan' => Pembayaran::where('status_pembayaran', 'Lunas')->sum('harga') ?? 0,
            'belum_bayar' => Pembayaran::where('status_pembayaran', 'Menunggu')->count(),
            'perlu_lapangan' => Laporan::whereNotIn('status', ['selesai', 'ditolak'])->count(),
            'rasio_penyelesaian' => $totalLaporan > 0 ? round(($laporanSelesai / $totalLaporan) * 100, 1) : 0,
        ];

        $recentReports = Laporan::with(['kategoriLaporan', 'user'])
            ->latest()
            ->take(6)
            ->get();

        $statusDistribusi = [
            ['name' => 'Menunggu Validasi', 'value' => $stats['pending'], 'color' => '#f59e0b'],
            ['name' => 'Sedang Diproses', 'value' => $stats['diproses'], 'color' => '#8b5cf6'],
            ['name' => 'Menunggu Konfirmasi', 'value' => $stats['konfirmasi'], 'color' => '#06b6d4'],
            ['name' => 'Selesai', 'value' => $stats['selesai'], 'color' => '#10b981'],
            ['name' => 'Ditolak', 'value' => $stats['ditolak'], 'color' => '#ef4444'],
        ];

        // Fetch dynamic report statistics for the last 6 months
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $year = $month->year;
            $monthNum = $month->month;
            $monthName = $month->translatedFormat('M');

            $totalMonth = Laporan::whereYear('tanggal_lapor', $year)
                ->whereMonth('tanggal_lapor', $monthNum)
                ->count();

            $selesaiMonth = Laporan::whereYear('tanggal_lapor', $year)
                ->whereMonth('tanggal_lapor', $monthNum)
                ->where('status', 'selesai')
                ->count();

            $monthlyData[] = [
                'label' => $monthName,
                'total' => $totalMonth,
                'selesai' => $selesaiMonth,
            ];
        }

        // Fetch Persebaran Wilayah
        $persebaranWilayah = Laporan::select('wilayah_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->with('wilayah')
            ->groupBy('wilayah_id')
            ->orderByDesc('total')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentReports', 'statusDistribusi', 'monthlyData', 'persebaranWilayah'));
    }
}