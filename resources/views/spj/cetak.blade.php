@extends('spj.layout')

@section('content')
    @include('spj.halaman1-spt', ['spt' => $spt])
    <div class="page-break"></div>

    @include('spj.halaman2-spd', ['spt' => $spt])
    <div class="page-break"></div>

    @php
        $petugasList = $spt->petugasList();
    @endphp

    @foreach ($petugasList as $idx => $petugas)
        @include('spj.halaman3-kuitansi-1', [
            'spt' => $spt,
            'penerima' => $petugas,
            'index_petugas' => $idx,
        ])
        <div class="page-break"></div>

        @include('spj.halaman4-rincian-1', [
            'spt' => $spt,
            'penerima' => $petugas,
            'index_petugas' => $idx,
        ])
        <div class="page-break"></div>

        @include('spj.halaman5-rincian-2', [
            'spt' => $spt,
            'penerima' => $petugas,
            'index_petugas' => $idx,
        ])
        <div class="page-break"></div>
    @endforeach

    @include('spj.halaman6-laporan', ['spt' => $spt])
    <div class="page-break"></div>

    @include('spj.halaman7-sppd', ['spt' => $spt])
@endsection
