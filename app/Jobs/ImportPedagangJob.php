<?php

namespace App\Jobs;

use App\Helpers\PasarsCacheHelper;
use App\Helpers\PedagangCacheHelper;
use App\Models\Pedagang;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportPedagangJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // Timeout 10 menit untuk data ribuan

    public function __construct(
        public string $filePath, // Path relatif di storage disk
        public ?int $userPasarId,
        public bool $isAdmin
    ) {}

    public function handle(): void
    {
        $disk = Storage::disk('local');
        if (!$disk->exists($this->filePath)) {
            Log::error("ImportPedagangJob: File tidak ditemukan: {$this->filePath}");
            return;
        }

        $fullPath = $disk->path($this->filePath);
        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            Log::error("ImportPedagangJob: Gagal membuka file: {$fullPath}");
            return;
        }

        // Deteksi delimiter: koma (,) atau titik koma (;)
        $header = fgetcsv($handle, 2000, ',');
        if ($header && count($header) === 1 && str_contains($header[0], ';')) {
            rewind($handle);
            $header = fgetcsv($handle, 2000, ';');
            $delimiter = ';';
        } else {
            $delimiter = ',';
        }

        $pasars = PasarsCacheHelper::getAllPasar()->pluck('id', 'nama_pasar');
        $defaultPasarId = $this->userPasarId ?? PasarsCacheHelper::getAllPasar()->first()?->id;

        $batch = [];
        $batchSize = 250; // Upsert batch per 250 record untuk efisiensi memori & kecepatan
        $totalProcessed = 0;

        while (($row = fgetcsv($handle, 2000, $delimiter)) !== false && $row !== null) {
            if (!is_array($row) || empty(array_filter($row))) {
                continue;
            }

            $nama = trim($row[0] ?? '');
            if (!$nama) {
                continue;
            }

            $nik = trim($row[1] ?? '') ?: null;
            $alamat = trim($row[2] ?? '') ?: null;
            $jenisSarana = trim($row[3] ?? '') ?: 'Kios';
            $ukuran = (float)str_replace(',', '.', trim($row[4] ?? 0));
            $tarifMeter = (float)str_replace(',', '.', trim($row[5] ?? 0));
            $tarifBulanan = (float)str_replace(',', '.', trim($row[6] ?? 0));
            if ($tarifBulanan <= 0 && $ukuran > 0 && $tarifMeter > 0) {
                $tarifBulanan = $ukuran * $tarifMeter;
            }

            $jenisUsaha = trim($row[7] ?? '') ?: 'Sembako';

            $coords = trim($row[8] ?? '');
            $lat = null;
            $lng = null;
            if ($coords && str_contains($coords, ',')) {
                $parts = explode(',', $coords);
                $lat = (float)trim($parts[0] ?? 0) ?: null;
                $lng = (float)trim($parts[1] ?? 0) ?: null;
            }

            $alamatUsaha = trim($row[9] ?? '') ?: null;

            // Penentuan Pasar
            $pasarVal = trim($row[10] ?? '');
            $targetPasarId = null;
            if (is_numeric($pasarVal) && (int)$pasarVal > 0) {
                $targetPasarId = (int)$pasarVal;
            } elseif ($pasarVal) {
                $targetPasarId = $pasars[$pasarVal] ?? null;
            }

            if (!$targetPasarId) {
                $targetPasarId = $defaultPasarId;
            }

            // Jika bukan admin, kunci ke pasar yang ditugaskan
            if (!$this->isAdmin && $this->userPasarId) {
                $targetPasarId = $this->userPasarId;
            }

            $batch[] = [
                'nik' => $nik ?: ('NO-NIK-' . uniqid() . '-' . mt_rand(100, 999)),
                'nama' => $nama,
                'alamat' => $alamat,
                'jenis_sarana' => $jenisSarana,
                'ukuran' => $ukuran,
                'tarif_per_meter' => $tarifMeter,
                'tarif_bulanan' => $tarifBulanan,
                'jenis_usaha' => $jenisUsaha,
                'latitude' => $lat,
                'longitude' => $lng,
                'alamat_usaha' => $alamatUsaha,
                'pasar_id' => $targetPasarId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                Pedagang::upsert(
                    $batch,
                    ['nik'],
                    ['nama', 'alamat', 'jenis_sarana', 'ukuran', 'tarif_per_meter', 'tarif_bulanan', 'jenis_usaha', 'latitude', 'longitude', 'alamat_usaha', 'pasar_id', 'updated_at']
                );
                $totalProcessed += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            Pedagang::upsert(
                $batch,
                ['nik'],
                ['nama', 'alamat', 'jenis_sarana', 'ukuran', 'tarif_per_meter', 'tarif_bulanan', 'jenis_usaha', 'latitude', 'longitude', 'alamat_usaha', 'pasar_id', 'updated_at']
            );
            $totalProcessed += count($batch);
        }

        fclose($handle);

        // Hapus file temporary setelah selesai
        $disk->delete($this->filePath);

        // Refresh cache pedagang
        PedagangCacheHelper::refreshAll();

        Log::info("ImportPedagangJob: Berhasil mengimpor {$totalProcessed} data pedagang.");
    }
}

