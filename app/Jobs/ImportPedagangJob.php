<?php

namespace App\Jobs;

use App\Helpers\PedagangCacheHelper;
use App\Imports\PedagangImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportPedagangJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // Timeout 10 menit untuk data ribuan

    public function __construct(
        public string $filePath,   // Path relatif di storage disk 'local'
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

        try {
            Excel::import(
                new PedagangImport($this->userPasarId, $this->isAdmin),
                $fullPath
            );
        } catch (\Throwable $e) {
            Log::error('ImportPedagangJob: Import gagal — ' . $e->getMessage());
        } finally {
            $disk->delete($this->filePath);
            PedagangCacheHelper::refreshAll();
            Log::info("ImportPedagangJob: Selesai memproses file [{$this->filePath}].");
        }
    }
}
