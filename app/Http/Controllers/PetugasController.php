<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Petugas;
use App\Models\Activity;

class PetugasController extends Controller
{
    private function logActivity(Request $request, string $action, string $keterangan, array $data = []): void
    {
        Activity::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'method'     => strtoupper($request->method()),
            'route'      => optional($request->route())->getName(),
            'url'        => $request->fullUrl(),
            'payload'    => json_encode([
                'keterangan'   => $keterangan,
                'redirect_url' => route('petugas.index'),
                'data'         => $data,
            ], JSON_UNESCAPED_UNICODE),
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }

    public function index()
    {
        $petugas = Petugas::orderBy('created_at', 'desc')->get();
        return view('Datapetugas.Datapetugas', compact('petugas'));
    }

    public function create()
    {
        return view('Datapetugas.tambahpetugas');
    }

    /* =======================
     * STORE
     * ======================= */
    public function store(Request $request)
    {
        $request->validate([
            'nip'     => 'required|string|max:20|unique:petugas,nip',
            'nama'    => 'required|string|max:255',
            'pangkat' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
        ], [
            'nip.unique' => 'NIP sudah terdaftar!',
        ]);

        $petugas = Petugas::create(
            $request->only(['nip', 'nama', 'pangkat', 'jabatan'])
        );

        $this->logActivity($request, 'create', 'Menambah data Petugas', [
            'nip'     => $petugas->nip,
            'nama'    => $petugas->nama,
            'pangkat' => $petugas->pangkat,
            'jabatan' => $petugas->jabatan,
        ]);

        return redirect()->route('petugas.index')
            ->with('success', 'Data petugas berhasil ditambahkan');
    }

    public function show(Petugas $petugas)
    {
        return redirect()->route('petugas.index');
    }

    public function edit(Petugas $petugas)
    {
        return view('Datapetugas.editpetugas', compact('petugas'));
    }

    /* =======================
     * UPDATE
     * ======================= */
    public function update(Request $request, Petugas $petugas)
    {
        $request->validate([
            'nama'    => 'required|string|max:255',
            'pangkat' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
        ]);

        // ❗ NIP tidak diubah
        $petugas->update(
            $request->only(['nama', 'pangkat', 'jabatan'])
        );

        $this->logActivity($request, 'update', 'Mengubah data Petugas', [
            'nip'     => $petugas->nip,
            'nama'    => $petugas->nama,
            'pangkat' => $petugas->pangkat,
            'jabatan' => $petugas->jabatan,
        ]);

        return redirect()->route('petugas.index')
            ->with('success', 'Data berhasil diupdate');
    }

    /* =======================
     * DELETE
     * ======================= */
    public function destroy(Request $request, Petugas $petugas)
    {
        $info = [
            'nip'     => $petugas->nip,
            'nama'    => $petugas->nama,
            'pangkat' => $petugas->pangkat,
            'jabatan' => $petugas->jabatan,
        ];

        $petugas->delete();

        $this->logActivity($request, 'delete', 'Menghapus data Petugas', $info);

        return redirect()->route('petugas.index')
            ->with('success', 'Data petugas berhasil dihapus');
    }
}
