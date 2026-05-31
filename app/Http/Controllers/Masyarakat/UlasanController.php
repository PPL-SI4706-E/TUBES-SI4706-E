<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminSystemNotification;
use App\Notifications\GeneralSystemNotification;
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

        // PBI-18: Notifikasi Ulasan Buruk ke Admin
        if ($r->rating <= 2) {
            $admins = User::where('role', 'admin')->get();
            \Illuminate\Support\Facades\Notification::send($admins, new AdminSystemNotification(
                'Ulasan Buruk Diterima',
                "Laporan #{$laporan->id} mendapat rating {$r->rating} bintang. Mohon periksa kinerja petugas terkait.",
                route('admin.laporan.show', $laporan->id),
                'error'
            ));
        }

        // PBI-18: Notifikasi Apresiasi ke Petugas (Rating 4-5)
        $laporan->load('penugasan.petugas');
        if ($r->rating >= 4 && $laporan->penugasan && $laporan->penugasan->petugas) {
            $laporan->penugasan->petugas->notify(new GeneralSystemNotification(
                'Kerja Bagus!',
                "Anda mendapat rating {$r->rating} Bintang untuk Laporan #{$laporan->id} dari Warga.",
                route('petugas.tugas.show', $laporan->penugasan->id),
                'success'
            ));
        }

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