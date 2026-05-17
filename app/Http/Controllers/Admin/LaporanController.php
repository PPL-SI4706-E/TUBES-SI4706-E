<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Notification;
use App\Models\Penugasan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'keyword' => $request->input('keyword', $request->input('search')),
            'status_bayar' => $request->input('status_bayar'),
            'bulan_awal' => $request->input('bulan_awal'),
            'bulan_akhir' => $request->input('bulan_akhir'),
            'wilayah_id' => $request->input('wilayah_id'),
            'kategori_id' => $request->input('kategori_id'),
            'status' => $request->input('status'),
        ];

        $laporans = Laporan::query()
            ->filterKeyword($filters['keyword'] ?? null)
            ->filterStatusBayar($filters['status_bayar'] ?? null)
            ->filterRentangBulan($filters['bulan_awal'] ?? null, $filters['bulan_akhir'] ?? null)
            ->filterWilayah($filters['wilayah_id'] ?? null)
            ->filterKategori($filters['kategori_id'] ?? null)
            ->when(filled($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->with(['kategoriLaporan', 'wilayah', 'user', 'pembayaran'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $wilayahs = Wilayah::query()->orderBy('nama_wilayah')->get();
        $kategoris = KategoriLaporan::query()->orderBy('nama_kategori')->get();

        return view('admin.laporan.index', compact('laporans', 'wilayahs', 'kategoris'));
    }

    public function peta()
    {
        $laporans = Laporan::with(['mapLokasi', 'wilayah', 'kategoriLaporan'])->get();

        return view('admin.laporan.peta', compact('laporans'));
    }

    public function show($id)
    {
        $laporan = Laporan::with(['kategoriLaporan', 'wilayah', 'user', 'mapLokasi', 'penugasan.petugas'])->findOrFail($id);

        return view('admin.laporan.show', compact('laporan'));
    }

    public function validasi(Request $request, $id)
    {
        $request->validate([
            'aksi' => 'required|in:terima_lapangan,solusi_virtual,tolak',
            'catatan_admin' => 'nullable|string|min:5',
        ], [
            'aksi.required' => 'Aksi validasi harus dipilih.',
            'aksi.in' => 'Aksi tidak valid.',
            'catatan_admin.min' => 'Catatan minimal 5 karakter.',
        ]);

        $laporan = Laporan::with(['kategoriLaporan', 'wilayah', 'user', 'mapLokasi'])->findOrFail($id);

        if ($laporan->status !== 'pending') {
            return redirect()->route('admin.laporan.show', $laporan->id)
                ->with('error', 'Laporan ini sudah pernah divalidasi.');
        }

        $aksi = $request->string('aksi')->toString();
        $catatanAdmin = trim((string) $request->input('catatan_admin'));

        if (in_array($aksi, ['solusi_virtual', 'tolak'], true) && $catatanAdmin === '') {
            return redirect()
                ->route('admin.laporan.show', $laporan->id)
                ->withErrors(['catatan_admin' => 'Catatan wajib diisi untuk aksi ini.'])
                ->withInput();
        }

        $pesan = DB::transaction(function () use ($aksi, $catatanAdmin, $laporan) {
            return match ($aksi) {
                'terima_lapangan' => $this->terimaPenangananLapangan($laporan, $catatanAdmin),
                'solusi_virtual' => $this->selesaikanDenganSolusiVirtual($laporan, $catatanAdmin),
                'tolak' => $this->tolakLaporan($laporan, $catatanAdmin),
            };
        });

        return redirect()->route('admin.laporan.show', $laporan->id)
            ->with('success', $pesan);
    }

    public function assign(Request $request, $laporan)
    {
        return back()->with('success', 'Penugasan petugas sudah ditangani dari proses validasi laporan.');
    }

    private function terimaPenangananLapangan(Laporan $laporan, string $catatanAdmin): string
    {
        $laporan->update([
            'status' => 'diterima',
            'catatan_admin' => $catatanAdmin !== '' ? $catatanAdmin : null,
        ]);

        $petugas = User::query()
            ->where('role', 'petugas')
            ->where('is_active', true)
            ->orderByRaw('CASE WHEN wilayah_id = ? THEN 0 ELSE 1 END', [$laporan->wilayah_id])
            ->orderBy('id')
            ->first();

        if (! $petugas) {
            $petugas = User::query()->create([
                'name' => 'Budi Hartono',
                'email' => 'budi.hartono.' . now()->timestamp . '@tirtabantu.local',
                'password' => bcrypt('password'),
                'role' => 'petugas',
                'phone' => '081200000002',
                'is_active' => true,
                'wilayah_id' => $laporan->wilayah_id,
            ]);
        }

        Penugasan::query()->updateOrCreate(
            ['laporan_id' => $laporan->id],
            [
                'user_id' => $petugas->id,
                'tanggal_penugasan' => now()->toDateString(),
                'status_tugas' => 'Ditugaskan',
                'foto_bukti' => null,
            ]
        );

        $this->createNotificationIfSupported($petugas->id, $laporan);

        return 'Laporan diterima dan petugas lapangan berhasil ditugaskan.';
    }

    private function selesaikanDenganSolusiVirtual(Laporan $laporan, string $catatanAdmin): string
    {
        $laporan->update([
            'status' => 'selesai',
            'catatan_admin' => $catatanAdmin !== '' ? $catatanAdmin : null,
        ]);

        Penugasan::query()->where('laporan_id', $laporan->id)->delete();

        return 'Laporan diselesaikan dengan solusi virtual.';
    }

    private function tolakLaporan(Laporan $laporan, string $catatanAdmin): string
    {
        $laporan->update([
            'status' => 'ditolak',
            'catatan_admin' => $catatanAdmin !== '' ? $catatanAdmin : null,
        ]);

        Penugasan::query()->where('laporan_id', $laporan->id)->delete();

        return 'Laporan berhasil ditolak.';
    }

    private function createNotificationIfSupported(int $petugasId, Laporan $laporan): void
    {
        if (! Schema::hasTable('notification')) {
            return;
        }

        $requiredColumns = ['user_id', 'title', 'message', 'is_read'];

        foreach ($requiredColumns as $column) {
            if (! Schema::hasColumn('notification', $column)) {
                return;
            }
        }

        $payload = [
            'user_id' => $petugasId,
            'title' => 'Tugas Baru',
            'message' => sprintf(
                'Anda ditugaskan ke Laporan #%d (%s) di %s.',
                $laporan->id,
                $laporan->kategoriLaporan->nama_kategori ?? 'Laporan',
                $laporan->alamat ?? 'alamat belum tersedia'
            ),
            'is_read' => false,
        ];

        if (Schema::hasColumn('notification', 'data')) {
            $payload['data'] = [
                'laporan_id' => $laporan->id,
                'kategori' => $laporan->kategoriLaporan->nama_kategori ?? null,
                'alamat' => $laporan->alamat,
            ];
        }

        Notification::query()->create($payload);
    }
}
