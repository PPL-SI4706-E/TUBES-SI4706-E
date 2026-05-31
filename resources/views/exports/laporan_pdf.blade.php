<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan TirtaBantu</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #ffffff;
        }

        /* ── Header ─────────────────────────────────────── */
        .header {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
            color: #ffffff;
            padding: 18px 24px;
            margin-bottom: 0;
            border-radius: 0;
        }

        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .header-logo {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
        }

        .header-logo .app-name {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .header-logo .app-subtitle {
            font-size: 10px;
            opacity: 0.85;
            margin-top: 2px;
        }

        .header-meta {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 50%;
        }

        .header-meta p {
            font-size: 10px;
            opacity: 0.85;
            margin-bottom: 2px;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 10px;
            margin-top: 8px;
        }

        /* ── Summary Bar ─────────────────────────────────── */
        .summary-bar {
            background: #f0f9ff;
            border-left: 4px solid #0369a1;
            padding: 10px 16px;
            margin: 16px 0;
            font-size: 11px;
            color: #0369a1;
            font-weight: bold;
        }

        .summary-bar .total {
            font-size: 18px;
            font-weight: bold;
        }

        /* ── Filter Info ─────────────────────────────────── */
        .filter-info {
            background: #fafafa;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 10px;
            color: #475569;
        }

        .filter-info .filter-label {
            font-weight: bold;
            color: #0369a1;
            margin-bottom: 4px;
        }

        .filter-tag {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 3px;
            padding: 1px 6px;
            margin: 2px;
            font-size: 9px;
        }

        /* ── Table ───────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }

        thead tr {
            background: #0369a1;
            color: #ffffff;
        }

        thead th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #0284c7;
        }

        tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr:hover {
            background: #f0f9ff;
        }

        tbody td {
            padding: 7px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .id-cell {
            font-weight: bold;
            color: #0369a1;
            white-space: nowrap;
        }

        /* ── Status Badges ───────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: 8.5px;
            font-weight: bold;
            white-space: nowrap;
        }

        .badge-selesai     { background: #d1fae5; color: #065f46; }
        .badge-diterima    { background: #dbeafe; color: #1e40af; }
        .badge-dikerjakan  { background: #cffafe; color: #155e75; }
        .badge-ditolak     { background: #fee2e2; color: #991b1b; }
        .badge-pending     { background: #fef3c7; color: #92400e; }
        .badge-lunas       { background: #d1fae5; color: #065f46; }
        .badge-belum       { background: #f1f5f9; color: #475569; }
        .badge-turun-ya    { background: #dbeafe; color: #1e40af; }
        .badge-turun-tidak { background: #f1f5f9; color: #64748b; }

        /* ── Footer ──────────────────────────────────────── */
        .footer {
            margin-top: 20px;
            border-top: 2px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
        }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-top">
            <div class="header-logo">
                <div class="app-name">💧 TirtaBantu</div>
                <div class="app-subtitle">Sistem Manajemen Laporan Air Bersih</div>
            </div>
            <div class="header-meta">
                <p>Tanggal Export: {{ $tanggalExport }}</p>
                <p>Dibuat oleh: {{ $exportedBy }}</p>
            </div>
        </div>
        <div class="report-title">📋 Rekap Data Laporan Masyarakat</div>
    </div>

    {{-- ── Summary Bar ─────────────────────────────────────────────── --}}
    <div class="summary-bar">
        Total Data: <span class="total">{{ $total }}</span> laporan
    </div>

    {{-- ── Filter Info ─────────────────────────────────────────────── --}}
    @if($hasFilter)
    <div class="filter-info">
        <div class="filter-label">Filter Aktif:</div>
        @if(!empty($filterLabels['keyword']))
            <span class="filter-tag">Pencarian: {{ $filterLabels['keyword'] }}</span>
        @endif
        @if(!empty($filterLabels['status_bayar']))
            <span class="filter-tag">Status Bayar: {{ $filterLabels['status_bayar'] }}</span>
        @endif
        @if(!empty($filterLabels['bulan_awal']))
            <span class="filter-tag">Dari: {{ $filterLabels['bulan_awal'] }}</span>
        @endif
        @if(!empty($filterLabels['bulan_akhir']))
            <span class="filter-tag">Sampai: {{ $filterLabels['bulan_akhir'] }}</span>
        @endif
        @if(!empty($filterLabels['wilayah']))
            <span class="filter-tag">Wilayah: {{ $filterLabels['wilayah'] }}</span>
        @endif
        @if(!empty($filterLabels['kategori']))
            <span class="filter-tag">Kategori: {{ $filterLabels['kategori'] }}</span>
        @endif
    </div>
    @endif

    {{-- ── Table ───────────────────────────────────────────────────── --}}
    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:6%">ID</th>
                <th style="width:13%">Nama Pelapor</th>
                <th style="width:10%">Kategori</th>
                <th style="width:9%">Wilayah</th>
                <th style="width:20%">Alamat</th>
                <th style="width:9%">Tanggal</th>
                <th style="width:10%">Status</th>
                <th style="width:10%">Pembayaran</th>
                <th style="width:9%">Turun</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporans as $index => $laporan)
                @php
                    $statusMap = [
                        'pending'    => ['badge-pending',    'Menunggu'],
                        'diterima'   => ['badge-diterima',   'Diterima'],
                        'ditolak'    => ['badge-ditolak',    'Ditolak'],
                        'dikerjakan' => ['badge-dikerjakan', 'Dikerjakan'],
                        'selesai'    => ['badge-selesai',    'Selesai'],
                    ];
                    [$statusClass, $statusLabel] = $statusMap[$laporan->status] ?? ['badge-pending', $laporan->status];

                    $paymentStatus = $laporan->pembayaran->status_pembayaran ?? null;
                    $paymentMap = [
                        'Lunas'        => ['badge-lunas', 'Lunas'],
                        'Terverifikasi'=> ['badge-pending', 'Menunggu'],
                        'Menunggu'     => ['badge-belum', 'Belum'],
                        'Ditolak'      => ['badge-ditolak', 'Ditolak'],
                        'Kadaluarsa'   => ['badge-ditolak', 'Expired'],
                    ];
                    [$paymentClass, $paymentLabel] = $paymentMap[$paymentStatus] ?? ['badge-belum', 'Belum'];

                    $turunLapangan = $laporan->penugasan ? true : false;
                @endphp
                <tr>
                    <td style="text-align:center">{{ $index + 1 }}</td>
                    <td class="id-cell">#{{ $laporan->id }}</td>
                    <td>{{ $laporan->user->name ?? '-' }}</td>
                    <td>{{ $laporan->kategoriLaporan->nama_kategori ?? '-' }}</td>
                    <td>{{ $laporan->wilayah->nama_wilayah ?? '-' }}</td>
                    <td>{{ $laporan->alamat ?? '-' }}</td>
                    <td>{{ optional($laporan->tanggal_lapor)->format('d/m/Y') ?? '-' }}</td>
                    <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                    <td><span class="badge {{ $paymentClass }}">{{ $paymentLabel }}</span></td>
                    <td>
                        @if($turunLapangan)
                            <span class="badge badge-turun-ya">Ya</span>
                        @else
                            <span class="badge badge-turun-tidak">Tidak</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding: 20px; color: #94a3b8;">
                        Tidak ada data laporan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Footer ───────────────────────────────────────────────────── --}}
    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem TirtaBantu &mdash; {{ $tanggalExport }}</p>
        <p>Halaman ini bersifat rahasia dan hanya untuk keperluan internal.</p>
    </div>

</body>
</html>
