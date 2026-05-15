<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterLaporanRequest;
use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Wilayah;
use Illuminate\Http\Request;

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
