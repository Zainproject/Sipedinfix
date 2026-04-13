@extends('index')

@section('main')
    <div id="content">
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h1 class="h3 mb-0 text-gray-800">Data SPT</h1>

                <a href="{{ route('spt.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah SPT
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- SEARCH --}}
            <div class="card mb-3 shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('spt.index') }}" id="searchFormSpt">
                        <div class="form-group mb-1">
                            <label for="searchSpt" class="font-weight-bold text-primary mb-2">
                                Pencarian Data SPT
                            </label>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                </div>

                                <input type="text" id="searchSpt" name="search" class="form-control"
                                    placeholder="Ketik nomor surat, keperluan, petugas, tujuan, status, MAK, atau kwitansi..."
                                    value="{{ request('search') }}" autocomplete="off">

                                <div class="input-group-append">
                                    <button type="button" id="clearSearchSpt" class="btn btn-light border"
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

            <div class="card shadow">
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th width="60">No</th>
                                    <th>Nomor Surat</th>
                                    <th>Keperluan</th>
                                    <th>Petugas</th>
                                    <th>Tujuan</th>
                                    <th>Tanggal</th>
                                    <th>Status Anggaran</th>
                                    <th width="210" class="text-center">Aksi</th>
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
                                                if (
                                                    $t->jenis_tujuan === 'kabupaten_kota' ||
                                                    $t->jenis_tujuan === 'kota'
                                                ) {
                                                    return $t->deskripsi_kota;
                                                }
                                                if (
                                                    $t->jenis_tujuan === 'lain_lain' ||
                                                    $t->jenis_tujuan === 'lainnya'
                                                ) {
                                                    return $t->deskripsi_lainnya;
                                                }
                                                return $t->poktan_nama ?? ($t->deskripsi_kota ?? $t->deskripsi_lainnya);
                                            })
                                            ->filter()
                                            ->unique()
                                            ->values();

                                        $petugasText = $petugasList->implode(' | ');
                                        $tujuanText = $tujuanList->implode(' | ');

                                        $tanggalText =
                                            \Carbon\Carbon::parse($spt->tanggal_berangkat)->format('d/m/Y') .
                                            ' - ' .
                                            \Carbon\Carbon::parse($spt->tanggal_kembali)->format('d/m/Y');

                                        $statusBendaharaRaw = trim((string) ($spt->status_bendahara ?? ''));
                                        $statusBendaharaText = $statusBendaharaRaw !== '' ? $statusBendaharaRaw : '-';
                                        $isStatusSelesai = str_contains(strtolower($statusBendaharaRaw), 'sudah');

                                        $makText = $spt->keuangan->mak ?? '-';
                                        $nomorKwitansiText = $spt->keuangan->nomor_kwitansi ?? '-';
                                        $totalBiayaText = $spt->keuangan
                                            ? 'Rp ' . number_format($spt->keuangan->total_biaya ?? 0, 0, ',', '.')
                                            : '-';

                                        $detailBiayaPreview = '-';
                                        if ($spt->keuangan && !empty($spt->keuangan->detail_petugas)) {
                                            $html = '';

                                            foreach ($spt->keuangan->detail_petugas as $detail) {
                                                $html .= '<div style="margin-bottom:10px;">';
                                                $html .=
                                                    '<strong>' . e($detail['nama_petugas'] ?? '-') . '</strong><br>';

                                                if (!empty($detail['rincian']) && is_array($detail['rincian'])) {
                                                    foreach ($detail['rincian'] as $idx => $r) {
                                                        $html .=
                                                            $idx +
                                                            1 .
                                                            '. ' .
                                                            e($r['keterangan'] ?? '-') .
                                                            ' - Rp ' .
                                                            number_format((float) ($r['harga'] ?? 0), 0, ',', '.');

                                                        if (!empty($r['catatan'])) {
                                                            $html .= ' <em>(' . e($r['catatan']) . ')</em>';
                                                        }

                                                        $html .= '<br>';
                                                    }
                                                } else {
                                                    $html .= '-<br>';
                                                }

                                                $html .=
                                                    '<strong>Total ' .
                                                    e($detail['nama_petugas'] ?? '-') .
                                                    ': Rp ' .
                                                    number_format((float) ($detail['total_biaya'] ?? 0), 0, ',', '.') .
                                                    '</strong>';

                                                $html .= '</div>';
                                            }

                                            $detailBiayaPreview = $html;
                                        }
                                    @endphp

                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $spt->nomor_surat }}</td>
                                        <td>{{ $spt->keperluan }}</td>

                                        <td>
                                            @foreach ($petugasList as $p)
                                                <div>{{ $p }}</div>
                                            @endforeach
                                        </td>

                                        <td>
                                            @foreach ($tujuanList as $t)
                                                <div>{{ $t }}</div>
                                            @endforeach
                                        </td>

                                        <td>{{ $tanggalText }}</td>

                                        <td>
                                            @if ($isStatusSelesai)
                                                <span class="badge badge-success">
                                                    {{ $statusBendaharaText }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    {{ $statusBendaharaText }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <a href="{{ route('spt.edit', $spt->id) }}" class="btn btn-warning btn-sm"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <button class="btn btn-info btn-sm btn-preview-spt" data-toggle="modal"
                                                data-target="#previewSptModal"
                                                data-print-url="{{ route('spt.print', $spt->id) }}"
                                                data-nomor_surat="{{ $spt->nomor_surat }}"
                                                data-keperluan="{{ $spt->keperluan }}" data-petugas="{{ $petugasText }}"
                                                data-tujuan="{{ $tujuanText }}" data-tanggal="{{ $tanggalText }}"
                                                data-status="{{ $statusBendaharaText }}"
                                                data-status_selesai="{{ $isStatusSelesai ? '1' : '0' }}"
                                                data-arahan="{{ $spt->arahan }}"
                                                data-masalah_temuan="{{ $spt->masalah_temuan }}"
                                                data-saran_tindakan="{{ $spt->saran_tindakan }}"
                                                data-lain_lain="{{ $spt->lain_lain }}" data-mak="{{ $makText }}"
                                                data-nomor_kwitansi="{{ $nomorKwitansiText }}"
                                                data-total_biaya="{{ $totalBiayaText }}"
                                                data-rincian_biaya="{{ $detailBiayaPreview }}" title="Preview">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <a href="{{ route('spt.print', $spt->id) }}" class="btn btn-success btn-sm"
                                                title="Print">
                                                <i class="fas fa-print"></i>
                                            </a>

                                            <form action="{{ route('spt.destroy', $spt->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Yakin hapus?')"
                                                    class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Data kosong</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL --}}
    <div class="modal fade" id="previewSptModal" tabindex="-1" role="dialog" aria-labelledby="previewSptModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">

                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="previewSptModalLabel">Preview SPT</h5>
                    <button class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Nomor</th>
                            <td id="preview_nomor_surat"></td>
                        </tr>
                        <tr>
                            <th>Keperluan</th>
                            <td id="preview_keperluan"></td>
                        </tr>
                        <tr>
                            <th>Petugas</th>
                            <td id="preview_petugas"></td>
                        </tr>
                        <tr>
                            <th>Tujuan</th>
                            <td id="preview_tujuan"></td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td id="preview_tanggal"></td>
                        </tr>
                        <tr>
                            <th>Status Anggaran</th>
                            <td id="preview_status"></td>
                        </tr>
                        <tr>
                            <th>Arahan</th>
                            <td id="preview_arahan"></td>
                        </tr>
                        <tr>
                            <th>Masalah</th>
                            <td id="preview_masalah_temuan"></td>
                        </tr>
                        <tr>
                            <th>Saran</th>
                            <td id="preview_saran_tindakan"></td>
                        </tr>
                        <tr>
                            <th>Lain</th>
                            <td id="preview_lain_lain"></td>
                        </tr>
                        <tr>
                            <th>MAK</th>
                            <td id="preview_mak"></td>
                        </tr>
                        <tr>
                            <th>Nomor Kwitansi</th>
                            <td id="preview_nomor_kwitansi"></td>
                        </tr>
                        <tr>
                            <th>Rincian Biaya Bendahara</th>
                            <td id="preview_rincian_biaya"></td>
                        </tr>
                        <tr>
                            <th>Total Biaya</th>
                            <td id="preview_total_biaya"></td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        function formatNumbered(text) {
            if (!text) return '-';

            return text.split(/\n/)
                .filter(t => t.trim() !== '')
                .map((t, i) => (i + 1) + '. ' + t)
                .join('<br>');
        }

        document.querySelectorAll('.btn-preview-spt').forEach(btn => {
            btn.addEventListener('click', function() {
                preview_nomor_surat.innerHTML = this.dataset.nomor_surat || '-';
                preview_keperluan.innerHTML = this.dataset.keperluan || '-';
                preview_petugas.innerHTML = this.dataset.petugas ? this.dataset.petugas.split(' | ').join(
                    '<br>') : '-';
                preview_tujuan.innerHTML = this.dataset.tujuan ? this.dataset.tujuan.split(' | ').join(
                    '<br>') : '-';
                preview_tanggal.innerHTML = this.dataset.tanggal || '-';

                if (this.dataset.status_selesai === '1') {
                    preview_status.innerHTML = '<span class="badge badge-success">' + (this.dataset
                        .status || '-') + '</span>';
                } else {
                    preview_status.innerHTML = '<span class="badge badge-secondary">' + (this.dataset
                        .status || '-') + '</span>';
                }

                preview_arahan.innerHTML = formatNumbered(this.dataset.arahan);
                preview_masalah_temuan.innerHTML = formatNumbered(this.dataset.masalah_temuan);
                preview_saran_tindakan.innerHTML = formatNumbered(this.dataset.saran_tindakan);
                preview_lain_lain.innerHTML = formatNumbered(this.dataset.lain_lain);

                preview_mak.innerHTML = this.dataset.mak || '-';
                preview_nomor_kwitansi.innerHTML = this.dataset.nomor_kwitansi || '-';
                preview_total_biaya.innerHTML = this.dataset.total_biaya || '-';
                preview_rincian_biaya.innerHTML = this.dataset.rincian_biaya || '-';
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('searchFormSpt');
            const input = document.getElementById('searchSpt');
            const clearBtn = document.getElementById('clearSearchSpt');

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
