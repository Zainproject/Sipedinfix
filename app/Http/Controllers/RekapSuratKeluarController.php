<?php

namespace App\Http\Controllers;

use App\Models\Petugas;
use App\Models\Spt;
use Illuminate\Http\Request;

class RekapSuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->baseQuery($request);

        $spts = $query->orderByDesc('tanggal_berangkat')->get();

        $tahunOptions = Spt::query()
            ->select('tahun')
            ->whereNotNull('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $makOptions = Spt::query()
            ->whereHas('keuangan')
            ->with('keuangan:id,spt_id,mak')
            ->get()
            ->pluck('keuangan.mak')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $petugasOptions = Petugas::query()
            ->orderBy('nama')
            ->get(['nip', 'nama']);

        $totalSurat = $spts->count();

        $totalBiaya = $spts->sum(function ($spt) {
            return (float) optional($spt->keuangan)->total_biaya;
        });

        $rekapPetugas = $this->buildRekapPetugas($spts);
        $rekapPoktan = $this->buildRekapPoktan($spts);
        $rekapAnggaran = $this->buildRekapAnggaran($spts);
        $filterInfo = $this->buildFilterInfo($request, $petugasOptions);

        return view('rekap_surat_keluar.index', compact(
            'spts',
            'tahunOptions',
            'makOptions',
            'petugasOptions',
            'totalSurat',
            'totalBiaya',
            'rekapPetugas',
            'rekapPoktan',
            'rekapAnggaran',
            'filterInfo'
        ));
    }

    public function print(Request $request)
    {
        $jenis = $request->get('jenis', 'all');

        $query = $this->baseQuery($request);

        $spts = $query->orderByDesc('tanggal_berangkat')->get();

        $petugasOptions = Petugas::query()
            ->orderBy('nama')
            ->get(['nip', 'nama']);

        $totalSurat = $spts->count();

        $totalBiaya = $spts->sum(function ($spt) {
            return (float) optional($spt->keuangan)->total_biaya;
        });

        $rekapPetugas = $this->buildRekapPetugas($spts);
        $rekapPoktan = $this->buildRekapPoktan($spts);
        $rekapAnggaran = $this->buildRekapAnggaran($spts);
        $filterInfo = $this->buildFilterInfo($request, $petugasOptions);

        return view('rekap_surat_keluar.print', compact(
            'jenis',
            'spts',
            'totalSurat',
            'totalBiaya',
            'rekapPetugas',
            'rekapPoktan',
            'rekapAnggaran',
            'filterInfo'
        ));
    }

    private function baseQuery(Request $request)
    {
        $query = Spt::with([
            'petugasRel',
            'sptTujuan.poktan',
            'keuangan',
        ]);

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }

        if ($request->filled('petugas')) {
            $nip = trim((string) $request->petugas);

            $query->whereHas('petugasRel', function ($q) use ($nip) {
                $q->where('nip', $nip);
            });
        }

        if ($request->filled('mak')) {
            $mak = trim((string) $request->mak);

            $query->whereHas('keuangan', function ($q) use ($mak) {
                $q->where('mak', $mak);
            });
        }

        if ($request->filled('status')) {
            $query->where('status_pencairan', $request->status);
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('nomor_surat', 'like', "%{$keyword}%")
                    ->orWhere('keperluan', 'like', "%{$keyword}%")
                    ->orWhere('status_bendahara', 'like', "%{$keyword}%")
                    ->orWhere('status_pencairan', 'like', "%{$keyword}%")
                    ->orWhereHas('petugasRel', function ($q2) use ($keyword) {
                        $q2->where('nama', 'like', "%{$keyword}%")
                            ->orWhere('nip', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('sptTujuan', function ($q3) use ($keyword) {
                        $q3->where('poktan_nama', 'like', "%{$keyword}%")
                            ->orWhere('deskripsi_kota', 'like', "%{$keyword}%")
                            ->orWhere('deskripsi_lainnya', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('keuangan', function ($q4) use ($keyword) {
                        $q4->where('mak', 'like', "%{$keyword}%")
                            ->orWhere('nomor_kwitansi', 'like', "%{$keyword}%");
                    });
            });
        }

        return $query;
    }

    private function buildRekapPetugas($spts)
    {
        return $spts
            ->flatMap(function ($spt) {
                return $spt->petugasRel->map(function ($petugas) use ($spt) {
                    return [
                        'nip'         => $petugas->nip,
                        'nama'        => $petugas->nama,
                        'jumlah'      => 1,
                        'total_biaya' => (float) optional($spt->keuangan)->total_biaya,
                    ];
                });
            })
            ->groupBy('nip')
            ->map(function ($items, $nip) {
                return [
                    'nip'         => $nip,
                    'nama'        => $items->first()['nama'] ?? '-',
                    'jumlah'      => $items->sum('jumlah'),
                    'total_biaya' => $items->sum('total_biaya'),
                ];
            })
            ->sortByDesc('jumlah')
            ->values();
    }

    private function buildRekapPoktan($spts)
    {
        return $spts
            ->flatMap(function ($spt) {
                return $spt->sptTujuan->map(function ($tujuan) use ($spt) {
                    return [
                        'nama'        => $this->formatTujuan($tujuan),
                        'jumlah'      => 1,
                        'total_biaya' => (float) optional($spt->keuangan)->total_biaya,
                    ];
                });
            })
            ->groupBy('nama')
            ->map(function ($items, $nama) {
                return [
                    'nama'        => $nama,
                    'jumlah'      => $items->sum('jumlah'),
                    'total_biaya' => $items->sum('total_biaya'),
                ];
            })
            ->sortByDesc('jumlah')
            ->values();
    }

    private function buildRekapAnggaran($spts)
    {
        return $spts
            ->map(function ($spt) {
                return [
                    'mak'         => optional($spt->keuangan)->mak ?? '-',
                    'status'      => $spt->status_pencairan ?: 'belum cair',
                    'jumlah'      => 1,
                    'total'       => (float) optional($spt->keuangan)->total_biaya,
                ];
            })
            ->groupBy(function ($item) {
                return $item['mak'] . '||' . $item['status'];
            })
            ->map(function ($items) {
                return [
                    'mak'    => $items->first()['mak'] ?? '-',
                    'status' => $items->first()['status'] ?? '-',
                    'jumlah' => $items->sum('jumlah'),
                    'total'  => $items->sum('total'),
                ];
            })
            ->values();
    }

    private function buildFilterInfo(Request $request, $petugasOptions): array
    {
        $bulanMap = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $petugasLabel = null;
        if ($request->filled('petugas')) {
            $petugas = $petugasOptions->firstWhere('nip', $request->petugas);
            $petugasLabel = $petugas ? ($petugas->nama . ' (' . $petugas->nip . ')') : $request->petugas;
        }

        return [
            'tahun'   => $request->tahun,
            'bulan'   => $request->filled('bulan') ? ($bulanMap[(int) $request->bulan] ?? $request->bulan) : null,
            'mak'     => $request->mak,
            'petugas' => $petugasLabel,
            'status'  => $request->status,
            'keyword' => $request->keyword,
        ];
    }

    private function formatTujuan($tujuan): string
    {
        if ($tujuan->jenis_tujuan === 'poktan') {
            $desa = optional($tujuan->poktan)->desa ?? '-';
            $kecamatan = optional($tujuan->poktan)->kecamatan ?? '-';

            return 'Poktan ' . ($tujuan->poktan_nama ?? '-') . ', Desa ' . $desa . ', Kecamatan ' . $kecamatan;
        }

        if ($tujuan->jenis_tujuan === 'kabupaten_kota') {
            return $tujuan->deskripsi_kota ?: '-';
        }

        if ($tujuan->jenis_tujuan === 'lain_lain') {
            return $tujuan->deskripsi_lainnya ?: '-';
        }

        return '-';
    }
}
