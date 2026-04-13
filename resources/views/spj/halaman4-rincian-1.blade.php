@php
    use Carbon\Carbon;

    $penerima = $penerima ?? $spt->petugasList()->first();
    $tglLampiran = now()->translatedFormat('d F Y');

    $start = $spt->tanggal_berangkat ? Carbon::parse($spt->tanggal_berangkat) : null;
    $end = $spt->tanggal_kembali ? Carbon::parse($spt->tanggal_kembali) : null;
    $lama = $start && $end ? $start->diffInDays($end) + 1 : 0;

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

    $items = collect($detailUntukPenerima['rincian'] ?? [])
        ->map(function ($item) {
            return [
                'keterangan' => trim((string) ($item['keterangan'] ?? '')) ?: 'Biaya Bantuan Transport',
                'harga' => (float) ($item['harga'] ?? 0),
                'catatan' => trim((string) ($item['catatan'] ?? '')),
            ];
        })
        ->values()
        ->all();

    $total = (float) ($detailUntukPenerima['total_biaya'] ?? 0);

    if (count($items) === 0 && $total <= 0) {
        $items = [];
        $total = 0;
    }

    $pejabat = \App\Models\User::where(function ($q) {
        $q->where('jabatan', 'ketua')->orWhere('role', 'ketua');
    })->first();

    $bendahara = \App\Models\User::where(function ($q) {
        $q->where('jabatan', 'bendahara')->orWhere('role', 'bendahara');
    })->first();

    $namaPejabat = $pejabat?->nama ?? ($pejabat?->name ?? '-');
    $nipPejabat = $pejabat?->nip ?? '-';
    $jabatanPejabat = $pejabat?->jabatan ?? 'Ketua';

    $namaBendahara = $bendahara?->nama ?? ($bendahara?->name ?? '-');
    $nipBendahara = $bendahara?->nip ?? '-';
    $jabatanBendahara = $bendahara?->jabatan ?? 'Bendahara Pengeluaran';

    $namaPenerima = $penerima->nama ?? '-';
    $nipPenerima = $penerima->nip ?? '-';
@endphp

<div class="page">

    <div style="text-align:center; font-weight:bold; text-transform:uppercase;">
        <span style="text-decoration: underline;">RINCIAN BIAYA PERJALANAN DINAS (REALISASI)</span>
    </div>

    <br>

    <table style="width:65%; margin-left:10px; font-size:10.5pt;">
        <tr>
            <td style="width:38%;">Lampiran SPD Nomor</td>
            <td style="width:3%;">:</td>
            <td>{{ $spt->nomor_surat }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $tglLampiran }}</td>
        </tr>
    </table>

    <br>

    <table border="1" cellpadding="6" cellspacing="0"
        style="width:92%; margin:0 auto; border-collapse:collapse; font-size:10.5pt;">
        <tr style="background:#d9d9d9; text-align:center; font-weight:bold;">
            <td style="width:6%;">NO</td>
            <td style="width:54%;">PERINCIAN BIAYA</td>
            <td style="width:20%;">JUMLAH (Rp)</td>
            <td style="width:20%;">KETERANGAN</td>
        </tr>

        @php $no = 1; @endphp
        @forelse ($items as $it)
            <tr>
                <td style="text-align:center;">{{ $no++ }}.</td>
                <td>{{ $it['keterangan'] }}</td>
                <td style="text-align:right;">
                    {{ $it['harga'] > 0 ? number_format($it['harga'], 0, ',', '.') : '-' }},
                </td>
                <td style="text-align:center;">
                    {{ $it['catatan'] !== '' ? $it['catatan'] : ($lama ? $lama . ' hari' : '-') }}
                </td>
            </tr>
        @empty
            @for ($i = 0; $i < 5; $i++)
                <tr style="height:22px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        @endforelse

        @if (count($items) > 0 && count($items) < 5)
            @for ($i = count($items); $i < 5; $i++)
                <tr style="height:22px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        @endif

        <tr style="font-weight:bold;">
            <td></td>
            <td>JUMLAH</td>
            <td style="text-align:right;">
                {{ $total > 0 ? number_format($total, 0, ',', '.') : '-' }},
            </td>
            <td></td>
        </tr>
    </table>

    <br><br>

    <table style="width:92%; margin:0 auto; font-size:10.5pt;">
        <tr>
            <td style="width:50%; vertical-align:top;"><br>
                Telah dibayar sejumlah<br>
                <b>Rp. {{ $total > 0 ? number_format($total, 0, ',', '.') : '-' }},-</b><br><br>
                Bendahara Pengeluaran<br><br><br><br>

                <b style="text-transform:uppercase; text-decoration: underline;">
                    {{ strtoupper($namaBendahara) }}
                </b><br>
                <span style="white-space: pre;">NIP. {{ $nipBendahara }}</span>
            </td>

            <td style="width:50%; vertical-align:top; text-align:left; padding-left:40px;">
                Sumenep, {{ $tglLampiran }}<br>
                Telah menerima jumlah sebesar<br>
                <b>Rp. {{ $total > 0 ? number_format($total, 0, ',', '.') : '-' }},-</b><br><br>
                Yang Menerima,<br><br><br><br>

                <b style="text-transform:uppercase; text-decoration: underline;">
                    {{ strtoupper($namaPenerima) }}
                </b><br>
                <span style="white-space: pre;">NIP. {{ $nipPenerima }}</span>
            </td>
        </tr>
    </table>

    <br><br>

    <div style="width:92%; margin:0 auto; border-top:1px solid #000;"></div>

    <br>

    <div style="text-align:center; font-weight:bold; text-transform:uppercase; font-size:10.5pt;">
        PERHITUNGAN SPPD RAMPUNG
    </div>

    <br>

    <table>
        <tr>
            <td style="width:60%; vertical-align:top;">
                <table style="width:100%;">
                    <tr>
                        <td style="width:55%;">Ditetapkan Sejumlah</td>
                        <td style="width:5%;">:</td>
                        <td style="width:10%;">Rp.</td>
                        <td style="text-align:right;">
                            <b>{{ $total > 0 ? number_format($total, 0, ',', '.') : '-' }},-</b>
                        </td>
                    </tr>
                    <tr>
                        <td>Yang Telah Dibayar Semula</td>
                        <td>:</td>
                        <td>Rp.</td>
                        <td style="text-align:right;">
                            <b>{{ $total > 0 ? number_format($total, 0, ',', '.') : '-' }},-</b>
                        </td>
                    </tr>
                    <tr>
                        <td>Sisa Telah Dibayar Semula</td>
                        <td>:</td>
                        <td>Rp.</td>
                        <td style="text-align:right;">-</td>
                    </tr>
                    <tr>
                        <td>Sisa Kurang / Lebih</td>
                        <td>:</td>
                        <td>Rp.</td>
                        <td style="text-align:right;">-</td>
                    </tr>
                </table>
            </td>

            <td style="width:40%; vertical-align:top; text-align:left; padding-top:105px;">
                An. Kuasa Pengguna Anggaran<br> <br>
                {{ strtoupper($jabatanPejabat) }}<br><br><br><br>

                <b style="text-transform:uppercase; text-decoration: underline;">
                    {{ strtoupper($namaPejabat) }}
                </b><br>
                <span style="white-space: pre;">NIP. {{ $nipPejabat }}</span>
            </td>
        </tr>
    </table>

</div>
