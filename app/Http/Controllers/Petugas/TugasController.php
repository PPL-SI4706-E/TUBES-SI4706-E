<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TugasController extends Controller
{
    public function index()
    {
        return view('petugas.tugas.index');
    }

    public function show($penugasan)
    {
        return view('petugas.tugas.show', ['id' => $penugasan]);
    }

    public function updateStatus(Request $r, $id) { return back(); }
    public function uploadBukti(Request $r, $id)  { return back(); }
}