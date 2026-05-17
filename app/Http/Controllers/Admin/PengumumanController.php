<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    public function index(): View
    {
        $pengumuman = Pengumuman::query()
            ->latest('tanggal_post')
            ->latest()
            ->get();

        return view('admin.pengumuman.index', compact('pengumuman'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'kategori' => ['required', 'in:darurat,gangguan,jadwal,info'],
            'tanggal_post' => ['required', 'date'],
            'is_penting' => ['nullable', 'boolean'],
        ]);

        Pengumuman::query()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'is_penting' => $request->boolean('is_penting'),
        ]);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'kategori' => ['required', 'in:darurat,gangguan,jadwal,info'],
            'tanggal_post' => ['required', 'date'],
            'is_penting' => ['nullable', 'boolean'],
        ]);

        $pengumuman->update([
            ...$data,
            'is_penting' => $request->boolean('is_penting'),
        ]);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        $pengumuman->delete();

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
