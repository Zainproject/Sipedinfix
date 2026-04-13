<?php

namespace App\Http\Controllers;

use App\Models\Petugas;
use App\Models\Spt;
use Illuminate\Http\Request;

class RekapAnggaranController extends Controller
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

        $totalSpt = $spts->count();
        $totalBiaya = $spts->sum(function ($spt) {
            return (float) optional($spt->keuangan)->total_biaya;
        });

        $rekapAnggaran = $this->buildRekapAnggaran($spts);
        $detailAnggaran = $this->buildDetailAnggaran($spts);
        $filterInfo = $this->buildFilterInfo($request, $petugasOptions);

        return view('rekap_anggaran.index', compact(
            'spts',
            'tahunOptions',
            'makOptions',
            'petugasOptions',
            'totalSpt',
            'totalBiaya',
            'rekapAnggaran',
            'detailAnggaran',
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

        $totalSpt = $spts->count();
        $totalBiaya = $spts->sum(function ($spt) {
            return (float) optional($spt->keuangan)->total_biaya;
        });

        $rekapAnggaran = $this->buildRekapAnggaran($spts);
        $detailAnggaran = $this->buildDetailAnggaran($spts);
        $filterInfo = $this->buildFilterInfo($request, $petugasOptions);

        return view('rekap_anggaran.print', compact(
            'jenis',
            'spts',
            'totalSpt',
            'totalBiaya',
            'rekapAnggaran',
            'detailAnggaran',
            'filterInfo'
        ));
    }

    private function baseQuery(Request $request)
    {
        $query = Spt::with([
            'petugasRel',
            'sptTujuan.poktan',
            'keuangan',
        ])->whereHas('keuangan');

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }

        if ($request->filled('petugas')) {
            $nip = trim((string) $request->petugas);

            $query->whereHas('petugasRel', function ($q) use ($nip) {
                $q->where('petugas.nip', $nip);
            });
        }

        if ($request->filled('mak')) {
            $mak = trim((string) $request->mak);

            $query->whereHas('keuangan', function ($q) use ($mak) {
                $q->where('mak', $mak);
            });
        }

        // pakai status_pencairan, bukan status_bendahara lama
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
                    ->orWhereHas('keuangan', function ($q3) use ($keyword) {
                        $q3->where('mak', 'like', "%{$keyword}%")
                            ->orWhere('nomor_kwitansi', 'like', "%{$keyword}%");
                    });
            });
        }

        return $query;
    }

    private function buildRekapAnggaran($spts)
    {
        return $spts
            ->map(function ($spt) {
                $statusBendahara = trim((string) ($spt->status_bendahara ?? ''));
                $statusPencairan = trim((string) ($spt->status_pencairan ?? ''));

                return [
                    'mak'               => optional($spt->keuangan)->mak ?? '-',
                    'status_bendahara'  => $statusBendahara !== '' ? $statusBendahara : 'sudah diisi bendahara',
                    'status_pencairan'  => $statusPencairan !== '' ? $statusPencairan : 'belum cair',
                    'jumlah_spt'        => 1,
                    'total_biaya'       => (float) optional($spt->keuangan)->total_biaya,
                ];
            })
            ->groupBy(function ($item) {
                return $item['mak'] . '||' . $item['status_pencairan'];
            })
            ->map(function ($items) {
                return [
                    'mak'               => $items->first()['mak'] ?? '-',
                    'status_bendahara'  => $items->first()['status_bendahara'] ?? '-',
                    'status_pencairan'  => $items->first()['status_pencairan'] ?? '-',
                    'jumlah_spt'        => $items->sum('jumlah_spt'),
                    'total_biaya'       => $items->sum('total_biaya'),
                ];
            })
            ->sortBy([
                ['mak', 'asc'],
                ['status_pencairan', 'asc'],
            ])
            ->values();
    }

    private function buildDetailAnggaran($spts)
    {
        return $spts->map(function ($spt) {
            $petugasText = $spt->petugasRel
                ->pluck('nama')
                ->filter()
                ->implode(', ');

            $statusBendahara = trim((string) ($spt->status_bendahara ?? ''));
            $statusPencairan = trim((string) ($spt->status_pencairan ?? ''));

            return [
                'nomor_surat'       => $spt->nomor_surat ?? '-',
                'tanggal'           => ($spt->tanggal_berangkat ? date('d-m-Y', strtotime($spt->tanggal_berangkat)) : '-') .
                    ' s/d ' .
                    ($spt->tanggal_kembali ? date('d-m-Y', strtotime($spt->tanggal_kembali)) : '-'),
                'petugas'           => $petugasText !== '' ? $petugasText : '-',
                'keperluan'         => $spt->keperluan ?? '-',
                'mak'               => optional($spt->keuangan)->mak ?? '-',
                'nomor_kwitansi'    => optional($spt->keuangan)->nomor_kwitansi ?? '-',
                'status_bendahara'  => $statusBendahara !== '' ? $statusBendahara : 'sudah diisi bendahara',
                'status_pencairan'  => $statusPencairan !== '' ? $statusPencairan : 'belum cair',
                'total_biaya'       => (float) optional($spt->keuangan)->total_biaya,
            ];
        })->values();
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
}
