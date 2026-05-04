<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumumanList = Pengumuman::with('user')
            ->orderByDesc('is_penting')
            ->latest('tanggal_post')
            ->latest()
            ->get();

        return view('admin.pengumuman.index', compact('pengumumanList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string', 'min:20'],
            'kategori' => ['required', 'in:darurat,jadwal,informasi'],
            'is_penting' => ['nullable', 'boolean'],
            'tanggal_post' => ['required', 'date'],
        ]);

        $data['user_id'] = auth()->id();
        $data['is_penting'] = $request->boolean('is_penting');

        Pengumuman::create($data);

        return back()->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string', 'min:20'],
            'kategori' => ['required', 'in:darurat,jadwal,informasi'],
            'is_penting' => ['nullable', 'boolean'],
            'tanggal_post' => ['required', 'date'],
        ]);

        $data['is_penting'] = $request->boolean('is_penting');
        $pengumuman->update($data);

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
