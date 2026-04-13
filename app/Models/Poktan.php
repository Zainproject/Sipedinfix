<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poktan extends Model
{
    protected $table = 'poktan';

    protected $primaryKey = 'nama_poktan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_poktan',
        'ketua',
        'desa',
        'kecamatan',
    ];
}
