<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendapatanPasar extends Model
{
    use HasFactory;

    protected $table = 'pendapatan_pasars';

    protected $fillable = [
        'pasar_id',
        'tahun_ssrd',
        'no_rekening_bank',
        'masa_retribusi',
        'tahun_retribusi',
        'nomor_urut_ssrd',
        'jenis_retribusi',
        'nomor_ayat',
        'jumlah_retribusi',
        'terbilang',
        'tanggal_penyetoran',
        'nama_penyetor',
        'petugas_bank',
        'keterangan',
    ];

    protected $casts = [
        'tahun_ssrd'         => 'integer',
        'tahun_retribusi'    => 'integer',
        'jumlah_retribusi'   => 'float',
        'tanggal_penyetoran' => 'date',
    ];

    public function pasar(): BelongsTo
    {
        return $this->belongsTo(Pasar::class, 'pasar_id');
    }

    /**
     * Format rupiah helper
     */
    public function getFormattedJumlahAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah_retribusi, 0, ',', '.');
    }
}

