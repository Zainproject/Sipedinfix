@extends('index')

@section('main')
    <div id="content">
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <div>
                    <h1 class="h3 mb-1 text-gray-800">Rekap Surat Keluar</h1>
                    <div class="text-muted small">Filter & rekap surat berdasarkan data SPT.</div>
                </div>

                <div class="dropdown">
                    <button class="btn btn-success btn-sm shadow-sm dropdown-toggle" type="button" id="ddPrint"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-print mr-1"></i> Cetak
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="ddPrint">
                        <a class="dropdown-item"
                            href="{{ route('rekap-surat-keluar.print', array_merge(request()->query(), ['jenis' => 'spt'])) }}"
                            target="_blank">
                            Cetak Data SPT
                        </a>
                        <a class="dropdown-item"
                            href="{{ route('rekap-surat-keluar.print', array_merge(request()->query(), ['jenis' => 'petugas'])) }}"
                            target="_blank">
                            Cetak Rekap Petugas
                        </a>
                        <a class="dropdown-item"
                            href="{{ route('rekap-surat-keluar.print', array_merge(request()->query(), ['jenis' => 'poktan'])) }}"
                            target="_blank">
                            Cetak Rekap Tujuan
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item"
                            href="{{ route('rekap-surat-keluar.print', array_merge(request()->query(), ['jenis' => 'all'])) }}"
                            target="_blank">
                            Cetak Semua
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('rekap-surat-keluar.index') }}" id="formFilterRekap">
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
                                    @for ($b = 1; $b <= 12; $b++)
                                        <option value="{{ $b }}"
                                            {{ (string) request('bulan') === (string) $b ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                                        </option>
                                    @endfor
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
                                <label class="small mb-1 text-muted">Pencarian</label>
                                <input type="text" name="keyword" class="form-control form-control-sm"
                                    placeholder="Nomor surat / kwitansi / keperluan / petugas / tujuan..."
                                    value="{{ request('keyword') }}">
                            </div>
                        </div>

                        <div class="mt-2">
                            <button class="btn btn-primary btn-sm mr-2" type="submit">Terapkan</button>
                            <a href="{{ route('rekap-surat-keluar.index') }}"
                                class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 mb-2">
                    <div class="card border-left-info shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Surat</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalSurat) }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="card border-left-success shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Biaya</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="font-weight-bold text-primary">Data SPT</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Surat</th>
                                    <th>Keperluan</th>
                                    <th>Petugas</th>
                                    <th>Tujuan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>MAK</th>
                                    <th>No Kwitansi</th>
                                    <th class="text-right">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($spts as $i => $spt)
                                    @php
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
                                                    return $t->deskripsi_kota;
                                                }

                                                if ($t->jenis_tujuan === 'lain_lain') {
                                                    return $t->deskripsi_lainnya;
                                                }

                                                return '-';
                                            })
                                            ->filter()
                                            ->unique()
                                            ->values();

                                        $tanggalText =
                                            \Carbon\Carbon::parse($spt->tanggal_berangkat)->format('d-m-Y') .
                                            ' s/d ' .
                                            \Carbon\Carbon::parse($spt->tanggal_kembali)->format('d-m-Y');

                                        $statusBendahara = trim((string) ($spt->status_bendahara ?? ''));
                                        $statusPencairan = strtolower(trim((string) ($spt->status_pencairan ?? '')));
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $spt->nomor_surat ?? '-' }}</td>
                                        <td>{{ $spt->keperluan ?? '-' }}</td>
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
                                        <td>{{ $tanggalText }}</td>

                                        {{-- STATUS BERWARNA --}}
                                        <td>
                                            @if (!$spt->keuangan)
                                                <span class="badge badge-secondary">
                                                    Belum diisi bendahara
                                                </span>
                                            @else
                                                <div class="mb-1">
                                                    <span class="badge badge-info">
                                                        {{ $statusBendahara !== '' ? $statusBendahara : 'Sudah diisi bendahara' }}
                                                    </span>
                                                </div>

                                                @if ($statusPencairan === 'sudah dicairkan')
                                                    <span class="badge badge-success">Sudah Dicairkan</span>
                                                @elseif ($statusPencairan === 'selesai')
                                                    <span class="badge badge-primary">Selesai</span>
                                                @else
                                                    <span class="badge badge-warning">Belum Cair</span>
                                                @endif
                                            @endif
                                        </td>

                                        <td>{{ optional($spt->keuangan)->mak ?? '-' }}</td>
                                        <td>{{ optional($spt->keuangan)->nomor_kwitansi ?? '-' }}</td>
                                        <td class="text-right">
                                            Rp
                                            {{ number_format((float) optional($spt->keuangan)->total_biaya, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">Belum ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="font-weight-bold text-primary">Rekap Petugas</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Petugas</th>
                                    <th class="text-right">Jumlah SPT</th>
                                    <th class="text-right">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapPetugas as $i => $rp)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $rp['nama'] }} <span
                                                class="text-muted small">({{ $rp['nip'] }})</span></td>
                                        <td class="text-right">{{ number_format($rp['jumlah']) }}</td>
                                        <td class="text-right">Rp {{ number_format($rp['total_biaya'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Tidak ada data rekap petugas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="font-weight-bold text-primary">Rekap Tujuan / Poktan</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tujuan</th>
                                    <th class="text-right">Jumlah SPT</th>
                                    <th class="text-right">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapPoktan as $i => $rk)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $rk['nama'] }}</td>
                                        <td class="text-right">{{ number_format($rk['jumlah']) }}</td>
                                        <td class="text-right">Rp {{ number_format($rk['total_biaya'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Tidak ada data rekap tujuan.
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
                    document.getElementById('formFilterRekap').submit();
                });
            });
        </script>
    @endpush
@endsection
