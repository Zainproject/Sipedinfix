@extends('index')

@section('main')
    <div id="content">
        <div class="container-fluid">

            <h1 class="h3 mb-3 text-gray-800">Data Keuangan SPT</h1>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- DASHBOARD --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Dana Masuk dari Pemerintah
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($totalDanaMasuk ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Digunakan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($totalDigunakan ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Sisa Dana
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($sisaDana ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEARCH --}}
            <div class="card mb-3 shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('keuangan.index') }}" id="searchFormKeuangan">
                        <div class="form-group mb-1">
                            <label for="searchKeuangan" class="font-weight-bold text-primary mb-2">
                                Pencarian Data Keuangan
                            </label>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                </div>

                                <input type="text" id="searchKeuangan" name="search" class="form-control"
                                    placeholder="Ketik nomor surat, keperluan, petugas, tujuan, status, MAK, atau kwitansi..."
                                    value="{{ request('search') }}" autocomplete="off">

                                <div class="input-group-append">
                                    <button type="button" id="clearSearchKeuangan" class="btn btn-light border"
                                        title="Hapus pencarian" style="{{ request('search') ? '' : 'display:none;' }}">
                                        &times;
                                    </button>

                                    <button class="btn btn-primary" type="submit">
                                        Cari
                                    </button>
                                </div>
                            </div>
                        </div>

                        <small class="text-muted">
                            Tekan tombol Cari untuk menampilkan hasil.
                        </small>
                    </form>
                </div>
            </div>

            {{-- TABEL --}}
            <div class="card shadow">
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Nomor Surat</th>
                                <th>Keperluan</th>
                                <th>Petugas</th>
                                <th>Tujuan</th>
                                <th>Status Bendahara</th>
                                <th>Status Pencairan</th>
                                <th>Total</th>
                                <th width="220">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($spts as $i => $spt)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $spt->nomor_surat }}</td>
                                    <td>{{ $spt->keperluan }}</td>

                                    <td>
                                        @forelse ($spt->petugasRel as $p)
                                            <div>{{ $p->nama }}</div>
                                        @empty
                                            <div>-</div>
                                        @endforelse
                                    </td>

                                    <td>
                                        @forelse ($spt->sptTujuan as $t)
                                            <div>
                                                @if ($t->jenis_tujuan === 'poktan')
                                                    Poktan {{ $t->poktan_nama ?? '-' }},
                                                    Desa {{ optional($t->poktan)->desa ?? '-' }},
                                                    Kecamatan {{ optional($t->poktan)->kecamatan ?? '-' }}
                                                @elseif ($t->jenis_tujuan === 'kota' || $t->jenis_tujuan === 'kabupaten_kota')
                                                    {{ $t->deskripsi_kota ?? '-' }}
                                                @elseif ($t->jenis_tujuan === 'lainnya' || $t->jenis_tujuan === 'lain_lain')
                                                    {{ $t->deskripsi_lainnya ?? '-' }}
                                                @else
                                                    {{ $t->poktan_nama ?? ($t->deskripsi_kota ?? ($t->deskripsi_lainnya ?? '-')) }}
                                                @endif
                                            </div>
                                        @empty
                                            <div>-</div>
                                        @endforelse
                                    </td>

                                    {{-- STATUS BENDAHARA --}}
                                    <td>
                                        @if ($spt->keuangan)
                                            <span class="badge badge-info">
                                                {{ $spt->status_bendahara ?: 'sudah diisi bendahara' }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">
                                                {{ $spt->status_bendahara ?: 'belum diisi bendahara' }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- STATUS PENCAIRAN --}}
                                    <td>
                                        @if ($spt->keuangan)
                                            @php
                                                $statusPencairan = strtolower(
                                                    trim($spt->status_pencairan ?? 'belum cair'),
                                                );
                                            @endphp

                                            <form action="{{ route('keuangan.updateStatus', $spt->id) }}" method="POST"
                                                class="mb-1">
                                                @csrf
                                                @method('PATCH')

                                                <select name="status_pencairan" class="form-control form-control-sm"
                                                    onchange="this.form.submit()">
                                                    <option value="belum cair"
                                                        {{ $statusPencairan === 'belum cair' ? 'selected' : '' }}>
                                                        Belum Cair
                                                    </option>
                                                    <option value="sudah dicairkan"
                                                        {{ $statusPencairan === 'sudah dicairkan' ? 'selected' : '' }}>
                                                        Sudah Dicairkan
                                                    </option>
                                                    <option value="selesai"
                                                        {{ $statusPencairan === 'selesai' ? 'selected' : '' }}>
                                                        Selesai
                                                    </option>
                                                </select>
                                            </form>

                                            @if ($statusPencairan === 'sudah dicairkan')
                                                <span class="badge badge-success">Sudah Dicairkan</span>
                                            @elseif ($statusPencairan === 'selesai')
                                                <span class="badge badge-primary">Selesai</span>
                                            @else
                                                <span class="badge badge-warning">Belum Cair</span>
                                            @endif
                                        @else
                                            <span class="badge badge-light">Menunggu input bendahara</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $spt->keuangan ? 'Rp ' . number_format($spt->keuangan->total_biaya, 0, ',', '.') : '-' }}
                                    </td>

                                    <td class="text-center">
                                        @if (!$spt->keuangan)
                                            <a href="{{ route('keuangan.create', $spt->id) }}"
                                                class="btn btn-sm btn-success" title="Input">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-info" title="Review"
                                                data-toggle="modal" data-target="#reviewKeuanganModal{{ $spt->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <a href="{{ route('keuangan.edit', $spt->id) }}" class="btn btn-sm btn-warning"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('keuangan.destroy', $spt->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus data keuangan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MODAL REVIEW --}}
            @foreach ($spts as $spt)
                @if ($spt->keuangan)
                    <div class="modal fade" id="reviewKeuanganModal{{ $spt->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="reviewKeuanganLabel{{ $spt->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title" id="reviewKeuanganLabel{{ $spt->id }}">
                                        Review Keuangan SPT
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal"
                                        aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body">
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th width="30%">Nomor Surat</th>
                                                    <td>{{ $spt->nomor_surat ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Keperluan</th>
                                                    <td>{{ $spt->keperluan ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Petugas</th>
                                                    <td>
                                                        @forelse ($spt->petugasRel as $p)
                                                            <div>{{ $p->nama ?? '-' }}</div>
                                                        @empty
                                                            <div>-</div>
                                                        @endforelse
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Tujuan</th>
                                                    <td>
                                                        @forelse ($spt->sptTujuan as $t)
                                                            <div>
                                                                @if ($t->jenis_tujuan === 'poktan')
                                                                    Poktan {{ $t->poktan_nama ?? '-' }},
                                                                    Desa {{ optional($t->poktan)->desa ?? '-' }},
                                                                    Kecamatan {{ optional($t->poktan)->kecamatan ?? '-' }}
                                                                @elseif ($t->jenis_tujuan === 'kota' || $t->jenis_tujuan === 'kabupaten_kota')
                                                                    {{ $t->deskripsi_kota ?? '-' }}
                                                                @elseif ($t->jenis_tujuan === 'lainnya' || $t->jenis_tujuan === 'lain_lain')
                                                                    {{ $t->deskripsi_lainnya ?? '-' }}
                                                                @else
                                                                    {{ $t->poktan_nama ?? ($t->deskripsi_kota ?? ($t->deskripsi_lainnya ?? '-')) }}
                                                                @endif
                                                            </div>
                                                        @empty
                                                            <div>-</div>
                                                        @endforelse
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>MAK</th>
                                                    <td>{{ $spt->keuangan->mak ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Nomor Kwitansi</th>
                                                    <td>{{ $spt->keuangan->nomor_kwitansi ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Status Bendahara</th>
                                                    <td>{{ $spt->status_bendahara ?? 'sudah diisi bendahara' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Status Pencairan</th>
                                                    <td>{{ $spt->status_pencairan ?? 'belum cair' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <td>
                                                        {{ $spt->tanggal_berangkat ? \Carbon\Carbon::parse($spt->tanggal_berangkat)->format('d/m/Y') : '-' }}
                                                        -
                                                        {{ $spt->tanggal_kembali ? \Carbon\Carbon::parse($spt->tanggal_kembali)->format('d/m/Y') : '-' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Total Hari</th>
                                                    <td>{{ $spt->total_hari ?? 0 }} hari</td>
                                                </tr>
                                                <tr>
                                                    <th>Total Biaya</th>
                                                    <td>
                                                        <strong>Rp
                                                            {{ number_format($spt->keuangan->total_biaya ?? 0, 0, ',', '.') }}</strong>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    @foreach ($spt->keuangan->detail_petugas ?? [] as $detail)
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <strong>{{ $detail['nama_petugas'] ?? '-' }}</strong>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered mb-0">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th width="60">No</th>
                                                                <th>Keterangan</th>
                                                                <th>Nominal</th>
                                                                <th>Catatan Bendahara</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse (($detail['rincian'] ?? []) as $rIndex => $rincian)
                                                                <tr>
                                                                    <td>{{ $rIndex + 1 }}</td>
                                                                    <td>{{ $rincian['keterangan'] ?? '-' }}</td>
                                                                    <td>Rp
                                                                        {{ number_format((float) ($rincian['harga'] ?? 0), 0, ',', '.') }}
                                                                    </td>
                                                                    <td>{{ $rincian['catatan'] ?? '-' }}</td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="4" class="text-center">Tidak ada
                                                                        rincian</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <th colspan="2" class="text-right">
                                                                    Total {{ $detail['nama_petugas'] ?? '-' }}
                                                                </th>
                                                                <th colspan="2">
                                                                    Rp
                                                                    {{ number_format((float) ($detail['total_biaya'] ?? 0), 0, ',', '.') }}
                                                                </th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('searchFormKeuangan');
            const input = document.getElementById('searchKeuangan');
            const clearBtn = document.getElementById('clearSearchKeuangan');

            if (!form || !input || !clearBtn) return;

            function toggleClearButton() {
                clearBtn.style.display = input.value.trim() !== '' ? '' : 'none';
            }

            toggleClearButton();

            input.addEventListener('input', function() {
                toggleClearButton();
            });

            clearBtn.addEventListener('click', function() {
                input.value = '';
                toggleClearButton();
                form.submit();
            });
        });
    </script>
@endsection
