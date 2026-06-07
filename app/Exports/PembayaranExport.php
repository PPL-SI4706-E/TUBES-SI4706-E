<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembayaranExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Pembayaran::with(['laporan.kategoriLaporan', 'user'])->latest()->get();
    }

    public function headings(): array
    {
        return [
            'ID Pembayaran',
            'ID Laporan',
            'Nama Pelanggan',
            'Kategori Laporan',
            'Nominal (Rp)',
            'Metode Pembayaran',
            'Status Pembayaran',
            'Tanggal Dibuat',
        ];
    }

    public function map($pembayaran): array
    {
        return [
            $pembayaran->id,
            $pembayaran->laporan_id ?? '-',
            $pembayaran->user->name ?? 'Anonim',
            $pembayaran->laporan->kategoriLaporan->nama_kategori ?? '-',
            $pembayaran->harga,
            $pembayaran->metode_pembayaran ?? '-',
            $pembayaran->status_pembayaran,
            $pembayaran->created_at->format('d/m/Y H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
