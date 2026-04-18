<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index()
    {
        return view('admin.pengumuman.index');
    }

    public function store(Request $request)   { return back(); }
    public function update(Request $r, $id)   { return back(); }
    public function destroy($id)              { return back(); }
}