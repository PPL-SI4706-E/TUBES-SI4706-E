<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        return view('warga.laporan.index');
    }

    public function create()
    {
        return view('warga.laporan.create');
    }

    public function show($laporan)
    {
        return view('warga.laporan.show', ['id' => $laporan]);
    }

    public function store(Request $r)         { return back()->with('success', 'TODO: Sprint 1'); }
    public function konfirmasi(Request $r, $id) { return back(); }
}