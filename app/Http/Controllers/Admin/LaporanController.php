<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterLaporanRequest;
use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Wilayah;
use App\Models\Penugasan;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\GeneralSystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(FilterLaporanRequest $request)
    {
        $filters = $request->validated();

        $laporans = Laporan::query()
            ->filterKeyword($filters['keyword'] ?? null)
            ->filterStatusBayar($filters['status_bayar'] ?? null)
            ->filterRentangBulan($filters['bulan_awal'] ?? null, $filters['bulan_akhir'] ?? null)
            ->filterWilayah($filters['wilayah_id'] ?? null)
            ->filterKategori($filters['kategori_id'] ?? null)
            ->with(['kategoriLaporan', 'wilayah', 'user', 'pembayaran'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $wilayahs = Wilayah::query()
            ->orderBy('nama_wilayah')
            ->get();

        $kategoris = KategoriLaporan::query()
            ->orderBy('nama_kategori')
            ->get();

        return view('admin.laporan.index', compact('laporans', 'wilayahs', 'kategoris'));
    }

    public function peta()
    {
        $laporans = \App\Models\Laporan::with(['mapLokasi', 'wilayah', 'kategoriLaporan'])->get();
        return view('admin.laporan.peta', compact('laporans'));
    }

    public function show($id)
    {
        $laporan = Laporan::with([
            'kategoriLaporan', 'wilayah', 'user', 'mapLokasi',
            'penugasan.petugas.wilayah',
            'pembayaran',
        ])->findOrFail($id);

        $petugas = [];
        if ($laporan->status === 'diterima') {
            $petugas = User::where('role', 'petugas')->with('wilayah')->get();
        }
        return view('admin.laporan.show', compact('laporan', 'petugas'));
    }

    public function validasi(Request $request, $id)
    {
        $request->validate([
            'status'        => 'required|in:diterima,selesai,ditolak',
            'catatan_admin' => 'required|string|min:5',
        ], [
            'status.required'        => 'Aksi validasi harus dipilih.',
            'status.in'              => 'Aksi tidak valid.',
            'catatan_admin.required' => 'Catatan / alasan wajib diisi.',
            'catatan_admin.min'      => 'Catatan minimal 5 karakter.',
        ]);

        $laporan = Laporan::with('pembayaran')->findOrFail($id);

        // Hanya laporan berstatus pending yang bisa divalidasi
        if ($laporan->status !== 'pending') {
            return redirect()->route('admin.laporan.show', $laporan->id)
                ->with('error', 'Laporan ini sudah pernah divalidasi.');
        }

        // ── PAYMENT GUARD ─────────────────────────────────────────────────────
        // Laporan hanya bisa divalidasi jika pembayaran sudah berstatus 'Lunas'
        $pembayaran = $laporan->pembayaran;

        if (!$pembayaran || $pembayaran->status_pembayaran !== 'Lunas') {
            return redirect()->route('admin.laporan.show', $laporan->id)
                ->with('error', 'Laporan tidak dapat divalidasi. Pembayaran belum diverifikasi (Lunas).');
        }
        // ─────────────────────────────────────────────────────────────────────

        $laporan->status        = $request->status;
        $laporan->catatan_admin = $request->catatan_admin;
        $laporan->save();

        $pesanMap = [
            'diterima' => 'Laporan diterima dan siap untuk penugasan petugas lapangan.',
            'selesai'  => 'Laporan diselesaikan dengan solusi virtual.',
            'ditolak'  => 'Laporan berhasil ditolak.',
        ];

        // PBI-18: Notifikasi ke Warga (Pembuat Laporan) tentang hasil validasi
        if ($laporan->user) {
            $notifTitle = $request->status === 'diterima' ? 'Laporan Disetujui' : 
                         ($request->status === 'ditolak' ? 'Laporan Ditolak' : 'Laporan Diselesaikan');
            $notifType  = $request->status === 'ditolak' ? 'error' : 'success';
            $notifMsg   = $request->status === 'diterima' 
                          ? "Laporan Anda (#{$laporan->id}) telah disetujui dan sedang disiapkan petugasnya." 
                          : "Laporan Anda (#{$laporan->id}) telah ditolak. Catatan Admin: {$request->catatan_admin}";
            
            if ($request->status === 'selesai') {
                $notifMsg = "Laporan Anda (#{$laporan->id}) diselesaikan oleh sistem. Catatan: {$request->catatan_admin}";
            }

            $laporan->user->notify(new GeneralSystemNotification(
                $notifTitle,
                $notifMsg,
                route('warga.laporan.show', $laporan->id),
                $notifType
            ));
        }

        return redirect()->route('admin.laporan.show', $laporan->id)
            ->with('success', $pesanMap[$request->status]);
    }

    public function assign(Request $request, $id)
    {
        $request->validate([
            'petugas_id'    => 'required|exists:users,id',
            'catatan_admin' => 'nullable|string',
        ], [
            'petugas_id.required' => 'Petugas harus dipilih.',
            'petugas_id.exists'   => 'Petugas tidak valid.',
        ]);

        $laporan = Laporan::findOrFail($id);

        if ($laporan->status !== 'diterima') {
            return back()->with('error', 'Laporan belum diterima atau sudah ditugaskan.');
        }

        $petugas = User::findOrFail($request->petugas_id);
        if ($petugas->role !== 'petugas') {
            return back()->with('error', 'User yang dipilih bukan petugas lapangan.');
        }

        // NFR-02: Reliabilitas transaksi (Atomisitas)
        DB::transaction(function () use ($laporan, $petugas, $request) {
            // Buat penugasan
            $penugasan = new Penugasan();
            $penugasan->laporan_id = $laporan->id;
            $penugasan->user_id = $petugas->id;
            $penugasan->tanggal_penugasan = now()->toDateString();
            $penugasan->status_tugas = 'Ditugaskan';
            $penugasan->catatan_admin = $request->catatan_admin;
            $penugasan->save();

            // Update status laporan
            $laporan->status = 'dikerjakan';
            $laporan->save();

            // PBI-18: Kirim notifikasi ke petugas yang ditugaskan
            $laporan->load('penugasan');
            $petugas->notify(new TaskAssignedNotification($laporan, auth()->user()));
        });

        return back()->with('success', "Work Order untuk Laporan #{$laporan->id} berhasil dibuat dan ditugaskan ke {$petugas->name}.");
    }
}
