<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SptTujuan extends Model
{
    protected $table = 'spt_tujuan';

    protected $fillable = [
        'spt_id',
        'jenis_tujuan',
        'poktan_nama',
        'deskripsi_kota',
        'deskripsi_lainnya',
    ];

    public function spt()
    {
        return $this->belongsTo(Spt::class, 'spt_id', 'id');
    }

    public function poktan()
    {
        return $this->belongsTo(Poktan::class, 'poktan_nama', 'nama_poktan');
    }

    public function getTujuanLengkapAttribute()
    {
        if ($this->jenis_tujuan === 'poktan') {
            $namaPoktan = $this->poktan_nama ?: '-';
            $desa = optional($this->poktan)->desa ?: '-';
            $kecamatan = optional($this->poktan)->kecamatan ?: '-';

            return 'Poktan ' . $namaPoktan . ', Desa ' . $desa . ', Kecamatan ' . $kecamatan;
        }

        if ($this->jenis_tujuan === 'kota') {
            return $this->deskripsi_kota ?: '-';
        }

        if ($this->jenis_tujuan === 'lainnya') {
            return $this->deskripsi_lainnya ?: '-';
        }

        return $this->poktan_nama
            ?: $this->deskripsi_kota
            ?: $this->deskripsi_lainnya
            ?: '-';
    }
}
