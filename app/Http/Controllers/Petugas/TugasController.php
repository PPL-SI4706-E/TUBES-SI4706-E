<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Penugasan;
use App\Models\PenyelesaianTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TugasController extends Controller
{
    public function index()
    {
        $petugas = auth()->user();

        $penugasans = Penugasan::query()
            ->with([
                'laporan.kategoriLaporan',
                'laporan.user',
                'laporan.wilayah',
                'laporan.mapLokasi',
                'laporan.ulasan',
                'penyelesaian',
                'penyelesaianTugas',
            ])
            ->where('user_id', $petugas->id)
            ->orderByDesc('tanggal_penugasan')
            ->orderByDesc('id')
            ->get();

        $tugasAktif = $penugasans
            ->whereIn('status_tugas', ['Ditugaskan', 'Menuju Lokasi', 'Sedang Dikerjakan', 'Menunggu Konfirmasi'])
            ->values();

        $riwayatSelesai = $penugasans
            ->where('status_tugas', 'Selesai')
            ->values();

        $notifications = $this->getNotificationsForPetugas($petugas->id);

        return view('petugas.tugas.index', [
            'petugas' => $petugas,
            'tugasAktif' => $tugasAktif,
            'riwayatSelesai' => $riwayatSelesai,
            'notifications' => $notifications,
            'unreadNotificationsCount' => $notifications->where('is_read', false)->count(),
        ]);
    }

    public function show(Penugasan $penugasan)
    {
        abort_unless($penugasan->user_id === auth()->id(), 403);

        $penugasan->load([
            'laporan.kategoriLaporan',
            'laporan.user',
            'laporan.wilayah',
            'laporan.mapLokasi',
            'laporan.ulasan',
            'penyelesaian',
            'penyelesaianTugas',
        ]);

        return view('petugas.tugas.show', compact('penugasan'));
    }

    public function updateStatus(Request $request, Penugasan $penugasan)
    {
        abort_unless($penugasan->user_id === auth()->id(), 403);

        $request->validate([
            'status_tugas' => 'required|in:Ditugaskan,Menuju Lokasi,Sedang Dikerjakan,Menunggu Konfirmasi,Selesai',
        ]);

        $penugasan->update([
            'status_tugas' => $request->input('status_tugas'),
        ]);

        if ($request->input('status_tugas') === 'Selesai') {
            $penugasan->laporan?->update([
                'status' => 'selesai',
            ]);
        }

        return back()->with('success', 'Status tugas berhasil diperbarui.');
    }

    public function uploadBukti(Request $request, Penugasan $penugasan)
    {
        abort_unless($penugasan->user_id === auth()->id(), 403);

        $request->validate([
            'foto_bukti' => 'nullable|image|max:5120',
            'keterangan' => 'nullable|string',
        ]);

        if ($request->hasFile('foto_bukti')) {
            $path = $request->file('foto_bukti')->store('penugasan-bukti', 'public');

            $penugasan->update([
                'foto_bukti' => $path,
                'status_tugas' => 'Menunggu Konfirmasi',
            ]);

            PenyelesaianTugas::query()->updateOrCreate(
                ['penugasan_id' => $penugasan->id],
                [
                    'foto_bukti' => $path,
                    'tanggal_selesai' => now()->toDateString(),
                    'keterangan' => $request->input('keterangan'),
                ]
            );

            return back()->with('success', 'Bukti tugas berhasil diunggah dan menunggu konfirmasi warga.');
        }

        return back()->with('error', 'Silakan pilih file bukti terlebih dahulu.');
    }

    private function getNotificationsForPetugas(int $petugasId): Collection
    {
        if (! Schema::hasTable('notification')) {
            return collect();
        }

        $requiredColumns = ['user_id', 'title', 'message', 'is_read', 'created_at'];

        foreach ($requiredColumns as $column) {
            if (! Schema::hasColumn('notification', $column)) {
                return collect();
            }
        }

        return Notification::query()
            ->where('user_id', $petugasId)
            ->latest()
            ->take(5)
            ->get();
    }
}
