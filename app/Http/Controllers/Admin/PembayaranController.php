<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index()
    {
        return view('admin.pembayaran.index');
    }

    public function store(Request $request)
    {
        return back()->with('success', 'TODO: implement store (Sprint 1)');
    }

    public function verify(Request $request, $pembayaran)
    {
        return back()->with('success', 'TODO: implement verify (Sprint 1)');
    }
}