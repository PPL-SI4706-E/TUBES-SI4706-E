<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KinerjaController extends Controller
{
    public function index()
    {
        return view('admin.kinerja.index');
    }
}