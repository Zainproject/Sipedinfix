<?php

namespace App\Exports;

use App\Models\Petugas;
use Maatwebsite\Excel\Concerns\FromCollection;

class PetugasExport implements FromCollection
{
    public function collection()
    {
        return Petugas::select('nip', 'nama')->get();
    }
}
