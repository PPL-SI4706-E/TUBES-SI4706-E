<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Exports\PembayaranExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PaymentExportController extends Controller
{
    /**
     * GET /admin/pembayaran/export/excel
     */
    public function exportExcel(Request $request)
    {
        $pembayarans = Pembayaran::with(['laporan.kategoriLaporan', 'user'])->latest()->get();

        if ($pembayarans->isEmpty()) {
            return redirect()->route('admin.pembayaran.index')->with('error', 'Data pembayaran tidak tersedia. Tidak ada data yang dapat diekspor.');
        }

        $filename = 'pembayaran-tirtabantu-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new PembayaranExport(), $filename);
    }

    /**
     * GET /admin/pembayaran/export/pdf
     */
    public function exportPdf(Request $request)
    {
        $pembayarans = Pembayaran::with(['laporan.kategoriLaporan', 'user'])->latest()->get();

        if ($pembayarans->isEmpty()) {
            return redirect()->route('admin.pembayaran.index')->with('error', 'Data pembayaran tidak tersedia. Tidak ada data yang dapat diekspor.');
        }

        $pdf = Pdf::loadView('exports.pembayaran_pdf', [
            'pembayarans'  => $pembayarans,
            'total'        => $pembayarans->count(),
            'tanggalExport'=> now()->format('d/m/Y H:i'),
            'exportedBy'   => auth()->user()->name ?? 'Admin',
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'dpi'                  => 150,
            'defaultFont'          => 'DejaVu Sans',
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
        ]);

        $filename = 'pembayaran-tirtabantu-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }
}
