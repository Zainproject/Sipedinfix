<?php

namespace App\Http\Controllers;

use App\Models\DanaMasuk;
use App\Models\Petugas;
use App\Models\Poktan;
use App\Models\Spt;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $bulanIni = (int) $now->month;
        $tahunIni = (int) $now->year;

        /*
        |--------------------------------------------------------------------------
        | KARTU DASHBOARD
        |--------------------------------------------------------------------------
        */

        // SPT bulan ini
        $jumlahSptBulanIni = Spt::query()
            ->where('bulan', $bulanIni)
            ->where('tahun', $tahunIni)
            ->count();

        // Total biaya bulan ini dari relasi keuangan
        $totalBiayaBulanIni = Spt::query()
            ->with('keuangan')
            ->where('bulan', $bulanIni)
            ->where('tahun', $tahunIni)
            ->whereHas('keuangan')
            ->get()
            ->sum(function ($spt) {
                return (float) optional($spt->keuangan)->total_biaya;
            });

        // SPT sedang berjalan hari ini
        $sptBerjalan = Spt::query()
            ->with('petugasRel')
            ->whereDate('tanggal_berangkat', '<=', $today)
            ->whereDate('tanggal_kembali', '>=', $today)
            ->get();

        $jumlahSptBerjalan = $sptBerjalan->count();

        // Petugas yang sedang bertugas hari ini
        $jumlahPetugasBerangkat = $sptBerjalan
            ->flatMap(function ($spt) {
                return $spt->petugasRel->pluck('nip');
            })
            ->filter()
            ->unique()
            ->count();

        // Master data
        $jumlahPetugas = Petugas::count();
        $jumlahPoktan = Poktan::count();

        /*
        |--------------------------------------------------------------------------
        | ANGGARAN TAHUN INI
        |--------------------------------------------------------------------------
        */

        // Pagu anggaran tahun ini dari dana masuk
        // Kalau tabel dana_masuk pakai kolom tanggal selain created_at, ganti di sini.
        $paguAnggaranTahunIni = (float) DanaMasuk::query()
            ->whereYear('created_at', $tahunIni)
            ->sum('nominal');

        // Realisasi anggaran tahun ini dari keuangan SPT
        $realisasiAnggaranTahunIni = Spt::query()
            ->with('keuangan')
            ->where('tahun', $tahunIni)
            ->whereHas('keuangan')
            ->get()
            ->sum(function ($spt) {
                return (float) optional($spt->keuangan)->total_biaya;
            });

        $sisaAnggaranTahunIni = $paguAnggaranTahunIni - $realisasiAnggaranTahunIni;

        /*
        |--------------------------------------------------------------------------
        | GRAFIK 1: BIAYA PER BULAN (JAN–DES) TAHUN INI
        |--------------------------------------------------------------------------
        */

        $labelBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $dataBiayaBulanan = array_fill(0, 12, 0);

        $sptTahunan = Spt::query()
            ->with('keuangan')
            ->where('tahun', $tahunIni)
            ->whereHas('keuangan')
            ->get();

        foreach ($sptTahunan as $spt) {
            $bulan = (int) ($spt->bulan ?? 0);

            if ($bulan >= 1 && $bulan <= 12) {
                $dataBiayaBulanan[$bulan - 1] += (float) optional($spt->keuangan)->total_biaya;
            }
        }

        $chartBiayaBulanan = [
            'labels' => $labelBulan,
            'biaya'  => $dataBiayaBulanan,
            'tahun'  => $tahunIni,
        ];

        /*
        |--------------------------------------------------------------------------
        | GRAFIK 2: STATUS PENCAIRAN SPT
        |--------------------------------------------------------------------------
        */

        $sptBerkeuangan = Spt::query()
            ->whereHas('keuangan');

        $statusBelumCair = (clone $sptBerkeuangan)
            ->where(function ($q) {
                $q->whereNull('status_pencairan')
                    ->orWhere('status_pencairan', '')
                    ->orWhere('status_pencairan', 'belum cair');
            })
            ->count();

        $statusSudahDicairkan = (clone $sptBerkeuangan)
            ->where('status_pencairan', 'sudah dicairkan')
            ->count();

        $statusSelesai = (clone $sptBerkeuangan)
            ->where('status_pencairan', 'selesai')
            ->count();

        $chartStatus = [
            'labels' => ['Belum Cair', 'Sudah Dicairkan', 'Selesai'],
            'data'   => [
                (int) $statusBelumCair,
                (int) $statusSudahDicairkan,
                (int) $statusSelesai,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        |
        | Sesuaikan dengan file blade dashboard yang kamu pakai.
        | Jika file kamu memang resources/views/dashboard/index.blade.php
        | maka pakai 'dashboard.index'
        |--------------------------------------------------------------------------
        */

        return view('dashboard.index', compact(
            'jumlahSptBulanIni',
            'totalBiayaBulanIni',
            'jumlahSptBerjalan',
            'jumlahPetugasBerangkat',
            'jumlahPetugas',
            'jumlahPoktan',
            'chartBiayaBulanan',
            'chartStatus',
            'paguAnggaranTahunIni',
            'realisasiAnggaranTahunIni',
            'sisaAnggaranTahunIni'
        ));
    }
}
