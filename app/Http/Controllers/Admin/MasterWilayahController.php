<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use Illuminate\Http\Request;

class MasterWilayahController extends Controller
{
    public function index()
    {
        $wilayahs = Wilayah::withCount('laporans')->orderBy('nama_wilayah')->paginate(12);
        return view('admin.master-wilayah.index', compact('wilayahs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_wilayah' => 'required|string|max:100',
            'tipe'         => 'required|in:kecamatan,desa,kelurahan',
            'kode_wilayah' => 'nullable|string|max:20',
        ], [
            'nama_wilayah.required' => 'Nama wilayah wajib diisi.',
            'tipe.required'         => 'Tipe wilayah wajib dipilih.',
        ]);

        Wilayah::create($data);
        return back()->with('success', 'Wilayah berhasil ditambahkan.');
    }

    public function update(Request $request, Wilayah $masterWilayah)
    {
        $data = $request->validate([
            'nama_wilayah' => 'required|string|max:100',
            'tipe'         => 'required|in:kecamatan,desa,kelurahan',
            'kode_wilayah' => 'nullable|string|max:20',
        ]);

        $masterWilayah->update($data);
        return back()->with('success', 'Wilayah berhasil diperbarui.');
    }

    public function destroy(Wilayah $masterWilayah)
    {
        if ($masterWilayah->laporans()->count() > 0) {
            return back()->with('error', 'Wilayah tidak dapat dihapus karena masih memiliki laporan terkait.');
        }

        $masterWilayah->delete();
        return back()->with('success', 'Wilayah berhasil dihapus.');
    }
}