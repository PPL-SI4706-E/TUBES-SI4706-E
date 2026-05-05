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
        $testimonials = TestimoniPublik::query()->latest()->get();
        return view('admin.testimoni.index', compact('testimonials'));
    }

    public function approve(TestimoniPublik $testimoni): RedirectResponse
    {
        $testimoni->update([
            'status' => 'approved',
            'approved_at' => now(),
            'catatan_admin' => null,
        ]);

        return redirect()->route('admin.testimoni.index')->with('success', 'Testimoni berhasil disetujui.');
    }

    public function reject(Request $request, TestimoniPublik $testimoni): RedirectResponse
    {
        $testimoni->update([
            'status' => 'rejected',
            'approved_at' => null,
            'catatan_admin' => $request->input('catatan_admin'),
        ]);

        return redirect()->route('admin.testimoni.index')->with('success', 'Testimoni berhasil ditolak.');
    }

    public function destroy(TestimoniPublik $testimoni): RedirectResponse
    {
        $testimoni->delete();

        return redirect()->route('admin.testimoni.index')->with('success', 'Testimoni berhasil dihapus.');
    }
}
