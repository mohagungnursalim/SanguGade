<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasar extends Model
{
    protected $table = 'pasars';

    protected $fillable = [
        'nama_pasar',
        'image',
        'tahun_pembangunan',
        'luas_lahan',
        'ada_sertifikat',
        'kecamatan',
        'kelurahan',
        'status_pasar',
        'jumlah_pedagang',
        'kios',
        'los',
        'lapak',
        'ruko',
        'kondisi_baik',
        'kondisi_rusak',
        'kondisi_terpakai',
        'kepala_pasar',
        'petugas_kebersihan',
        'petugas_keamanan',
        'retribusi',
        'ptt',
    ];

    protected $casts = [
        'ada_sertifikat'     => 'boolean',
        'tahun_pembangunan'  => 'integer',
        'luas_lahan'         => 'float',
        'jumlah_pedagang'    => 'integer',
        'kios'               => 'integer',
        'los'                => 'integer',
        'lapak'              => 'integer',
        'ruko'               => 'integer',
        'kondisi_baik'       => 'integer',
        'kondisi_rusak'      => 'integer',
        'kondisi_terpakai'   => 'integer',
        'kepala_pasar'       => 'integer',
        'petugas_kebersihan' => 'integer',
        'petugas_keamanan'   => 'integer',
        'retribusi'          => 'integer',
        'ptt'                => 'integer',
    ];

    /**
     * Relasi ke data pendapatan pasar (SSRD)
     */
    public function pendapatanPasars()
    {
        return $this->hasMany(PendapatanPasar::class, 'pasar_id');
    }
}

