<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\Penugasan;
use App\Models\PenyelesaianTugas;
use App\Models\User;
use App\Notifications\TaskProgressNotification;
use App\Notifications\AdminSystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    // ── STATUS ORDER ───────────────────────────────────────────────────────────
    private const STATUS_ORDER = [
        'Ditugaskan',
        'Menuju Lokasi',
        'Sedang Dikerjakan',
        'Menunggu Konfirmasi',
        'Selesai',
    ];

    // ── INDEX — Daftar Tugas ───────────────────────────────────────────────────
    public function index()
    {
        $petugasId = auth()->id();

        $tugas_aktif = Penugasan::with(['laporan.kategoriLaporan', 'laporan.user', 'laporan.mapLokasi'])
            ->where('user_id', $petugasId)
            ->whereNotIn('status_tugas', ['Selesai'])
            ->latest()
            ->get();

        $riwayat = Penugasan::with([
            'laporan.kategoriLaporan',
            'laporan.user',
            'laporan.mapLokasi',
            'penyelesaian',
            'ulasan',
        ])
            ->where('user_id', $petugasId)
            ->where('status_tugas', 'Selesai')
            ->latest('updated_at')
            ->get();

        return view('petugas.tugas.index', compact('tugas_aktif', 'riwayat'));
    }

    // ── SHOW — Detail Tugas ────────────────────────────────────────────────────
    public function show($id)
    {
        $penugasan = Penugasan::with([
            'laporan.kategoriLaporan',
            'laporan.user',
            'laporan.mapLokasi',
            'laporan.wilayah',
            'penyelesaian',
            'ulasan',
        ])->where('user_id', auth()->id())->findOrFail($id);

        $statusOrder = self::STATUS_ORDER;

        return view('petugas.tugas.show', compact('penugasan', 'statusOrder'));
    }

    // ── UPDATE STATUS ──────────────────────────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $penugasan = Penugasan::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'status_tugas' => 'required|in:Ditugaskan,Menuju Lokasi,Sedang Dikerjakan,Menunggu Konfirmasi,Selesai',
        ], [
            'status_tugas.required' => 'Status wajib dipilih.',
            'status_tugas.in'       => 'Status tidak valid.',
        ]);

        // Pastikan hanya bisa maju (tidak bisa mundur)
        $currentIndex = array_search($penugasan->status_tugas, self::STATUS_ORDER);
        $newIndex     = array_search($request->status_tugas, self::STATUS_ORDER);

        if ($newIndex <= $currentIndex) {
            return back()->with('error', 'Status tidak dapat dikembalikan ke status sebelumnya.');
        }

        // Upload bukti wajib saat menuju Menunggu Konfirmasi atau Selesai
        if (in_array($request->status_tugas, ['Menunggu Konfirmasi', 'Selesai'])) {
            return back()->with('error', 'Gunakan form upload bukti untuk menyelesaikan tugas.');
        }

        $penugasan->status_tugas = $request->status_tugas;
        $penugasan->save();

        // PBI-18: Kirim notifikasi progres ke Warga pembuat laporan
        $penugasan->load('laporan.user');
        if ($penugasan->laporan && $penugasan->laporan->user) {
            $penugasan->laporan->user->notify(new TaskProgressNotification($penugasan, $request->status_tugas));
        }

        return back()->with('success', "Status berhasil diperbarui menjadi \"{$request->status_tugas}\".");
    }

    // ── UPLOAD BUKTI ───────────────────────────────────────────────────────────
    public function uploadBukti(Request $request, $id)
    {
        $penugasan = Penugasan::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'foto_bukti'  => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'keterangan'  => 'nullable|string|max:1000',
        ], [
            'foto_bukti.required' => 'Foto bukti wajib diupload.',
            'foto_bukti.image'    => 'File harus berupa gambar.',
            'foto_bukti.mimes'    => 'Format foto harus JPG, JPEG, atau PNG.',
            'foto_bukti.max'      => 'Ukuran foto maksimal 5MB.',
        ]);

        // Simpan foto
        $path = $request->file('foto_bukti')->store('bukti-penyelesaian', 'public');

        // Simpan ke penyelesaian_tugas
        PenyelesaianTugas::create([
            'penugasan_id'    => $penugasan->id,
            'foto_bukti'      => $path,
            'tanggal_selesai' => now()->toDateString(),
            'keterangan'      => $request->keterangan,
        ]);

        // Update status penugasan → Menunggu Konfirmasi
        $penugasan->update(['status_tugas' => 'Menunggu Konfirmasi']);

        // Update status laporan SPESIFIK berdasarkan laporan_id (bukan update massal)
        Laporan::where('id', $penugasan->laporan_id)
            ->update(['status' => 'menunggu_konfirmasi']);

        // PBI-18: Kirim notifikasi 'selesai/butuh konfirmasi' ke Warga pembuat laporan
        $penugasan->load('laporan.user');
        if ($penugasan->laporan && $penugasan->laporan->user) {
            $penugasan->laporan->user->notify(new TaskProgressNotification($penugasan, 'Selesai (Menunggu Konfirmasi)', true));
        }

        // PBI-18: Notifikasi ke Admin bahwa Petugas telah menyelesaikan tugas
        $admins = User::where('role', 'admin')->get();
        \Illuminate\Support\Facades\Notification::send($admins, new AdminSystemNotification(
            'Tugas Diselesaikan Petugas',
            "Petugas " . auth()->user()->name . " telah menyelesaikan laporan #{$penugasan->laporan_id} dan mengunggah bukti.",
            route('admin.laporan.show', $penugasan->laporan_id),
            'success'
        ));

        return redirect()
            ->route('petugas.tugas.show', $penugasan->id)
            ->with('success', 'Bukti penyelesaian berhasil diupload. Menunggu konfirmasi dari warga.');
    }
}