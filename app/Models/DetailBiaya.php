<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBiaya extends Model
{
    protected $table = 'detail_biaya';

    protected $fillable = [
        'keuangan_id',
        'nip_petugas',
        'no_urut',
        'nama_barang',
        'biaya',
        'keterangan',
    ];

    public function keuangan()
    {
        return $this->belongsTo(Keuangan::class, 'keuangan_id', 'id');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'nip_petugas', 'nip');
    }
}
