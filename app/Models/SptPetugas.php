<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SptPetugas extends Model
{
    protected $table = 'spt_petugas';

    protected $fillable = [
        'spt_id',
        'nip_petugas',
    ];

    public function spt()
    {
        return $this->belongsTo(Spt::class, 'spt_id', 'id');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'nip_petugas', 'nip');
    }
}
