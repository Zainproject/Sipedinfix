@extends('index')

@section('main')
    <div id="content">
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <div>
                    <h1 class="h3 mb-1 text-gray-800">Rekap Anggaran</h1>
                    <div class="text-muted small">Filter dan rekap data anggaran berdasarkan SPT yang memiliki data keuangan.
                    </div>
                </div>

                <div class="dropdown">
                    <button class="btn btn-success btn-sm shadow-sm dropdown-toggle" type="button" id="ddPrint"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-print mr-1"></i> Cetak
                    </button>

                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="ddPrint">
                        <a class="dropdown-item"
                            href="{{ route('rekap-anggaran.print', array_merge(request()->query(), ['jenis' => 'detail'])) }}"
                            target="_blank">
                            <i class="fas fa-file-invoice-dollar mr-2 text-success"></i> Cetak Detail Anggaran
                        </a>

                        <a class="dropdown-item"
                            href="{{ route('rekap-anggaran.print', array_merge(request()->query(), ['jenis' => 'rekap'])) }}"
                            target="_blank">
                            <i class="fas fa-wallet mr-2 text-success"></i> Cetak Rekap Anggaran
                        </a>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item"
                            href="{{ route('rekap-anggaran.print', array_merge(request()->query(), ['jenis' => 'all'])) }}"
                            target="_blank">
                            <i class="fas fa-layer-group mr-2 text-success"></i> Cetak Semua
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <div class="font-weight-bold text-primary">
                        <i class="fas fa-filter mr-2"></i>Filter Data
                    </div>
                    <div class="small text-muted">
                        Filter anggaran berdasarkan periode, MAK, petugas, status pencairan, dan keyword.
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('rekap-anggaran.index') }}" id="formFilterRekapAnggaran">
                        <div class="form-row">
                            <div class="col-md-2 mb-2">
                                <label class="small mb-1 text-muted">Tahun</label>
                                <select name="tahun" class="form-control form-control-sm js-autosubmit">
                                    <option value="">Semua</option>
                                    @foreach ($tahunOptions as $th)
                                        <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>
                                            {{ $th }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="small mb-1 text-muted">Bulan</label>
                                <select name="bulan" class="form-control form-control-sm js-autosubmit">
                                    <option value="">Semua</option>
                                    @php
                                        $bulanMap = [
                                            1 => 'Januari',
                                            2 => 'Februari',
                                            3 => 'Maret',
                                            4 => 'April',
                                            5 => 'Mei',
                                            6 => 'Juni',
                                            7 => 'Juli',
                                            8 => 'Agustus',
                                            9 => 'September',
                                            10 => 'Oktober',
                                            11 => 'November',
                                            12 => 'Desember',
                                        ];
                                    @endphp
                                    @foreach ($bulanMap as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ (string) request('bulan') === (string) $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="small mb-1 text-muted">MAK</label>
                                <select name="mak" class="form-control form-control-sm js-autosubmit">
                                    <option value="">Semua</option>
                                    @foreach ($makOptions as $mk)
                                        <option value="{{ $mk }}" {{ request('mak') == $mk ? 'selected' : '' }}>
                                            {{ $mk }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="small mb-1 text-muted">Petugas</label>
                                <select name="petugas" class="form-control form-control-sm js-autosubmit">
                                    <option value="">Semua</option>
                                    @foreach ($petugasOptions as $p)
                                        <option value="{{ $p->nip }}"
                                            {{ request('petugas') == $p->nip ? 'selected' : '' }}>
                                            {{ $p->nama }} ({{ $p->nip }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="small mb-1 text-muted">Status Pencairan</label>
                                <select name="status" class="form-control form-control-sm js-autosubmit">
                                    <option value="">Semua</option>
                                    <option value="belum cair" {{ request('status') == 'belum cair' ? 'selected' : '' }}>
                                        belum cair
                                    </option>
                                    <option value="sudah dicairkan"
                                        {{ request('status') == 'sudah dicairkan' ? 'selected' : '' }}>
                                        sudah dicairkan
                                    </option>
                                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>
                                        selesai
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="small mb-1 text-muted">Pencarian</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="keyword" class="form-control"
                                        placeholder="Nomor surat / kwitansi / keperluan / petugas / MAK / status..."
                                        value="{{ request('keyword') }}">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                            <button class="btn btn-primary btn-sm shadow-sm mr-2" type="submit">
                                <i class="fas fa-filter mr-1"></i> Terapkan
                            </button>

                            <a href="{{ route('rekap-anggaran.index') }}"
                                class="btn btn-outline-secondary btn-sm shadow-sm">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>

                            @php
                                $hasFilter =
                                    request()->filled('tahun') ||
                                    request()->filled('bulan') ||
                                    request()->filled('mak') ||
                                    request()->filled('petugas') ||
                                    request()->filled('status') ||
                                    request()->filled('keyword');
                            @endphp

                            @if ($hasFilter)
                                <span class="badge badge-info badge-pill ml-2">Filter aktif</span>
                            @else
                                <span class="badge badge-light badge-pill ml-2">Tanpa filter</span>
                            @endif
                        </div>
                    </form>

                    <div class="row mt-3">
                        <div class="col-md-6 mb-2">
                            <div class="card border-left-info shadow-sm h-100">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Total SPT Beranggaran
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ number_format($totalSpt) }}
                                            </div>
                                        </div>
                                        <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <div class="card border-left-success shadow-sm h-100">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total Biaya
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp {{ number_format($totalBiaya ?? 0, 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <div class="font-weight-bold text-primary">
                        <i class="fas fa-wallet mr-2"></i>Rekap Anggaran
                    </div>
                    <div class="small text-muted">Ringkasan per MAK dan status pencairan</div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:60px;">No</th>
                                    <th>MAK</th>
                                    <th>Status Bendahara</th>
                                    <th>Status Pencairan</th>
                                    <th class="text-right" style="width:160px;">Jumlah SPT</th>
                                    <th class="text-right" style="width:180px;">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapAnggaran as $i => $ra)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $ra['mak'] ?? '-' }}</td>
                                        <td>{{ $ra['status_bendahara'] ?? '-' }}</td>
                                        <td>{{ $ra['status_pencairan'] ?? '-' }}</td>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($ra['jumlah_spt'] ?? 0) }}
                                        </td>
                                        <td class="text-right">
                                            Rp {{ number_format($ra['total_biaya'] ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada data rekap anggaran.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <div class="font-weight-bold text-primary">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>Detail Anggaran
                    </div>
                    <div class="small text-muted">Rincian anggaran per SPT</div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:60px;">No</th>
                                    <th>No Surat</th>
                                    <th>Tanggal</th>
                                    <th>Petugas</th>
                                    <th>Keperluan</th>
                                    <th>MAK</th>
                                    <th>No Kwitansi</th>
                                    <th>Status Bendahara</th>
                                    <th>Status Pencairan</th>
                                    <th class="text-right">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($detailAnggaran as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $item['nomor_surat'] }}</td>
                                        <td>{{ $item['tanggal'] }}</td>
                                        <td>{{ $item['petugas'] }}</td>
                                        <td>{{ $item['keperluan'] }}</td>
                                        <td>{{ $item['mak'] }}</td>
                                        <td>{{ $item['nomor_kwitansi'] }}</td>
                                        <td>{{ $item['status_bendahara'] }}</td>
                                        <td>{{ $item['status_pencairan'] }}</td>
                                        <td class="text-right">Rp {{ number_format($item['total_biaya'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">Tidak ada data detail anggaran.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.js-autosubmit').forEach(function(el) {
                el.addEventListener('change', function() {
                    document.getElementById('formFilterRekapAnggaran').submit();
                });
            });
        </script>
    @endpush
@endsection
