<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        return view('public.home');
    }

    public function pengumumanDetail($id)
    {
        return view('public.pengumuman-detail', ['id' => $id]);
    }
}