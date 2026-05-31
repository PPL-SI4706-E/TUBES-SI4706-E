<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\LaporanExport;
use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Wilayah;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    /**
     * Ambil filter dari request (sama seperti query di LaporanController).
     */
    private function resolveFilters(Request $request): array
    {
        return [
            'keyword'      => $request->filled('keyword')      ? trim($request->string('keyword')->toString()) : null,
            'status_bayar' => $request->filled('status_bayar') ? $request->input('status_bayar') : null,
            'bulan_awal'   => $request->filled('bulan_awal')   ? $request->input('bulan_awal')   : null,
            'bulan_akhir'  => $request->filled('bulan_akhir')  ? $request->input('bulan_akhir')  : null,
            'wilayah_id'   => $request->filled('wilayah_id')   ? $request->input('wilayah_id')   : null,
            'kategori_id'  => $request->filled('kategori_id')  ? $request->input('kategori_id')  : null,
        ];
    }

    /**
     * Bangun query laporan dengan filter yang sama persis dengan LaporanController.
     */
    private function buildQuery(array $filters)
    {
        return Laporan::query()
            ->filterKeyword($filters['keyword'])
            ->filterStatusBayar($filters['status_bayar'])
            ->filterRentangBulan($filters['bulan_awal'], $filters['bulan_akhir'])
            ->filterWilayah(isset($filters['wilayah_id']) ? (int) $filters['wilayah_id'] : null)
            ->filterKategori(isset($filters['kategori_id']) ? (int) $filters['kategori_id'] : null)
            ->with(['kategoriLaporan', 'wilayah', 'user', 'pembayaran', 'penugasan'])
            ->latest();
    }

    /**
     * Bangun label filter yang tampil di PDF.
     */
    private function buildFilterLabels(array $filters): array
    {
        $labels = [];

        if ($filters['keyword']) {
            $labels['keyword'] = $filters['keyword'];
        }

        if ($filters['status_bayar']) {
            $map = [
                'lunas'                => 'Lunas',
                'belum_lunas'          => 'Belum Lunas',
                'menunggu_verifikasi'  => 'Menunggu Verifikasi',
            ];
            $labels['status_bayar'] = $map[$filters['status_bayar']] ?? $filters['status_bayar'];
        }

        if ($filters['bulan_awal']) {
            try {
                $labels['bulan_awal'] = Carbon::createFromFormat('Y-m', $filters['bulan_awal'])->format('M Y');
            } catch (\Throwable) {
                $labels['bulan_awal'] = $filters['bulan_awal'];
            }
        }

        if ($filters['bulan_akhir']) {
            try {
                $labels['bulan_akhir'] = Carbon::createFromFormat('Y-m', $filters['bulan_akhir'])->format('M Y');
            } catch (\Throwable) {
                $labels['bulan_akhir'] = $filters['bulan_akhir'];
            }
        }

        if ($filters['wilayah_id']) {
            $w = Wilayah::find((int) $filters['wilayah_id']);
            $labels['wilayah'] = $w?->nama_wilayah ?? 'Wilayah #' . $filters['wilayah_id'];
        }

        if ($filters['kategori_id']) {
            $k = KategoriLaporan::find((int) $filters['kategori_id']);
            $labels['kategori'] = $k?->nama_kategori ?? 'Kategori #' . $filters['kategori_id'];
        }

        return $labels;
    }

    // ──────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/laporan/export/excel
     * Export data laporan ke file Excel (.xlsx), mengikuti filter aktif.
     */
    public function exportExcel(Request $request)
    {
        $filters = $this->resolveFilters($request);

        // Validasi: jangan generate file kosong
        $count = $this->buildQuery($filters)->count();
        if ($count === 0) {
            return redirect()
                ->route('admin.laporan.index', array_filter($filters))
                ->with('error', 'Data laporan tidak tersedia. Tidak ada data yang dapat diekspor.');
        }

        $filename = 'laporan-tirtabantu-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new LaporanExport($filters), $filename);
    }

    /**
     * GET /admin/laporan/export/pdf
     * Export data laporan ke file PDF, mengikuti filter aktif.
     */
    public function exportPdf(Request $request)
    {
        $filters      = $this->resolveFilters($request);
        $laporans     = $this->buildQuery($filters)->get();

        // Validasi: jangan generate file kosong
        if ($laporans->isEmpty()) {
            return redirect()
                ->route('admin.laporan.index', array_filter($filters))
                ->with('error', 'Data laporan tidak tersedia. Tidak ada data yang dapat diekspor.');
        }

        $filterLabels = $this->buildFilterLabels($filters);
        $hasFilter    = count($filterLabels) > 0;

        $pdf = Pdf::loadView('exports.laporan_pdf', [
            'laporans'     => $laporans,
            'total'        => $laporans->count(),
            'tanggalExport'=> now()->format('d/m/Y H:i'),
            'exportedBy'   => auth()->user()->name ?? 'Admin',
            'filterLabels' => $filterLabels,
            'hasFilter'    => $hasFilter,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'dpi'                  => 150,
            'defaultFont'          => 'DejaVu Sans',
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
        ]);

        $filename = 'laporan-tirtabantu-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}
