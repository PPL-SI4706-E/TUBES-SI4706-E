<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Pembayaran;
use App\Notifications\GeneralSystemNotification;

class PembayaranController extends Controller
{
    public function index()
    {
        $pembayarans = Pembayaran::with(['laporan.kategoriLaporan', 'user'])->latest()->get();

        $stats = [
            'total_transaksi' => Pembayaran::count(),
            'belum_dibayar' => Pembayaran::where('status_pembayaran', 'Menunggu')->sum('harga'),
            'menunggu_verif' => Pembayaran::where('status_pembayaran', 'Terverifikasi')->count(),
            'sudah_lunas' => Pembayaran::where('status_pembayaran', 'Lunas')->sum('harga'),
        ];

        return view('admin.pembayaran.index', compact('pembayarans', 'stats'));
    }

    public function store(Request $request)
    {
        return back()->with('success', 'Berhasil!');
    }

    public function verify(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Lunas,Ditolak,Menunggu'
        ]);

        $pembayaran = Pembayaran::with('user')->findOrFail($id);
        $pembayaran->update([
            'status_pembayaran' => $request->status
        ]);

        // PBI-18: Notifikasi ke Warga
        if ($pembayaran->user) {
            $notifTitle = $request->status === 'Lunas' ? 'Pembayaran Berhasil' : 'Pembayaran Ditolak';
            $notifType  = $request->status === 'Lunas' ? 'success' : 'error';
            $notifMsg   = $request->status === 'Lunas' 
                          ? "Pembayaran manual Anda sebesar Rp " . number_format($pembayaran->harga, 0, ',', '.') . " untuk Laporan #{$pembayaran->laporan_id} telah disetujui (Lunas)." 
                          : "Bukti pembayaran manual Anda untuk Laporan #{$pembayaran->laporan_id} ditolak oleh Admin. Silakan unggah ulang.";

            $pembayaran->user->notify(new GeneralSystemNotification(
                $notifTitle,
                $notifMsg,
                route('warga.pembayaran.index'),
                $notifType
            ));
        }        $message = $request->status === 'Lunas' ? 'Pembayaran berhasil disetujui!' : 'Pembayaran ditolak!';
        return back()->with('success', $message);
    }
}