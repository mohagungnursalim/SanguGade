<?php

namespace App\Livewire\Dashboard;

use App\Exports\PedagangTemplateExport;
use App\Helpers\PasarsCacheHelper;
use App\Helpers\PedagangCacheHelper;
use App\Jobs\ExportPedagangJob;
use App\Jobs\ImportPedagangJob;
use App\Models\Pedagang;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class DataPedagang extends Component
{
    use WithFileUploads;

    public ?int $selectedPasarId = null;
    public string $search = '';
    public string $perPage = '10'; // '10', '20', '50', '100', 'all'
    public array $selectedPedagangs = [];
    public bool $selectAll = false;

    // Form attributes
    public ?int $pedagangId = null;
    public string $nama = '';
    public string $nik = '';
    public string $alamat = '';
    public string $jenis_sarana = '';
    public $ukuran = '';
    public $tarif_per_meter = '';
    public $tarif_bulanan = '';
    public string $jenis_usaha = '';
    public $latitude = '';
    public $longitude = '';
    public string $alamat_usaha = '';
    public string $nama_tempat = '';
    public string $keterangan = '';
    public ?int $pasar_id = null;

    // Delete target
    public ?int $deleteId = null;
    public string $deleteName = '';

    // Import file
    public $excelFile = null;

    // Export tracking
    public ?string $lastExportFile = null;

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:1000',
            'nama_tempat' => 'nullable|string|max:255',
            'jenis_sarana' => 'required|string|max:100',
            'ukuran' => 'nullable|numeric|min:0',
            'tarif_per_meter' => 'nullable|numeric|min:0',
            'tarif_bulanan' => 'nullable|numeric|min:0',
            'jenis_usaha' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'alamat_usaha' => 'nullable|string|max:1000',
            'keterangan' => 'nullable|string|max:2000',
            'pasar_id' => 'required|exists:pasars,id',
        ];
    }

    protected $messages = [
        'nama.required' => 'Nama pedagang wajib diisi.',
        'nik.unique' => 'NIK sudah terdaftar.',
        'jenis_sarana.required' => 'Jenis sarana wajib diisi.',
        'jenis_usaha.required' => 'Jenis usaha wajib diisi.',
        'pasar_id.required' => 'Pilih pasar lokasi pedagang.',
        'pasar_id.exists' => 'Pasar tidak valid.',
    ];

    public function mount()
    {
        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && $user->pasar_id) {
            $this->selectedPasarId = $user->pasar_id;
            $this->pasar_id = $user->pasar_id;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPageSelection();
    }

    public function updatedSelectedPasarId(): void
    {
        $this->resetPageSelection();
    }

    public function updatedPerPage(): void
    {
        $this->resetPageSelection();
    }

    public function loadMore(): void
    {
        if ($this->perPage === 'all') {
            return;
        }

        $current = (int) $this->perPage;
        $this->perPage = (string) ($current + 10);
        $this->resetPageSelection();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedPedagangs = array_map('strval', $this->getCurrentPageIds());
        } else {
            $this->selectedPedagangs = [];
        }
    }

    public function updatedSelectedPedagangs(): void
    {
        $pageIds = $this->getCurrentPageIds();
        $selectedCount = count(array_intersect($pageIds, array_map('intval', $this->selectedPedagangs)));
        $this->selectAll = count($pageIds) > 0 && $selectedCount === count($pageIds);
    }

    private function resetPageSelection(): void
    {
        $this->selectedPedagangs = [];
        $this->selectAll = false;
    }

    private function getFilteredPedagangs(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        $targetPasarId = (!$user->hasRole('admin') && $user->pasar_id)
            ? $user->pasar_id
            : $this->selectedPasarId;

        $allPedagangs = PedagangCacheHelper::getAll($targetPasarId);

        if (!$this->search) {
            return $allPedagangs;
        }

        $s = strtolower($this->search);

        return $allPedagangs->filter(function ($item) use ($s) {
            return str_contains(strtolower($item->nama), $s)
                || str_contains(strtolower($item->nik ?? ''), $s)
                || str_contains(strtolower($item->nama_tempat ?? ''), $s)
                || str_contains(strtolower($item->jenis_usaha ?? ''), $s)
                || str_contains(strtolower($item->jenis_sarana ?? ''), $s)
                || str_contains(strtolower($item->alamat_usaha ?? ''), $s)
                || str_contains(strtolower($item->alamat ?? ''), $s)
                || str_contains(strtolower($item->keterangan ?? ''), $s);
        });
    }

    private function getCurrentPageIds(): array
    {
        $filtered = $this->getFilteredPedagangs();

        if ($this->perPage === 'all') {
            return $filtered->pluck('id')->all();
        }

        $limit = (int) $this->perPage;
        return $filtered->take($limit > 0 ? $limit : 10)->pluck('id')->all();
    }

    public function bulkDelete(): void
    {
        $user = auth()->user();
        abort_if(!$user->can('delete'), 403, 'Tidak memiliki izin hapus.');

        if (empty($this->selectedPedagangs)) {
            $this->dispatch('notify', type: 'error', message: 'Pilih pedagang yang ingin dihapus terlebih dahulu.');
            return;
        }

        $query = Pedagang::whereIn('id', $this->selectedPedagangs);

        // Operator hanya boleh menghapus pedagang di pasarnya sendiri
        if (!$user->hasRole('admin') && $user->pasar_id) {
            $query->where('pasar_id', $user->pasar_id);
        }

        $count = $query->count();
        $query->delete();

        PedagangCacheHelper::refreshAll();

        $this->resetPageSelection();
        $this->dispatch('bulk-deleted');
        $this->dispatch('notify', type: 'success', message: "Berhasil menghapus {$count} data pedagang!");
    }

    public function calculateTarifBulanan(): void
    {
        $uk = (float)($this->ukuran ?: 0);
        $tpm = (float)($this->tarif_per_meter ?: 0);
        if ($uk > 0 && $tpm > 0) {
            $this->tarif_bulanan = $uk * $tpm;
        }
    }

    public function save(): void
    {
        $user = auth()->user();
        abort_if(!$user->can('create') && !$user->can('update'), 403, 'Akses tidak diizinkan.');

        if ($this->pedagangId) {
            abort_if(!$user->can('update'), 403, 'Tidak memiliki izin update.');
            if (!$user->hasRole('admin') && $user->pasar_id) {
                $existing = Pedagang::findOrFail($this->pedagangId);
                abort_if($user->pasar_id !== $existing->pasar_id, 403, 'Hanya dapat mengubah pedagang di pasar Anda.');
                $this->pasar_id = $user->pasar_id;
            }
        } else {
            abort_if(!$user->can('create'), 403, 'Tidak memiliki izin create.');
            if (!$user->hasRole('admin') && $user->pasar_id) {
                $this->pasar_id = $user->pasar_id;
            }
        }

        $this->calculateTarifBulanan();
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'nik' => $this->nik ?: null,
            'alamat' => $this->alamat ?: null,
            'nama_tempat' => $this->nama_tempat ?: null,
            'jenis_sarana' => $this->jenis_sarana,
            'ukuran' => $this->ukuran !== '' ? (float)$this->ukuran : 0,
            'tarif_per_meter' => $this->tarif_per_meter !== '' ? (float)$this->tarif_per_meter : 0,
            'tarif_bulanan' => $this->tarif_bulanan !== '' ? (float)$this->tarif_bulanan : 0,
            'jenis_usaha' => $this->jenis_usaha,
            'latitude' => $this->latitude !== '' ? (float)$this->latitude : null,
            'longitude' => $this->longitude !== '' ? (float)$this->longitude : null,
            'alamat_usaha' => $this->alamat_usaha ?: null,
            'keterangan' => $this->keterangan ?: null,
            'pasar_id' => $this->pasar_id,
        ];

        Pedagang::updateOrCreate(['id' => $this->pedagangId], $data);
        PedagangCacheHelper::refreshAll();

        $this->resetForm();
        $this->dispatch('pedagang-saved');
        $this->dispatch('notify', type: 'success', message: 'Data pedagang berhasil disimpan!');
    }

    public function delete(): void
    {
        $user = auth()->user();
        abort_if(!$user->can('delete'), 403, 'Tidak memiliki izin hapus.');

        if ($this->deleteId) {
            $pedagang = Pedagang::findOrFail($this->deleteId);
            if (!$user->hasRole('admin') && $user->pasar_id) {
                abort_if($user->pasar_id !== $pedagang->pasar_id, 403, 'Hanya dapat menghapus pedagang di pasar Anda.');
            }
            $pedagang->delete();
            PedagangCacheHelper::refreshAll();

            $this->dispatch('pedagang-deleted');
            $this->dispatch('notify', type: 'success', message: 'Data pedagang berhasil dihapus!');
        }

        $this->reset(['deleteId', 'deleteName']);
    }

    public function importExcel(): void
    {
        $user = auth()->user();
        abort_if(!$user->can('create') && !$user->can('import'), 403, 'Tidak memiliki izin import.');

        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        // Simpan file sementara di storage lokal
        $path = $this->excelFile->store('temp_imports', 'local');

        $userPasarId = $user->pasar_id;
        $isAdmin = $user->hasRole('admin');

        // Dispatch background Queue Job agar aman untuk data ribuan
        ImportPedagangJob::dispatch($path, $userPasarId, $isAdmin);

        $this->reset('excelFile');
        $this->dispatch('excel-imported');
        $this->dispatch('notify', type: 'success', message: 'File Excel berhasil diunggah dan sedang diproses di background Queue!');
    }

    public function downloadTemplateExcel()
    {
        return Excel::download(new PedagangTemplateExport(), 'template_import_pedagang.xlsx');
    }

    public function exportExcel(): void
    {
        $user = auth()->user();
        abort_if(!$user->can('read') && !$user->can('export'), 403, 'Tidak memiliki izin export.');

        $exportPasarId = (!$user->hasRole('admin') && $user->pasar_id)
            ? $user->pasar_id
            : $this->selectedPasarId;

        $fileName = 'pedagang_export_' . date('Ymd_His') . '.xlsx';

        ExportPedagangJob::dispatch($exportPasarId, $fileName);

        $this->lastExportFile = $fileName;
        $this->dispatch('export-queued');
        $this->dispatch('notify', type: 'success', message: 'Export Excel sedang diproses di background Queue! File dapat diunduh.');
    }

    public function downloadExport(string $file)
    {
        $path = storage_path('app/public/exports/' . basename($file));
        if (file_exists($path)) {
            return response()->download($path);
        }

        $this->dispatch('notify', type: 'error', message: 'File export belum selesai diproses atau tidak ditemukan.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'pedagangId',
            'nama',
            'nik',
            'alamat',
            'nama_tempat',
            'jenis_sarana',
            'ukuran',
            'tarif_per_meter',
            'tarif_bulanan',
            'jenis_usaha',
            'latitude',
            'longitude',
            'alamat_usaha',
            'keterangan',
        ]);

        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && $user->pasar_id) {
            $this->pasar_id = $user->pasar_id;
        } else {
            $this->pasar_id = null;
        }

        $this->resetValidation();
    }

    public function render()
    {
        $filtered = $this->getFilteredPedagangs();
        $totalFiltered = $filtered->count();

        if ($this->perPage === 'all') {
            $pedagangs = $filtered;
            $hasMorePages = false;
        } else {
            $limit = (int) $this->perPage;
            $limit = $limit > 0 ? $limit : 10;
            $pedagangs = $filtered->take($limit);
            $hasMorePages = $totalFiltered > $limit;
        }

        return view('livewire.dashboard.data-pedagang', [
            'pedagangs' => $pedagangs,
            'totalFiltered' => $totalFiltered,
            'hasMorePages' => $hasMorePages,
            'pasars' => PasarsCacheHelper::getAllPasar(),
        ])->layout('layouts.dashboard.main');
    }
}
