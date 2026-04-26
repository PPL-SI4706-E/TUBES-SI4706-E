<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;

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
        $laporan = Laporan::with(['kategoriLaporan', 'wilayah', 'user', 'mapLokasi'])->findOrFail($id);
        return view('admin.laporan.show', compact('laporan'));
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

    public function assign(Request $request, $laporan)
    {
        return back()->with('success', 'TODO: implement assign (Sprint 2)');
    }
}