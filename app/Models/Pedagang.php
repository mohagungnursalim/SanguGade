<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedagang extends Model
{
    use HasFactory;

    protected $table = 'pedagangs';

    protected $fillable = [
        'nama',
        'nik',
        'email',
        'no_telepon',
        'jenis_kelamin',
        'alamat',
        'nama_tempat',
        'alamat_usaha',
        'jenis_sarana',
        'ukuran',
        'keterangan',
        'tarif_per_meter',
        'tarif_bulanan',
        'jenis_usaha',
        'pasar_id',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'ukuran' => 'float',
        'tarif_per_meter' => 'float',
        'tarif_bulanan' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function pasar(): BelongsTo
    {
        return $this->belongsTo(Pasar::class, 'pasar_id');
    }
}

