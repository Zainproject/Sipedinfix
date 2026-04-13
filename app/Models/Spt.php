<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spt extends Model
{
    protected $table = 'spts';

    protected $fillable = [
        'poktan_id',
        'nomor_surat',
        'alat_angkut',
        'berangkat_dari',
        'keperluan',
        'tanggal_berangkat',
        'tanggal_kembali',
        'total_hari',
        'bulan',
        'tahun',
        'kehadiran',
        'arahan',
        'masalah_temuan',
        'saran_tindakan',
        'lain_lain',
        'status_bendahara',
        'status_pencairan',

        // field keuangan / cetak
        'nomor_kwitansi',
        'mak',
        'subtotal_perhari',
        'total_biaya',
        'keterangan_biaya',
        'harga_biaya',

        // field tujuan dinamis
        'tujuan',
        'poktan_nama',
        'deskripsi_kota',
        'deskripsi_lainnya',
    ];

    protected $casts = [
        'tanggal_berangkat' => 'date',
        'tanggal_kembali'   => 'date',
        'total_hari'        => 'integer',
        'bulan'             => 'integer',
        'tahun'             => 'integer',

        // dipakai di halaman print
        'tujuan'            => 'array',
        'poktan_nama'       => 'array',
        'deskripsi_kota'    => 'array',
        'deskripsi_lainnya' => 'array',
        'keterangan_biaya'  => 'array',
        'harga_biaya'       => 'array',
    ];

    public function poktan()
    {
        return $this->belongsTo(Poktan::class, 'poktan_id', 'nama_poktan');
    }

    public function sptPetugas()
    {
        return $this->hasMany(SptPetugas::class, 'spt_id', 'id');
    }

    public function petugasRel()
    {
        return $this->belongsToMany(
            Petugas::class,
            'spt_petugas',
            'spt_id',
            'nip_petugas',
            'id',
            'nip'
        )->withTimestamps();
    }

    public function sptTujuan()
    {
        return $this->hasMany(SptTujuan::class, 'spt_id', 'id');
    }

    public function keuangan()
    {
        return $this->hasOne(Keuangan::class, 'spt_id', 'id');
    }

    /**
     * Ambil daftar petugas yang terhubung ke SPT ini.
     * Dipakai oleh view print seperti:
     * $spt->petugasList()
     */
    public function petugasList()
    {
        if ($this->relationLoaded('petugasRel')) {
            return $this->petugasRel->values();
        }

        return $this->petugasRel()->get()->values();
    }
}
