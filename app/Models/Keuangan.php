<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    protected $table = 'keuangan';

    protected $fillable = [
        'spt_id',
        'mak',
        'nomor_kwitansi',
        'detail_petugas',
        'total_biaya',
    ];

    protected $casts = [
        'detail_petugas' => 'array',
    ];

    public function spt()
    {
        return $this->belongsTo(Spt::class, 'spt_id', 'id');
    }
}
