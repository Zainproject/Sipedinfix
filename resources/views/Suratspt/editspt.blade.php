@extends('index')

@section('main')
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0 text-gray-800">Edit Data SPT</h1>

                <a href="{{ route('spt.index') }}" class="btn btn-secondary">
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
                $serverErrorFieldStepMap = [
                    'petugas_ids' => 0,
                    'petugas_ids.*' => 0,
                    'jenis_tujuan' => 0,
                    'jenis_tujuan.*' => 0,
                    'tujuan' => 0,
                    'tujuan_poktan' => 0,
                    'tujuan_poktan.*' => 0,
                    'tujuan_kabupaten' => 0,
                    'tujuan_kabupaten.*' => 0,
                    'tujuan_lainnya' => 0,
                    'tujuan_lainnya.*' => 0,
                    'alat_angkut' => 0,
                    'berangkat_dari' => 0,
                    'keperluan' => 0,

                    'tanggal_berangkat' => 1,
                    'tanggal_kembali' => 1,
                    'kehadiran' => 1,

                    'nomor_surat_input' => 2,
                    'arahan' => 2,
                    'arahan.*' => 2,
                    'masalah_temuan' => 2,
                    'masalah_temuan.*' => 2,
                    'saran_tindakan' => 2,
                    'saran_tindakan.*' => 2,
                    'lain_lain' => 2,
                    'lain_lain.*' => 2,
                ];

                $firstServerErrorKey = null;
                foreach (array_keys($errors->toArray()) as $errKey) {
                    $firstServerErrorKey = $errKey;
                    break;
                }

                $serverErrorStep = 0;
                if ($firstServerErrorKey !== null) {
                    foreach ($serverErrorFieldStepMap as $pattern => $stepIndex) {
                        if ($firstServerErrorKey === $pattern || fnmatch($pattern, $firstServerErrorKey)) {
                            $serverErrorStep = $stepIndex;
                            break;
                        }
                    }
                }
            @endphp

            <div class="card shadow mb-4">
                <div class="card-body">
                    <form id="formSPT" action="{{ route('spt.update', $spt->id) }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_current_step" id="current_step"
                            value="{{ $errors->any() ? $serverErrorStep : old('_current_step', 0) }}">

                        {{-- STEP 1 --}}
                        <div class="step" id="step1">
                            <h4 class="mb-3">Step 1: Petugas dan Tujuan</h4>

                            <label>Petugas</label>
                            <div id="petugasContainer">
                                @php
                                    $oldPetugas = old('petugas_ids', $selectedPetugas ?? []);
                                    if (!is_array($oldPetugas) || count($oldPetugas) < 1) {
                                        $oldPetugas = [''];
                                    }
                                @endphp

                                @foreach ($oldPetugas as $i => $petugasNip)
                                    <div class="input-group mb-2 petugas-item">
                                        <select name="petugas_ids[]" class="form-control petugas-select"
                                            data-field-name="petugas_ids">
                                            <option value="">-- Pilih Petugas --</option>
                                            @foreach ($petugas as $p)
                                                <option value="{{ $p->nip }}"
                                                    {{ (string) $petugasNip === (string) $p->nip ? 'selected' : '' }}>
                                                    {{ $p->nama }} ({{ $p->nip }})
                                                </option>
                                            @endforeach
                                        </select>

                                        <div class="input-group-append">
                                            @if ($i == 0)
                                                <button type="button" class="btn btn-success addPetugas">+</button>
                                            @else
                                                <button type="button" class="btn btn-danger removePetugas">−</button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <label class="mt-3">Tujuan</label>
                            <div id="tujuanContainer">
                                @php
                                    $defaultJenisTujuan = collect($selectedTujuan ?? [])
                                        ->pluck('jenis_tujuan')
                                        ->toArray();
                                    $defaultPoktanTujuan = collect($selectedTujuan ?? [])
                                        ->map(fn($item) => $item->poktan_nama ?? '')
                                        ->toArray();
                                    $defaultKabKotaTujuan = collect($selectedTujuan ?? [])
                                        ->map(fn($item) => $item->deskripsi_kota ?? '')
                                        ->toArray();
                                    $defaultLainnyaTujuan = collect($selectedTujuan ?? [])
                                        ->map(fn($item) => $item->deskripsi_lainnya ?? '')
                                        ->toArray();

                                    $oldJenisTujuan = old('jenis_tujuan', $defaultJenisTujuan);
                                    $oldPoktanTujuan = old('tujuan_poktan', $defaultPoktanTujuan);
                                    $oldKabKotaTujuan = old('tujuan_kabupaten', $defaultKabKotaTujuan);
                                    $oldLainnyaTujuan = old('tujuan_lainnya', $defaultLainnyaTujuan);

                                    $jumlahTujuan = max(
                                        count($oldJenisTujuan),
                                        count($oldPoktanTujuan),
                                        count($oldKabKotaTujuan),
                                        count($oldLainnyaTujuan),
                                        1,
                                    );
                                @endphp

                                @for ($i = 0; $i < $jumlahTujuan; $i++)
                                    @php
                                        $jenis = $oldJenisTujuan[$i] ?? '';
                                        $poktanVal = $oldPoktanTujuan[$i] ?? '';
                                        $kabVal = $oldKabKotaTujuan[$i] ?? '';
                                        $lainVal = $oldLainnyaTujuan[$i] ?? '';
                                    @endphp

                                    <div class="tujuan-item border rounded p-3 mb-3">
                                        <div class="form-group mb-2">
                                            <label>Jenis Tujuan</label>
                                            <div class="input-group">
                                                <select name="jenis_tujuan[]" class="form-control jenis-tujuan-select"
                                                    required data-field-name="jenis_tujuan">
                                                    <option value="">-- Pilih Jenis Tujuan --</option>
                                                    <option value="poktan" {{ $jenis === 'poktan' ? 'selected' : '' }}>
                                                        Poktan</option>
                                                    <option value="kabupaten_kota"
                                                        {{ $jenis === 'kabupaten_kota' ? 'selected' : '' }}>Kabupaten /
                                                        Kota</option>
                                                    <option value="lain_lain"
                                                        {{ $jenis === 'lain_lain' ? 'selected' : '' }}>Lain-lain</option>
                                                </select>

                                                <div class="input-group-append">
                                                    @if ($i == 0)
                                                        <button type="button" class="btn btn-success addTujuan">+</button>
                                                    @else
                                                        <button type="button"
                                                            class="btn btn-danger removeTujuan">−</button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tujuan-poktan-field {{ $jenis === 'poktan' ? '' : 'd-none' }}">
                                            <label>Pilih Poktan</label>
                                            <select name="tujuan_poktan[]" class="form-control tujuan-poktan-input"
                                                data-field-name="tujuan_poktan">
                                                <option value="">-- Pilih Poktan --</option>
                                                @foreach ($poktan as $pt)
                                                    <option value="{{ $pt->nama_poktan }}"
                                                        {{ $poktanVal === $pt->nama_poktan ? 'selected' : '' }}>
                                                        {{ $pt->nama_poktan }}
                                                        ({{ $pt->desa ?? '-' }}/{{ $pt->kecamatan ?? '-' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div
                                            class="tujuan-kabupaten-field {{ $jenis === 'kabupaten_kota' ? '' : 'd-none' }}">
                                            <label>Kabupaten / Kota</label>
                                            <input type="text" name="tujuan_kabupaten[]"
                                                class="form-control tujuan-kabupaten-input"
                                                placeholder="Contoh: Kabupaten Sumenep" value="{{ $kabVal }}"
                                                data-field-name="tujuan_kabupaten">
                                        </div>

                                        <div class="tujuan-lainnya-field {{ $jenis === 'lain_lain' ? '' : 'd-none' }}">
                                            <label>Tujuan Lain-lain</label>
                                            <input type="text" name="tujuan_lainnya[]"
                                                class="form-control tujuan-lainnya-input" placeholder="Isi tujuan lainnya"
                                                value="{{ $lainVal }}" data-field-name="tujuan_lainnya">
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <div class="form-group mt-3">
                                <label for="alat_angkut">Alat Angkut</label>
                                <input type="text" name="alat_angkut" id="alat_angkut" class="form-control"
                                    value="{{ old('alat_angkut', $spt->alat_angkut) }}" required
                                    data-field-name="alat_angkut">
                            </div>

                            <div class="form-group">
                                <label for="berangkat_dari">Berangkat Dari</label>
                                <input type="text" name="berangkat_dari" id="berangkat_dari" class="form-control"
                                    value="{{ old('berangkat_dari', $spt->berangkat_dari) }}" required
                                    data-field-name="berangkat_dari">
                            </div>

                            <div class="form-group">
                                <label for="keperluan">Keperluan</label>
                                <textarea name="keperluan" id="keperluan" rows="3" class="form-control" required data-field-name="keperluan">{{ old('keperluan', $spt->keperluan) }}</textarea>
                            </div>

                            <button type="button" class="btn btn-primary next-step">Lanjut</button>
                        </div>

                        {{-- STEP 2 --}}
                        <div class="step d-none" id="step2">
                            <h4 class="mb-3">Step 2: Tanggal dan Kehadiran</h4>

                            <div class="form-group">
                                <label for="tanggal_berangkat">Tanggal Berangkat</label>
                                <input type="date" id="tanggal_berangkat" name="tanggal_berangkat"
                                    class="form-control"
                                    value="{{ old('tanggal_berangkat', \Carbon\Carbon::parse($spt->tanggal_berangkat)->format('Y-m-d')) }}"
                                    required data-field-name="tanggal_berangkat">
                            </div>

                            <div class="form-group">
                                <label for="tanggal_kembali">Tanggal Kembali</label>
                                <input type="date" id="tanggal_kembali" name="tanggal_kembali" class="form-control"
                                    value="{{ old('tanggal_kembali', \Carbon\Carbon::parse($spt->tanggal_kembali)->format('Y-m-d')) }}"
                                    required data-field-name="tanggal_kembali">
                            </div>

                            <div class="form-group">
                                <label>Total Hari</label>
                                <input type="text" id="preview_total_hari" class="form-control" readonly>
                                <small class="text-muted">Total hari dihitung otomatis oleh sistem.</small>
                            </div>

                            <div class="form-group">
                                <label for="kehadiran">Yang Hadir</label>
                                <input type="text" name="kehadiran" id="kehadiran" class="form-control"
                                    value="{{ old('kehadiran', $spt->kehadiran) }}" required data-field-name="kehadiran">
                            </div>

                            <button type="button" class="btn btn-secondary prev-step">Kembali</button>
                            <button type="button" class="btn btn-primary next-step">Lanjut</button>
                        </div>

                        {{-- STEP 3 --}}
                        <div class="step d-none" id="step3">
                            <h4 class="mb-3">Step 3: Nomor Surat dan Catatan</h4>

                            <div class="alert alert-secondary">
                                <div><strong>Nomor surat saat ini:</strong> {{ $spt->nomor_surat ?? '-' }}</div>
                                <div><strong>Format otomatis:</strong> [nomor input]{{ $nomorSuratSuffix ?? '' }}</div>
                            </div>

                            @php
                                $nomorInputDefault = old('nomor_surat_input');
                                if ($nomorInputDefault === null) {
                                    $nomorInputDefault = $spt->nomor_surat ?? '';
                                    if (
                                        preg_match(
                                            '/^(.*?)\/KSASK\/KASMDA\/[IVXLCDM]+\/\d{4}$/',
                                            $nomorInputDefault,
                                            $matches,
                                        )
                                    ) {
                                        $nomorInputDefault = $matches[1];
                                    }
                                }

                                $defaultArahan = filled($spt->arahan) ? preg_split('/\r\n|\r|\n/', $spt->arahan) : [''];
                                $defaultMasalah = filled($spt->masalah_temuan)
                                    ? preg_split('/\r\n|\r|\n/', $spt->masalah_temuan)
                                    : [''];
                                $defaultSaran = filled($spt->saran_tindakan)
                                    ? preg_split('/\r\n|\r|\n/', $spt->saran_tindakan)
                                    : [''];
                                $defaultLain = filled($spt->lain_lain)
                                    ? preg_split('/\r\n|\r|\n/', $spt->lain_lain)
                                    : [''];

                                $oldArahan = old('arahan', $defaultArahan);
                                if (!is_array($oldArahan) || count($oldArahan) < 1) {
                                    $oldArahan = [''];
                                }

                                $oldMasalah = old('masalah_temuan', $defaultMasalah);
                                if (!is_array($oldMasalah) || count($oldMasalah) < 1) {
                                    $oldMasalah = [''];
                                }

                                $oldSaran = old('saran_tindakan', $defaultSaran);
                                if (!is_array($oldSaran) || count($oldSaran) < 1) {
                                    $oldSaran = [''];
                                }

                                $oldLain = old('lain_lain', $defaultLain);
                                if (!is_array($oldLain) || count($oldLain) < 1) {
                                    $oldLain = [''];
                                }
                            @endphp

                            <div class="form-group">
                                <label for="nomor_surat_input">Nomor Surat (isi angka / nomor depan saja)</label>
                                <input type="text" name="nomor_surat_input" id="nomor_surat_input"
                                    class="form-control" value="{{ $nomorInputDefault }}" required
                                    data-field-name="nomor_surat_input">
                                <small class="text-muted">
                                    Contoh isi: <strong>01</strong>
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="nomor_surat_preview">Preview Nomor Surat Lengkap</label>
                                <input type="text" id="nomor_surat_preview" class="form-control" readonly>
                            </div>

                            <div class="form-group">
                                <label>Arahan</label>
                                <div id="arahanContainer">
                                    @foreach ($oldArahan as $i => $val)
                                        <div class="input-group mb-2 arahan-item catatan-item">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text nomor-urutan">{{ $i + 1 }}</span>
                                            </div>
                                            <input type="text" name="arahan[]" class="form-control"
                                                value="{{ $val }}" data-field-name="arahan">
                                            <div class="input-group-append">
                                                <button type="button"
                                                    class="btn btn-success addArahan {{ $i !== count($oldArahan) - 1 ? 'd-none' : '' }}">+</button>
                                                <button type="button"
                                                    class="btn btn-danger removeArahan {{ count($oldArahan) < 2 ? 'd-none' : '' }}">−</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Masalah / Temuan</label>
                                <div id="masalahContainer">
                                    @foreach ($oldMasalah as $i => $val)
                                        <div class="input-group mb-2 masalah-item catatan-item">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text nomor-urutan">{{ $i + 1 }}</span>
                                            </div>
                                            <input type="text" name="masalah_temuan[]" class="form-control"
                                                value="{{ $val }}" data-field-name="masalah_temuan">
                                            <div class="input-group-append">
                                                <button type="button"
                                                    class="btn btn-success addMasalah {{ $i !== count($oldMasalah) - 1 ? 'd-none' : '' }}">+</button>
                                                <button type="button"
                                                    class="btn btn-danger removeMasalah {{ count($oldMasalah) < 2 ? 'd-none' : '' }}">−</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Saran Tindakan</label>
                                <div id="saranContainer">
                                    @foreach ($oldSaran as $i => $val)
                                        <div class="input-group mb-2 saran-item catatan-item">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text nomor-urutan">{{ $i + 1 }}</span>
                                            </div>
                                            <input type="text" name="saran_tindakan[]" class="form-control"
                                                value="{{ $val }}" data-field-name="saran_tindakan">
                                            <div class="input-group-append">
                                                <button type="button"
                                                    class="btn btn-success addSaran {{ $i !== count($oldSaran) - 1 ? 'd-none' : '' }}">+</button>
                                                <button type="button"
                                                    class="btn btn-danger removeSaran {{ count($oldSaran) < 2 ? 'd-none' : '' }}">−</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Lain-lain</label>
                                <div id="lainContainer">
                                    @foreach ($oldLain as $i => $val)
                                        <div class="input-group mb-2 lain-item catatan-item">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text nomor-urutan">{{ $i + 1 }}</span>
                                            </div>
                                            <input type="text" name="lain_lain[]" class="form-control"
                                                value="{{ $val }}" data-field-name="lain_lain">
                                            <div class="input-group-append">
                                                <button type="button"
                                                    class="btn btn-success addLain {{ $i !== count($oldLain) - 1 ? 'd-none' : '' }}">+</button>
                                                <button type="button"
                                                    class="btn btn-danger removeLain {{ count($oldLain) < 2 ? 'd-none' : '' }}">−</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <button type="button" class="btn btn-secondary prev-step">Kembali</button>
                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <template id="petugasTemplate">
        <div class="input-group mb-2 petugas-item">
            <select name="petugas_ids[]" class="form-control petugas-select" data-field-name="petugas_ids">
                <option value="">-- Pilih Petugas --</option>
                @foreach ($petugas as $p)
                    <option value="{{ $p->nip }}">
                        {{ $p->nama }} ({{ $p->nip }})
                    </option>
                @endforeach
            </select>

            <div class="input-group-append">
                <button type="button" class="btn btn-danger removePetugas">−</button>
            </div>
        </div>
    </template>

    <template id="tujuanTemplate">
        <div class="tujuan-item border rounded p-3 mb-3">
            <div class="form-group mb-2">
                <label>Jenis Tujuan</label>
                <div class="input-group">
                    <select name="jenis_tujuan[]" class="form-control jenis-tujuan-select" required
                        data-field-name="jenis_tujuan">
                        <option value="">-- Pilih Jenis Tujuan --</option>
                        <option value="poktan">Poktan</option>
                        <option value="kabupaten_kota">Kabupaten / Kota</option>
                        <option value="lain_lain">Lain-lain</option>
                    </select>

                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger removeTujuan">−</button>
                    </div>
                </div>
            </div>

            <div class="tujuan-poktan-field d-none">
                <label>Pilih Poktan</label>
                <select name="tujuan_poktan[]" class="form-control tujuan-poktan-input" data-field-name="tujuan_poktan">
                    <option value="">-- Pilih Poktan --</option>
                    @foreach ($poktan as $pt)
                        <option value="{{ $pt->nama_poktan }}">
                            {{ $pt->nama_poktan }} ({{ $pt->desa ?? '-' }}/{{ $pt->kecamatan ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="tujuan-kabupaten-field d-none">
                <label>Kabupaten / Kota</label>
                <input type="text" name="tujuan_kabupaten[]" class="form-control tujuan-kabupaten-input"
                    placeholder="Contoh: Kabupaten Sumenep" data-field-name="tujuan_kabupaten">
            </div>

            <div class="tujuan-lainnya-field d-none">
                <label>Tujuan Lain-lain</label>
                <input type="text" name="tujuan_lainnya[]" class="form-control tujuan-lainnya-input"
                    placeholder="Isi tujuan lainnya" data-field-name="tujuan_lainnya">
            </div>
        </div>
    </template>

    <template id="arahanTemplate">
        <div class="input-group mb-2 arahan-item catatan-item">
            <div class="input-group-prepend">
                <span class="input-group-text nomor-urutan">1</span>
            </div>
            <input type="text" name="arahan[]" class="form-control" data-field-name="arahan">
            <div class="input-group-append">
                <button type="button" class="btn btn-success addArahan">+</button>
                <button type="button" class="btn btn-danger removeArahan d-none">−</button>
            </div>
        </div>
    </template>

    <template id="masalahTemplate">
        <div class="input-group mb-2 masalah-item catatan-item">
            <div class="input-group-prepend">
                <span class="input-group-text nomor-urutan">1</span>
            </div>
            <input type="text" name="masalah_temuan[]" class="form-control" data-field-name="masalah_temuan">
            <div class="input-group-append">
                <button type="button" class="btn btn-success addMasalah">+</button>
                <button type="button" class="btn btn-danger removeMasalah d-none">−</button>
            </div>
        </div>
    </template>

    <template id="saranTemplate">
        <div class="input-group mb-2 saran-item catatan-item">
            <div class="input-group-prepend">
                <span class="input-group-text nomor-urutan">1</span>
            </div>
            <input type="text" name="saran_tindakan[]" class="form-control" data-field-name="saran_tindakan">
            <div class="input-group-append">
                <button type="button" class="btn btn-success addSaran">+</button>
                <button type="button" class="btn btn-danger removeSaran d-none">−</button>
            </div>
        </div>
    </template>

    <template id="lainTemplate">
        <div class="input-group mb-2 lain-item catatan-item">
            <div class="input-group-prepend">
                <span class="input-group-text nomor-urutan">1</span>
            </div>
            <input type="text" name="lain_lain[]" class="form-control" data-field-name="lain_lain">
            <div class="input-group-append">
                <button type="button" class="btn btn-success addLain">+</button>
                <button type="button" class="btn btn-danger removeLain d-none">−</button>
            </div>
        </div>
    </template>

    <style>
        .catatan-item .input-group-text {
            min-width: 40px;
            justify-content: center;
            background-color: #f8f9fc;
        }

        .catatan-item .btn {
            min-width: 40px;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("formSPT");
            const steps = document.querySelectorAll(".step");
            const currentStepInput = document.getElementById("current_step");
            const petugasContainer = document.getElementById("petugasContainer");
            const nomorSuratInput = document.getElementById("nomor_surat_input");
            const nomorSuratPreview = document.getElementById("nomor_surat_preview");
            let currentStep = parseInt(currentStepInput?.value || 0, 10);

            if (isNaN(currentStep) || currentStep < 0 || currentStep >= steps.length) {
                currentStep = 0;
            }

            function toRomanMonth(month) {
                const romans = {
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
                return romans[month] || '';
            }

            function getNomorSuratSuffix() {
                const tanggal = document.getElementById("tanggal_berangkat")?.value;
                let dateObj = tanggal ? new Date(tanggal) : new Date();

                if (Number.isNaN(dateObj.getTime())) {
                    dateObj = new Date();
                }

                const month = dateObj.getMonth() + 1;
                const year = dateObj.getFullYear();

                return `/KSASK/KASMDA/${toRomanMonth(month)}/${year}`;
            }

            function updateNomorSuratPreview() {
                if (!nomorSuratPreview || !nomorSuratInput) return;
                const nomorDepan = (nomorSuratInput.value || '').trim();
                nomorSuratPreview.value = nomorDepan ? `${nomorDepan}${getNomorSuratSuffix()}` : '';
            }

            function showStep(index) {
                steps.forEach((step, i) => {
                    step.classList.toggle("d-none", i !== index);
                });

                if (currentStepInput) {
                    currentStepInput.value = index;
                }

                updateNomorSuratPreview();
            }

            function goToStepAndFocus(stepIndex, field = null) {
                currentStep = stepIndex;
                showStep(currentStep);

                setTimeout(() => {
                    if (field) {
                        field.classList.add("is-invalid");
                        field.focus({
                            preventScroll: false
                        });
                        field.scrollIntoView({
                            behavior: "smooth",
                            block: "center"
                        });
                    }
                }, 120);
            }

            function getFieldLabel(field) {
                const formGroup = field.closest(".form-group");
                if (formGroup) {
                    const label = formGroup.querySelector("label");
                    if (label) return label.textContent.trim();
                }

                if (field.dataset.fieldName) {
                    return field.dataset.fieldName.replaceAll("_", " ");
                }

                return "Kolom";
            }

            function getLamaHari() {
                const tglBerangkat = document.getElementById("tanggal_berangkat")?.value;
                const tglKembali = document.getElementById("tanggal_kembali")?.value;

                if (!tglBerangkat || !tglKembali) return "";

                const start = new Date(tglBerangkat);
                const end = new Date(tglKembali);
                const diff = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;

                if (diff <= 0 || Number.isNaN(diff)) return "Tanggal tidak valid";
                return diff + " hari";
            }

            function updatePreviewHari() {
                const el = document.getElementById("preview_total_hari");
                if (el) {
                    el.value = getLamaHari();
                }
            }

            function toggleTujuanFields(item) {
                const jenis = item.querySelector(".jenis-tujuan-select")?.value || "";

                const poktanField = item.querySelector(".tujuan-poktan-field");
                const kabField = item.querySelector(".tujuan-kabupaten-field");
                const lainField = item.querySelector(".tujuan-lainnya-field");

                const poktanInput = item.querySelector(".tujuan-poktan-input");
                const kabInput = item.querySelector(".tujuan-kabupaten-input");
                const lainInput = item.querySelector(".tujuan-lainnya-input");

                poktanField?.classList.add("d-none");
                kabField?.classList.add("d-none");
                lainField?.classList.add("d-none");

                if (poktanInput) {
                    poktanInput.required = false;
                    if (jenis !== "poktan") poktanInput.value = "";
                }

                if (kabInput) {
                    kabInput.required = false;
                    if (jenis !== "kabupaten_kota") kabInput.value = "";
                }

                if (lainInput) {
                    lainInput.required = false;
                    if (jenis !== "lain_lain") lainInput.value = "";
                }

                if (jenis === "poktan") {
                    poktanField?.classList.remove("d-none");
                    if (poktanInput) poktanInput.required = true;
                } else if (jenis === "kabupaten_kota") {
                    kabField?.classList.remove("d-none");
                    if (kabInput) kabInput.required = true;
                } else if (jenis === "lain_lain") {
                    lainField?.classList.remove("d-none");
                    if (lainInput) lainInput.required = true;
                }
            }

            function isVisible(el) {
                return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
            }

            function validateVisibleRequired(step) {
                const requiredFields = step.querySelectorAll("[required]");

                for (let field of requiredFields) {
                    if (!isVisible(field)) {
                        field.classList.remove("is-invalid");
                        continue;
                    }

                    const value = (field.value ?? "").toString().trim();

                    if (value === "") {
                        field.classList.add("is-invalid");
                        goToStepAndFocus(Array.from(steps).indexOf(step), field);
                        alert(getFieldLabel(field) + " wajib diisi.");
                        return false;
                    } else {
                        field.classList.remove("is-invalid");
                    }
                }

                return true;
            }

            function validateTanggal() {
                const hasil = getLamaHari();
                if (hasil === "Tanggal tidak valid") {
                    const field = document.getElementById("tanggal_kembali");
                    goToStepAndFocus(1, field);
                    alert("Tanggal kembali harus sama atau setelah tanggal berangkat.");
                    return false;
                }
                return true;
            }

            function getSelectedPetugasValues() {
                if (!petugasContainer) return [];

                return Array.from(
                        petugasContainer.querySelectorAll('.petugas-item select[name="petugas_ids[]"]')
                    )
                    .map(select => (select.value || "").toString().trim())
                    .filter(val => val !== "");
            }

            function validatePetugasFinal() {
                const petugasValues = getSelectedPetugasValues();
                const allPetugasSelect = petugasContainer ?
                    petugasContainer.querySelectorAll('.petugas-item select[name="petugas_ids[]"]') : [];

                allPetugasSelect.forEach(select => select.classList.remove("is-invalid"));

                if (petugasValues.length < 1) {
                    const first = petugasContainer ?
                        petugasContainer.querySelector('.petugas-item select[name="petugas_ids[]"]') :
                        null;

                    if (first) {
                        goToStepAndFocus(0, first);
                    }

                    alert("Minimal 1 petugas harus dipilih.");
                    return false;
                }

                return true;
            }

            function renumberItems(containerSelector, itemSelector) {
                const container = document.querySelector(containerSelector);
                if (!container) return;

                const items = container.querySelectorAll(itemSelector);
                items.forEach((item, index) => {
                    const nomor = item.querySelector(".nomor-urutan");
                    if (nomor) {
                        nomor.textContent = index + 1;
                    }
                });
            }

            function refreshCatatanButtons(containerSelector, itemSelector, addClass, removeClass) {
                const container = document.querySelector(containerSelector);
                if (!container) return;

                const items = container.querySelectorAll(itemSelector);
                const total = items.length;

                items.forEach((item, index) => {
                    const addBtn = item.querySelector(`.${addClass}`);
                    const removeBtn = item.querySelector(`.${removeClass}`);

                    if (addBtn) {
                        addBtn.classList.toggle("d-none", index !== total - 1);
                    }

                    if (removeBtn) {
                        removeBtn.classList.toggle("d-none", total < 2);
                    }
                });
            }

            document.querySelectorAll(".next-step").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();

                    const step = steps[currentStep];
                    if (!validateVisibleRequired(step)) return;
                    if (step.id === "step2" && !validateTanggal()) return;

                    if (currentStep < steps.length - 1) {
                        currentStep++;
                        showStep(currentStep);
                    }
                });
            });

            document.querySelectorAll(".prev-step").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();

                    if (currentStep > 0) {
                        currentStep--;
                        showStep(currentStep);
                    }
                });
            });

            document.addEventListener("click", function(e) {
                if (e.target.classList.contains("addPetugas")) {
                    const template = document.getElementById("petugasTemplate");
                    petugasContainer.appendChild(template.content.cloneNode(true));
                }

                if (e.target.classList.contains("removePetugas")) {
                    const items = petugasContainer.querySelectorAll(".petugas-item");
                    if (items.length > 1) {
                        e.target.closest(".petugas-item").remove();
                    } else {
                        alert("Minimal 1 petugas wajib ada.");
                    }
                }

                if (e.target.classList.contains("addTujuan")) {
                    const template = document.getElementById("tujuanTemplate");
                    const container = document.getElementById("tujuanContainer");
                    const clone = template.content.cloneNode(true);
                    container.appendChild(clone);

                    const items = container.querySelectorAll(".tujuan-item");
                    const newItem = items[items.length - 1];
                    toggleTujuanFields(newItem);
                }

                if (e.target.classList.contains("removeTujuan")) {
                    const items = document.querySelectorAll(".tujuan-item");
                    if (items.length > 1) {
                        e.target.closest(".tujuan-item").remove();
                    } else {
                        alert("Minimal 1 tujuan wajib ada.");
                    }
                }

                if (e.target.classList.contains("addArahan")) {
                    const template = document.getElementById("arahanTemplate");
                    const container = document.getElementById("arahanContainer");
                    container.appendChild(template.content.cloneNode(true));
                    renumberItems("#arahanContainer", ".arahan-item");
                    refreshCatatanButtons("#arahanContainer", ".arahan-item", "addArahan", "removeArahan");
                }

                if (e.target.classList.contains("removeArahan")) {
                    const items = document.querySelectorAll("#arahanContainer .arahan-item");
                    if (items.length > 1) {
                        e.target.closest(".arahan-item").remove();
                        renumberItems("#arahanContainer", ".arahan-item");
                        refreshCatatanButtons("#arahanContainer", ".arahan-item", "addArahan",
                            "removeArahan");
                    }
                }

                if (e.target.classList.contains("addMasalah")) {
                    const template = document.getElementById("masalahTemplate");
                    const container = document.getElementById("masalahContainer");
                    container.appendChild(template.content.cloneNode(true));
                    renumberItems("#masalahContainer", ".masalah-item");
                    refreshCatatanButtons("#masalahContainer", ".masalah-item", "addMasalah",
                        "removeMasalah");
                }

                if (e.target.classList.contains("removeMasalah")) {
                    const items = document.querySelectorAll("#masalahContainer .masalah-item");
                    if (items.length > 1) {
                        e.target.closest(".masalah-item").remove();
                        renumberItems("#masalahContainer", ".masalah-item");
                        refreshCatatanButtons("#masalahContainer", ".masalah-item", "addMasalah",
                            "removeMasalah");
                    }
                }

                if (e.target.classList.contains("addSaran")) {
                    const template = document.getElementById("saranTemplate");
                    const container = document.getElementById("saranContainer");
                    container.appendChild(template.content.cloneNode(true));
                    renumberItems("#saranContainer", ".saran-item");
                    refreshCatatanButtons("#saranContainer", ".saran-item", "addSaran", "removeSaran");
                }

                if (e.target.classList.contains("removeSaran")) {
                    const items = document.querySelectorAll("#saranContainer .saran-item");
                    if (items.length > 1) {
                        e.target.closest(".saran-item").remove();
                        renumberItems("#saranContainer", ".saran-item");
                        refreshCatatanButtons("#saranContainer", ".saran-item", "addSaran", "removeSaran");
                    }
                }

                if (e.target.classList.contains("addLain")) {
                    const template = document.getElementById("lainTemplate");
                    const container = document.getElementById("lainContainer");
                    container.appendChild(template.content.cloneNode(true));
                    renumberItems("#lainContainer", ".lain-item");
                    refreshCatatanButtons("#lainContainer", ".lain-item", "addLain", "removeLain");
                }

                if (e.target.classList.contains("removeLain")) {
                    const items = document.querySelectorAll("#lainContainer .lain-item");
                    if (items.length > 1) {
                        e.target.closest(".lain-item").remove();
                        renumberItems("#lainContainer", ".lain-item");
                        refreshCatatanButtons("#lainContainer", ".lain-item", "addLain", "removeLain");
                    }
                }
            });

            document.addEventListener("change", function(e) {
                if (e.target.classList.contains("jenis-tujuan-select")) {
                    const item = e.target.closest(".tujuan-item");
                    if (item) toggleTujuanFields(item);
                }

                if (e.target.id === "tanggal_berangkat" || e.target.id === "tanggal_kembali") {
                    updatePreviewHari();
                    updateNomorSuratPreview();
                }

                if (e.target.matches('#petugasContainer .petugas-item select[name="petugas_ids[]"]')) {
                    e.target.classList.remove("is-invalid");
                }

                if (e.target.matches('[data-field-name]')) {
                    e.target.classList.remove("is-invalid");
                }
            });

            if (nomorSuratInput) {
                nomorSuratInput.addEventListener("input", updateNomorSuratPreview);
            }

            form.addEventListener("submit", function(e) {
                const visibleStep = Array.from(steps).find(step => !step.classList.contains("d-none"));

                if (visibleStep && !validateVisibleRequired(visibleStep)) {
                    e.preventDefault();
                    return false;
                }

                if (!validateTanggal()) {
                    e.preventDefault();
                    return false;
                }

                if (!validatePetugasFinal()) {
                    e.preventDefault();
                    currentStep = 0;
                    showStep(currentStep);
                    return false;
                }
            });

            document.querySelectorAll(".tujuan-item").forEach(item => {
                toggleTujuanFields(item);
            });

            renumberItems("#arahanContainer", ".arahan-item");
            renumberItems("#masalahContainer", ".masalah-item");
            renumberItems("#saranContainer", ".saran-item");
            renumberItems("#lainContainer", ".lain-item");

            refreshCatatanButtons("#arahanContainer", ".arahan-item", "addArahan", "removeArahan");
            refreshCatatanButtons("#masalahContainer", ".masalah-item", "addMasalah", "removeMasalah");
            refreshCatatanButtons("#saranContainer", ".saran-item", "addSaran", "removeSaran");
            refreshCatatanButtons("#lainContainer", ".lain-item", "addLain", "removeLain");

            showStep(currentStep);
            updatePreviewHari();
            updateNomorSuratPreview();

            const firstServerErrorField = @json($firstServerErrorKey);

            if (firstServerErrorField) {
                const normalizedField = firstServerErrorField.replace(/\.\d+$/, '');
                const targetField = document.querySelector(`[data-field-name="${normalizedField}"]`);

                if (targetField) {
                    setTimeout(() => {
                        targetField.classList.add('is-invalid');
                        targetField.focus();
                        targetField.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }, 200);
                }
            }
        });
    </script>
@endsection
