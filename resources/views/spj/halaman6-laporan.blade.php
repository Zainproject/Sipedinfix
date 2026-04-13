<div class="page">

    <center>
        <b><u>LAPORAN PERJALANAN DINAS</u></b>
    </center>

    <br>

    @php
        use Carbon\Carbon;

        // ===== TANGGAL =====
        $tglDasar = $spt->tanggal_berangkat ? Carbon::parse($spt->tanggal_berangkat)->translatedFormat('d F Y') : '-';

        $tglBerangkat = $spt->tanggal_berangkat ? Carbon::parse($spt->tanggal_berangkat) : null;
        $tglKembali = $spt->tanggal_kembali ? Carbon::parse($spt->tanggal_kembali) : null;

        $waktuPelaksanaan = '-';
        if ($tglBerangkat && $tglKembali) {
            $waktuPelaksanaan = $tglBerangkat->equalTo($tglKembali)
                ? $tglBerangkat->translatedFormat('d F Y')
                : $tglBerangkat->translatedFormat('d F Y') . ' s/d ' . $tglKembali->translatedFormat('d F Y');
        } elseif ($tglBerangkat) {
            $waktuPelaksanaan = $tglBerangkat->translatedFormat('d F Y');
        }

        // ===== PETUGAS =====
        $petugasList = $spt->petugasList();

        // ===== NORMALISASI (AMAN JSON/ARRAY/CSV) =====
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

        // ===== TUJUAN: ambil dari kolom spt dulu =====
        $tujuan = $norm($spt->tujuan ?? null);
        $poktanNama = $norm($spt->poktan_nama ?? null);
        $deskripsiKota = $norm($spt->deskripsi_kota ?? null);
        $deskripsiLainnya = $norm($spt->deskripsi_lainnya ?? null);

        // ===== fallback ke relasi spt_tujuan =====
        if (count($tujuan) === 0 && isset($spt->sptTujuan) && $spt->sptTujuan && $spt->sptTujuan->count() > 0) {
            foreach ($spt->sptTujuan as $row) {
                $tujuan[] = trim((string) ($row->jenis_tujuan ?? ''));
                $poktanNama[] = $row->poktan_nama ?? null;
                $deskripsiKota[] = $row->deskripsi_kota ?? null;
                $deskripsiLainnya[] = $row->deskripsi_lainnya ?? null;
            }
        }

        // ===== TUJUAN RINGKAS =====
        $tujuanLines = [];
        foreach ($tujuan as $i => $tj) {
            $tj = trim((string) $tj);

            if ($tj === 'kelompok_tani' || $tj === 'poktan') {
                $nama = trim((string) ($poktanNama[$i] ?? '-'));
                if ($nama !== '' && $nama !== '-') {
                    $tujuanLines[] = str_starts_with(strtoupper($nama), 'KT.') ? $nama : 'KT. ' . $nama;
                }
            } elseif ($tj === 'kabupaten_kota') {
                $kota = trim((string) ($deskripsiKota[$i] ?? '-'));
                if ($kota !== '' && $kota !== '-') {
                    $tujuanLines[] = $kota;
                }
            } elseif ($tj === 'lainnya' || $tj === 'lain_lain') {
                $lain = trim((string) ($deskripsiLainnya[$i] ?? '-'));
                if ($lain !== '' && $lain !== '-') {
                    $tujuanLines[] = $lain;
                }
            }
        }

        // ===== MAKSUD & TUJUAN =====
        $tanggalNarasi = $tglBerangkat ? $tglBerangkat->translatedFormat('d F Y') : '-';

        $keperluanParts = array_values(array_filter(array_map('trim', explode(';', (string) ($spt->keperluan ?? '')))));
        $getKeperluan = function ($i) use ($keperluanParts, $spt) {
            if (count($keperluanParts) === 0) {
                return trim((string) ($spt->keperluan ?? '-'));
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
                if ($kota !== '' && $kota !== '-') {
                    if (!isset($kabByKep[$kep])) {
                        $kabByKep[$kep] = [];
                        $order[] = ['type' => 'kab', 'kep' => $kep];
                    }
                    $kabByKep[$kep][] = $kota;
                }
            } elseif ($tj === 'lainnya' || $tj === 'lain_lain') {
                $lain = trim((string) ($deskripsiLainnya[$i] ?? ''));
                if ($lain !== '' && $lain !== '-') {
                    if (!isset($lainByKep[$kep])) {
                        $lainByKep[$kep] = [];
                        $order[] = ['type' => 'lain', 'kep' => $kep];
                    }
                    $lainByKep[$kep][] = $lain;
                }
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

        $finalNarasiStr = trim(implode(' dan ', array_filter($segments)));
        $maksudTujuanText = $finalNarasiStr !== '' ? ucfirst($finalNarasiStr) . ' pada ' . $tanggalNarasi . '.' : '-';

        // ===== BERSIHKAN LIST =====
        $cleanList = function ($arr) use ($norm) {
            $arr = $norm($arr);

            return array_values(
                array_filter($arr, function ($v) {
                    $v = trim((string) $v);
                    return $v !== '' && $v !== '-' && $v !== '—';
                }),
            );
        };

        // support berbagai nama field
        $arahan = $cleanList($spt->arahan ?? null);
        $masalah = $cleanList($spt->masalah ?? ($spt->masalah_temuan ?? null));
        $saran = $cleanList($spt->saran ?? ($spt->saran_tindakan ?? null));
        $lainnya = $cleanList($spt->lainnya ?? ($spt->lain_lain ?? null));
    @endphp

    <table width="100%" cellpadding="2" cellspacing="0">
        <tr>
            <td width="5%">I.</td>
            <td width="30%">Dasar</td>
            <td width="2%">:</td>
            <td>
                SPT {{ $spt->nomor_surat ?? '-' }}<br>
                Tanggal : {{ $tglDasar }}
            </td>
        </tr>

        <tr>
            <td>II.</td>
            <td>Maksud dan Tujuan</td>
            <td>:</td>
            <td>{{ $maksudTujuanText }}</td>
        </tr>

        <tr>
            <td>III.</td>
            <td>Waktu Pelaksanaan</td>
            <td>:</td>
            <td>Tanggal {{ $waktuPelaksanaan }}</td>
        </tr>

        <tr>
            <td>IV.</td>
            <td>Nama Petugas / NIP</td>
            <td>:</td>
            <td>
                @if ($petugasList->count())
                    <table width="100%" cellpadding="0" cellspacing="0">
                        @foreach ($petugasList as $i => $p)
                            <tr>
                                <td width="5%">{{ $i + 1 }}.</td>
                                <td width="12%">Nama</td>
                                <td width="2%">:</td>
                                <td width="81%">{{ strtoupper($p->nama) }}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>NIP.</td>
                                <td>:</td>
                                <td>{{ $p->nip }}</td>
                            </tr>
                            @if (!$loop->last)
                                <tr>
                                    <td colspan="4" height="8"></td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                @else
                    -
                @endif
            </td>
        </tr>

        <tr>
            <td>V.</td>
            <td>Daerah tujuan / instansi<br>yang dikunjungi</td>
            <td>:</td>
            <td>
                @if (count($tujuanLines))
                    @foreach ($tujuanLines as $i => $t)
                        @if ($i === 0)
                            {{ $t }}<br>
                        @else
                            dan<br>{{ $t }}<br>
                        @endif
                    @endforeach
                @else
                    -
                @endif
            </td>
        </tr>

        <tr>
            <td>VI.</td>
            <td>Hadir dalam pertemuan</td>
            <td>:</td>
            <td>{{ trim((string) ($spt->kehadiran ?? '')) !== '' ? $spt->kehadiran : '-' }}</td>
        </tr>

        <tr>
            <td>VII.</td>
            <td>Petunjuk/<br>arahan</td>
            <td>:</td>
            <td>
                @if (count($arahan))
                    @foreach ($arahan as $i => $v)
                        {{ $i + 1 }}. {{ $v }}<br>
                    @endforeach
                @else
                    -
                @endif
            </td>
        </tr>

        <tr>
            <td>VIII.</td>
            <td>Masalah/temuan</td>
            <td>:</td>
            <td>
                @if (count($masalah))
                    @foreach ($masalah as $i => $v)
                        {{ $i + 1 }}. {{ $v }}<br>
                    @endforeach
                @else
                    -
                @endif
            </td>
        </tr>

        <tr>
            <td>IX.</td>
            <td>Saran tindakan</td>
            <td>:</td>
            <td>
                @if (count($saran))
                    @foreach ($saran as $i => $v)
                        {{ $i + 1 }}. {{ $v }}<br>
                    @endforeach
                @else
                    -
                @endif
            </td>
        </tr>

        <tr>
            <td>X.</td>
            <td>Lain-lain</td>
            <td>:</td>
            <td>
                @if (count($lainnya))
                    @foreach ($lainnya as $i => $v)
                        {{ $i + 1 }}. {{ $v }}<br>
                    @endforeach
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <br><br>

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="45%"></td>
            <td width="55%">
                Sumenep, {{ $tglDasar }}
                <br><br>
                Pelapor,
                <br><br>

                <table width="100%" cellpadding="2" cellspacing="0">
                    @if ($petugasList->count())
                        @foreach ($petugasList as $i => $p)
                            <tr>
                                <td width="5%">{{ $i + 1 }}.</td>
                                <td width="60%">
                                    {{ strtoupper($p->nama) }}<br>
                                    NIP. {{ $p->nip }}
                                </td>
                                <td width="5%">{{ $i + 1 }}.</td>
                                <td width="30%">..............................</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4">-</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

</div>
