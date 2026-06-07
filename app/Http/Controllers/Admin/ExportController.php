<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Exports\KinerjaExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    private function getKinerjaData()
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

        return $petugasQuery->sortByDesc(function ($petugas) {
            return ($petugas->rata_rata_rating * 1000) + $petugas->tugas_selesai_count;
        })->values();
    }

    public function kinerjaExcel(Request $request)
    {
        $petugasList = $this->getKinerjaData();

        if ($petugasList->isEmpty()) {
            return redirect()->route('admin.kinerja.index')->with('error', 'Data kinerja petugas tidak tersedia.');
        }

        $filename = 'kinerja-petugas-tirtabantu-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new KinerjaExport(), $filename);
    }

    public function kinerjaPdf(Request $request)
    {
        $petugasList = $this->getKinerjaData();

        if ($petugasList->isEmpty()) {
            return redirect()->route('admin.kinerja.index')->with('error', 'Data kinerja petugas tidak tersedia.');
        }

        $pdf = Pdf::loadView('exports.kinerja_pdf', [
            'petugasList'  => $petugasList,
            'total'        => $petugasList->count(),
            'tanggalExport'=> now()->format('d/m/Y H:i'),
            'exportedBy'   => auth()->user()->name ?? 'Admin',
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'dpi'                  => 150,
            'defaultFont'          => 'DejaVu Sans',
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
        ]);

        $filename = 'kinerja-petugas-tirtabantu-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }
}