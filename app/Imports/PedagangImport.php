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

        // Parse koordinat "lat, lng" dari satu kolom (misal: "-0.891, 119.876" atau ada teks sebelum "-0")
        $koordinatRaw = trim((string)($row['titik_kordinat'] ?? $row['koordinat'] ?? $row['titik_koordinat'] ?? ''));
        [$lat, $lng] = $this->parseCoordinates($koordinatRaw);

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
            'nama_tempat'     => trim($row['nama_tempat'] ?? $row['tempat'] ?? '') ?: null,
            'jenis_sarana'    => trim($row['jenis_sarana'] ?? 'Kios'),
            'ukuran'          => $ukuran,
            'tarif_per_meter' => $tarifMeter,
            'tarif_bulanan'   => $tarifBulanan,
            'jenis_usaha'     => trim($row['jenis_usaha'] ?? 'Sembako'),
            'latitude'        => $lat,
            'longitude'       => $lng,
            'alamat_usaha'    => trim($row['alamat_usaha'] ?? '') ?: null,
            'keterangan'      => trim($row['keterangan'] ?? $row['catatan'] ?? '') ?: null,
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

    /**
     * Ekstrak & bersihkan koordinat Latitude & Longitude.
     * Mampu menangani:
     * - Teks/kata di depan "-0" (misal: "Depan Toko -0.895872, 119.895955" atau "Loc: -0.895872")
     * - Longitude tanpa koma jika terpisah spasi
     * - Melindungi dari error numeric out of range (-90 <= lat <= 90 dan -180 <= lng <= 180)
     *
     * @return array{0: ?float, 1: ?float}
     */
    private function parseCoordinates(string $input): array
    {
        if (!$input) {
            return [null, null];
        }

        // Cari posisi awal "-0" atau "-?[0-9]"
        // Jika ada teks di depan "-0", potong dari mulai "-0"
        $posMinusZero = strpos($input, '-0');
        if ($posMinusZero !== false) {
            $input = substr($input, $posMinusZero);
        }

        // Ekstrak angka desimal (bisa bertanda minus) menggunakan regex
        // Menangkap format seperti -0.895872 dan 119.895955
        preg_match_all('/[-+]?[0-9]+(?:[\.,][0-9]+)?/', $input, $matches);

        $numbers = $matches[0] ?? [];

        if (count($numbers) >= 2) {
            $latVal = (float) str_replace(',', '.', $numbers[0]);
            $lngVal = (float) str_replace(',', '.', $numbers[1]);
        } elseif (count($numbers) === 1) {
            $val = (float) str_replace(',', '.', $numbers[0]);
            $latVal = $val;
            $lngVal = null;
        } else {
            return [null, null];
        }

        // Validasi batasan geografis MySQL decimal(10, 7)
        // Latitude: -90 s/d 90
        $lat = ($latVal !== null && $latVal >= -90.0 && $latVal <= 90.0) ? round($latVal, 7) : null;

        // Longitude: -180 s/d 180
        $lng = ($lngVal !== null && $lngVal >= -180.0 && $lngVal <= 180.0) ? round($lngVal, 7) : null;

        return [$lat, $lng];
    }
}

