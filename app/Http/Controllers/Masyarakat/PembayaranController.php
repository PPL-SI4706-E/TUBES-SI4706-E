<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class PembayaranController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $expiredQuery = Pembayaran::whereIn('status_pembayaran', ['Menunggu', 'Ditolak'])
            ->where('created_at', '<', now()->subHours(24));

        $laporanIds = (clone $expiredQuery)->pluck('laporan_id');

        if ($laporanIds->isNotEmpty()) {
            (clone $expiredQuery)->update(['status_pembayaran' => 'Kadaluarsa']);

            \App\Models\Laporan::whereIn('id', $laporanIds)->update([
                'status' => 'ditolak',
                'catatan_admin' => 'Pembayaran Kadaluarsa (Hangus). Silakan buat laporan ulang.'
            ]);
        }

        $stats = [
            'total_tagihan' => Pembayaran::where('user_id', $user->id)->count(),
            'belum_dibayar' => Pembayaran::where('user_id', $user->id)
                ->whereIn('status_pembayaran', ['Menunggu', 'Ditolak'])
                ->sum('harga'),
            'menunggu_verif' => Pembayaran::where('user_id', $user->id)
                ->where('status_pembayaran', 'Terverifikasi')
                ->count(),
            'sudah_lunas' => Pembayaran::where('user_id', $user->id)
                ->where('status_pembayaran', 'Lunas')
                ->sum('harga'),
        ];

        $tagihanAktif = Pembayaran::with('laporan')
            ->where('user_id', $user->id)
            ->whereIn('status_pembayaran', ['Menunggu', 'Ditolak'])
            ->get();

        $riwayat = Pembayaran::with('laporan')
            ->where('user_id', $user->id)
            ->whereIn('status_pembayaran', ['Terverifikasi', 'Lunas', 'Kadaluarsa'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('warga.pembayaran.index', compact('stats', 'tagihanAktif', 'riwayat'));
    }

    public function uploadBukti(Request $request, $id)
    {
        $request->validate([
            'bukti_transaksi' => 'required_unless:metode_pembayaran,Tunai di Kantor|nullable|image|mimes:jpg,png,jpeg|max:5120',
            'metode_pembayaran' => 'required'
        ], [
            'bukti_transaksi.required_unless' => 'Harap unggah bukti transfer.',
            'bukti_transaksi.image' => 'File harus berupa gambar.',
            'bukti_transaksi.mimes' => 'Format file tidak didukung, harap unggah JPG atau PNG.',
            'bukti_transaksi.max' => 'Ukuran file terlalu besar, maksimal 5 MB.',
        ]);

        $pembayaran = Pembayaran::where('user_id', Auth::id())->findOrFail($id);

        $updateData = [
            'metode_pembayaran' => $request->metode_pembayaran,
            'status_pembayaran' => 'Terverifikasi',
        ];

        if ($request->hasFile('bukti_transaksi')) {
            if ($pembayaran->bukti_transaksi) {
                Storage::delete('public/bukti_pembayaran/' . $pembayaran->bukti_transaksi);
            }

            $file = $request->file('bukti_transaksi');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/bukti_pembayaran', $filename);

            $updateData['bukti_transaksi'] = $filename;
        }

        $pembayaran->update($updateData);

        return back()->with('success', 'Pembayaran Berhasil! Menunggu verifikasi admin.');
    }
}