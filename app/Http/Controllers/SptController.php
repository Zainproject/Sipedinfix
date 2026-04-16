<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Spt;
use App\Models\Petugas;
use App\Models\Poktan;
use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

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

    private function formatTanggalIndonesia(?string $tanggal): string
    {
        return $tanggal
            ? Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y')
            : '-';
    }

    private function normalizeToArray($val): array
    {
        if (is_array($val)) {
            return array_values($val);
        }

        if (is_string($val)) {
            $val = trim($val);
            if ($val === '') {
                return [];
            }

            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                return array_values($decoded);
            }

            $val = str_replace(['|'], ';', $val);

            return array_values(array_filter(array_map('trim', explode(';', $val))));
        }

        return [];
    }

    private function getPejabatPenandatangan(): array
    {
        $ketua = User::where(function ($query) {
            $query->where('role', 'ketua')
                ->orWhere('jabatan', 'ketua');
        })->first();

        return [
            'nama' => $ketua->name ?? $ketua->nama ?? 'ERFAN EVENDI, SP., M.Si',
            'nip'  => $ketua->nip ?? '19760723199901 1 001',
        ];
    }

    private function getBendaharaData(): array
    {
        $bendahara = User::where(function ($query) {
            $query->where('role', 'bendahara')
                ->orWhere('jabatan', 'bendahara');
        })->first();

        return [
            'nama' => $bendahara->name ?? $bendahara->nama ?? 'NUR KHAYATI, ST',
            'nip'  => $bendahara->nip ?? '19911019 201903 2 013',
        ];
    }

    private function getJumlahUangText($spt): string
    {
        $nominal = $spt->keuangan->jumlah ?? $spt->keuangan->nominal ?? 80000;
        $nominal = (int) $nominal;

        return 'Rp. ' . number_format($nominal, 0, ',', '.') . ',-';
    }

    private function getJumlahAngkaOnly($spt): string
    {
        $nominal = $spt->keuangan->jumlah ?? $spt->keuangan->nominal ?? 80000;
        $nominal = (int) $nominal;

        return number_format($nominal, 0, ',', '.') . ',-';
    }

    private function getTerbilangText($spt): string
    {
        return 'Delapan Puluh Ribu Rupiah';
    }

    private function getSafeMak($spt): string
    {
        $mak = trim((string) ($spt->mak ?? ''));
        if ($mak !== '') {
            return $mak;
        }

        return trim((string) ($spt->keuangan->mak ?? 'APBN')) ?: 'APBN';
    }

    private function getTujuanData($spt): array
    {
        $tujuan = $this->normalizeToArray($spt->tujuan ?? null);
        $poktanNama = $this->normalizeToArray($spt->poktan_nama ?? null);
        $deskripsiKota = $this->normalizeToArray($spt->deskripsi_kota ?? null);
        $deskripsiLainnya = $this->normalizeToArray($spt->deskripsi_lainnya ?? null);

        if (
            count($tujuan) === 0 &&
            isset($spt->sptTujuan) &&
            $spt->sptTujuan &&
            $spt->sptTujuan->count() > 0
        ) {
            foreach ($spt->sptTujuan as $row) {
                $tujuan[] = trim((string) ($row->jenis_tujuan ?? ''));
                $poktanNama[] = $row->poktan_nama ?? null;
                $deskripsiKota[] = $row->deskripsi_kota ?? null;
                $deskripsiLainnya[] = $row->deskripsi_lainnya ?? null;
            }
        }

        return [$tujuan, $poktanNama, $deskripsiKota, $deskripsiLainnya];
    }

    private function buildFinalNarasi($spt): string
    {
        [$tujuan, $poktanNama, $deskripsiKota, $deskripsiLainnya] = $this->getTujuanData($spt);

        $keperluanParts = array_values(
            array_filter(array_map('trim', explode(';', (string) $spt->keperluan)))
        );

        $getKeperluan = function ($i) use ($keperluanParts, $spt) {
            if (count($keperluanParts) === 0) {
                return trim((string) $spt->keperluan);
            }

            return $keperluanParts[$i] ?? $keperluanParts[0];
        };

        $ktByKep = [];
        $kabByKep = [];
        $lainByKep = [];
        $order = [];

        foreach ($tujuan as $i => $tj) {
            $tj = trim((string) $tj);
            $kep = $getKeperluan($i);

            if ($tj === 'kelompok_tani' || $tj === 'poktan') {
                $namaPoktan = trim((string) ($poktanNama[$i] ?? ''));

                if ($namaPoktan === '') {
                    continue;
                }

                $poktan = Poktan::where('nama_poktan', $namaPoktan)->first();

                $teksPoktan = $poktan
                    ? 'KT. ' . $poktan->nama_poktan . ' Desa ' . $poktan->desa . ' Kecamatan ' . $poktan->kecamatan
                    : 'KT. ' . $namaPoktan;

                if (!isset($ktByKep[$kep])) {
                    $ktByKep[$kep] = [];
                    $order[] = ['type' => 'kt', 'kep' => $kep];
                }

                $ktByKep[$kep][] = $teksPoktan;
            } elseif ($tj === 'kabupaten_kota') {
                $kota = trim((string) ($deskripsiKota[$i] ?? ''));
                if ($kota === '' || $kota === '-') {
                    continue;
                }

                if (!isset($kabByKep[$kep])) {
                    $kabByKep[$kep] = [];
                    $order[] = ['type' => 'kab', 'kep' => $kep];
                }

                $kabByKep[$kep][] = $kota;
            } elseif ($tj === 'lainnya' || $tj === 'lain_lain') {
                $lain = trim((string) ($deskripsiLainnya[$i] ?? ''));
                if ($lain === '' || $lain === '-') {
                    continue;
                }

                if (!isset($lainByKep[$kep])) {
                    $lainByKep[$kep] = [];
                    $order[] = ['type' => 'lain', 'kep' => $kep];
                }

                $lainByKep[$kep][] = $lain;
            }
        }

        $segments = [];

        foreach ($order as $o) {
            if ($o['type'] === 'kt') {
                $kep = $o['kep'];
                $segments[] = $kep . ' ke ' . implode(' dan ', $ktByKep[$kep]) . ' Kabupaten Sumenep';
            }

            if ($o['type'] === 'kab') {
                $kep = $o['kep'];
                $segments[] = $kep . ' ke ' . implode(' dan ', $kabByKep[$kep]);
            }

            if ($o['type'] === 'lain') {
                $kep = $o['kep'];
                $segments[] = $kep . ' ke ' . implode(' dan ', $lainByKep[$kep]);
            }
        }

        return trim(implode(' dan ', array_filter($segments)));
    }

    private function buildLokasiUtama($spt): string
    {
        [$tujuan, $poktanNama, $deskripsiKota, $deskripsiLainnya] = $this->getTujuanData($spt);

        $listKT = [];
        $listLokasi = [];

        foreach ($tujuan as $i => $tj) {
            $tj = trim((string) $tj);

            if ($tj === 'kelompok_tani' || $tj === 'poktan') {
                $namaPoktan = trim((string) ($poktanNama[$i] ?? ''));
                if ($namaPoktan !== '' && $namaPoktan !== '-') {
                    $listKT[] = 'KT. ' . $namaPoktan;
                }
            } elseif ($tj === 'kabupaten_kota') {
                $kota = trim((string) ($deskripsiKota[$i] ?? ''));
                if ($kota !== '' && $kota !== '-') {
                    $listLokasi[] = $kota;
                }
            } elseif ($tj === 'lainnya' || $tj === 'lain_lain') {
                $lain = trim((string) ($deskripsiLainnya[$i] ?? ''));
                if ($lain !== '' && $lain !== '-') {
                    $listLokasi[] = $lain;
                }
            }
        }

        $listKT = array_values(array_unique($listKT));
        $listLokasi = array_values(array_unique($listLokasi));

        if (count($listLokasi) > 0) {
            return implode(' dan ', $listLokasi);
        }

        if (count($listKT) > 0) {
            return implode(' dan ', $listKT);
        }

        return '-';
    }

    private function buildUntukPembayaranText($spt): string
    {
        $narasi = $this->buildFinalNarasi($spt);
        $tanggal = $this->formatTanggalIndonesia($spt->tanggal_berangkat);

        return 'Biaya Bantuan Transport untuk ' . $narasi
            . ', sesuai SPT Nomor : ' . ($spt->nomor_surat ?? '-')
            . ' tanggal ' . $tanggal
            . ' dan SPD Nomor : ' . ($spt->nomor_surat ?? '-')
            . ', dengan perincian terlampir.';
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

    public function downloadWord($id)
    {
        $spt = Spt::with(['petugasRel', 'sptTujuan', 'keuangan'])->findOrFail($id);

        $templatePath = storage_path('app/templates/master_spj_template.docx');

        if (!file_exists($templatePath)) {
            return back()->with('error', 'Template master tidak ditemukan di storage/app/templates/master_spj_template.docx');
        }

        $petugasList = collect($spt->petugasRel)->values();

        if ($petugasList->count() < 1) {
            return back()->with('error', 'Data petugas pada SPT tidak ditemukan.');
        }

        $petugasUtama = $petugasList->first();
        $pengikut = $petugasList->slice(1)->values();
        $adaPengikut = $pengikut->count() > 0;

        $template = new TemplateProcessor($templatePath);

        $pejabat = $this->getPejabatPenandatangan();
        $bendahara = $this->getBendaharaData();
        $jumlahText = $this->getJumlahUangText($spt);
        $jumlahAngka = $this->getJumlahAngkaOnly($spt);

        // SPT
        $template->setValue('nomor_surat', $spt->nomor_surat ?? '-');
        $template->setValue('untuk_narasi', $this->buildFinalNarasi($spt) ?: '-');
        $template->setValue('tempat_keluar', 'Sumenep');
        $template->setValue('tanggal_surat', $this->formatTanggalIndonesia($spt->tanggal_berangkat));
        $template->setValue('nama_pejabat', $pejabat['nama']);
        $template->setValue('nip_pejabat', $pejabat['nip']);

        $template->cloneBlock('petugas_block', $petugasList->count(), true, true);
        foreach ($petugasList as $i => $petugas) {
            $n = $i + 1;
            $template->setValue("no#{$n}", $n);
            $template->setValue("nama#{$n}", $petugas->nama ?? '-');
            $template->setValue("nip#{$n}", $petugas->nip ?? '-');
            $template->setValue("pangkat#{$n}", $petugas->pangkat ?? '-');
            $template->setValue("jabatan#{$n}", $petugas->jabatan ?? '-');
        }

        // SPD
        $template->setValue('nomor_spd', $spt->nomor_surat ?? '-');
        $template->setValue('lembar_spd', 'I / II');
        $template->setValue('nama', $petugasUtama->nama ?? '-');
        $template->setValue('nip', $petugasUtama->nip ?? '-');
        $template->setValue('pangkat', $petugasUtama->pangkat ?? '-');
        $template->setValue('jabatan', $petugasUtama->jabatan ?? '-');
        $template->setValue('maksud_perjalanan', $this->buildFinalNarasi($spt) ?: '-');
        $template->setValue('alat_angkut', $spt->alat_angkut ?? '-');
        $template->setValue('tempat_berangkat', 'Sumenep');
        $template->setValue('tempat_tujuan', $this->buildLokasiUtama($spt));
        $template->setValue('lama_perjalanan', ($spt->total_hari ?? 1) . ' hari');
        $template->setValue('tanggal_berangkat', $this->formatTanggalIndonesia($spt->tanggal_berangkat));
        $template->setValue('tanggal_kembali', $this->formatTanggalIndonesia($spt->tanggal_kembali));
        $template->setValue('skpd', 'DINAS KETAHANAN PANGAN DAN PERTANIAN KABUPATEN SUMENEP');
        $template->setValue('kode_rekening', $this->getSafeMak($spt));
        $template->setValue('keterangan_lain', '-');

        if ($adaPengikut) {
            $template->cloneRow('pengikut_nama', $pengikut->count());

            foreach ($pengikut as $i => $p) {
                $n = $i + 1;
                $template->setValue("pengikut_nama#{$n}", $p->nama ?? '-');
                $template->setValue("pengikut_pangkat#{$n}", $p->pangkat ?? '-');
                $template->setValue("pengikut_jabatan#{$n}", $p->jabatan ?? '-');
            }
        } else {
            $template->setValue('pengikut_nama', '-');
            $template->setValue('pengikut_pangkat', '-');
            $template->setValue('pengikut_jabatan', '-');
        }

        // Kuitansi
        $template->cloneBlock('kuitansi_block', $petugasList->count(), true, true);
        foreach ($petugasList as $i => $petugas) {
            $n = $i + 1;
            $template->setValue("tahun_anggaran#{$n}", $spt->tahun ?? date('Y'));
            $template->setValue("nomor_bukti#{$n}", $spt->keuangan->nomor_bukti ?? '');
            $template->setValue("mak#{$n}", $this->getSafeMak($spt));
            $template->setValue("dipa#{$n}", $spt->keuangan->dipa ?? '');
            $template->setValue("sudah_terima_dari#{$n}", 'KUASA PENGGUNA ANGGARAN DINAS PERTANIAN DAN KETAHANAN PANGAN PROVINSI JAWA TIMUR');
            $template->setValue("jumlah_uang#{$n}", $jumlahText);
            $template->setValue("terbilang#{$n}", $this->getTerbilangText($spt));
            $template->setValue("untuk_pembayaran#{$n}", $this->buildUntukPembayaranText($spt));
            $template->setValue("nama_penerima#{$n}", $petugas->nama ?? '-');
            $template->setValue("nip_penerima#{$n}", $petugas->nip ?? '-');
            $template->setValue("tanggal_lunas#{$n}", '');
            $template->setValue("nama_bendahara#{$n}", $bendahara['nama']);
            $template->setValue("nip_bendahara#{$n}", $bendahara['nip']);
        }

        // Realisasi
        $template->cloneBlock('realisasi_block', $petugasList->count(), true, true);
        foreach ($petugasList as $i => $petugas) {
            $n = $i + 1;
            $template->setValue("realisasi_nomor_spd#{$n}", $spt->nomor_surat ?? '-');
            $template->setValue("realisasi_tanggal_surat#{$n}", $this->formatTanggalIndonesia($spt->tanggal_berangkat));
            $template->setValue("realisasi_rincian_1#{$n}", 'Biaya Bantuan Transport');
            $template->setValue("realisasi_jumlah_1#{$n}", $jumlahAngka);
            $template->setValue("realisasi_total_biaya#{$n}", $jumlahAngka);
            $template->setValue("realisasi_nama_bendahara#{$n}", $bendahara['nama']);
            $template->setValue("realisasi_nip_bendahara#{$n}", $bendahara['nip']);
            $template->setValue("realisasi_nama_penerima#{$n}", $petugas->nama ?? '-');
            $template->setValue("realisasi_nip_penerima#{$n}", $petugas->nip ?? '-');
            $template->setValue("ditetapkan_sejumlah#{$n}", $jumlahAngka);
            $template->setValue("dibayar_semula#{$n}", $jumlahAngka);
            $template->setValue("sisa_dibayar#{$n}", ',-');
            $template->setValue("sisa_selisih#{$n}", ',-');
        }

        // Rencana
        $template->cloneBlock('rencana_block', $petugasList->count(), true, true);
        foreach ($petugasList as $i => $petugas) {
            $n = $i + 1;
            $template->setValue("rencana_nomor_spd#{$n}", $spt->nomor_surat ?? '-');
            $template->setValue("rencana_tanggal_surat#{$n}", $this->formatTanggalIndonesia($spt->tanggal_berangkat));
            $template->setValue("rencana_rincian_1#{$n}", 'Biaya Bantuan Transport');
            $template->setValue("rencana_jumlah_1#{$n}", $jumlahAngka);
            $template->setValue("rencana_total_biaya#{$n}", $jumlahAngka);
            $template->setValue("rencana_nama_bendahara#{$n}", $bendahara['nama']);
            $template->setValue("rencana_nip_bendahara#{$n}", $bendahara['nip']);
            $template->setValue("rencana_nama_penerima#{$n}", $petugas->nama ?? '-');
            $template->setValue("rencana_nip_penerima#{$n}", $petugas->nip ?? '-');
        }

        // Laporan
        $template->setValue('dasar_spt', $spt->nomor_surat ?? '-');
        $template->setValue('tanggal_spt', $this->formatTanggalIndonesia($spt->tanggal_berangkat));
        $template->setValue('maksud_tujuan', $this->buildFinalNarasi($spt) ?: '-');
        $template->setValue('tanggal_pelaksanaan', $this->formatTanggalIndonesia($spt->tanggal_berangkat));
        $template->setValue('daerah_tujuan', $this->buildLokasiUtama($spt));
        $template->setValue('peserta_hadir', $spt->kehadiran ?? '-');
        $template->setValue('petunjuk_arahan', $spt->arahan ?? '-');
        $template->setValue('masalah', $spt->masalah_temuan ?? '-');
        $template->setValue('saran', $spt->saran_tindakan ?? '-');
        $template->setValue('lain_lain', $spt->lain_lain ?? '-');

        $template->cloneBlock('laporan_petugas_block', $petugasList->count(), true, true);
        foreach ($petugasList as $i => $petugas) {
            $n = $i + 1;
            $template->setValue("laporan_no#{$n}", $n);
            $template->setValue("laporan_nama#{$n}", $petugas->nama ?? '-');
            $template->setValue("laporan_nip#{$n}", $petugas->nip ?? '-');
        }

        $template->cloneBlock('pelapor_block', $petugasList->count(), true, true);
        foreach ($petugasList as $i => $petugas) {
            $n = $i + 1;
            $template->setValue("pelapor_no#{$n}", $n);
            $template->setValue("pelapor_nama#{$n}", $petugas->nama ?? '-');
            $template->setValue("pelapor_nip#{$n}", $petugas->nip ?? '-');
        }

        // Perjalanan
        $tujuan = collect($spt->sptTujuan)->values();
        $template->setValue('perjalanan_nama_pejabat', $pejabat['nama']);
        $template->setValue('perjalanan_nip_pejabat', $pejabat['nip']);
        $template->setValue('perjalanan_tanggal_berangkat', $this->formatTanggalIndonesia($spt->tanggal_berangkat));
        $template->setValue('perjalanan_tanggal_kembali', $this->formatTanggalIndonesia($spt->tanggal_kembali));
        $template->setValue('perjalanan_tempat_berangkat', $spt->berangkat_dari ?? 'BPP Gapura');
        $template->setValue(
            'perjalanan_tempat_tujuan_1',
            $tujuan[0]->poktan_nama ?? $tujuan[0]->deskripsi_kota ?? $tujuan[0]->deskripsi_lainnya ?? '-'
        );
        $template->setValue(
            'perjalanan_tempat_tujuan_2',
            $tujuan[1]->poktan_nama ?? $tujuan[1]->deskripsi_kota ?? $tujuan[1]->deskripsi_lainnya ?? '-'
        );
        $template->setValue('pejabat_lokasi_1', 'Ismail');
        $template->setValue('pejabat_lokasi_2', 'Sahiruddin');
        $template->setValue('nip_lokasi_1', '');
        $template->setValue('nip_lokasi_2', '');

        $safeNomor = preg_replace('/[^A-Za-z0-9\-]/', '_', (string) ($spt->nomor_surat ?? 'SPJ'));
        $fileName = 'SPJ_' . $safeNomor . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word_');

        $template->saveAs($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
