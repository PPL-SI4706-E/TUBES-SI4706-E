<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function laporan()  { return back()->with('success', 'TODO: Export laporan (Sprint 2)'); }
    public function kinerja()  { return back()->with('success', 'TODO: Export kinerja (Sprint 2)'); }
}