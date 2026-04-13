<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    protected $table = 'petugas';
    protected $primaryKey = 'nip';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nip',
        'nama',
        'pangkat',
        'jabatan',
    ];

    public function sptPetugas()
    {
        return $this->hasMany(SptPetugas::class, 'nip_petugas', 'nip');
    }

    public function detailBiaya()
    {
        return $this->hasMany(DetailBiaya::class, 'nip_petugas', 'nip');
    }
}
