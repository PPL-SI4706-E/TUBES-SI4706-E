<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\Penugasan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = Laporan::with(['kategoriLaporan', 'wilayah', 'user'])->latest();

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pencarian berdasarkan ID, alamat, atau deskripsi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Cari berdasarkan ID (jika input numerik)
                if (is_numeric($search)) {
                    $q->where('id', $search);
                }
                // Cari berdasarkan alamat
                $q->orWhere('alamat', 'like', "%{$search}%")
                  // Cari berdasarkan deskripsi
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $laporans = $query->paginate(15)->withQueryString();
        return view('admin.laporan.index', compact('laporans'));
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
            'penugasan.petugas.wilayah',   // eager-load untuk tampilan info penugasan
        ])->findOrFail($id);

        $petugas = [];
        if ($laporan->status === 'diterima') {
            $petugas = User::role('petugas')->with('wilayah')->get();
        }
        return view('admin.laporan.show', compact('laporan', 'petugas'));
    }

    public function validasi(Request $request, $id)
    {
        // Validasi input — status dan catatan_admin wajib untuk semua aksi
        $request->validate([
            'status'        => 'required|in:diterima,selesai,ditolak',
            'catatan_admin' => 'required|string|min:5',
        ], [
            'status.required'        => 'Aksi validasi harus dipilih.',
            'status.in'              => 'Aksi tidak valid.',
            'catatan_admin.required' => 'Catatan / alasan wajib diisi.',
            'catatan_admin.min'      => 'Catatan minimal 5 karakter.',
        ]);

        $laporan = Laporan::findOrFail($id);

        // Hanya laporan berstatus pending yang bisa divalidasi
        if ($laporan->status !== 'pending') {
            return redirect()->route('admin.laporan.show', $laporan->id)
                ->with('error', 'Laporan ini sudah pernah divalidasi.');
        }

        $laporan->status        = $request->status;
        $laporan->catatan_admin = $request->catatan_admin;
        $laporan->save();

        $pesanMap = [
            'diterima' => 'Laporan diterima dan siap untuk penugasan petugas lapangan.',
            'selesai'  => 'Laporan diselesaikan dengan solusi virtual.',
            'ditolak'  => 'Laporan berhasil ditolak.',
        ];

        return redirect()->route('admin.laporan.show', $laporan->id)
            ->with('success', $pesanMap[$request->status]);
    }

    public function assign(Request $request, $id)
    {
        $request->validate([
            'petugas_id' => 'required|exists:user,id',
            'catatan_admin' => 'nullable|string',
        ], [
            'petugas_id.required' => 'Petugas harus dipilih.',
            'petugas_id.exists' => 'Petugas tidak valid.',
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
            $laporan->status = 'dikerjakan'; // Di database enumnya 'dikerjakan' (bukan 'ditugaskan')
            $laporan->save();
        });

        return back()->with('success', "Work Order untuk Laporan #{$laporan->id} berhasil dibuat dan ditugaskan ke {$petugas->name}.");
    }
}