@php
    use Carbon\Carbon;

    $start = $spt->tanggal_berangkat ? Carbon::parse($spt->tanggal_berangkat) : null;
    $end = $spt->tanggal_kembali ? Carbon::parse($spt->tanggal_kembali) : null;

    $tglBerangkat = $start ? $start->translatedFormat('d F Y') : '-';
    $tglKembali = $end ? $end->translatedFormat('d F Y') : '-';
    $lama = $start && $end ? $start->diffInDays($end) + 1 : 0;
    $waktuRingkas = $start && $end ? $tglBerangkat . ' s/d ' . $tglKembali . ' (' . $lama . ' hari)' : '-';

    $penerima = $penerima ?? $spt->petugasList()->first();

    /*
    |--------------------------------------------------------------------------
    | TOTAL SESUAI INPUT BENDAHARA (dari keuangan.detail_petugas)
    |--------------------------------------------------------------------------
    */
    $detailPetugas = $spt->keuangan->detail_petugas ?? [];

    if (is_string($detailPetugas)) {
        $decoded = json_decode($detailPetugas, true);
        $detailPetugas = is_array($decoded) ? $decoded : [];
    }

    $petugasKey = (string) ($penerima->getKey() ?? '');
    $petugasNip = (string) ($penerima->nip ?? '');

    $detailUntukPenerima = collect($detailPetugas)->first(function ($item) use ($petugasKey, $petugasNip) {
        $id = (string) ($item['petugas_id'] ?? '');
        return $id === $petugasKey || $id === $petugasNip;
    });

    $total = (float) ($detailUntukPenerima['total_biaya'] ?? 0);

    /*
    |--------------------------------------------------------------------------
    | TERBILANG
    |--------------------------------------------------------------------------
    */
    $penyebut = function ($nilai) use (&$penyebut) {
        $nilai = abs((int) $nilai);
        $huruf = [
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas',
        ];

        if ($nilai < 12) {
            return ' ' . $huruf[$nilai];
        } elseif ($nilai < 20) {
            return $penyebut($nilai - 10) . ' belas';
        } elseif ($nilai < 100) {
            return $penyebut(intval($nilai / 10)) . ' puluh' . $penyebut($nilai % 10);
        } elseif ($nilai < 200) {
            return ' seratus' . $penyebut($nilai - 100);
        } elseif ($nilai < 1000) {
            return $penyebut(intval($nilai / 100)) . ' ratus' . $penyebut($nilai % 100);
        } elseif ($nilai < 2000) {
            return ' seribu' . $penyebut($nilai - 1000);
        } elseif ($nilai < 1000000) {
            return $penyebut(intval($nilai / 1000)) . ' ribu' . $penyebut($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            return $penyebut(intval($nilai / 1000000)) . ' juta' . $penyebut($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            return $penyebut(intval($nilai / 1000000000)) . ' miliar' . $penyebut(fmod($nilai, 1000000000));
        }

        return '';
    };

    $terbilang = function ($angka) use ($penyebut) {
        if ((int) round($angka) <= 0) {
            return '-';
        }

        $hasil = trim($penyebut((int) round($angka)));
        return ucfirst($hasil) . ' rupiah';
    };

    /*
    |--------------------------------------------------------------------------
    | LOGIKA TUJUAN / NARASI
    |--------------------------------------------------------------------------
    */
    $norm = function ($val) {
        if (is_array($val)) {
            return array_values($val);
        }

        if (is_string($val)) {
            $val = trim($val);
            if ($val === '') {
                return [];
            }

            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                return array_values($decoded);
            }

            $val = str_replace(['|'], ';', $val);
            return array_values(array_filter(array_map('trim', explode(';', $val))));
        }

        return [];
    };

    $tujuan = $norm($spt->tujuan ?? null);
    $poktanNama = $norm($spt->poktan_nama ?? null);
    $deskripsiKota = $norm($spt->deskripsi_kota ?? null);
    $deskripsiLainnya = $norm($spt->deskripsi_lainnya ?? null);

    if (count($tujuan) === 0 && isset($spt->sptTujuan) && $spt->sptTujuan && $spt->sptTujuan->count() > 0) {
        foreach ($spt->sptTujuan as $row) {
            $tujuan[] = trim((string) ($row->jenis_tujuan ?? ''));
            $poktanNama[] = $row->poktan_nama ?? null;
            $deskripsiKota[] = $row->deskripsi_kota ?? null;
            $deskripsiLainnya[] = $row->deskripsi_lainnya ?? null;
        }
    }

    $keperluanParts = array_values(array_filter(array_map('trim', explode(';', (string) $spt->keperluan))));

    $getKeperluan = function ($i) use ($keperluanParts, $spt) {
        if (count($keperluanParts) === 0) {
            return trim((string) $spt->keperluan);
        }
        return $keperluanParts[$i] ?? $keperluanParts[0];
    };

    $ktByKep = [];
    $kabByKep = [];
    $lainByKep = [];
    $order = [];

    foreach ($tujuan as $i => $tj) {
        $tj = trim((string) $tj);
        $kep = $getKeperluan($i);

        if ($tj === 'kelompok_tani' || $tj === 'poktan') {
            $namaPoktan = trim((string) ($poktanNama[$i] ?? ''));

            if ($namaPoktan === '') {
                continue;
            }

            $poktan = \App\Models\Poktan::where('nama_poktan', $namaPoktan)->first();

            $teksPoktan = $poktan
                ? 'KT. ' . $poktan->nama_poktan . ' Desa ' . $poktan->desa . ' Kecamatan ' . $poktan->kecamatan
                : 'KT. ' . $namaPoktan;

            if (!isset($ktByKep[$kep])) {
                $ktByKep[$kep] = [];
                $order[] = ['type' => 'kt', 'kep' => $kep];
            }

            $ktByKep[$kep][] = $teksPoktan;
        } elseif ($tj === 'kabupaten_kota') {
            $kota = trim((string) ($deskripsiKota[$i] ?? ''));
            if ($kota === '' || $kota === '-') {
                continue;
            }

            if (!isset($kabByKep[$kep])) {
                $kabByKep[$kep] = [];
                $order[] = ['type' => 'kab', 'kep' => $kep];
            }

            $kabByKep[$kep][] = $kota;
        } elseif ($tj === 'lainnya' || $tj === 'lain_lain') {
            $lain = trim((string) ($deskripsiLainnya[$i] ?? ''));
            if ($lain === '' || $lain === '-') {
                continue;
            }

            if (!isset($lainByKep[$kep])) {
                $lainByKep[$kep] = [];
                $order[] = ['type' => 'lain', 'kep' => $kep];
            }

            $lainByKep[$kep][] = $lain;
        }
    }

    $segments = [];
    foreach ($order as $o) {
        if ($o['type'] === 'kt') {
            $kep = $o['kep'];
            $segments[] = $kep . ' ke ' . implode(' dan ', $ktByKep[$kep]) . ' Kabupaten Sumenep';
        }
        if ($o['type'] === 'kab') {
            $kep = $o['kep'];
            $segments[] = $kep . ' ke ' . implode(' dan ', $kabByKep[$kep]);
        }
        if ($o['type'] === 'lain') {
            $kep = $o['kep'];
            $segments[] = $kep . ' ke ' . implode(' dan ', $lainByKep[$kep]);
        }
    }

    $finalNarasi = trim(implode(' dan ', array_filter($segments)));

    /*
    |--------------------------------------------------------------------------
    | PEJABAT DAN BENDAHARA
    |--------------------------------------------------------------------------
    */
    $pejabat = \App\Models\User::where(function ($q) {
        $q->where('jabatan', 'ketua')->orWhere('role', 'ketua');
    })->first();

    $bendahara = \App\Models\User::where(function ($q) {
        $q->where('jabatan', 'bendahara')->orWhere('role', 'bendahara');
    })->first();

    $namaPejabat = $pejabat?->nama ?? ($pejabat?->name ?? '-');
    $nipPejabat = $pejabat?->nip ?? '-';

    $namaBendahara = $bendahara?->nama ?? ($bendahara?->name ?? '-');
    $nipBendahara = $bendahara?->nip ?? '-';

    /*
    |--------------------------------------------------------------------------
    | KOTAK KANAN ATAS
    |--------------------------------------------------------------------------
    */
    $nomorBukti = trim((string) ($spt->nomor_kwitansi ?? ''));
    if ($nomorBukti === '' || $nomorBukti === '-') {
        $nomorBukti = trim((string) ($spt->keuangan->nomor_kwitansi ?? '-'));
    }

    $mak = trim((string) ($spt->mak ?? ''));
    if ($mak === '' || $mak === '-') {
        $mak = trim((string) ($spt->keuangan->mak ?? '-'));
    }

    $tahunAnggaran = trim((string) ($spt->tahun ?? ''));
    if ($tahunAnggaran === '') {
        $tahunAnggaran = $start ? $start->format('Y') : '-';
    }
@endphp

<div class="page">

    <div style="width:100%; margin-bottom:25px;">
        <table style="width:45%; margin-left:auto; border-collapse:collapse;" border="1" cellpadding="4">
            <tr>
                <td style="width:45%;">&nbsp;Tahun Anggaran</td>
                <td>: {{ $tahunAnggaran ?: '-' }}</td>
            </tr>
            <tr>
                <td>&nbsp;Nomor Bukti</td>
                <td>: {{ $nomorBukti ?: '-' }}</td>
            </tr>
            <tr>
                <td>&nbsp;MAK</td>
                <td>: {{ $mak ?: '-' }}</td>
            </tr>
            <tr>
                <td>&nbsp;DIPA</td>
                <td>:</td>
            </tr>
        </table>
    </div>

    <div style="text-align:center; margin:30px 0;">
        <b><u>KUITANSI / BUKTI PEMBAYARAN</u></b>
    </div>

    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:22%;">Sudah Terima Dari</td>
            <td style="width:3%;">:</td>
            <td>
                KUASA PENGGUNA ANGGARAN DINAS KETAHANAN PANGAN DAN PERTANIAN
                KABUPATEN SUMENEP
            </td>
        </tr>

        <tr>
            <td>Jumlah Uang</td>
            <td>:</td>
            <td>
                <b>Rp. {{ $total > 0 ? number_format($total, 0, ',', '.') : '-' }},-</b>
            </td>
        </tr>

        <tr>
            <td>Terbilang</td>
            <td>:</td>
            <td><i>{{ $terbilang($total) }}</i></td>
        </tr>

        <tr>
            <td style="vertical-align:top;">Untuk Pembayaran</td>
            <td style="vertical-align:top;">:</td>
            <td style="text-align:justify;">
                @if ($finalNarasi !== '')
                    Biaya bantuan untuk melaksanakan kegiatan
                    {{ $finalNarasi }}
                    pada {{ $waktuRingkas }},
                    sesuai SPT Nomor : {{ $spt->nomor_surat }}
                    dan SPD terkait, dengan perincian terlampir.
                @else
                    Biaya bantuan untuk melaksanakan kegiatan
                    {{ trim((string) $spt->keperluan) !== '' ? $spt->keperluan : '-' }}
                    pada {{ $waktuRingkas }},
                    sesuai SPT Nomor : {{ $spt->nomor_surat }}
                    dan SPD terkait, dengan perincian terlampir.
                @endif
            </td>
        </tr>
    </table>

    <div style="width:100%; margin-top:40px; text-align:right;">
        Sumenep, {{ now()->translatedFormat('d F Y') }}<br>
        Yang Menerima
    </div>

    <div style="width:100%; margin-top:70px; text-align:right;">
        <b>{{ strtoupper($penerima->nama ?? '-') }}</b><br>
        NIP. {{ $penerima->nip ?? '-' }}
    </div>

    <table style="width:100%; margin-top:50px;">
        <tr>
            <td style="width:50%; vertical-align:top;">
                Setuju dibayar,<br>
                An. Kuasa Pengguna Anggaran<br>
                Pejabat Pembuat Komitmen<br>
                <br><br><br>
                <div style="margin-top:20px;">
                    <b>{{ strtoupper($namaPejabat) }}</b>
                </div>
                <div style="white-space: pre;">NIP. {{ $nipPejabat }}</div>
            </td>

            <td style="width:50%; vertical-align:top; text-align:right;">
                <br>
                Lunas dibayar, Tgl........................................<br>
                Bendahara Pengeluaran<br>
                <br><br><br>
                <div style="margin-top:20px;">
                    <b>{{ strtoupper($namaBendahara) }}</b>
                </div>
                <div style="white-space: pre;">NIP. {{ $nipBendahara }}</div>
            </td>
        </tr>
    </table>

</div>
