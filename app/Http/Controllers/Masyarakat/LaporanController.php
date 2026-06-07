<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\MapLokasi;
use App\Models\KategoriLaporan;
use App\Models\Wilayah;
use App\Models\User;
use App\Notifications\TaskConfirmedNotification;
use App\Notifications\NewReportNotification;
use App\Notifications\AdminSystemNotification;
use App\Notifications\GeneralSystemNotification;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    public function index()
    {
        $laporans = Laporan::with(['kategoriLaporan', 'mapLokasi', 'ulasan'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
        return view('warga.laporan.index', compact('laporans'));
    }

    public function create()
    {
        $kategoris = KategoriLaporan::all();
        $wilayahs = Wilayah::all();
        return view('warga.laporan.create', compact('kategoris', 'wilayahs'));
    }

    public function show($id)
    {
        $laporan = Laporan::with(['mapLokasi', 'kategoriLaporan', 'wilayah', 'penugasan.penyelesaian', 'penugasan.petugas', 'pembayaran', 'ulasan'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('warga.laporan.show', compact('laporan'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'kategori_laporan_id' => 'required|exists:kategori_laporan,id',
            'wilayah_id'          => 'required|exists:wilayah,id',
            'deskripsi'           => 'required|string|min:10',
            'alamat'              => 'nullable|string|max:255',
            'map_marked'          => 'required|in:1',          // wajib klik peta
            'latitude'            => 'required|numeric',
            'longitude'           => 'required|numeric',
            'foto'                => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'kategori_laporan_id.required' => 'Kategori masalah wajib dipilih.',
            'wilayah_id.required'          => 'Wilayah wajib dipilih.',
            'deskripsi.required'           => 'Deskripsi masalah wajib diisi.',
            'deskripsi.min'                => 'Deskripsi masalah minimal 10 karakter.',
            'map_marked.required'          => 'Titik lokasi wajib ditentukan di peta.',
            'map_marked.in'                => 'Titik lokasi wajib ditentukan di peta.',
            'foto.image'                   => 'File yang diunggah harus berupa foto (JPG/PNG).',
            'foto.mimes'                   => 'Format foto tidak didukung. Gunakan JPG atau PNG.',
            'foto.max'                      => 'Ukuran foto maksimal 5MB.',
        ]);

        $fotoPath = null;
        if ($r->hasFile('foto')) {
            $fotoPath = $r->file('foto')->store('laporans', 'public');
        }

        $kategori = KategoriLaporan::findOrFail($data['kategori_laporan_id']);

        $laporan = Laporan::create([
            'user_id' => auth()->id(),
            'wilayah_id' => $data['wilayah_id'],
            'kategori_laporan_id' => $data['kategori_laporan_id'],
            'judul' => $kategori->nama_kategori . ' - ' . now()->format('d/m/Y H:i'),
            'deskripsi' => $data['deskripsi'],
            'alamat' => $data['alamat'] ?? '',
            'foto' => $fotoPath,
            'status' => 'pending',
            'tanggal_lapor' => now()
        ]);

        MapLokasi::create([
            'laporan_id' => $laporan->id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        // AUTOMATIC PAYMENT CREATION (Sinkronisasi Otomatis)
        $tarif = (float) $kategori->tarif;
        \App\Models\Pembayaran::create([
            'laporan_id' => $laporan->id,
            'user_id' => auth()->id(),
            'harga' => $tarif,
            'metode_pembayaran' => $tarif <= 0 ? 'Sistem (Gratis)' : null,
            'status_pembayaran' => $tarif <= 0 ? 'Lunas' : 'Menunggu',
        ]);

        // PBI-18: Kirim notifikasi laporan baru ke semua Admin
        $admins = User::where('role', 'admin')->get();
        \Illuminate\Support\Facades\Notification::send($admins, new NewReportNotification($laporan));

        return redirect()->route('warga.laporan.index')->with('success', 'Laporan berhasil dibuat! Silakan cek menu Pembayaran untuk melunasi tagihan.');
    }

    public function konfirmasi(Request $r, $id)
    {
        $laporan = Laporan::with(['penugasan.penyelesaian', 'ulasan'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        // Hanya boleh konfirmasi jika status menunggu_konfirmasi
        if ($laporan->status !== 'menunggu_konfirmasi') {
            return back()->with('error', 'Laporan ini tidak dalam status menunggu konfirmasi.');
        }

        // Cegah double-submit ulasan
        if ($laporan->ulasan) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk laporan ini.');
        }

        $r->validate([
            'action' => 'required|in:selesai,revisi',
        ]);

        if ($r->action === 'revisi') {
            $r->validate([
                'komentar' => 'required|string|max:1000',
            ], [
                'komentar.required' => 'Mohon berikan alasan revisi (komentar).',
            ]);

            // Hapus bukti penyelesaian jika ada
            if ($laporan->penugasan && $laporan->penugasan->penyelesaian) {
                if ($laporan->penugasan->penyelesaian->foto_bukti) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($laporan->penugasan->penyelesaian->foto_bukti);
                }
                $laporan->penugasan->penyelesaian()->delete();
            }

            // Update status Laporan ke dikerjakan
            $laporan->update(['status' => 'dikerjakan']);

            // Update status Penugasan kembali ke Sedang Dikerjakan & tambahkan catatan
            if ($laporan->penugasan) {
                $catatanBaru = $laporan->penugasan->catatan_admin;
                $catatanBaru .= "\n\n--- Revisi dari Warga (" . now()->format('d/m/Y H:i') . ") ---\n" . $r->komentar;
                
                $laporan->penugasan->update([
                    'status_tugas' => 'Sedang Dikerjakan',
                    'catatan_admin' => trim($catatanBaru),
                ]);

                // PBI-18: Kirim notif revisi ke Petugas
                if ($laporan->penugasan->petugas) {
                    $laporan->penugasan->petugas->notify(new TaskConfirmedNotification($laporan, true));
                }
            }

            return back()->with('success', 'Permintaan revisi telah dikirim ke petugas. Laporan kembali ke status Sedang Dikerjakan.');
        }

        // Logika untuk action == 'selesai'
        $r->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Mohon berikan rating bintang.',
        ]);

        // Simpan Ulasan
        \App\Models\Ulasan::create([
            'laporan_id'     => $laporan->id,
            'user_id'        => auth()->id(),
            'rating'         => $r->rating,
            'komentar'       => $r->komentar,
            'tanggal_ulasan' => now()->toDateString(),
        ]);

        // Simpan ke Testimoni Publik jika ada komentar agar Admin bisa me-review dan menampilkannya di Landing Page
        if (!empty($r->komentar)) {
            \App\Models\TestimoniPublik::create([
                'nama' => auth()->user()->name,
                'email' => auth()->user()->email,
                'rating' => $r->rating,
                'pesan' => $r->komentar,
                'status' => 'pending',
                'session_token' => \Illuminate\Support\Str::random(40),
                'editable_until' => now()->addMinutes(5),
            ]);
        }

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
        if ($r->rating >= 4 && $laporan->penugasan && $laporan->penugasan->petugas) {
            $laporan->penugasan->petugas->notify(new GeneralSystemNotification(
                'Kerja Bagus!',
                "Anda mendapat rating {$r->rating} Bintang untuk Laporan #{$laporan->id} dari Warga.",
                route('petugas.tugas.show', $laporan->penugasan->id),
                'success'
            ));
        }

        // Update status Laporan → selesai (spesifik berdasarkan ID)
        Laporan::where('id', $laporan->id)
            ->update(['status' => 'selesai']);

        // Update status Penugasan → Selesai
        if ($laporan->penugasan) {
            $laporan->penugasan->update(['status_tugas' => 'Selesai']);

            // PBI-18: Kirim notif konfirmasi ke Petugas
            if ($laporan->penugasan->petugas) {
                $laporan->penugasan->petugas->notify(new TaskConfirmedNotification($laporan, false));
            }
        }

        return back()->with('success', 'Terima kasih! Konfirmasi dan ulasan Anda telah tersimpan. Laporan dinyatakan selesai.');
    }
}