<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Anggaran</title>
    <style>
        @page {
            size: A4;
            margin: 14mm 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .no-print {
            margin-bottom: 12px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }

        .toolbar button {
            padding: 6px 10px;
            border: 1px solid #333;
            background: #f5f5f5;
            cursor: pointer;
            margin-right: 6px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header .title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: .5px;
            margin-bottom: 4px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #333;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 10px 0 12px 0;
            font-size: 11px;
        }

        .meta .box {
            border: 1px solid #000;
            padding: 8px;
            flex: 1;
        }

        .meta .box b {
            display: inline-block;
            min-width: 80px;
        }

        .kpi {
            margin: 6px 0 12px 0;
            font-size: 11px;
        }

        .kpi .pill {
            display: inline-block;
            border: 1px solid #000;
            padding: 6px 10px;
            margin-right: 8px;
        }

        hr.sep {
            border: none;
            border-top: 1px solid #000;
            margin: 10px 0;
        }

        .section-title {
            font-weight: 700;
            margin: 12px 0 6px 0;
            text-transform: uppercase;
            letter-spacing: .4px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #eee;
            font-weight: 700;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .nowrap {
            white-space: nowrap;
        }

        .page-break {
            page-break-after: always;
        }

        .footer {
            margin-top: 10px;
            font-size: 10px;
            color: #333;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>

    <div class="no-print toolbar">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Tutup</button>
    </div>

    @php
        $jenis = $jenis ?? 'all';

        $jenisLabel =
            [
                'detail' => 'DETAIL ANGGARAN',
                'rekap' => 'REKAP ANGGARAN',
                'all' => 'SEMUA',
            ][$jenis] ?? 'SEMUA';
    @endphp

    <div class="header">
        <div class="title">REKAP ANGGARAN</div>
        <div class="subtitle">Mode Cetak: <b>{{ $jenisLabel }}</b></div>
    </div>

    <div class="meta">
        <div class="box">
            <div><b>Tahun</b>: {{ $filterInfo['tahun'] ?: 'Semua' }}</div>
            <div><b>Bulan</b>: {{ $filterInfo['bulan'] ?: 'Semua' }}</div>
            <div><b>MAK</b>: {{ $filterInfo['mak'] ?: 'Semua' }}</div>
        </div>
        <div class="box">
            <div><b>Petugas</b>: {{ $filterInfo['petugas'] ?: 'Semua' }}</div>
            <div><b>Status</b>: {{ $filterInfo['status'] ?: 'Semua' }}</div>
            <div><b>Keyword</b>: {{ $filterInfo['keyword'] ?: '-' }}</div>
            <div><b>Tanggal Cetak</b>: {{ now()->format('d-m-Y H:i') }}</div>
        </div>
    </div>

    <div class="kpi">
        <span class="pill"><b>Total SPT:</b> {{ number_format($totalSpt ?? 0) }}</span>
        <span class="pill"><b>Total Biaya:</b> Rp {{ number_format($totalBiaya ?? 0, 0, ',', '.') }}</span>
    </div>

    <hr class="sep">

    @if ($jenis === 'rekap' || $jenis === 'all')
        <div class="section-title">Rekap Anggaran</div>
        <table>
            <thead>
                <tr>
                    <th class="nowrap" style="width:40px;">No</th>
                    <th>MAK</th>
                    <th>Status Anggaran</th>
                    <th class="nowrap right" style="width:120px;">Jumlah SPT</th>
                    <th class="nowrap right" style="width:140px;">Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapAnggaran as $i => $ra)
                    <tr>
                        <td class="center nowrap">{{ $i + 1 }}</td>
                        <td>{{ $ra['mak'] ?? '-' }}</td>
                        <td>{{ $ra['status'] ?? '-' }}</td>
                        <td class="right nowrap">{{ number_format($ra['jumlah_spt'] ?? 0) }}</td>
                        <td class="right nowrap">Rp {{ number_format($ra['total_biaya'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center">Tidak ada data rekap anggaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if ($jenis === 'all')
        <div class="page-break"></div>
    @endif

    @if ($jenis === 'detail' || $jenis === 'all')
        <div class="section-title">Detail Anggaran</div>
        <table>
            <thead>
                <tr>
                    <th class="nowrap" style="width:40px;">No</th>
                    <th>No Surat</th>
                    <th>Tanggal</th>
                    <th>Petugas</th>
                    <th>Keperluan</th>
                    <th>MAK</th>
                    <th>No Kwitansi</th>
                    <th>Status</th>
                    <th class="nowrap right" style="width:120px;">Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailAnggaran as $i => $item)
                    <tr>
                        <td class="center nowrap">{{ $i + 1 }}</td>
                        <td>{{ $item['nomor_surat'] }}</td>
                        <td>{{ $item['tanggal'] }}</td>
                        <td>{{ $item['petugas'] }}</td>
                        <td>{{ $item['keperluan'] }}</td>
                        <td>{{ $item['mak'] }}</td>
                        <td>{{ $item['nomor_kwitansi'] }}</td>
                        <td>{{ $item['status'] }}</td>
                        <td class="right nowrap">Rp {{ number_format($item['total_biaya'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="center">Tidak ada data detail anggaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">
        <div>Dicetak oleh: {{ auth()->user()->name ?? 'User' }}</div>
        <div>— Rekap Anggaran —</div>
    </div>

</body>

</html>
