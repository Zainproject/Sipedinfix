<?php

namespace App\Http\Controllers;

use App\Models\DanaMasuk;
use Illuminate\Http\Request;

class DanaMasukController extends Controller
{
    public function index()
    {
        $data = DanaMasuk::latest()->get();

        $totalDanaMasuk = $data->sum('nominal');

        return view('dana_masuk.index', compact('data', 'totalDanaMasuk'));
    }

    public function create()
    {
        return view('dana_masuk.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'sumber_dana' => 'required|string|max:100',
            'nominal' => 'required|numeric|min:0|max:999999999999.99',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DanaMasuk::create([
            'tanggal' => $request->tanggal,
            'sumber_dana' => $request->sumber_dana,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('dana-masuk.index')
            ->with('success', 'Dana masuk berhasil disimpan.');
    }

    public function edit($id)
    {
        $item = DanaMasuk::findOrFail($id);

        return view('dana_masuk.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = DanaMasuk::findOrFail($id);

        $request->validate([
            'tanggal' => 'required|date',
            'sumber_dana' => 'required|string|max:100',
            'nominal' => 'required|numeric|min:0|max:999999999999.99',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $item->update([
            'tanggal' => $request->tanggal,
            'sumber_dana' => $request->sumber_dana,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('dana-masuk.index')
            ->with('success', 'Dana masuk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = DanaMasuk::findOrFail($id);
        $item->delete();

        return redirect()->route('dana-masuk.index')
            ->with('success', 'Dana masuk berhasil dihapus.');
    }
}
