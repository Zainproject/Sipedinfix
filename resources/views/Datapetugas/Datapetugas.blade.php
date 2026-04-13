@extends('index')

@section('main')
    <div id="content">
        <div class="container-fluid">

            <h1 class="h3 mb-2 text-gray-800">Data Petugas</h1>
            <p class="mb-4">Daftar data petugas sesuai rancangan sistem SPT.</p>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Data Petugas</h6>

                    <a href="{{ route('petugas.create') }}" class="btn btn-success btn-icon-split btn-sm">
                        <span class="icon text-white-50">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="text">Tambah</span>
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 70px;">No</th>
                                    <th>Nama</th>
                                    <th style="width: 180px;">NIP</th>
                                    <th>Pangkat</th>
                                    <th>Jabatan</th>
                                    <th style="width: 170px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($petugas as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->nama }}</td>
                                        <td>{{ $item->nip }}</td>
                                        <td>{{ $item->pangkat }}</td>
                                        <td>{{ $item->jabatan }}</td>
                                        <td>
                                            <a href="{{ route('petugas.edit', $item->nip) }}"
                                                class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>

                                            <form action="{{ route('petugas.destroy', $item->nip) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('Yakin ingin menghapus data petugas ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Data petugas belum tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
