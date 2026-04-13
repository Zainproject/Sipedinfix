@extends('index')

@section('main')
    <div class="container-fluid">

        <h1 class="h3 mb-4 text-gray-800">Tambah Data Petugas</h1>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('petugas.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="nip">NIP</label>
                        <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip"
                            name="nip" value="{{ old('nip') }}" placeholder="Masukkan NIP">
                        @error('nip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                            name="nama" value="{{ old('nama') }}" placeholder="Masukkan Nama">
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="pangkat">Pangkat</label>
                        <input type="text" class="form-control @error('pangkat') is-invalid @enderror" id="pangkat"
                            name="pangkat" value="{{ old('pangkat') }}" placeholder="Masukkan Pangkat">
                        @error('pangkat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="jabatan">Jabatan</label>
                        <input type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan"
                            name="jabatan" value="{{ old('jabatan') }}" placeholder="Masukkan Jabatan">
                        @error('jabatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success btn-icon-split">
                        <span class="icon text-white-50">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="text">Simpan</span>
                    </button>

                    <a href="{{ route('petugas.index') }}" class="btn btn-secondary btn-icon-split ml-2">
                        <span class="icon text-white-50">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span class="text">Kembali</span>
                    </a>
                </form>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                alert(@json($errors->first()));
            });
        </script>
    @endif
@endsection
