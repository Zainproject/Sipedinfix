@php
    use Carbon\Carbon;

    $tglBerangkat = Carbon::parse($spt->tanggal_berangkat)->translatedFormat('d F Y');
    $tglKembali = Carbon::parse($spt->tanggal_kembali)->translatedFormat('d F Y');
    $lama = Carbon::parse($spt->tanggal_berangkat)->diffInDays(Carbon::parse($spt->tanggal_kembali)) + 1;

    $petugasList = $spt->petugasList();
    $petugasUtama = $petugasList->first();

    $adaPengikut = $petugasList->count() > 1;
    $no9 = $adaPengikut ? 9 : 8;
    $no10 = $adaPengikut ? 10 : 9;

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

    // ambil dari kolom spts dulu
    $tujuan = $norm($spt->tujuan ?? null);
    $poktanNama = $norm($spt->poktan_nama ?? null);
    $deskripsiKota = $norm($spt->deskripsi_kota ?? null);
    $deskripsiLainnya = $norm($spt->deskripsi_lainnya ?? null);

    // fallback ke relasi spt_tujuan kalau array di spts kosong
    if (count($tujuan) === 0 && isset($spt->sptTujuan) && $spt->sptTujuan && $spt->sptTujuan->count() > 0) {
        foreach ($spt->sptTujuan as $row) {
            $tujuan[] = trim((string) ($row->jenis_tujuan ?? ''));
            $poktanNama[] = $row->poktan_nama ?? null;
            $deskripsiKota[] = $row->deskripsi_kota ?? null;
            $deskripsiLainnya[] = $row->deskripsi_lainnya ?? null;
        }
    }

    $listKT = [];
    $listLokasi = [];

    foreach ($tujuan as $i => $tj) {
        $tj = trim((string) $tj);

        if ($tj === 'kelompok_tani' || $tj === 'poktan') {
            $namaPoktan = trim((string) ($poktanNama[$i] ?? ''));
            if ($namaPoktan !== '' && $namaPoktan !== '-') {
                $listKT[] = 'KT. ' . $namaPoktan;
            }
        } elseif ($tj === 'kabupaten_kota') {
            $kota = trim((string) ($deskripsiKota[$i] ?? ''));
            if ($kota !== '' && $kota !== '-') {
                $listLokasi[] = $kota;
            }
        } elseif ($tj === 'lainnya' || $tj === 'lain_lain') {
            $lain = trim((string) ($deskripsiLainnya[$i] ?? ''));
            if ($lain !== '' && $lain !== '-') {
                $listLokasi[] = $lain;
            }
        }
    }

    $listKT = array_values(array_unique($listKT));
    $listLokasi = array_values(array_unique($listLokasi));

    if (count($listLokasi) > 0) {
        $lokasiUtama = implode(' dan ', $listLokasi);
    } elseif (count($listKT) > 0) {
        $lokasiUtama = implode(' dan ', $listKT);
    } else {
        $lokasiUtama = '-';
    }

    $pejabat = \App\Models\User::where(function ($query) {
        $query->where('jabatan', 'ketua')->orWhere('role', 'ketua');
    })->first();

    $namaPejabat = $pejabat?->nama ?? ($pejabat?->name ?? '-');
    $nipPejabat = $pejabat?->nip ?? '-';
    $jabatanPejabat = $pejabat?->jabatan ?? 'Ketua';

    // kode rekening: prioritas dari spt, fallback dari relasi keuangan
    $kodeRekening = trim((string) ($spt->mak ?? ''));
    if ($kodeRekening === '' || $kodeRekening === '-') {
        $kodeRekening = trim((string) ($spt->keuangan->mak ?? '-'));
    }
@endphp

<div class="page">

    @include('spj.partials.kop') <br>

    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse">

        <tr>
            <td colspan="4">
                <div style="padding-left:450px; text-align:left;">
                    Nomor : {{ $spt->nomor_surat }} <br>
                    Lembar : I / II
                </div>
            </td>
        </tr>

        <tr>
            <td colspan="4" style="text-align:center; font-weight:bold;">
                SURAT PERJALANAN DINAS (SPD)
            </td>
        </tr>

        <tr>
            <td width="5%">1</td>
            <td width="35%">Pejabat Berwenang Yang Mengeluarkan SPD</td>
            <td colspan="2">
                KUASA PENGGUNA ANGGARAN DINAS KETAHANAN PANGAN DAN PERTANIAN
                KABUPATEN SUMENEP
            </td>
        </tr>

        <tr>
            <td>2</td>
            <td>Nama / NIP Pegawai</td>
            <td colspan="2">
                <b>{{ strtoupper($petugasUtama->nama ?? '-') }}</b><br>
                NIP. {{ $petugasUtama->nip ?? '-' }}
            </td>
        </tr>

        <tr>
            <td rowspan="2">3</td>
            <td>a. Pangkat dan Golongan</td>
            <td colspan="2">{{ $petugasUtama->pangkat ?? '-' }}</td>
        </tr>
        <tr>
            <td>b. Jabatan</td>
            <td colspan="2">{{ $petugasUtama->jabatan ?? '-' }}</td>
        </tr>

        <tr>
            <td>4</td>
            <td>Maksud Perjalanan Dinas</td>
            <td colspan="2">
                @php
                    $tglBerangkatNarasi = \Carbon\Carbon::parse($spt->tanggal_berangkat)->translatedFormat('d F Y');

                    $keperluanParts = array_values(
                        array_filter(array_map('trim', explode(';', (string) $spt->keperluan))),
                    );

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
                                ? 'KT. ' .
                                    $poktan->nama_poktan .
                                    ' Desa ' .
                                    $poktan->desa .
                                    ' Kecamatan ' .
                                    $poktan->kecamatan
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
                @endphp

                @if ($finalNarasi !== '')
                    {{ ucfirst($finalNarasi) }} pada {{ $tglBerangkatNarasi }}.
                @else
                    {{ ucfirst((string) $spt->keperluan) }} pada {{ $tglBerangkatNarasi }}.
                @endif
            </td>
        </tr>

        <tr>
            <td>5</td>
            <td>Alat Angkut</td>
            <td colspan="2">{{ $spt->alat_angkut }}</td>
        </tr>

        <tr>
            <td rowspan="2">6</td>
            <td>a. Tempat Berangkat</td>
            <td colspan="2">Sumenep</td>
        </tr>
        <tr>
            <td>b. Tempat Tujuan</td>
            <td colspan="2">{{ $lokasiUtama }}</td>
        </tr>

        <tr>
            <td rowspan="3">7</td>
            <td>a. Lamanya Perjalanan</td>
            <td colspan="2">{{ $lama }} hari</td>
        </tr>
        <tr>
            <td>b. Tanggal Berangkat</td>
            <td colspan="2">{{ $tglBerangkat }}</td>
        </tr>
        <tr>
            <td>c. Tanggal Kembali</td>
            <td colspan="2">{{ $tglKembali }}</td>
        </tr>

        @if ($adaPengikut)
            <tr>
                <td rowspan="{{ $petugasList->count() }}">8</td>
                <td>Pengikut</td>
                <td style="text-align:center;">Pangkat</td>
                <td style="text-align:center;">Jabatan</td>
            </tr>
            @foreach ($petugasList->skip(1) as $pg)
                <tr>
                    <td><b>{{ strtoupper($pg->nama ?? '-') }}</b><br>NIP. {{ $pg->nip ?? '-' }}</td>
                    <td style="text-align:center;">{{ $pg->pangkat ?? '-' }}</td>
                    <td style="text-align:center;">{{ $pg->jabatan ?? '-' }}</td>
                </tr>
            @endforeach
        @endif

        <tr>
            <td rowspan="2">{{ $no9 }}</td>
            <td>Pembebanan Anggaran</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td>a. SKPD</td>
            <td colspan="2">
                DINAS KETAHANAN PANGAN DAN PERTANIAN
                KABUPATEN SUMENEP
            </td>
        </tr>
        <tr>
            <td></td>
            <td>b. Kode Rekening</td>
            <td colspan="2">{{ $kodeRekening !== '' ? $kodeRekening : '-' }}</td>
        </tr>

        <tr>
            <td>{{ $no10 }}</td>
            <td>Keterangan lain-lain</td>
            <td colspan="2">-</td>
        </tr>

    </table>

    <div class="ttd">
        <p>
            Dikeluarkan di : Sumenep<br>
            Tanggal : {{ now()->translatedFormat('d F Y') }}
        </p>

        <p>
            An. Kuasa Pengguna Anggaran<br>
            Pejabat Pembuat Komitmen (PPK)
        </p>
        <br>
        <div class="nama">{{ strtoupper($namaPejabat) }}</div>
        <div class="nip" style="white-space: pre;">NIP. {{ $nipPejabat }}</div>
    </div>

</div>
