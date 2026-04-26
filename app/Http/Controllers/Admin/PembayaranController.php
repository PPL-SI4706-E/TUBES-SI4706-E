<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Pembayaran;

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

        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->update([
            'status_pembayaran' => $request->status
        ]);



        $message = $request->status === 'Lunas' ? 'Pembayaran berhasil disetujui!' : 'Pembayaran ditolak!';
        return back()->with('success', $message);
    }
}