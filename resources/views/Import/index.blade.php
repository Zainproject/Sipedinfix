@extends('index')

@section('main')
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Import & Export Data</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <div class="row">

            {{-- IMPORT PETUGAS --}}
            <div class="col-lg-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Import Petugas</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('import.petugas') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="file_petugas">Pilih File Petugas</label>
                                <input type="file" name="file_petugas" id="file_petugas"
                                    class="form-control @error('file_petugas') is-invalid @enderror" required>
                                @error('file_petugas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload"></i> Import Petugas
                            </button>

                            <a href="{{ route('import.export.petugas') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            {{-- IMPORT POKTAN --}}
            <div class="col-lg-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Import Poktan</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('import.poktan') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="file_poktan">Pilih File Poktan</label>
                                <input type="file" name="file_poktan" id="file_poktan"
                                    class="form-control @error('file_poktan') is-invalid @enderror" required>
                                @error('file_poktan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload"></i> Import Poktan
                            </button>

                            <a href="{{ route('import.export.poktan') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            {{-- IMPORT SPT --}}
            <div class="col-lg-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Import SPT</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('import.spt') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="file_spt">Pilih File SPT</label>
                                <input type="file" name="file_spt" id="file_spt"
                                    class="form-control @error('file_spt') is-invalid @enderror" required>
                                @error('file_spt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload"></i> Import SPT
                            </button>

                            <a href="{{ route('import.export.spt') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        {{-- FORMAT INFO --}}
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Catatan Format File</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Format file yang didukung: <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong></li>
                    <li>Pastikan header kolom sesuai dengan kebutuhan import masing-masing data.</li>
                    <li>Untuk data SPT, sesuaikan kolom dengan struktur import `SptImport` yang kamu pakai.</li>
                </ul>
            </div>
        </div>

    </div>
@endsection
