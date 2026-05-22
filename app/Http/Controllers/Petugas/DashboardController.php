<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Penugasan;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $petugasId = auth()->id();
        $petugas = User::findOrFail($petugasId);

        // 1. Hitung Tugas Selesai
        $tugasSelesai = Penugasan::where('user_id', $petugasId)
            ->where('status_tugas', 'Selesai')
            ->count();

        // 2. Hitung Tugas Diproses (Aktif)
        $tugasDiproses = Penugasan::where('user_id', $petugasId)
            ->whereNotIn('status_tugas', ['Selesai'])
            ->count();

        // 3. Rata-rata Rating & Ulasan Terbaru
        // Ulasan yang diisi masyarakat ada di relasi: Penugasan -> Ulasan
        // Kita ambil semua penugasan yang selesai dan punya ulasan
        $penugasanSelesai = Penugasan::with('ulasan', 'laporan.kategoriLaporan')
            ->where('user_id', $petugasId)
            ->where('status_tugas', 'Selesai')
            ->whereHas('ulasan')
            ->latest('updated_at')
            ->get();

        $totalRating = 0;
        $jumlahUlasan = $penugasanSelesai->count();

        foreach ($penugasanSelesai as $tugas) {
            $totalRating += $tugas->ulasan->rating;
        }

        $rataRataRating = $jumlahUlasan > 0 ? $totalRating / $jumlahUlasan : 0;

        // Ambil 5 ulasan terbaru untuk ditampilkan di dashboard
        $ulasanTerbaru = $penugasanSelesai->take(5);

        // 4. Status Keaktifan (Aktivitas Terkini)
        $lastTask = Penugasan::where('user_id', $petugasId)->latest('tanggal_penugasan')->first();
        $daysSinceLastTask = $lastTask ? \Carbon\Carbon::parse($lastTask->tanggal_penugasan)->diffInDays(now()) : null;
        
        if ($tugasDiproses > 0) {
            $statusBadge = ['text' => 'Aktif Bertugas', 'bg' => 'bg-emerald-50', 'textCol' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'];
        } elseif ($lastTask && $daysSinceLastTask <= 3) {
            $statusBadge = ['text' => 'Standby', 'bg' => 'bg-amber-50', 'textCol' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'];
        } else {
            $statusBadge = ['text' => 'Lama Tidak Aktif', 'bg' => 'bg-rose-50', 'textCol' => 'text-rose-700', 'border' => 'border-rose-200', 'dot' => 'bg-rose-500'];
        }

        return view('petugas.dashboard.index', compact(
            'petugas',
            'tugasSelesai',
            'tugasDiproses',
            'rataRataRating',
            'ulasanTerbaru',
            'statusBadge'
        ));
    }
}
