<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestimoniPublik;
use Illuminate\Http\RedirectResponse;

class TestimoniPublikController extends Controller
{
    public function index()
    {
        $testimoni = TestimoniPublik::query()
            ->latest()
            ->paginate(12);

        $summary = [
            'pending' => TestimoniPublik::pending()->count(),
            'approved' => TestimoniPublik::where('status', TestimoniPublik::STATUS_APPROVED)->count(),
            'rejected' => TestimoniPublik::where('status', TestimoniPublik::STATUS_REJECTED)->count(),
        ];

        return view('admin.testimoni.index', compact('testimoni', 'summary'));
    }

    public function approve(TestimoniPublik $testimoni): RedirectResponse
    {
        $testimoni->update([
            'status' => TestimoniPublik::STATUS_APPROVED,
            'validated_at' => now(),
        ]);

        return back()->with('success', 'Testimoni berhasil disetujui dan ditampilkan di landing page.');
    }

    public function reject(TestimoniPublik $testimoni): RedirectResponse
    {
        $testimoni->update([
            'status' => TestimoniPublik::STATUS_REJECTED,
            'validated_at' => now(),
        ]);

        return back()->with('success', 'Testimoni ditolak dan disembunyikan dari landing page.');
    }

    public function pending(TestimoniPublik $testimoni): RedirectResponse
    {
        $testimoni->update([
            'status' => TestimoniPublik::STATUS_PENDING,
            'validated_at' => null,
        ]);

        return back()->with('success', 'Testimoni dikembalikan ke status pending untuk ditinjau ulang.');
    }

    public function destroy(TestimoniPublik $testimoni): RedirectResponse
    {
        $testimoni->delete();

        return back()->with('success', 'Testimoni berhasil dihapus permanen.');
    }
}
