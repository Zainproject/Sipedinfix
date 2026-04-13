@extends('index')

@section('main')
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>

            <div>
                <a href="{{ route('rekap-surat-keluar.index') }}"
                    class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
                    <i class="fas fa-file-alt fa-sm text-white-50"></i> Rekap Surat Keluar
                </a>

                <a href="{{ route('rekap-anggaran.index') }}"
                    class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                    <i class="fas fa-wallet fa-sm text-white-50"></i> Rekap Anggaran
                </a>
            </div>
        </div>

        {{-- RINGKASAN OPERASIONAL --}}
        <div class="row">

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Data SPT Keluar Bulan Ini
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $jumlahSptBulanIni ?? 0 }} Surat
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Biaya SPD Bulan Ini
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp {{ number_format($totalBiayaBulanIni ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    SPT Sedang Berjalan (Hari Ini)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $jumlahSptBerjalan ?? 0 }} Surat
                                </div>
                                <div class="text-xs text-gray-600 mt-1">
                                    Petugas bertugas: {{ $jumlahPetugasBerangkat ?? 0 }} orang
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Data Poktan Saat Ini
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $jumlahPoktan ?? 0 }} Kelompok
                                </div>
                                <div class="text-xs text-gray-600 mt-1">
                                    Total petugas: {{ $jumlahPetugas ?? 0 }} orang
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-database fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- RINGKASAN ANGGARAN TAHUN INI --}}
        <div class="row">

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Pagu Anggaran Tahun Ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($paguAnggaranTahunIni ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Realisasi Anggaran Tahun Ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($realisasiAnggaranTahunIni ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Sisa Anggaran Tahun Ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($sisaAnggaranTahunIni ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- CHART --}}
        <div class="row">

            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">

                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Grafik Biaya SPD per Bulan ({{ $chartBiayaBulanan['tahun'] ?? date('Y') }})
                        </h6>
                    </div>

                    <div class="card-body">
                        <div class="chart-area" style="height: 320px;">
                            <canvas id="chartBiayaBulanan"></canvas>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">

                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Status Pencairan SPT</h6>
                    </div>

                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2" style="height: 260px;">
                            <canvas id="chartStatusSpt"></canvas>
                        </div>

                        @php
                            $statusData = $chartStatus['data'] ?? [0, 0, 0];
                        @endphp

                        <div class="mt-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-700">Belum Cair</span>
                                <strong>{{ $statusData[0] ?? 0 }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-700">Sudah Dicairkan</span>
                                <strong>{{ $statusData[1] ?? 0 }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-700">Selesai</span>
                                <strong>{{ $statusData[2] ?? 0 }}</strong>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>

    <script>
        var biayaBulananLabels = {!! json_encode($chartBiayaBulanan['labels'] ?? []) !!};
        var biayaBulananData = {!! json_encode($chartBiayaBulanan['biaya'] ?? []) !!};

        var statusLabels = {!! json_encode($chartStatus['labels'] ?? ['Belum Cair', 'Sudah Dicairkan', 'Selesai']) !!};
        var statusData = {!! json_encode($chartStatus['data'] ?? [0, 0, 0]) !!};

        var elBiaya = document.getElementById('chartBiayaBulanan');
        if (elBiaya) {
            new Chart(elBiaya, {
                type: 'line',
                data: {
                    labels: biayaBulananLabels,
                    datasets: [{
                        label: 'Total Biaya (Rp)',
                        data: biayaBulananData,
                        borderColor: 'rgba(78, 115, 223, 1)',
                        backgroundColor: 'rgba(78, 115, 223, 0.15)',
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointBorderColor: 'rgba(255, 255, 255, 1)',
                        pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointHoverBorderColor: 'rgba(255, 255, 255, 1)',
                        borderWidth: 2,
                        lineTension: 0.3,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: true
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        display: true
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem) {
                                var v = tooltipItem.yLabel || 0;
                                return 'Total Biaya: Rp ' + v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) {
                                    return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g,
                                        ".");
                                }
                            }
                        }]
                    }
                }
            });
        }

        var elStatus = document.getElementById('chartStatusSpt');
        if (elStatus) {
            new Chart(elStatus, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusData,
                        backgroundColor: [
                            'rgba(246, 194, 62, 0.9)',
                            'rgba(28, 200, 138, 0.9)',
                            'rgba(78, 115, 223, 0.9)'
                        ],
                        hoverBackgroundColor: [
                            'rgba(246, 194, 62, 1)',
                            'rgba(28, 200, 138, 1)',
                            'rgba(78, 115, 223, 1)'
                        ],
                        borderColor: 'rgba(255,255,255,1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        display: true
                    },
                    cutoutPercentage: 70,
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.labels[tooltipItem.index] || '';
                                var value = data.datasets[0].data[tooltipItem.index] || 0;
                                return label + ': ' + value;
                            }
                        }
                    }
                }
            });
        }
    </script>
@endsection
