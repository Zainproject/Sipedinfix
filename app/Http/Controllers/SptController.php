<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Spt;
use App\Models\Petugas;
use App\Models\Poktan;
use App\Models\Activity;
use Carbon\Carbon;

class SptController extends Controller
{
    private function logActivity(Request $request, string $action, string $keterangan, array $data = []): void
    {
        if (!class_exists(Activity::class)) {
            return;
        }

        Activity::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'method'     => strtoupper($request->method()),
            'route'      => optional($request->route())->getName(),
            'url'        => $request->fullUrl(),
            'payload'    => json_encode([
                'keterangan' => $keterangan,
                'data'       => $data,
            ], JSON_UNESCAPED_UNICODE),
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }

    private function cleanPetugasIds(array $petugasIds = []): array
    {
        return collect($petugasIds)
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => trim((string) $id))
            ->unique()
            ->values()
            ->toArray();
    }

    private function cleanTextArray(array $items = []): array
    {
        return collect($items)
            ->map(fn($item) => trim((string) $item))
            ->filter(fn($item) => $item !== '')
            ->values()
            ->toArray();
    }

    private function buildTujuanRows(Request $request): array
    {
        $rows = [];

        foreach ($request->jenis_tujuan ?? [] as $i => $jenis) {
            $jenis = trim((string) $jenis);

            if (!in_array($jenis, ['poktan', 'kabupaten_kota', 'lain_lain'], true)) {
                continue;
            }

            $row = [
                'jenis_tujuan'      => $jenis,
                'poktan_nama'       => null,
                'deskripsi_kota'    => null,
                'deskripsi_lainnya' => null,
            ];

            if ($jenis === 'poktan') {
                $val = trim((string) ($request->tujuan_poktan[$i] ?? ''));
                if ($val === '') {
                    continue;
                }
                $row['poktan_nama'] = $val;
            }

            if ($jenis === 'kabupaten_kota') {
                $val = trim((string) ($request->tujuan_kabupaten[$i] ?? ''));
                if ($val === '') {
                    continue;
                }
                $row['deskripsi_kota'] = $val;
            }

            if ($jenis === 'lain_lain') {
                $val = trim((string) ($request->tujuan_lainnya[$i] ?? ''));
                if ($val === '') {
                    continue;
                }
                $row['deskripsi_lainnya'] = $val;
            }

            $rows[] = $row;
        }

        return array_values($rows);
    }

    private function toRomanMonth(int $month): string
    {
        $roman = [
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

        return $roman[$month] ?? '';
    }

    private function nomorSuratSuffix(?string $tanggal = null): string
    {
        $date = $tanggal ? Carbon::parse($tanggal) : now();

        return '/KSASK/KASMDA/' . $this->toRomanMonth((int) $date->month) . '/' . $date->year;
    }

    private function buildNomorSurat(string $nomor, ?string $tanggal = null): string
    {
        return trim($nomor) . $this->nomorSuratSuffix($tanggal);
    }

    private function getLastNomorSurat(): ?string
    {
        return Spt::orderByDesc('id')->value('nomor_surat');
    }

    private function checkBentrokPetugas(array $petugas, string $tgl1, string $tgl2, ?int $ignore = null): void
    {
        foreach ($petugas as $nip) {
            $q = DB::table('spt_petugas')
                ->join('spts', 'spts.id', '=', 'spt_petugas.spt_id')
                ->where('nip_petugas', $nip)
                ->whereDate('tanggal_berangkat', '<=', $tgl2)
                ->whereDate('tanggal_kembali', '>=', $tgl1);

            if ($ignore) {
                $q->where('spts.id', '!=', $ignore);
            }

            if ($q->exists()) {
                throw new \Exception('Petugas sudah ada jadwal di tanggal tersebut.');
            }
        }
    }

    private function validateSpt(Request $request): void
    {
        $request->validate([
            'petugas_ids'              => 'required|array|min:1',
            'petugas_ids.*'            => 'nullable|exists:petugas,nip',

            'jenis_tujuan'             => 'required|array|min:1',
            'jenis_tujuan.*'           => 'required|in:poktan,kabupaten_kota,lain_lain',

            'tujuan_poktan'            => 'nullable|array',
            'tujuan_poktan.*'          => 'nullable|string',

            'tujuan_kabupaten'         => 'nullable|array',
            'tujuan_kabupaten.*'       => 'nullable|string',

            'tujuan_lainnya'           => 'nullable|array',
            'tujuan_lainnya.*'         => 'nullable|string',

            'nomor_surat_input'        => 'required|string|max:50',
            'alat_angkut'              => 'required|string|max:255',
            'berangkat_dari'           => 'required|string|max:255',
            'keperluan'                => 'required|string',
            'kehadiran'                => 'required|string|max:255',
            'tanggal_berangkat'        => 'required|date',
            'tanggal_kembali'          => 'required|date|after_or_equal:tanggal_berangkat',

            'arahan'                   => 'nullable|array',
            'arahan.*'                 => 'nullable|string',

            'masalah_temuan'           => 'nullable|array',
            'masalah_temuan.*'         => 'nullable|string',

            'saran_tindakan'           => 'nullable|array',
            'saran_tindakan.*'         => 'nullable|string',

            'lain_lain'                => 'nullable|array',
            'lain_lain.*'              => 'nullable|string',
        ], [
            'petugas_ids.required'           => 'Pilih minimal 1 petugas.',
            'petugas_ids.min'                => 'Pilih minimal 1 petugas.',
            'petugas_ids.*.exists'           => 'Petugas yang dipilih tidak valid.',

            'jenis_tujuan.required'          => 'Tujuan wajib diisi.',
            'jenis_tujuan.min'               => 'Tujuan wajib diisi.',
            'jenis_tujuan.*.required'        => 'Jenis tujuan wajib dipilih.',
            'jenis_tujuan.*.in'              => 'Jenis tujuan tidak valid.',

            'nomor_surat_input.required'     => 'Nomor surat wajib diisi.',
            'alat_angkut.required'           => 'Alat angkut wajib diisi.',
            'berangkat_dari.required'        => 'Tempat berangkat wajib diisi.',
            'keperluan.required'             => 'Keperluan wajib diisi.',
            'kehadiran.required'             => 'Yang hadir wajib diisi.',
            'tanggal_berangkat.required'     => 'Tanggal berangkat wajib diisi.',
            'tanggal_kembali.required'       => 'Tanggal kembali wajib diisi.',
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali tidak valid.',
        ]);
    }

    private function buildCatatanValue(array $items = []): ?string
    {
        $clean = $this->cleanTextArray($items);
        return count($clean) ? implode("\n", $clean) : null;
    }

    public function index(Request $request)
    {
        $query = Spt::with(['poktan', 'petugasRel', 'sptTujuan.poktan', 'keuangan']);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('keperluan', 'like', "%{$search}%")
                    ->orWhere('status_bendahara', 'like', "%{$search}%")
                    ->orWhereHas('petugasRel', function ($q2) use ($search) {
                        $q2->where('nama', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%");
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

        return view('Suratspt.Dataspt', compact('spts'));
    }

    public function show($id)
    {
        return redirect()->route('spt.index');
    }

    public function create()
    {
        return view('Suratspt.tambahspt', [
            'petugas'          => Petugas::orderBy('nama')->get(),
            'poktan'           => Poktan::orderBy('nama_poktan')->get(),
            'lastNomorSurat'   => $this->getLastNomorSurat(),
            'nomorSuratSuffix' => $this->nomorSuratSuffix(),
        ]);
    }

    public function store(Request $request)
    {
        $this->validateSpt($request);

        DB::beginTransaction();

        try {
            $petugas = $this->cleanPetugasIds($request->petugas_ids ?? []);
            $tujuan = $this->buildTujuanRows($request);

            if (count($petugas) < 1) {
                return back()->withInput()->withErrors([
                    'petugas_ids' => 'Pilih minimal 1 petugas.',
                ]);
            }

            if (count($tujuan) < 1) {
                return back()->withInput()->withErrors([
                    'tujuan' => 'Isi minimal 1 tujuan.',
                ]);
            }

            $this->checkBentrokPetugas(
                $petugas,
                $request->tanggal_berangkat,
                $request->tanggal_kembali
            );

            $tanggalBerangkat = Carbon::parse($request->tanggal_berangkat);
            $tanggalKembali = Carbon::parse($request->tanggal_kembali);

            $spt = Spt::create([
                'nomor_surat'       => $this->buildNomorSurat($request->nomor_surat_input, $request->tanggal_berangkat),
                'alat_angkut'       => $request->alat_angkut,
                'berangkat_dari'    => $request->berangkat_dari,
                'keperluan'         => $request->keperluan,
                'tanggal_berangkat' => $request->tanggal_berangkat,
                'tanggal_kembali'   => $request->tanggal_kembali,
                'total_hari'        => $tanggalBerangkat->diffInDays($tanggalKembali) + 1,
                'bulan'             => $tanggalBerangkat->month,
                'tahun'             => $tanggalBerangkat->year,
                'kehadiran'         => $request->kehadiran,
                'arahan'            => $this->buildCatatanValue($request->arahan ?? []),
                'masalah_temuan'    => $this->buildCatatanValue($request->masalah_temuan ?? []),
                'saran_tindakan'    => $this->buildCatatanValue($request->saran_tindakan ?? []),
                'lain_lain'         => $this->buildCatatanValue($request->lain_lain ?? []),
                'status_bendahara'  => 'menunggu anggaran',
            ]);

            foreach ($petugas as $p) {
                DB::table('spt_petugas')->insert([
                    'spt_id'      => $spt->id,
                    'nip_petugas' => $p,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            foreach ($tujuan as $t) {
                DB::table('spt_tujuan')->insert([
                    'spt_id'            => $spt->id,
                    'jenis_tujuan'      => $t['jenis_tujuan'],
                    'poktan_nama'       => $t['poktan_nama'],
                    'deskripsi_kota'    => $t['deskripsi_kota'],
                    'deskripsi_lainnya' => $t['deskripsi_lainnya'],
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            DB::commit();

            $this->logActivity($request, 'create', 'Menambah data SPT', [
                'id'          => $spt->id,
                'nomor_surat' => $spt->nomor_surat,
            ]);

            return redirect()->route('spt.index')->with('success', 'SPT berhasil disimpan');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $spt = Spt::with(['poktan', 'petugasRel', 'sptTujuan'])->findOrFail($id);

        $selectedPetugas = DB::table('spt_petugas')
            ->where('spt_id', $id)
            ->pluck('nip_petugas')
            ->toArray();

        $selectedTujuan = DB::table('spt_tujuan')
            ->where('spt_id', $id)
            ->get();

        return view('Suratspt.editspt', [
            'spt'              => $spt,
            'petugas'          => Petugas::orderBy('nama')->get(),
            'poktan'           => Poktan::orderBy('nama_poktan')->get(),
            'selectedPetugas'  => $selectedPetugas,
            'selectedTujuan'   => $selectedTujuan,
            'nomorSuratSuffix' => $this->nomorSuratSuffix($spt->tanggal_berangkat),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateSpt($request);

        $spt = Spt::findOrFail($id);

        DB::beginTransaction();

        try {
            $petugas = $this->cleanPetugasIds($request->petugas_ids ?? []);
            $tujuan = $this->buildTujuanRows($request);

            if (count($petugas) < 1) {
                return back()->withInput()->withErrors([
                    'petugas_ids' => 'Pilih minimal 1 petugas.',
                ]);
            }

            if (count($tujuan) < 1) {
                return back()->withInput()->withErrors([
                    'tujuan' => 'Isi minimal 1 tujuan.',
                ]);
            }

            $this->checkBentrokPetugas(
                $petugas,
                $request->tanggal_berangkat,
                $request->tanggal_kembali,
                (int) $id
            );

            $tanggalBerangkat = Carbon::parse($request->tanggal_berangkat);
            $tanggalKembali = Carbon::parse($request->tanggal_kembali);

            $spt->update([
                'nomor_surat'       => $this->buildNomorSurat($request->nomor_surat_input, $request->tanggal_berangkat),
                'alat_angkut'       => $request->alat_angkut,
                'berangkat_dari'    => $request->berangkat_dari,
                'keperluan'         => $request->keperluan,
                'tanggal_berangkat' => $request->tanggal_berangkat,
                'tanggal_kembali'   => $request->tanggal_kembali,
                'total_hari'        => $tanggalBerangkat->diffInDays($tanggalKembali) + 1,
                'bulan'             => $tanggalBerangkat->month,
                'tahun'             => $tanggalBerangkat->year,
                'kehadiran'         => $request->kehadiran,
                'arahan'            => $this->buildCatatanValue($request->arahan ?? []),
                'masalah_temuan'    => $this->buildCatatanValue($request->masalah_temuan ?? []),
                'saran_tindakan'    => $this->buildCatatanValue($request->saran_tindakan ?? []),
                'lain_lain'         => $this->buildCatatanValue($request->lain_lain ?? []),
            ]);

            DB::table('spt_petugas')->where('spt_id', $id)->delete();
            foreach ($petugas as $p) {
                DB::table('spt_petugas')->insert([
                    'spt_id'      => $id,
                    'nip_petugas' => $p,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            DB::table('spt_tujuan')->where('spt_id', $id)->delete();
            foreach ($tujuan as $t) {
                DB::table('spt_tujuan')->insert([
                    'spt_id'            => $id,
                    'jenis_tujuan'      => $t['jenis_tujuan'],
                    'poktan_nama'       => $t['poktan_nama'],
                    'deskripsi_kota'    => $t['deskripsi_kota'],
                    'deskripsi_lainnya' => $t['deskripsi_lainnya'],
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            DB::commit();

            $this->logActivity($request, 'update', 'Mengubah data SPT', [
                'id'          => $spt->id,
                'nomor_surat' => $spt->nomor_surat,
            ]);

            return redirect()->route('spt.index')->with('success', 'Data berhasil diupdate');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        $spt = Spt::findOrFail($id);

        DB::beginTransaction();

        try {
            DB::table('spt_petugas')->where('spt_id', $id)->delete();
            DB::table('spt_tujuan')->where('spt_id', $id)->delete();
            $spt->delete();

            DB::commit();

            $this->logActivity($request, 'delete', 'Menghapus data SPT', [
                'id'          => $spt->id,
                'nomor_surat' => $spt->nomor_surat,
            ]);

            return redirect()->route('spt.index')->with('success', 'Data SPT berhasil dihapus');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('spt.index')->with('error', 'Data SPT gagal dihapus');
        }
    }

    public function print($id = null)
    {
        $user = Auth::user();

        if ($id) {
            $spt = Spt::with(['poktan', 'petugasRel', 'sptTujuan', 'keuangan'])->findOrFail($id);

            return view('spj.cetak', [
                'mode' => 'single',
                'spt'  => $spt,
                'user' => $user,
            ]);
        }

        $spts = Spt::with(['poktan', 'petugasRel', 'sptTujuan', 'keuangan'])
            ->orderByDesc('created_at')
            ->get();

        return view('spj.cetak', [
            'mode' => 'all',
            'spts' => $spts,
            'user' => $user,
        ]);
    }
}
