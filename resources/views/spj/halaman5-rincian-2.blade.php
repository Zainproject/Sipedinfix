@php
    use Carbon\Carbon;

    $penerima = $penerima ?? $spt->petugasList()->first();

    $start = $spt->tanggal_berangkat ? Carbon::parse($spt->tanggal_berangkat) : null;
    $end = $spt->tanggal_kembali ? Carbon::parse($spt->tanggal_kembali) : null;

    $tglBerangkat = $start ? $start->translatedFormat('d F Y') : '-';
    $lama = $start && $end ? $start->diffInDays($end) + 1 : 0;

    /*
    |--------------------------------------------------------------------------
    | AMBIL DATA RINCIAN SESUAI INPUT BENDAHARA
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

    /*
    |--------------------------------------------------------------------------
    | PEJABAT / BENDAHARA
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
@endphp

<div class="page">
    <div style="text-align:center; margin-bottom:15px;">
        <b><u>RINCIAN RENCANA BIAYA PERJALANAN DINAS (REALISASI)</u></b>
    </div>

    <table style="width:60%; margin-bottom:10px;">
        <tr>
            <td style="width:40%;">Lampiran SPD Nomor</td>
            <td style="width:5%;">:</td>
            <td>{{ $spt->nomor_surat }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $tglBerangkat }}</td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; margin-bottom:15px;" border="1" cellpadding="4">
        <tr style="background:#d9d9d9; text-align:center; font-weight:bold;">
            <td style="width:5%;">NO</td>
            <td style="width:45%;">PERINCIAN BIAYA</td>
            <td style="width:20%;">JUMLAH (Rp)</td>
            <td style="width:30%;">KETERANGAN</td>
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
            @for ($i = 0; $i < 3; $i++)
                <tr style="height:18px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        @endforelse

        @if (count($items) > 0 && count($items) < 3)
            @for ($i = count($items); $i < 3; $i++)
                <tr style="height:18px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        @endif

        <tr style="font-weight:bold;">
            <td colspan="2" style="text-align:center;">JUMLAH</td>
            <td style="text-align:right;">
                {{ $total > 0 ? number_format($total, 0, ',', '.') : '-' }},
            </td>
            <td></td>
        </tr>
    </table>

    <table style="width:100%; margin-bottom:15px;">
        <tr>
            <td style="width:50%; vertical-align:top;"><br>
                Telah dibayar sejumlah<br>
                <b>Rp. {{ $total > 0 ? number_format($total, 0, ',', '.') : '-' }},-</b><br><br>
                Bendahara Pengeluaran<br>
                <div class="nama">{{ strtoupper($namaBendahara) }}</div>
                <div class="nip" style="white-space: pre;">NIP. {{ $nipBendahara }}</div>
            </td>

            <td style="width:50%; vertical-align:top;">
                Sumenep, {{ now()->translatedFormat('d F Y') }}<br>
                Telah menerima jumlah sebesar<br>
                <b>Rp. {{ $total > 0 ? number_format($total, 0, ',', '.') : '-' }},-</b><br><br>
                Yang Menerima,<br>
                <div class="nama">{{ strtoupper($penerima->nama ?? '-') }}</div>
                NIP. {{ $penerima->nip ?? '-' }}
            </td>
        </tr>
    </table>
</div>
