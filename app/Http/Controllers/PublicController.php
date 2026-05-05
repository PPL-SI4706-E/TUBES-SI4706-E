<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\TestimoniPublik;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home(Request $request)
    {
        $pengumuman = Pengumuman::query()
            ->latest('tanggal_post')
            ->latest()
            ->take(5)
            ->get();

        $approvedTestimonials = TestimoniPublik::approved()
            ->latest('validated_at')
            ->latest()
            ->take(6)
            ->get();

        $activeTestimoni = null;
        $sessionTestimoniId = $request->session()->get(PublicTestimoniController::sessionKey());

        if ($sessionTestimoniId) {
            $activeTestimoni = TestimoniPublik::find($sessionTestimoniId);

            if ($activeTestimoni === null) {
                $request->session()->forget(PublicTestimoniController::sessionKey());
            }
        }

        return view('public.home', [
            'dbPengumuman' => $pengumuman,
            'approvedTestimonials' => $approvedTestimonials,
            'activeTestimoni' => $activeTestimoni,
        ]);
    }

    public function pengumumanDetail($id)
    {
        return view('public.pengumuman-detail', ['id' => $id]);
    }
}
