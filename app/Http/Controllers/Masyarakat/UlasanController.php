<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UlasanController extends Controller
{
    public function store(Request $r, $id)
    {
        $r->validate([
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string'
        ]);

        $laporan = \App\Models\Laporan::where('user_id', auth()->id())->findOrFail($id);

        \App\Models\Ulasan::updateOrCreate(
            ['laporan_id' => $laporan->id],
            [
                'user_id' => auth()->id(),
                'rating' => $r->rating,
                'komentar' => $r->komentar,
                'tanggal_ulasan' => now(),
            ]
        );

        // Jika ada komentar, masukkan juga sebagai Testimoni Publik agar bisa dikelola Admin
        if ($r->komentar) {
            \App\Models\TestimoniPublik::create([
                'nama' => auth()->user()->name,
                'email' => auth()->user()->email,
                'rating' => $r->rating,
                'pesan' => $r->komentar,
                'status' => 'pending',
                'editable_until' => now()->addMinutes(5),
                'session_token' => session()->getId(),
            ]);
        }

        return back()->with('success', 'Terima kasih atas feedback Anda!');
    }
}