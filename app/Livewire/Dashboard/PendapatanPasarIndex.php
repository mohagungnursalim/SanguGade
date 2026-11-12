<?php

namespace App\Livewire\Dashboard;

use App\Helpers\PasarsCacheHelper;
use App\Helpers\PendapatanCacheHelper;
use App\Helpers\TerbilangHelper;
use App\Models\Pasar;
use App\Models\PendapatanPasar;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PendapatanPasarIndex extends Component
{
    use WithPagination;

    // ─── Filter & Search ──────────────────────────────────────────────────
    
    public string $search = '';

    
    public ?string $filterPasar = null;

    
    public ?string $filterTahun = null;

    
    public ?string $filterStartDate = null;

    
    public ?string $filterEndDate = null;

    
    public ?string $filterJenis = null;

    public int $perPage = 10;

    // ─── State Delete ─────────────────────────────────────────────────────
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;
    public string $deleteInfo = '';

    // ─── State Form Create ────────────────────────────────────────────────
    public ?int $pasar_id = null;
    public ?int $tahun_ssrd = null;
    public string $no_rekening_bank = '2501300007827';
    public string $masa_retribusi = '';
    public ?int $tahun_retribusi = null;
    public string $nomor_urut_ssrd = '';
    public string $jenis_retribusi = 'SKRD';
    public string $nomor_ayat = '01.02.2002';
    public $jumlah_retribusi = 0;
    public string $terbilang = '';
    public string $tanggal_penyetoran = '';
    public string $nama_penyetor = '';
    public string $petugas_bank = '';
    public string $keterangan = '';

    // ─── State Form Edit ──────────────────────────────────────────────────
    public ?int $editId = null;
    public ?int $edit_pasar_id = null;
    public ?int $edit_tahun_ssrd = null;
    public string $edit_no_rekening_bank = '2501300007827';
    public string $edit_masa_retribusi = '';
    public ?int $edit_tahun_retribusi = null;
    public string $edit_nomor_urut_ssrd = '';
    public string $edit_jenis_retribusi = 'SKRD';
    public string $edit_nomor_ayat = '01.02.2002';
    public $edit_jumlah_retribusi = 0;
    public string $edit_terbilang = '';
    public string $edit_tanggal_penyetoran = '';
    public string $edit_nama_penyetor = '';
    public string $edit_petugas_bank = '';
    public string $edit_keterangan = '';

    // ─── State Modal Detail Pasar ─────────────────────────────────────────
    public bool $showDetailModal = false;
    public ?int $detailPasarId = null;

    public function mount(): void
    {
        $currentYear = (int) date('Y');
        $this->tahun_ssrd = $currentYear;
        $this->tahun_retribusi = $currentYear;
        $this->tanggal_penyetoran = date('Y-m-d');
        $this->terbilang = TerbilangHelper::make(0);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPasar(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTahun(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEndDate(): void
    {
        $this->resetPage();
    }

    public function updatingFilterJenis(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'filterPasar',
            'filterTahun',
            'filterStartDate',
            'filterEndDate',
            'filterJenis'
        ]);
        $this->resetPage();
    }

    // ─── Validation Rules ─────────────────────────────────────────────────
    protected function createRules(): array
    {
        return [
            'pasar_id'           => 'required|exists:pasars,id',
            'tahun_ssrd'         => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 5),
            'no_rekening_bank'   => 'nullable|string|max:50',
            'masa_retribusi'     => 'nullable|string|max:100',
            'tahun_retribusi'    => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 5),
            'nomor_urut_ssrd'    => 'nullable|string|max:50',
            'jenis_retribusi'    => 'required|string|max:50',
            'nomor_ayat'         => 'nullable|string|max:50',
            'jumlah_retribusi'   => 'required|numeric|min:0',
            'tanggal_penyetoran' => 'required|date',
            'nama_penyetor'      => 'required|string|max:150',
            'petugas_bank'       => 'nullable|string|max:150',
            'keterangan'         => 'required|string|max:500',
        ];
    }

    protected function editRules(): array
    {
        return [
            'editId'                 => 'required|exists:pendapatan_pasars,id',
            'edit_pasar_id'          => 'required|exists:pasars,id',
            'edit_tahun_ssrd'        => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 5),
            'edit_no_rekening_bank'  => 'nullable|string|max:50',
            'edit_masa_retribusi'    => 'nullable|string|max:100',
            'edit_tahun_retribusi'   => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 5),
            'edit_nomor_urut_ssrd'   => 'nullable|string|max:50',
            'edit_jenis_retribusi'   => 'required|string|max:50',
            'edit_nomor_ayat'        => 'nullable|string|max:50',
            'edit_jumlah_retribusi'  => 'required|numeric|min:0',
            'edit_tanggal_penyetoran'=> 'required|date',
            'edit_nama_penyetor'     => 'required|string|max:150',
            'edit_petugas_bank'      => 'nullable|string|max:150',
            'edit_keterangan'        => 'required|string|max:500',
        ];
    }

    // ─── CRUD Actions ─────────────────────────────────────────────────────
    public function store(): void
    {
        $this->validate($this->createRules());

        DB::transaction(function () {
            $terbilangText = TerbilangHelper::make($this->jumlah_retribusi);

            PendapatanPasar::create([
                'pasar_id'           => $this->pasar_id,
                'tahun_ssrd'         => $this->tahun_ssrd,
                'no_rekening_bank'   => $this->no_rekening_bank,
                'masa_retribusi'     => $this->masa_retribusi,
                'tahun_retribusi'    => $this->tahun_retribusi,
                'nomor_urut_ssrd'    => $this->nomor_urut_ssrd,
                'jenis_retribusi'    => $this->jenis_retribusi,
                'nomor_ayat'         => $this->nomor_ayat,
                'jumlah_retribusi'   => $this->jumlah_retribusi,
                'terbilang'          => $terbilangText,
                'tanggal_penyetoran' => $this->tanggal_penyetoran,
                'nama_penyetor'      => $this->nama_penyetor,
                'petugas_bank'       => $this->petugas_bank,
                'keterangan'         => $this->keterangan,
            ]);
        });

        $this->resetCreateForm();
        PendapatanCacheHelper::refreshAll();
        $this->dispatch('ssrd-saved');
        $this->dispatch('notify', type: 'success', message: 'Data SSRD Pendapatan Pasar berhasil disimpan!');
    }

    public function update(): void
    {
        $this->validate($this->editRules());

        DB::transaction(function () {
            $item = PendapatanPasar::findOrFail($this->editId);

            $terbilangText = TerbilangHelper::make($this->edit_jumlah_retribusi);

            $item->update([
                'pasar_id'           => $this->edit_pasar_id,
                'tahun_ssrd'         => $this->edit_tahun_ssrd,
                'no_rekening_bank'   => $this->edit_no_rekening_bank,
                'masa_retribusi'     => $this->edit_masa_retribusi,
                'tahun_retribusi'    => $this->edit_tahun_retribusi,
                'nomor_urut_ssrd'    => $this->edit_nomor_urut_ssrd,
                'jenis_retribusi'    => $this->edit_jenis_retribusi,
                'nomor_ayat'         => $this->edit_nomor_ayat,
                'jumlah_retribusi'   => $this->edit_jumlah_retribusi,
                'terbilang'          => $terbilangText,
                'tanggal_penyetoran' => $this->edit_tanggal_penyetoran,
                'nama_penyetor'      => $this->edit_nama_penyetor,
                'petugas_bank'       => $this->edit_petugas_bank,
                'keterangan'         => $this->edit_keterangan,
            ]);
        });

        $this->resetEditForm();
        PendapatanCacheHelper::refreshAll();
        $this->dispatch('ssrd-updated');
        $this->dispatch('notify', type: 'success', message: 'Data SSRD Pendapatan Pasar berhasil diperbarui!');
    }

    public function confirmDelete(int $id): void
    {
        $item = PendapatanPasar::with('pasar')->findOrFail($id);
        $this->deleteId = $item->id;
        $this->deleteInfo = 'SSRD No: ' . ($item->nomor_urut_ssrd ?: '-') . ' (' . ($item->pasar->nama_pasar ?? '-') . ' - Rp ' . number_format($item->jumlah_retribusi, 0, ',', '.') . ')';
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            DB::transaction(function () {
                $item = PendapatanPasar::findOrFail($this->deleteId);
                $item->delete();
            });

            $this->reset(['deleteId', 'deleteInfo', 'showDeleteModal']);
            PendapatanCacheHelper::refreshAll();
            $this->dispatch('notify', type: 'success', message: 'Data SSRD berhasil dihapus!');
        }
    }

    // ─── Detail Modal Pasar ───────────────────────────────────────────────
    public function viewPasarDetail(int $pasarId): void
    {
        $this->detailPasarId = $pasarId;
        $this->showDetailModal = true;
    }

    public function closePasarDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailPasarId = null;
    }

    // ─── Reset Forms ──────────────────────────────────────────────────────
    private function resetCreateForm(): void
    {
        $currentYear = (int) date('Y');
        $this->reset([
            'pasar_id', 'no_rekening_bank', 'masa_retribusi',
            'nomor_urut_ssrd', 'nomor_ayat', 'jumlah_retribusi',
            'nama_penyetor', 'petugas_bank', 'keterangan'
        ]);
        $this->tahun_ssrd = $currentYear;
        $this->tahun_retribusi = $currentYear;
        $this->jenis_retribusi = 'SKRD';
        $this->tanggal_penyetoran = date('Y-m-d');
        $this->terbilang = TerbilangHelper::make(0);
        $this->resetValidation();
    }

    private function resetEditForm(): void
    {
        $this->reset([
            'editId', 'edit_pasar_id', 'edit_tahun_ssrd', 'edit_no_rekening_bank',
            'edit_masa_retribusi', 'edit_tahun_retribusi', 'edit_nomor_urut_ssrd',
            'edit_nomor_ayat', 'edit_jumlah_retribusi', 'edit_terbilang',
            'edit_tanggal_penyetoran', 'edit_nama_penyetor', 'edit_petugas_bank', 'edit_keterangan'
        ]);
        $this->edit_jenis_retribusi = 'SKRD';
        $this->resetValidation();
    }

    public function render()
    {
        // Query List Pendapatan SSRD
        $query = PendapatanPasar::with('pasar')
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('nomor_urut_ssrd', 'like', $term)
                        ->orWhere('no_rekening_bank', 'like', $term)
                        ->orWhere('nama_penyetor', 'like', $term)
                        ->orWhere('petugas_bank', 'like', $term)
                        ->orWhere('nomor_ayat', 'like', $term)
                        ->orWhereHas('pasar', fn ($p) => $p->where('nama_pasar', 'like', $term));
                });
            })
            ->when($this->filterPasar, fn ($q) => $q->where('pasar_id', $this->filterPasar))
            ->when($this->filterTahun, fn ($q) => $q->where(function ($sub) {
                $sub->where('tahun_ssrd', $this->filterTahun)
                    ->orWhere('tahun_retribusi', $this->filterTahun);
            }))
            ->when($this->filterJenis, fn ($q) => $q->where('jenis_retribusi', $this->filterJenis))
            ->when($this->filterStartDate, fn ($q) => $q->whereDate('tanggal_penyetoran', '>=', $this->filterStartDate))
            ->when($this->filterEndDate, fn ($q) => $q->whereDate('tanggal_penyetoran', '<=', $this->filterEndDate))
            ->latest('tanggal_penyetoran')
            ->latest('id');

        $ssrdList = $query->paginate($this->perPage);

        // Agregasi Ringkasan Keseluruhan (filtered & total)
        $aggregateFiltered = (clone $query)->getQuery();
        $totalNominalFiltered = (float) $aggregateFiltered->sum('jumlah_retribusi');
        $totalTransaksiFiltered = (int) $aggregateFiltered->count();

        // Total All-Time Global dari Cache
        $totalNominalAll = PendapatanCacheHelper::getTotalNominalAll();
        $totalTransaksiAll = PendapatanCacheHelper::getTotalTransaksiAll();

        // Data List Pasars untuk dropdown dari Cache Global Pasar
        $pasars = PasarsCacheHelper::getAllPasar();

        // Dapatkan Nama Pasar yang difilter (jika ada)
        $namaPasarTerpilih = null;
        if ($this->filterPasar) {
            $pasarTerpilih = $pasars->firstWhere('id', $this->filterPasar);
            if ($pasarTerpilih) {
                $namaPasarTerpilih = $pasarTerpilih->nama_pasar;
            }
        }

        // Data Ringkasan Per Pasar dari Cache Global Pendapatan
        $ringkasanPerPasar = PendapatanCacheHelper::getRingkasanPerPasar();

        // Detail Pasar jika dipilih modal detail
        $detailPasar = null;
        $detailRiwayat = collect();
        if ($this->detailPasarId) {
            $detailPasar = Pasar::withSum('pendapatanPasars', 'jumlah_retribusi')
                ->withCount('pendapatanPasars')
                ->find($this->detailPasarId);

            if ($detailPasar) {
                $detailRiwayat = PendapatanPasar::where('pasar_id', $this->detailPasarId)
                    ->latest('tanggal_penyetoran')
                    ->latest('id')
                    ->take(50)
                    ->get();
            }
        }

        // List Tahun dari Cache
        $tahunList = PendapatanCacheHelper::getTahunList();

        return view('livewire.dashboard.pendapatan-pasar-index', [
            'ssrdList'              => $ssrdList,
            'pasars'                => $pasars,
            'namaPasarTerpilih'     => $namaPasarTerpilih,
            'totalNominalFiltered'  => $totalNominalFiltered,
            'totalTransaksiFiltered'=> $totalTransaksiFiltered,
            'totalNominalAll'       => $totalNominalAll,
            'totalTransaksiAll'     => $totalTransaksiAll,
            'ringkasanPerPasar'     => $ringkasanPerPasar,
            'detailPasar'           => $detailPasar,
            'detailRiwayat'         => $detailRiwayat,
            'tahunList'             => $tahunList,
        ])->layout('layouts.dashboard.main');
    }
}

