<?php

namespace App\Exports;

use App\Models\Laporan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class LaporanExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return Laporan::query()
            ->filterKeyword($this->filters['keyword'] ?? null)
            ->filterStatusBayar($this->filters['status_bayar'] ?? null)
            ->filterRentangBulan($this->filters['bulan_awal'] ?? null, $this->filters['bulan_akhir'] ?? null)
            ->filterWilayah(isset($this->filters['wilayah_id']) ? (int) $this->filters['wilayah_id'] : null)
            ->filterKategori(isset($this->filters['kategori_id']) ? (int) $this->filters['kategori_id'] : null)
            ->with(['kategoriLaporan', 'wilayah', 'user', 'penugasan'])
            ->latest();
    }

    public function headings(): array
    {
        return [
            'ID Laporan',
            'Nama Pelapor',
            'Kategori',
            'Wilayah',
            'Alamat',
            'Tanggal Lapor',
            'Status Laporan',
            'Status Pembayaran',
            'Turun Lapangan',
        ];
    }

    /**
     * @param Laporan $laporan
     */
    public function map($laporan): array
    {
        $statusMap = [
            'pending'    => 'Menunggu Validasi',
            'diterima'   => 'Diterima',
            'ditolak'    => 'Ditolak',
            'dikerjakan' => 'Sedang Dikerjakan',
            'selesai'    => 'Selesai',
        ];

        $turunLapangan = $laporan->penugasan ? 'Ya' : 'Tidak';

        return [
            '#' . $laporan->id,
            $laporan->user->name ?? '-',
            $laporan->kategoriLaporan->nama_kategori ?? '-',
            $laporan->wilayah->nama_wilayah ?? '-',
            $laporan->alamat ?? '-',
            optional($laporan->tanggal_lapor)->format('d/m/Y H:i') ?? '-',
            $statusMap[$laporan->status] ?? $laporan->status,
            $laporan->pembayaran->status_pembayaran ?? 'Belum Bayar',
            $turunLapangan,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row style
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0369A1'], // sky-700
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Data Laporan';
    }
}
