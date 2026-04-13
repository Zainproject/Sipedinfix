<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PetugasImport;
use App\Imports\PoktanImport;
use App\Imports\SptImport;
use App\Exports\PetugasExport;
use App\Exports\PoktanExport;
use App\Exports\SptExport;

class ImportController extends Controller
{
    public function index()
    {
        return view('Import.index');
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT
    |--------------------------------------------------------------------------
    */

    public function importPetugas(Request $request)
    {
        $request->validate([
            'file_petugas' => 'required|file|mimes:xlsx,xls,csv',
        ], [
            'file_petugas.required' => 'File petugas wajib dipilih.',
            'file_petugas.mimes' => 'Format file petugas harus xlsx, xls, atau csv.',
        ]);

        try {
            Excel::import(new PetugasImport, $request->file('file_petugas'));
            return back()->with('success', 'Import Petugas berhasil.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Import Petugas gagal: ' . $e->getMessage());
        }
    }

    public function importPoktan(Request $request)
    {
        $request->validate([
            'file_poktan' => 'required|file|mimes:xlsx,xls,csv',
        ], [
            'file_poktan.required' => 'File poktan wajib dipilih.',
            'file_poktan.mimes' => 'Format file poktan harus xlsx, xls, atau csv.',
        ]);

        try {
            Excel::import(new PoktanImport, $request->file('file_poktan'));
            return back()->with('success', 'Import Poktan berhasil.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Import Poktan gagal: ' . $e->getMessage());
        }
    }

    public function importSpt(Request $request)
    {
        $request->validate([
            'file_spt' => 'required|file|mimes:xlsx,xls,csv',
        ], [
            'file_spt.required' => 'File SPT wajib dipilih.',
            'file_spt.mimes' => 'Format file SPT harus xlsx, xls, atau csv.',
        ]);

        try {
            Excel::import(new SptImport, $request->file('file_spt'));
            return back()->with('success', 'Import SPT berhasil.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Import SPT gagal: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */

    public function exportPetugas()
    {
        return Excel::download(new PetugasExport, 'data_petugas.xlsx');
    }

    public function exportPoktan()
    {
        return Excel::download(new PoktanExport, 'data_poktan.xlsx');
    }

    public function exportSpt()
    {
        return Excel::download(new SptExport, 'data_spt.xlsx');
    }
}
