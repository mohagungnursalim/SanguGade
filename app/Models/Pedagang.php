<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedagang extends Model
{
    use HasFactory, SoftDeletes, MassPrunable;

    /**
     * Hapus permanen data pedagang yang sudah dihapus (soft delete) lebih dari 30 hari.
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subDays(30));
    }

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

