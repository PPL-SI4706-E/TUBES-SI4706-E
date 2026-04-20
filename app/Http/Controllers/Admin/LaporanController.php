<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        return view('admin.laporan.index');
    }

    public function peta()
    {
        $laporans = \App\Models\Laporan::with(['mapLokasi', 'wilayah', 'kategoriLaporan'])->get();
        return view('admin.laporan.peta', compact('laporans'));
    }

    public function show($laporan)
    {
        return view('admin.laporan.show', ['id' => $laporan]);
    }

    public function validasi(Request $request, $laporan)
    {
        return back()->with('success', 'TODO: implement validasi (Sprint 1)');
    }

    public function assign(Request $request, $laporan)
    {
        return back()->with('success', 'TODO: implement assign (Sprint 2)');
    }
}