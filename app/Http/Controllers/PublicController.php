<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $pengumuman = Pengumuman::query()
            ->where('is_published', true)
            ->latest('tanggal_post')
            ->latest('created_at')
            ->get();

        $featuredPengumuman = $pengumuman->firstWhere('priority', 'penting') ?? $pengumuman->first();
        $pengumumanLainnya = $featuredPengumuman
            ? $pengumuman->where('id', '!=', $featuredPengumuman->id)->values()
            : collect();

        return view('public.home', compact('featuredPengumuman', 'pengumumanLainnya'));
    }

    public function pengumumanDetail($id): View
    {
        $pengumuman = Pengumuman::query()
            ->where('is_published', true)
            ->findOrFail($id);

        return view('public.pengumuman-detail', compact('pengumuman'));
    }
}
