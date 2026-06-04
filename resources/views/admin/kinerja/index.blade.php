@extends('layouts.admin')

@section('content')
<h1>Kinerja Petugas</h1>

<table class="min-w-full border border-gray-200">
    <thead>
        <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">Nama</th>
            <th class="px-4 py-2 text-left">
                <a href="{{ request()->fullUrlWithQuery([
                    'sort_by' => 'tugas_selesai_count',
                    'sort_dir' => 'desc',
                ]) }}">
                    Jumlah Tugas Selesai
                </a>
            </th>
            <th class="px-4 py-2 text-left">Rating Rata‑Rata</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($petugasList as $petugas)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $petugas->name }}</td>
                <td class="px-4 py-2">{{ $petugas->tugas_selesai_count ?? 0 }}</td>
                <td class="px-4 py-2">
                    {{ number_format($petugas->rata_rata_rating ?? $petugas->average_rating ?? 0, 1) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="px-4 py-2 text-center text-gray-500">Belum ada data kinerja petugas</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
