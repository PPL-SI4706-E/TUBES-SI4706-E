<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestimoniPublik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimoniPublikController extends Controller
{
    public function index(): View
    {
        $testimoni = TestimoniPublik::latest()->paginate(12);

        return view('admin.testimoni.index', compact('testimoni'));
    }

    public function updateStatus(Request $request, TestimoniPublik $testimoni): RedirectResponse
    {
        $data = $request->validate([
            'status_validasi' => ['required', 'in:pending,disetujui,ditolak'],
        ]);

        $testimoni->update([
            'status_validasi' => $data['status_validasi'],
            'validated_at' => $data['status_validasi'] === 'pending' ? null : now(),
        ]);

        return redirect()
            ->route('admin.testimoni.index')
            ->with('success', 'Status testimoni berhasil diperbarui.');
    }

    public function destroy(TestimoniPublik $testimoni): RedirectResponse
    {
        $testimoni->delete();

        return redirect()
            ->route('admin.testimoni.index')
            ->with('success', 'Testimoni berhasil dihapus dari sistem.');
    }
}
