<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index()
    {
        return view('warga.pembayaran.index');
    }

    public function uploadBukti(Request $r, $id) { return back(); }
}