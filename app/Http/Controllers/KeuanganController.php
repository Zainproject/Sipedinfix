<?php

namespace App\Http\Controllers;

use App\Models\Spt;
use App\Models\Keuangan;
use App\Models\DanaMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        $query = Spt::with([
            'petugasRel',
            'sptTujuan.poktan',
            'keuangan'
        ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('keperluan', 'like', "%{$search}%")
                    ->orWhere('status_bendahara', 'like', "%{$search}%")
                    ->orWhere('status_pencairan', 'like', "%{$search}%")
                    ->orWhereHas('petugasRel', function ($q2) use ($search) {
                        $q2->where('nama', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sptTujuan', function ($q3) use ($search) {
                        $q3->where('poktan_nama', 'like', "%{$search}%")
                            ->orWhere('deskripsi_kota', 'like', "%{$search}%")
                            ->orWhere('deskripsi_lainnya', 'like', "%{$search}%");
                    })
                    ->orWhereHas('keuangan', function ($q4) use ($search) {
                        $q4->where('mak', 'like', "%{$search}%")
                            ->orWhere('nomor_kwitansi', 'like', "%{$search}%");
                    });
            });
        }

        $spts = $query->latest()->get();

        $totalDigunakan = $spts->sum(function ($spt) {
            return (float) ($spt->keuangan->total_biaya ?? 0);
        });

        $totalDanaMasuk = (float) DanaMasuk::sum('nominal');
        $sisaDana = $totalDanaMasuk - $totalDigunakan;

        return view('keuangan.index', compact(
            'spts',
            'totalDanaMasuk',
            'totalDigunakan',
            'sisaDana'
        ));
    }

    public function create($spt_id)
    {
        $spt = Spt::with([
            'petugasRel',
            'sptTujuan.poktan',
            'keuangan'
        ])->findOrFail($spt_id);

        if ($spt->keuangan) {
            return redirect()->route('keuangan.edit', $spt->id)
                ->with('error', 'Data keuangan untuk SPT ini sudah ada.');
        }

        $keuanganTerakhir = Keuangan::latest('id')->first();
        $nomorKwitansiTerakhir = $keuanganTerakhir ? $keuanganTerakhir->nomor_kwitansi : null;

        $nomorKwitansiDefault = 1;
        if ($keuanganTerakhir && !empty($keuanganTerakhir->nomor_kwitansi)) {
            $bagian = explode('/', $keuanganTerakhir->nomor_kwitansi);
            $nomorAwal = isset($bagian[0]) ? (int) $bagian[0] : 0;
            $nomorKwitansiDefault = $nomorAwal + 1;
        }

        return view('keuangan.create', compact(
            'spt',
            'nomorKwitansiTerakhir',
            'nomorKwitansiDefault'
        ));
    }

    public function store(Request $request, $spt_id)
    {
        $spt = Spt::with([
            'petugasRel',
            'sptTujuan.poktan',
            'keuangan'
        ])->findOrFail($spt_id);

        if ($spt->keuangan) {
            return redirect()->route('keuangan.edit', $spt->id)
                ->with('error', 'Data keuangan untuk SPT ini sudah ada.');
        }

        $request->validate([
            'mak' => 'required|string|in:APBN,APBD,BOK,DAK,LAINNYA',
            'nomor_kwitansi' => 'required|integer|min:1|max:999999',
            'petugas' => 'required|array|min:1',
            'petugas.*.petugas_id' => 'required',
            'petugas.*.rincian' => 'required|array|min:1',
            'petugas.*.rincian.*.keterangan' => 'required|string|max:255',
            'petugas.*.rincian.*.harga' => 'required|numeric|min:0|max:999999999999.99',
            'petugas.*.rincian.*.catatan' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $bulanRomawi = $this->bulanRomawi(date('n'));
            $tahun = date('Y');

            $nomorKwitansiFinal = $request->nomor_kwitansi . '/' . strtoupper($request->mak) . '/' . $bulanRomawi . '/' . $tahun;

            $detailPetugas = [];
            $grandTotal = 0;

            foreach ($request->petugas as $petugasData) {
                $petugasModel = $spt->petugasRel->first(function ($item) use ($petugasData) {
                    return (string) $item->getKey() === (string) $petugasData['petugas_id'];
                });

                if (!$petugasModel) {
                    throw new \Exception('Petugas tidak valid untuk SPT ini.');
                }

                $rincian = collect($petugasData['rincian'])
                    ->map(function ($item) {
                        return [
                            'keterangan' => $item['keterangan'] ?? '',
                            'harga' => (float) ($item['harga'] ?? 0),
                            'catatan' => $item['catatan'] ?? null,
                        ];
                    })
                    ->values()
                    ->toArray();

                $totalPetugas = collect($rincian)->sum('harga');

                $detailPetugas[] = [
                    'petugas_id' => $petugasModel->getKey(),
                    'nama_petugas' => $petugasModel->nama ?? '-',
                    'rincian' => $rincian,
                    'total_biaya' => $totalPetugas,
                ];

                $grandTotal += $totalPetugas;
            }

            Keuangan::create([
                'spt_id' => $spt->id,
                'mak' => strtoupper($request->mak),
                'nomor_kwitansi' => $nomorKwitansiFinal,
                'detail_petugas' => $detailPetugas,
                'total_biaya' => $grandTotal,
            ]);

            $spt->update([
                'status_bendahara' => 'sudah diisi bendahara',
                'status_pencairan' => $spt->status_pencairan ?: 'belum cair',
            ]);

            DB::commit();

            return redirect()->route('keuangan.index')
                ->with('success', 'Data keuangan berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Data keuangan gagal disimpan: ' . $e->getMessage());
        }
    }

    public function show($spt_id)
    {
        $spt = Spt::with([
            'petugasRel',
            'sptTujuan.poktan',
            'keuangan'
        ])->findOrFail($spt_id);

        if (!$spt->keuangan) {
            return redirect()->route('keuangan.index')
                ->with('error', 'Data keuangan belum ada.');
        }

        return view('keuangan.show', compact('spt'));
    }

    public function edit($spt_id)
    {
        $spt = Spt::with([
            'petugasRel',
            'sptTujuan.poktan',
            'keuangan'
        ])->findOrFail($spt_id);

        if (!$spt->keuangan) {
            return redirect()->route('keuangan.create', $spt->id)
                ->with('error', 'Data keuangan belum ada.');
        }

        $keuanganTerakhir = Keuangan::where('id', '!=', $spt->keuangan->id)
            ->latest('id')
            ->first();

        $nomorKwitansiTerakhir = $keuanganTerakhir ? $keuanganTerakhir->nomor_kwitansi : null;

        return view('keuangan.edit', compact('spt', 'nomorKwitansiTerakhir'));
    }

    public function update(Request $request, $spt_id)
    {
        $spt = Spt::with([
            'petugasRel',
            'sptTujuan.poktan',
            'keuangan'
        ])->findOrFail($spt_id);

        if (!$spt->keuangan) {
            return redirect()->route('keuangan.create', $spt->id)
                ->with('error', 'Data keuangan belum ada.');
        }

        $request->validate([
            'mak' => 'required|string|in:APBN,APBD,BOK,DAK,LAINNYA',
            'nomor_kwitansi' => 'required|integer|min:1|max:999999',
            'petugas' => 'required|array|min:1',
            'petugas.*.petugas_id' => 'required',
            'petugas.*.rincian' => 'required|array|min:1',
            'petugas.*.rincian.*.keterangan' => 'required|string|max:255',
            'petugas.*.rincian.*.harga' => 'required|numeric|min:0|max:999999999999.99',
            'petugas.*.rincian.*.catatan' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $bulanRomawi = $this->bulanRomawi(date('n'));
            $tahun = date('Y');

            $nomorKwitansiFinal = $request->nomor_kwitansi . '/' . strtoupper($request->mak) . '/' . $bulanRomawi . '/' . $tahun;

            $detailPetugas = [];
            $grandTotal = 0;

            foreach ($request->petugas as $petugasData) {
                $petugasModel = $spt->petugasRel->first(function ($item) use ($petugasData) {
                    return (string) $item->getKey() === (string) $petugasData['petugas_id'];
                });

                if (!$petugasModel) {
                    throw new \Exception('Petugas tidak valid untuk SPT ini.');
                }

                $rincian = collect($petugasData['rincian'])
                    ->map(function ($item) {
                        return [
                            'keterangan' => $item['keterangan'] ?? '',
                            'harga' => (float) ($item['harga'] ?? 0),
                            'catatan' => $item['catatan'] ?? null,
                        ];
                    })
                    ->values()
                    ->toArray();

                $totalPetugas = collect($rincian)->sum('harga');

                $detailPetugas[] = [
                    'petugas_id' => $petugasModel->getKey(),
                    'nama_petugas' => $petugasModel->nama ?? '-',
                    'rincian' => $rincian,
                    'total_biaya' => $totalPetugas,
                ];

                $grandTotal += $totalPetugas;
            }

            $spt->keuangan->update([
                'mak' => strtoupper($request->mak),
                'nomor_kwitansi' => $nomorKwitansiFinal,
                'detail_petugas' => $detailPetugas,
                'total_biaya' => $grandTotal,
            ]);

            $spt->update([
                'status_bendahara' => 'sudah diisi bendahara',
                'status_pencairan' => $spt->status_pencairan ?: 'belum cair',
            ]);

            DB::commit();

            return redirect()->route('keuangan.index')
                ->with('success', 'Data keuangan berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Data keuangan gagal diperbarui: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $spt_id)
    {
        $request->validate([
            'status_pencairan' => 'required|in:belum cair,sudah dicairkan,selesai',
        ], [
            'status_pencairan.required' => 'Status pencairan wajib dipilih.',
            'status_pencairan.in' => 'Status pencairan tidak valid.',
        ]);

        $spt = Spt::with('keuangan')->findOrFail($spt_id);

        if (!$spt->keuangan) {
            return redirect()->route('keuangan.index')
                ->with('error', 'Status pencairan tidak bisa diubah karena data keuangan belum diisi.');
        }

        $spt->update([
            'status_bendahara' => 'sudah diisi bendahara',
            'status_pencairan' => $request->status_pencairan,
        ]);

        return redirect()->route('keuangan.index')
            ->with('success', 'Status pencairan berhasil diperbarui.');
    }

    public function destroy($spt_id)
    {
        $spt = Spt::with('keuangan')->findOrFail($spt_id);

        if (!$spt->keuangan) {
            return redirect()->route('keuangan.index')
                ->with('error', 'Data keuangan tidak ditemukan.');
        }

        DB::beginTransaction();

        try {
            $spt->keuangan->delete();

            $spt->update([
                'status_bendahara' => 'belum diisi bendahara',
                'status_pencairan' => null,
            ]);

            DB::commit();

            return redirect()->route('keuangan.index')
                ->with('success', 'Data keuangan berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('keuangan.index')
                ->with('error', 'Data keuangan gagal dihapus: ' . $e->getMessage());
        }
    }

    private function bulanRomawi($bulan)
    {
        $romawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $romawi[(int) $bulan] ?? '';
    }
}
