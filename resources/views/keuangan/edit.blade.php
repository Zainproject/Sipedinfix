@extends('index')

@section('main')
    <div id="content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0 text-gray-800">Edit Keuangan SPT</h1>

                <a href="{{ route('keuangan.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $detailPetugas = old('petugas', $spt->keuangan->detail_petugas ?? []);
                $nomorKwitansiAwal = old('nomor_kwitansi');

                if ($nomorKwitansiAwal === null) {
                    $bagianNomor = explode('/', $spt->keuangan->nomor_kwitansi ?? '');
                    $nomorKwitansiAwal = $bagianNomor[0] ?? 1;
                }
            @endphp

            {{-- INFORMASI SPT --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong>Informasi SPT</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th width="30%">Nomor</th>
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
                                                @elseif ($t->jenis_tujuan === 'kota')
                                                    {{ $t->deskripsi_kota ?? '-' }}
                                                @elseif ($t->jenis_tujuan === 'lainnya')
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- FORM KEUANGAN --}}
            <div class="card shadow">
                <div class="card-body">
                    <form action="{{ route('keuangan.update', $spt->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mak">MAK</label>
                                    <select name="mak" id="mak" class="form-control" required>
                                        <option value="">-- Pilih MAK --</option>
                                        <option value="APBN"
                                            {{ old('mak', $spt->keuangan->mak) == 'APBN' ? 'selected' : '' }}>APBN</option>
                                        <option value="APBD"
                                            {{ old('mak', $spt->keuangan->mak) == 'APBD' ? 'selected' : '' }}>APBD</option>
                                        <option value="BOK"
                                            {{ old('mak', $spt->keuangan->mak) == 'BOK' ? 'selected' : '' }}>BOK</option>
                                        <option value="DAK"
                                            {{ old('mak', $spt->keuangan->mak) == 'DAK' ? 'selected' : '' }}>DAK</option>
                                        <option value="LAINNYA"
                                            {{ old('mak', $spt->keuangan->mak) == 'LAINNYA' ? 'selected' : '' }}>LAINNYA
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nomor_kwitansi_input">Nomor Kwitansi</label>
                                    <input type="number" name="nomor_kwitansi" id="nomor_kwitansi_input"
                                        class="form-control" value="{{ $nomorKwitansiAwal }}" min="1" required>

                                    <small class="text-muted d-block mt-1">
                                        Nomor kwitansi terakhir:
                                        <strong>{{ $nomorKwitansiTerakhir ?? '-' }}</strong>
                                    </small>

                                    <small class="text-muted d-block">
                                        Format otomatis:
                                        <span id="preview_nomor_kwitansi">-</span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        @foreach ($detailPetugas as $petugasIndex => $petugasData)
                            <div class="card mb-4 border-left-primary">
                                <div class="card-header">
                                    <strong>Rincian Biaya -
                                        {{ $petugasData['nama_petugas'] ?? 'Petugas ' . ($petugasIndex + 1) }}</strong>
                                </div>
                                <div class="card-body petugas-card">
                                    <input type="hidden" name="petugas[{{ $petugasIndex }}][petugas_id]"
                                        value="{{ $petugasData['petugas_id'] ?? '' }}">

                                    <div class="biaya-container" data-petugas-index="{{ $petugasIndex }}">
                                        @php
                                            $rincianList = $petugasData['rincian'] ?? [];
                                            if (count($rincianList) === 0) {
                                                $rincianList = [['keterangan' => '', 'harga' => '', 'catatan' => '']];
                                            }
                                        @endphp

                                        @foreach ($rincianList as $rincianIndex => $rincian)
                                            <div class="biaya-item row align-items-end mb-2">
                                                <div class="col-md-1 mb-2">
                                                    <label>No</label>
                                                    <input type="text" class="form-control nomor-biaya"
                                                        value="{{ $rincianIndex + 1 }}" readonly>
                                                </div>

                                                <div class="col-md-4 mb-2">
                                                    <label>Keterangan</label>
                                                    <input type="text"
                                                        name="petugas[{{ $petugasIndex }}][rincian][{{ $rincianIndex }}][keterangan]"
                                                        class="form-control input-keterangan"
                                                        placeholder="Contoh: Uang Harian"
                                                        value="{{ $rincian['keterangan'] ?? '' }}" required>
                                                </div>

                                                <div class="col-md-3 mb-2">
                                                    <label>Nominal</label>
                                                    <input type="number"
                                                        name="petugas[{{ $petugasIndex }}][rincian][{{ $rincianIndex }}][harga]"
                                                        class="form-control harga-biaya" placeholder="Contoh: 150000"
                                                        min="0" step="0.01" value="{{ $rincian['harga'] ?? '' }}"
                                                        required>
                                                </div>

                                                <div class="col-md-3 mb-2">
                                                    <label>Catatan Bendahara</label>
                                                    <input type="text"
                                                        name="petugas[{{ $petugasIndex }}][rincian][{{ $rincianIndex }}][catatan]"
                                                        class="form-control input-catatan" placeholder="Catatan"
                                                        value="{{ $rincian['catatan'] ?? '' }}">
                                                </div>

                                                <div class="col-md-1 mb-2">
                                                    <div class="d-flex">
                                                        <button type="button"
                                                            class="btn btn-success btn-sm addBiaya mr-1">+</button>
                                                        <button type="button"
                                                            class="btn btn-danger btn-sm removeBiaya">−</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="form-group mt-3">
                                        <label>Total
                                            {{ $petugasData['nama_petugas'] ?? 'Petugas ' . ($petugasIndex + 1) }}</label>
                                        <input type="text" class="form-control total-petugas-preview" readonly
                                            value="Rp 0">
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="form-group">
                            <label>Grand Total</label>
                            <input type="text" id="grand_total_preview" class="form-control" readonly value="Rp 0">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Update Keuangan
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <template id="biayaTemplate">
        <div class="biaya-item row align-items-end mb-2">
            <div class="col-md-1 mb-2">
                <label>No</label>
                <input type="text" class="form-control nomor-biaya" readonly>
            </div>

            <div class="col-md-4 mb-2">
                <label>Keterangan</label>
                <input type="text" class="form-control input-keterangan" placeholder="Contoh: Uang Harian" required>
            </div>

            <div class="col-md-3 mb-2">
                <label>Nominal</label>
                <input type="number" class="form-control harga-biaya" placeholder="Contoh: 150000" min="0"
                    step="0.01" required>
            </div>

            <div class="col-md-3 mb-2">
                <label>Catatan Bendahara</label>
                <input type="text" class="form-control input-catatan" placeholder="Catatan">
            </div>

            <div class="col-md-1 mb-2">
                <div class="d-flex">
                    <button type="button" class="btn btn-success btn-sm addBiaya mr-1">+</button>
                    <button type="button" class="btn btn-danger btn-sm removeBiaya">−</button>
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function formatRupiah(n) {
                const v = Number(n || 0);
                return "Rp " + v.toLocaleString("id-ID", {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
            }

            function bulanRomawi(bulan) {
                const romawi = {
                    1: 'I',
                    2: 'II',
                    3: 'III',
                    4: 'IV',
                    5: 'V',
                    6: 'VI',
                    7: 'VII',
                    8: 'VIII',
                    9: 'IX',
                    10: 'X',
                    11: 'XI',
                    12: 'XII'
                };
                return romawi[bulan] || '';
            }

            function refreshNumber(container) {
                container.querySelectorAll(".biaya-item").forEach((item, i) => {
                    item.querySelector(".nomor-biaya").value = i + 1;
                });
            }

            function renameInputs(container) {
                const petugasIndex = container.dataset.petugasIndex;

                container.querySelectorAll(".biaya-item").forEach((item, rincianIndex) => {
                    item.querySelector(".input-keterangan").name =
                        `petugas[${petugasIndex}][rincian][${rincianIndex}][keterangan]`;

                    item.querySelector(".harga-biaya").name =
                        `petugas[${petugasIndex}][rincian][${rincianIndex}][harga]`;

                    item.querySelector(".input-catatan").name =
                        `petugas[${petugasIndex}][rincian][${rincianIndex}][catatan]`;
                });
            }

            function updateTotals() {
                let grandTotal = 0;

                document.querySelectorAll(".biaya-container").forEach(container => {
                    let totalPetugas = 0;

                    container.querySelectorAll(".harga-biaya").forEach(input => {
                        const val = parseFloat(input.value || 0);
                        if (!isNaN(val)) {
                            totalPetugas += val;
                        }
                    });

                    const petugasCard = container.closest(".petugas-card");
                    petugasCard.querySelector(".total-petugas-preview").value = formatRupiah(totalPetugas);
                    grandTotal += totalPetugas;
                });

                document.getElementById("grand_total_preview").value = formatRupiah(grandTotal);
            }

            function updatePreviewNomorKwitansi() {
                const nomorInput = document.getElementById("nomor_kwitansi_input").value;
                const makInput = document.getElementById("mak").value;
                const preview = document.getElementById("preview_nomor_kwitansi");

                const bulan = bulanRomawi(new Date().getMonth() + 1);
                const tahun = new Date().getFullYear();

                if (nomorInput && makInput) {
                    preview.textContent = `${nomorInput}/${makInput}/${bulan}/${tahun}`;
                } else {
                    preview.textContent = "-";
                }
            }

            document.addEventListener("click", function(e) {
                if (e.target.classList.contains("addBiaya")) {
                    const container = e.target.closest(".petugas-card").querySelector(".biaya-container");
                    const template = document.getElementById("biayaTemplate");
                    container.appendChild(template.content.cloneNode(true));

                    refreshNumber(container);
                    renameInputs(container);
                    updateTotals();
                }

                if (e.target.classList.contains("removeBiaya")) {
                    const container = e.target.closest(".biaya-container");
                    const items = container.querySelectorAll(".biaya-item");

                    if (items.length > 1) {
                        e.target.closest(".biaya-item").remove();
                        refreshNumber(container);
                        renameInputs(container);
                        updateTotals();
                    } else {
                        alert("Minimal 1 rincian biaya wajib ada.");
                    }
                }
            });

            document.addEventListener("input", function(e) {
                if (e.target.classList.contains("harga-biaya")) {
                    updateTotals();
                }

                if (e.target.id === "nomor_kwitansi_input" || e.target.id === "mak") {
                    updatePreviewNomorKwitansi();
                }
            });

            document.addEventListener("change", function(e) {
                if (e.target.id === "mak") {
                    updatePreviewNomorKwitansi();
                }
            });

            document.querySelectorAll(".biaya-container").forEach(container => {
                refreshNumber(container);
                renameInputs(container);
            });

            updateTotals();
            updatePreviewNomorKwitansi();
        });
    </script>
@endsection
