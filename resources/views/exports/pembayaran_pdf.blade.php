<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Export Pembayaran TirtaBantu</title>
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
            margin-bottom: 3px;
            font-size: 10px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.2);
            text-align: center;
        }

        /* ── Summary & Filter ────────────────────────────── */
        .summary-bar {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 24px;
            font-size: 11px;
            color: #475569;
        }

        .summary-bar .total {
            font-weight: bold;
            color: #0369a1;
        }

        /* ── Table ───────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        thead {
            background: #f1f5f9;
        }

        thead th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            color: #475569;
            text-transform: uppercase;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            border-right: 1px solid #e2e8f0;
            border-left: 1px solid #e2e8f0;
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

        .badge-lunas       { background: #d1fae5; color: #065f46; }
        .badge-pending     { background: #fef3c7; color: #92400e; }
        .badge-belum       { background: #f1f5f9; color: #475569; }
        .badge-ditolak     { background: #fee2e2; color: #991b1b; }

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
        <div class="report-title">💰 Rekap Data Pembayaran</div>
    </div>

    <div class="summary-bar">
        Total Data: <span class="total">{{ $total }}</span> transaksi pembayaran
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:10%">ID Transaksi</th>
                <th style="width:10%">ID Laporan</th>
                <th style="width:20%">Nama Pelanggan</th>
                <th style="width:20%">Kategori Laporan</th>
                <th style="width:15%">Nominal (Rp)</th>
                <th style="width:10%">Status</th>
                <th style="width:10%">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembayarans as $index => $pembayaran)
                @php
                    $paymentStatus = $pembayaran->status_pembayaran ?? null;
                    $paymentMap = [
                        'Lunas'        => ['badge-lunas', 'Lunas'],
                        'Terverifikasi'=> ['badge-pending', 'Menunggu'],
                        'Menunggu'     => ['badge-belum', 'Belum'],
                        'Ditolak'      => ['badge-ditolak', 'Ditolak'],
                        'Kadaluarsa'   => ['badge-ditolak', 'Expired'],
                    ];
                    [$paymentClass, $paymentLabel] = $paymentMap[$paymentStatus] ?? ['badge-belum', 'Belum'];
                @endphp
                <tr>
                    <td style="text-align:center">{{ $index + 1 }}</td>
                    <td class="id-cell">#{{ $pembayaran->id }}</td>
                    <td class="id-cell">#{{ $pembayaran->laporan_id ?? '-' }}</td>
                    <td>{{ $pembayaran->user->name ?? '-' }}</td>
                    <td>{{ $pembayaran->laporan->kategoriLaporan->nama_kategori ?? '-' }}</td>
                    <td>{{ number_format($pembayaran->harga, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $paymentClass }}">{{ $paymentLabel }}</span></td>
                    <td>{{ $pembayaran->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding: 20px; color: #94a3b8;">
                        Tidak ada data pembayaran.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem TirtaBantu &mdash; {{ $tanggalExport }}</p>
        <p>Halaman ini bersifat rahasia dan hanya untuk keperluan internal.</p>
    </div>

</body>
</html>
