<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KinerjaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
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

    public function headings(): array
    {
        return [
            'ID Petugas',
            'Nama Petugas',
            'Email',
            'Jumlah Tugas Selesai',
            'Jumlah Tugas Aktif',
            'Rata-rata Rating',
        ];
    }

    public function map($petugas): array
    {
        return [
            $petugas->id,
            $petugas->name,
            $petugas->email,
            $petugas->tugas_selesai_count,
            $petugas->tugas_aktif_count,
            $petugas->rata_rata_rating > 0 ? $petugas->rata_rata_rating : '0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
