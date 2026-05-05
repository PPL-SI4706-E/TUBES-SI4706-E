<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumuman = Pengumuman::query()
            ->latest('tanggal_post')
            ->latest()
            ->get();

        return view('admin.pengumuman.index', compact('pengumuman'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['user_id'] = auth()->id();
        $data['is_penting'] = $request->boolean('is_penting');

        Pengumuman::create($data);

        return back()->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $data = $this->validateData($request);
        $data['is_penting'] = $request->boolean('is_penting');

        $pengumuman->update($data);

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string|min:10',
            'kategori' => ['required', Rule::in(['darurat', 'jadwal', 'gangguan', 'info'])],
            'tanggal_post' => 'required|date',
            'is_penting' => 'nullable|boolean',
        ], [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
            'isi.min' => 'Isi pengumuman minimal 10 karakter.',
            'kategori.required' => 'Kategori pengumuman wajib dipilih.',
            'tanggal_post.required' => 'Tanggal post wajib diisi.',
        ]);
    }
}
