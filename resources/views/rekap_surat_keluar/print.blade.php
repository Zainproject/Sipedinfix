<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Surat Keluar</title>
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
                'spt' => 'DATA SPT',
                'petugas' => 'REKAP PETUGAS',
                'poktan' => 'REKAP TUJUAN / POKTAN',
                'all' => 'SEMUA (SPT + REKAP)',
            ][$jenis] ?? 'SEMUA (SPT + REKAP)';

        $filterInfo = $filterInfo ?? [
            'tahun' => null,
            'bulan' => null,
            'mak' => null,
            'petugas' => null,
            'keyword' => null,
        ];

        $rekapPetugas = $rekapPetugas ?? collect();
        $rekapPoktan = $rekapPoktan ?? collect();
    @endphp

    <div class="header">
        <div class="title">REKAP SURAT KELUAR</div>
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
            <div><b>Keyword</b>: {{ $filterInfo['keyword'] ?: '-' }}</div>
            <div><b>Tanggal Cetak</b>: {{ now()->format('d-m-Y H:i') }}</div>
        </div>
    </div>

    <div class="kpi">
        <span class="pill"><b>Total Surat:</b> {{ number_format($totalSurat ?? 0) }}</span>
        <span class="pill"><b>Total Biaya:</b> Rp {{ number_format($totalBiaya ?? 0, 0, ',', '.') }}</span>
    </div>

    <hr class="sep">

    {{-- =======================
        1) CETAK DATA SPT
       ======================= --}}
    @if ($jenis === 'spt' || $jenis === 'all')
        <div class="section-title">Data SPT</div>
        <table>
            <thead>
                <tr>
                    <th class="nowrap" style="width:40px;">No</th>
                    <th>No Surat</th>
                    <th>Petugas</th>
                    <th>Tujuan</th>
                    <th>No Kwitansi</th>
                    <th class="nowrap" style="width:90px;">Tahun/Bulan</th>
                    <th>Keperluan</th>
                    <th class="nowrap" style="width:120px;">Berangkat - Kembali</th>
                    <th>Status</th>
                    <th class="nowrap right" style="width:110px;">Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                @forelse($spts as $i => $spt)
                    @php
                        $tgl1 = $spt->tanggal_berangkat
                            ? \Carbon\Carbon::parse($spt->tanggal_berangkat)->format('d-m-Y')
                            : '-';
                        $tgl2 = $spt->tanggal_kembali
                            ? \Carbon\Carbon::parse($spt->tanggal_kembali)->format('d-m-Y')
                            : '-';

                        $petugasList = $spt->petugasRel->pluck('nama')->filter()->values();

                        $tujuanList = collect($spt->sptTujuan)
                            ->map(function ($t) {
                                if ($t->jenis_tujuan === 'poktan') {
                                    return 'Poktan ' .
                                        ($t->poktan_nama ?? '-') .
                                        ', Desa ' .
                                        (optional($t->poktan)->desa ?? '-') .
                                        ', Kecamatan ' .
                                        (optional($t->poktan)->kecamatan ?? '-');
                                }

                                if ($t->jenis_tujuan === 'kabupaten_kota') {
                                    return $t->deskripsi_kota ?: '-';
                                }

                                if ($t->jenis_tujuan === 'lain_lain') {
                                    return $t->deskripsi_lainnya ?: '-';
                                }

                                return '-';
                            })
                            ->filter()
                            ->unique()
                            ->values();

                        $statusText = trim((string) ($spt->status_bendahara ?? '')) ?: '-';
                        $kwitansi = optional($spt->keuangan)->nomor_kwitansi ?? '-';
                        $totalBiayaRow = (float) optional($spt->keuangan)->total_biaya;
                    @endphp
                    <tr>
                        <td class="center nowrap">{{ $i + 1 }}</td>
                        <td>{{ $spt->nomor_surat ?? '-' }}</td>
                        <td>
                            @forelse($petugasList as $p)
                                <div>{{ $p }}</div>
                            @empty
                                -
                            @endforelse
                        </td>
                        <td>
                            @forelse($tujuanList as $t)
                                <div>{{ $t }}</div>
                            @empty
                                -
                            @endforelse
                        </td>
                        <td>{{ $kwitansi }}</td>
                        <td class="center nowrap">{{ $spt->tahun ?? '-' }}/{{ $spt->bulan ?? '-' }}</td>
                        <td>{{ $spt->keperluan ?? '-' }}</td>
                        <td class="nowrap">{{ $tgl1 }} s/d {{ $tgl2 }}</td>
                        <td>{{ $statusText }}</td>
                        <td class="right nowrap">Rp {{ number_format($totalBiayaRow, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if ($jenis === 'all')
        <div class="page-break"></div>
    @endif

    {{-- =======================
        2) CETAK REKAP PETUGAS
       ======================= --}}
    @if ($jenis === 'petugas' || $jenis === 'all')
        <div class="section-title">Rekap Petugas</div>
        <table>
            <thead>
                <tr>
                    <th class="nowrap" style="width:40px;">No</th>
                    <th>Petugas</th>
                    <th class="nowrap right" style="width:120px;">Jumlah SPT</th>
                    <th class="nowrap right" style="width:140px;">Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapPetugas as $i => $rp)
                    <tr>
                        <td class="center nowrap">{{ $i + 1 }}</td>
                        <td>{{ $rp['nama'] ?? '-' }} <span class="nowrap">({{ $rp['nip'] ?? '-' }})</span></td>
                        <td class="right nowrap">{{ number_format($rp['jumlah'] ?? 0) }}</td>
                        <td class="right nowrap">Rp {{ number_format($rp['total_biaya'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="center">Tidak ada data rekap petugas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if ($jenis === 'all')
        <div class="page-break"></div>
    @endif

    {{-- =======================
        3) CETAK REKAP TUJUAN / POKTAN
       ======================= --}}
    @if ($jenis === 'poktan' || $jenis === 'all')
        <div class="section-title">Rekap Tujuan / Poktan</div>
        <table>
            <thead>
                <tr>
                    <th class="nowrap" style="width:40px;">No</th>
                    <th>Tujuan</th>
                    <th class="nowrap right" style="width:120px;">Jumlah SPT</th>
                    <th class="nowrap right" style="width:140px;">Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapPoktan as $i => $rk)
                    <tr>
                        <td class="center nowrap">{{ $i + 1 }}</td>
                        <td>{{ $rk['nama'] ?? '-' }}</td>
                        <td class="right nowrap">{{ number_format($rk['jumlah'] ?? 0) }}</td>
                        <td class="right nowrap">Rp {{ number_format($rk['total_biaya'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="center">Tidak ada data rekap tujuan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">
        <div>Dicetak oleh: {{ auth()->user()->name ?? 'User' }}</div>
        <div>— Rekap Surat Keluar —</div>
    </div>

</body>

</html>
