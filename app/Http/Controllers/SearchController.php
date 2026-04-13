<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Spt;
use App\Models\Petugas;
use App\Models\Poktan;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        return view('search.index');
    }

    public function results(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'q' => $q,
                'counts' => ['spt' => 0, 'petugas' => 0, 'poktan' => 0],
                'data' => ['spt' => [], 'petugas' => [], 'poktan' => []],
                'message' => 'Ketik minimal 2 karakter untuk mulai mencari.',
            ]);
        }

        $limit = (int) $request->get('limit', 200);
        $limit = max(10, min($limit, 500));

        // taruh di sini supaya scope-nya aman untuk seluruh method
        $possibleKetuaCols = ['ketua', 'ketua_poktan', 'nama_ketua', 'ketua_nama'];

        /*
        |--------------------------------------------------------------------------
        | 1) PETUGAS
        |--------------------------------------------------------------------------
        */
        $petugasQuery = Petugas::query()
            ->where('nama', 'like', "%{$q}%");

        if (Schema::hasColumn('petugas', 'nip')) {
            $petugasQuery->orWhere('nip', 'like', "%{$q}%");
        }

        $petugas = $petugasQuery
            ->latest()
            ->limit(50)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 2) POKTAN
        |--------------------------------------------------------------------------
        */
        $poktanQuery = Poktan::query()
            ->where('nama_poktan', 'like', "%{$q}%")
            ->orWhere('desa', 'like', "%{$q}%")
            ->orWhere('kecamatan', 'like', "%{$q}%");

        foreach ($possibleKetuaCols as $col) {
            if (Schema::hasColumn('poktan', $col)) {
                $poktanQuery->orWhere($col, 'like', "%{$q}%");
            }
        }

        $poktan = $poktanQuery
            ->latest()
            ->limit(50)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 3) KEYWORD TUJUAN
        |--------------------------------------------------------------------------
        */
        $qLower = mb_strtolower($q);
        $tujuanKeys = [];

        if (str_contains($qLower, 'kabupaten') || str_contains($qLower, 'kota')) {
            $tujuanKeys[] = 'kabupaten_kota';
        }

        if (
            str_contains($qLower, 'poktan') ||
            str_contains($qLower, 'kelompok tani') ||
            str_contains($qLower, 'tani')
        ) {
            $tujuanKeys[] = 'poktan';
        }

        if (str_contains($qLower, 'lain') || str_contains($qLower, 'lain-lain')) {
            $tujuanKeys[] = 'lain_lain';
        }

        /*
        |--------------------------------------------------------------------------
        | 4) SPT (STRUKTUR BARU)
        |--------------------------------------------------------------------------
        */
        $sptQuery = Spt::query()
            ->with([
                'petugasRel:nip,nama',
                'sptTujuan:id,spt_id,jenis_tujuan,poktan_nama,deskripsi_kota,deskripsi_lainnya',
                'sptTujuan.poktan',
                'keuangan:id,spt_id,mak,nomor_kwitansi,total_biaya',
            ])
            ->where(function ($w) use ($q, $petugas, $poktan, $tujuanKeys, $possibleKetuaCols) {
                $w->where('nomor_surat', 'like', "%{$q}%")
                    ->orWhere('keperluan', 'like', "%{$q}%")
                    ->orWhere('status_bendahara', 'like', "%{$q}%")
                    ->orWhere('status_pencairan', 'like', "%{$q}%")
                    ->orWhereHas('keuangan', function ($q2) use ($q) {
                        $q2->where('nomor_kwitansi', 'like', "%{$q}%")
                            ->orWhere('mak', 'like', "%{$q}%");
                    })
                    ->orWhereHas('petugasRel', function ($q3) use ($q) {
                        $q3->where('nama', 'like', "%{$q}%")
                            ->orWhere('nip', 'like', "%{$q}%");
                    })
                    ->orWhereHas('sptTujuan', function ($q4) use ($q, $tujuanKeys) {
                        $q4->where('poktan_nama', 'like', "%{$q}%")
                            ->orWhere('deskripsi_kota', 'like', "%{$q}%")
                            ->orWhere('deskripsi_lainnya', 'like', "%{$q}%");

                        if (!empty($tujuanKeys)) {
                            foreach ($tujuanKeys as $key) {
                                $q4->orWhere('jenis_tujuan', $key);
                            }
                        }
                    });

                if ($petugas->isNotEmpty()) {
                    $w->orWhereHas('petugasRel', function ($q5) use ($petugas) {
                        foreach ($petugas as $p) {
                            if (!empty($p->nama)) {
                                $q5->orWhere('nama', 'like', "%{$p->nama}%");
                            }
                            if (isset($p->nip) && !empty($p->nip)) {
                                $q5->orWhere('nip', 'like', "%{$p->nip}%");
                            }
                        }
                    });
                }

                if ($poktan->isNotEmpty()) {
                    $w->orWhereHas('sptTujuan', function ($q6) use ($poktan, $possibleKetuaCols) {
                        foreach ($poktan as $pk) {
                            if (!empty($pk->nama_poktan)) {
                                $q6->orWhere('poktan_nama', 'like', "%{$pk->nama_poktan}%");
                            }

                            if (!empty($pk->desa)) {
                                $q6->orWhere('deskripsi_kota', 'like', "%{$pk->desa}%")
                                    ->orWhere('deskripsi_lainnya', 'like', "%{$pk->desa}%");
                            }

                            if (!empty($pk->kecamatan)) {
                                $q6->orWhere('deskripsi_kota', 'like', "%{$pk->kecamatan}%")
                                    ->orWhere('deskripsi_lainnya', 'like', "%{$pk->kecamatan}%");
                            }

                            foreach ($possibleKetuaCols as $col) {
                                if (isset($pk->{$col}) && !empty($pk->{$col})) {
                                    $q6->orWhere('poktan_nama', 'like', "%{$pk->{$col}}%");
                                }
                            }
                        }
                    });
                }
            });

        $spt = $sptQuery
            ->latest()
            ->limit($limit)
            ->get()
            ->unique('id')
            ->values()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nomor_surat' => $item->nomor_surat,
                    'tahun' => $item->tahun,
                    'bulan' => $item->bulan,
                    'keperluan' => $item->keperluan,
                    'tanggal_berangkat' => $item->tanggal_berangkat,
                    'tanggal_kembali' => $item->tanggal_kembali,
                    'status_bendahara' => $item->status_bendahara,
                    'status_pencairan' => $item->status_pencairan,
                    'petugas' => $item->petugasRel->map(function ($p) {
                        return [
                            'nip' => $p->nip,
                            'nama' => $p->nama,
                        ];
                    })->values(),
                    'tujuan' => $item->sptTujuan->map(function ($t) {
                        return [
                            'jenis_tujuan' => $t->jenis_tujuan,
                            'poktan_nama' => $t->poktan_nama,
                            'deskripsi_kota' => $t->deskripsi_kota,
                            'deskripsi_lainnya' => $t->deskripsi_lainnya,
                        ];
                    })->values(),
                    'keuangan' => $item->keuangan ? [
                        'mak' => $item->keuangan->mak,
                        'nomor_kwitansi' => $item->keuangan->nomor_kwitansi,
                        'total_biaya' => $item->keuangan->total_biaya,
                    ] : null,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json([
            'q' => $q,
            'counts' => [
                'spt' => $spt->count(),
                'petugas' => $petugas->count(),
                'poktan' => $poktan->count(),
            ],
            'data' => [
                'spt' => $spt,
                'petugas' => $petugas,
                'poktan' => $poktan,
            ],
        ]);
    }
}
