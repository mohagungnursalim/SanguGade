<?php

namespace App\Jobs;

use App\Helpers\PedagangCacheHelper;
use App\Models\Pedagang;
use App\Services\XlsxExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportPedagangJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $pasarId,
        public string $fileName
    ) {}

    public function handle(): void
    {
        $query = Pedagang::with('pasar:id,nama_pasar')->orderBy('id', 'asc');
        if ($this->pasarId) {
            $query->where('pasar_id', $this->pasarId);
        }

        $headers = [
            'No',
            'Nama',
            'NIK',
            'Alamat Tinggal',
            'Jenis Sarana',
            'Ukuran',
            'Tarif per meter (m2)',
            'Total tarif bulanan (Rp)',
            'Jenis Usaha',
            'Titik Kordinat',
            'Alamat Usaha',
            'Pasar'
        ];

        $rows = [];
        $no = 1;
        $query->chunk(500, function ($pedagangs) use (&$rows, &$no) {
            foreach ($pedagangs as $item) {
                $kordinat = ($item->latitude && $item->longitude)
                    ? "{$item->latitude}, {$item->longitude}"
                    : '-';

                $rows[] = [
                    $no++,
                    $item->nama,
                    $item->nik ? "'{$item->nik}" : '-',
                    $item->alamat ?? '-',
                    $item->jenis_sarana ?? '-',
                    $item->ukuran ?? 0,
                    $item->tarif_per_meter ?? 0,
                    $item->tarif_bulanan ?? 0,
                    $item->jenis_usaha ?? '-',
                    $kordinat,
                    $item->alamat_usaha ?? '-',
                    $item->pasar?->nama_pasar ?? '-'
                ];
            }
        });

        $fullPath = storage_path('app/public/exports/' . $this->fileName);
        XlsxExportService::create($headers, $rows, $fullPath);
    }
}

