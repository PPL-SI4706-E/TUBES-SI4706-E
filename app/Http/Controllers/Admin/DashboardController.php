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

        return view('admin.dashboard', compact('stats', 'recentReports', 'statusDistribusi'));
    }
}