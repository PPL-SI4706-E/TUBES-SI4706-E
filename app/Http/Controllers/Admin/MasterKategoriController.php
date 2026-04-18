<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriLaporan;
use Illuminate\Http\Request;

class MasterKategoriController extends Controller
{
    public function index()
    {
        $kategoris = KategoriLaporan::withCount('laporans')->orderBy('nama_kategori')->get();
        return view('admin.master-kategori.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori_laporan,nama_kategori',
            'deskripsi'     => 'nullable|string',
            'tarif'         => 'required|numeric|min:0',
            'icon'          => 'nullable|string|max:50',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah ada.',
            'tarif.required'         => 'Tarif wajib diisi.',
            'tarif.numeric'          => 'Tarif harus berupa angka.',
        ]);

        $data['icon']      = $data['icon'] ?? 'droplet';
        $data['is_active'] = true;

        KategoriLaporan::create($data);
        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, KategoriLaporan $masterKategori)
    {
        $data = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori_laporan,nama_kategori,' . $masterKategori->id,
            'deskripsi'     => 'nullable|string',
            'tarif'         => 'required|numeric|min:0',
            'icon'          => 'nullable|string|max:50',
            'is_active'     => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $masterKategori->update($data);
        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(KategoriLaporan $masterKategori)
    {
        if ($masterKategori->laporans()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki laporan terkait.');
        }

        $masterKategori->delete();
        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}