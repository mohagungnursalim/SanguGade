<?php

namespace App\Imports;

use App\Helpers\PasarsCacheHelper;
use App\Models\Pedagang;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PedagangImport implements
    ToModel,
    WithHeadingRow,
    SkipsEmptyRows,
    WithBatchInserts,
    WithChunkReading
{
    // Batch upsert per 250 rows — hemat memori & cepat
    public function batchSize(): int { return 250; }

    // Chunk reading per 500 rows — aman untuk file besar (ribuan pedagang)
    public function chunkSize(): int { return 500; }

    public function __construct(
        private ?int $userPasarId,
        private bool $isAdmin
    ) {}

    public function model(array $row): ?Pedagang
    {
        $nama = trim($row['nama'] ?? $row['nama_pedagang'] ?? '');
        if (!$nama) {
            return null;
        }

        // Parse koordinat "lat, lng" dari satu kolom
        $koordinat = trim($row['titik_kordinat'] ?? $row['koordinat'] ?? $row['titik_koordinat'] ?? '');
        $lat = null;
        $lng = null;
        if ($koordinat && str_contains($koordinat, ',')) {
            [$latRaw, $lngRaw] = explode(',', $koordinat, 2);
            $lat = (float)trim($latRaw) ?: null;
            $lng = (float)trim($lngRaw) ?: null;
        }

        // Hitung auto jika total tarif bulanan tidak diisi
        $ukuran = (float)str_replace(',', '.', $row['ukuran'] ?? 0);
        $tarifMeter = (float)str_replace(',', '.', $row['tarif_per_meter_m2'] ?? $row['tarif_per_meter'] ?? 0);
        $tarifBulanan = (float)str_replace(',', '.', $row['total_tarif_bulanan_rp'] ?? $row['tarif_bulanan'] ?? 0);
        if ($tarifBulanan <= 0 && $ukuran > 0 && $tarifMeter > 0) {
            $tarifBulanan = $ukuran * $tarifMeter;
        }

        // Resolusi pasar
        $pasarRaw = trim((string)($row['pasar'] ?? $row['nama_pasar'] ?? $row['nama_atau_id_pasar'] ?? ''));
        $targetPasarId = $this->resolvePasar($pasarRaw);

        // Operator dikunci ke pasarnya sendiri
        if (!$this->isAdmin && $this->userPasarId) {
            $targetPasarId = $this->userPasarId;
        }

        $nik = trim((string)($row['nik'] ?? '')) ?: null;

        return new Pedagang([
            'nik'             => $nik,
            'nama'            => $nama,
            'alamat'          => trim($row['alamat_tinggal'] ?? $row['alamat'] ?? '') ?: null,
            'jenis_sarana'    => trim($row['jenis_sarana'] ?? 'Kios'),
            'ukuran'          => $ukuran,
            'tarif_per_meter' => $tarifMeter,
            'tarif_bulanan'   => $tarifBulanan,
            'jenis_usaha'     => trim($row['jenis_usaha'] ?? 'Sembako'),
            'latitude'        => $lat,
            'longitude'       => $lng,
            'alamat_usaha'    => trim($row['alamat_usaha'] ?? '') ?: null,
            'pasar_id'        => $targetPasarId,
        ]);
    }

    /**
     * Cari pasar by ID angka atau by nama exact/contains.
     */
    private function resolvePasar(string $val): ?int
    {
        if (!$val) {
            return $this->userPasarId ?? PasarsCacheHelper::getAllPasar()->first()?->id;
        }

        if (is_numeric($val)) {
            return (int)$val;
        }

        $pasar = PasarsCacheHelper::getAllPasar()->first(function ($p) use ($val) {
            return strcasecmp($p->nama_pasar, $val) === 0;
        });

        return $pasar?->id ?? ($this->userPasarId ?? PasarsCacheHelper::getAllPasar()->first()?->id);
    }
}

