<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanaMasuk extends Model
{
    protected $table = 'dana_masuk';

    protected $fillable = [
        'tanggal',
        'sumber_dana',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];
}
