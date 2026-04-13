@extends('index')

@section('main')
    <div id="content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0 text-gray-800">Tambah Dana Masuk Pemerintah</h1>

                <a href="{{ route('dana-masuk.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

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

            <div class="card shadow">
                <div class="card-body">
                    <form action="{{ route('dana-masuk.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control"
                                value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="sumber_dana">Sumber Dana</label>
                            <input type="text" name="sumber_dana" id="sumber_dana" class="form-control"
                                value="{{ old('sumber_dana', 'Dana Pemerintah') }}" placeholder="Contoh: Dana Pemerintah"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="nominal">Nominal</label>
                            <input type="number" name="nominal" id="nominal" class="form-control"
                                value="{{ old('nominal') }}" min="0" step="0.01" placeholder="Contoh: 10000000"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <input type="text" name="keterangan" id="keterangan" class="form-control"
                                value="{{ old('keterangan') }}" placeholder="Contoh: Dana tahap 1">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
