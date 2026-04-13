<?php

namespace App\Exports;

use App\Models\Spt;
use Maatwebsite\Excel\Concerns\FromCollection;

class SptExport implements FromCollection
{
    public function collection()
    {
        return Spt::select(
            'nomor_surat',
            'keperluan',
            'tanggal_berangkat',
            'tanggal_kembali',
            'bulan',
            'tahun',
            'status_bendahara',
            'status_pencairan'
        )->get();
    }
}
