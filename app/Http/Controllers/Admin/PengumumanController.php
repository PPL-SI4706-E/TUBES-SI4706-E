<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    public function index(): View
    {
        $pengumuman = Pengumuman::query()
            ->latest('tanggal_post')
            ->latest('created_at')
            ->paginate(10);

        return view('admin.pengumuman.index', compact('pengumuman'));
    }

    public function create(): View
    {
        return view('admin.pengumuman.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePengumuman($request);
        $data['user_id'] = auth()->id();
        $data['is_published'] = $request->boolean('is_published', true);

        Pengumuman::create($data);

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(Pengumuman $pengumuman): View
    {
        return view('admin.pengumuman.edit', compact('pengumuman'));
    }

    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $data = $this->validatePengumuman($request);
        $data['is_published'] = $request->boolean('is_published');

        $pengumuman->update($data);

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        $pengumuman->delete();

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function validatePengumuman(Request $request): array
    {
        return $request->validate([
            'category' => ['required', Rule::in(['darurat', 'jadwal', 'informasi'])],
            'priority' => ['required', Rule::in(['penting', 'normal'])],
            'tanggal_post' => ['required', 'date'],
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
        ], [
            'category.required' => 'Kategori wajib dipilih.',
            'priority.required' => 'Prioritas wajib dipilih.',
            'tanggal_post.required' => 'Tanggal wajib diisi.',
            'judul.required' => 'Judul wajib diisi.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
        ]);
    }
}
