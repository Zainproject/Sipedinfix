<div class="page">

    @include('spj.partials.kop')
    <br>
    <div class="judul">
        <h3>SURAT PERINTAH TUGAS</h3>
        <p>Nomor : {{ $spt->nomor_surat }}</p>
    </div>

    <table>
        <tr>
            <td class="label">DASAR</td>
            <td>:</td>
            <td class="isi">
                Surat Perintah dari Kuasa Pengguna Anggaran Pejabat Pembuat Komitmen
                Dinas Ketahanan Pangan dan Pertanian Kabupaten Sumenep
            </td>
        </tr>
    </table>

    <div class="bagian">MEMERINTAHKAN</div>

    <table>
        <tr>
            <td class="label">KEPADA</td>
            <td>:</td>
            <td></td>
        </tr>
    </table>

    <table style="margin-left:25px">
        @forelse ($spt->petugasRel as $index => $petugas)
            <tr>
                <td>{{ $index + 1 }}.</td>
                <td>Nama</td>
                <td>:</td>
                <td><b>{{ strtoupper($petugas->nama) }}</b></td>
            </tr>
            <tr>
                <td></td>
                <td>NIP</td>
                <td>:</td>
                <td>{{ $petugas->nip }}</td>
            </tr>
            <tr>
                <td></td>
                <td>Pangkat/Gol</td>
                <td>:</td>
                <td>{{ $petugas->pangkat }}</td>
            </tr>
            <tr>
                <td></td>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $petugas->jabatan }}</td>
            </tr>
            <tr>
                <td colspan="4" height="8"></td>
            </tr>
        @empty
            <tr>
                <td colspan="4"><i>Tidak ada data petugas</i></td>
            </tr>
        @endforelse
    </table>

    <table style="margin-top:12px; width:100%">
        <tr>
            <td class="label">UNTUK</td>
            <td>:</td>
            <td class="isi">
                @php
                    $tanggal = \Carbon\Carbon::parse($spt->tanggal_berangkat)->translatedFormat('d F Y');

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

                    // fallback dari relasi spt_tujuan kalau kolom array kosong
                    if (
                        count($tujuan) === 0 &&
                        isset($spt->sptTujuan) &&
                        $spt->sptTujuan &&
                        $spt->sptTujuan->count() > 0
                    ) {
                        foreach ($spt->sptTujuan as $row) {
                            $jenis = trim((string) ($row->jenis_tujuan ?? ''));

                            $tujuan[] = $jenis;
                            $poktanNama[] = $row->poktan_nama ?? null;
                            $deskripsiKota[] = $row->deskripsi_kota ?? null;
                            $deskripsiLainnya[] = $row->deskripsi_lainnya ?? null;
                        }
                    }

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
                    {{ ucfirst($finalNarasi) }} pada {{ $tanggal }}.
                @else
                    {{ ucfirst((string) $spt->keperluan) }} pada {{ $tanggal }}.
                @endif
                <br><br>
                Demikian surat tugas ini dibuat untuk dipergunakan dengan sebaik-baiknya.
            </td>
        </tr>
    </table>

    <br>

    @php
        $pejabat = \App\Models\User::where(function ($query) {
            $query->where('jabatan', 'ketua')->orWhere('role', 'ketua');
        })->first();

        $namaPejabat = $pejabat?->nama ?? ($pejabat?->name ?? '-');
        $nipPejabat = $pejabat?->nip ?? '-';
        $jabatanPejabat = $pejabat?->jabatan ?? 'Ketua';
    @endphp

    <div class="ttd">
        Dikeluarkan di : Sumenep<br>
        Tanggal : {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br><br>

        An. Kuasa Pengguna Anggaran<br>
        Pejabat Pembuat Komitmen<br><br><br>

        <div class="nama">{{ strtoupper($namaPejabat) }}</div>
        <div class="nip" style="white-space: pre;">NIP. {{ $nipPejabat }}</div>
    </div>

</div>
