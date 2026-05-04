<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
<<<<<<< Updated upstream
=======
use App\Models\Laporan;
use App\Models\Pembayaran;
use App\Models\TestimoniPublik;
use Illuminate\Support\Facades\DB;
>>>>>>> Stashed changes

class DashboardController extends Controller
{
    public function index()
    {
<<<<<<< Updated upstream
        return view('admin.dashboard');
=======
        $stats = [
            'total' => Laporan::count(),
            'pending' => Laporan::where('status', 'pending')->count(),
            'diproses' => Laporan::whereIn('status', ['diterima', 'dikerjakan'])->count(),
            'selesai' => Laporan::where('status', 'selesai')->count(),
            'konfirmasi' => 0, 
            'ditolak' => Laporan::where('status', 'ditolak')->count(),
            'pendapatan' => Pembayaran::where('status_pembayaran', 'Lunas')->sum('harga') ?? 0,
            'belum_bayar' => Pembayaran::where('status_pembayaran', 'Menunggu')->count(),
            'perlu_lapangan' => Laporan::count(),
            'testimoni_pending' => TestimoniPublik::where('status_validasi', 'pending')->count(),
        ];

        $recentReports = Laporan::with(['kategoriLaporan', 'user'])
            ->latest()
            ->take(6)
            ->get();

        $statusDistribusi = [
            ['name' => 'Menunggu Validasi', 'value' => $stats['pending'], 'color' => '#f59e0b'],
            ['name' => 'Sedang Dikerjakan', 'value' => Laporan::where('status', 'dikerjakan')->count(), 'color' => '#8b5cf6'],
            ['name' => 'Selesai', 'value' => $stats['selesai'], 'color' => '#10b981'],
            ['name' => 'Ditolak', 'value' => $stats['ditolak'], 'color' => '#ef4444'],
        ];

        $recentTestimoni = TestimoniPublik::latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentReports', 'statusDistribusi', 'recentTestimoni'));
>>>>>>> Stashed changes
    }
}
