<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;
use App\Notifications\LaporanStatusChanged;
use Illuminate\Support\Facades\Notification;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = Laporan::with(['user', 'kategoriLaporan', 'wilayah'])
                           ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                if (is_numeric($search)) {
                    $q->where('id', ltrim($search, '0'));
                } else {
                    $q->where('id', 'like', "%{$search}%");
                }
                $q->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $laporans = $query->get();
        return view('admin.laporan.index', compact('laporans'));
    }

    public function peta()
    {
        $laporans = Laporan::with(['mapLokasi', 'wilayah', 'kategoriLaporan'])->get();
        return view('admin.laporan.peta', compact('laporans'));
    }

    public function show($id)
    {
        $laporan = Laporan::with(['user', 'kategoriLaporan', 'mapLokasi', 'wilayah', 'validatedBy'])->findOrFail($id);
        return view('admin.laporan.show', compact('laporan'));
    }

    public function approveLapangan(Request $request, $id)
    {
        $laporan = Laporan::findOrFail($id);
        
        if ($laporan->status !== 'pending') {
            return back()->with('error', 'Laporan tidak dapat divalidasi karena status bukan Menunggu.');
        }

        $laporan->update([
            'status' => 'diterima',
            'jenis_penanganan' => 'lapangan',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        if ($laporan->user) {
            $laporan->user->notify(new LaporanStatusChanged($laporan, 'Laporan Anda telah divalidasi dan akan ditangani di lapangan.'));
        }

        return redirect()->route('admin.laporan.index')->with('success', 'Laporan berhasil disetujui untuk penanganan lapangan.');
    }

    public function approveVirtual(Request $request, $id)
    {
        $request->validate([
            'solusi' => 'required|string',
        ]);

        $laporan = Laporan::findOrFail($id);
        
        if ($laporan->status !== 'pending') {
            return back()->with('error', 'Laporan tidak dapat divalidasi karena status bukan Menunggu.');
        }

        $laporan->update([
            'status' => 'selesai',
            'jenis_penanganan' => 'virtual',
            'solusi' => $request->solusi,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        if ($laporan->user) {
            $laporan->user->notify(new LaporanStatusChanged($laporan, 'Laporan Anda telah diselesaikan dengan solusi virtual. Solusi: ' . $request->solusi));
        }

        return redirect()->route('admin.laporan.index')->with('success', 'Laporan berhasil diselesaikan dengan solusi virtual.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string',
        ]);

        $laporan = Laporan::findOrFail($id);
        
        if ($laporan->status !== 'pending') {
            return back()->with('error', 'Laporan tidak dapat divalidasi karena status bukan Menunggu.');
        }

        $laporan->update([
            'status' => 'ditolak',
            'alasan_penolakan' => $request->alasan_penolakan,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        if ($laporan->user) {
            $laporan->user->notify(new LaporanStatusChanged($laporan, 'Laporan Anda ditolak. Alasan: ' . $request->alasan_penolakan));
        }

        return redirect()->route('admin.laporan.index')->with('success', 'Laporan berhasil ditolak.');
    }

    public function assign(Request $request, $id)
    {
        return back()->with('success', 'TODO: implement assign (Sprint 2)');
    }
}