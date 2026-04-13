<?php

namespace App\Exports;

use App\Models\Poktan;
use Maatwebsite\Excel\Concerns\FromCollection;

class PoktanExport implements FromCollection
{
    public function collection()
    {
        return Poktan::select('nama_poktan', 'desa', 'kecamatan')->get();
    }
}
