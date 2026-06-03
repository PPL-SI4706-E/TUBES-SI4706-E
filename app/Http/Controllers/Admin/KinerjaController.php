<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class KinerjaController extends Controller
{
    public function index(Request $request)
    {
        $petugasQuery = User::where('role', 'petugas')
            ->with([
                'penugasanSebagaiPetugas' => function ($query) {
                    $query->with(['laporan', 'ulasan'])->orderByDesc('tanggal_penugasan');
                }
            ])
            ->withCount([
                'penugasanSebagaiPetugas as tugas_selesai_count' => function ($query) {
                    $query->where('status_tugas', 'Selesai');
                },
                'penugasanSebagaiPetugas as tugas_aktif_count' => function ($query) {
                    $query->where('status_tugas', '!=', 'Selesai');
                }
            ])
            ->get();

        foreach ($petugasQuery as $petugas) {
            $petugas->rata_rata_rating = round($petugas->average_rating ?? 0.0, 1);
        }

        $topPetugas = $petugasQuery
            ->sortByDesc(function ($petugas) {
                return ($petugas->rata_rata_rating * 1000) + $petugas->tugas_selesai_count;
            })
            ->take(3)
            ->values();

        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'desc');

        $validSortFields = ['name', 'tugas_selesai_count', 'rata_rata_rating'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'name';
        }

        if ($sortDir === 'desc') {
            $petugasList = $petugasQuery->sortByDesc($sortBy)->values();
        } else {
            $petugasList = $petugasQuery->sortBy($sortBy)->values();
        }

        return view('admin.kinerja.index', compact('petugasList', 'topPetugas', 'sortBy', 'sortDir'));
    }
}