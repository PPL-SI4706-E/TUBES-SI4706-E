<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\MapLokasi;
use App\Models\KategoriLaporan;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    public function index()
    {
        $laporans = Laporan::with(['kategoriLaporan', 'mapLokasi'])->where('user_id', auth()->id())->latest()->get();
        return view('warga.laporan.index', compact('laporans'));
    }

    public function create()
    {
        $kategoris = KategoriLaporan::all();
        $wilayahs = Wilayah::all();
        return view('warga.laporan.create', compact('kategoris', 'wilayahs'));
    }

    public function show($id)
    {
        $laporan = Laporan::with('mapLokasi')->where('user_id', auth()->id())->findOrFail($id);
        return view('warga.laporan.show', compact('laporan'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'kategori_laporan_id' => 'required|exists:kategori_laporan,id',
            'wilayah_id' => 'required|exists:wilayah,id',
            'deskripsi' => 'required|string',
            'alamat' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'nullable|image|max:5120'
        ]);

        $fotoPath = null;
        if ($r->hasFile('foto')) {
            $fotoPath = $r->file('foto')->store('laporans', 'public');
        }

        $kategori = KategoriLaporan::findOrFail($data['kategori_laporan_id']);

        $laporan = Laporan::create([
            'user_id' => auth()->id(),
            'wilayah_id' => $data['wilayah_id'],
            'kategori_laporan_id' => $data['kategori_laporan_id'],
            'judul' => $kategori->nama_kategori . ' - ' . now()->format('d/m/Y H:i'),
            'deskripsi' => $data['deskripsi'],
            'alamat' => $data['alamat'] ?? '',
            'foto' => $fotoPath,
            'status' => 'pending',
            'tanggal_lapor' => now()
        ]);

        MapLokasi::create([
            'laporan_id' => $laporan->id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        // AUTOMATIC PAYMENT CREATION (Sinkronisasi Otomatis)
        \App\Models\Pembayaran::create([
            'laporan_id' => $laporan->id,
            'user_id' => auth()->id(),
            'harga' => $kategori->tarif,
            'metode_pembayaran' => $kategori->tarif == 0 ? 'Sistem (Gratis)' : null,
            'status_pembayaran' => $kategori->tarif == 0 ? 'Lunas' : 'Menunggu',
        ]);

        return redirect()->route('warga.laporan.index')->with('success', 'Laporan berhasil dibuat! Silakan cek menu Pembayaran untuk melunasi tagihan.');
    }

    public function konfirmasi(Request $r, $id) { return back(); }
}