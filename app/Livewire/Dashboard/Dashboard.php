<?php

namespace App\Livewire\Dashboard;

use App\Helpers\PasarsCacheHelper;
use App\Helpers\PendapatanCacheHelper;
use Livewire\Attributes\Url;
use Livewire\Component;

class Dashboard extends Component
{
    // Filter chart
    
    public string $timeframe = 'bulan'; // 'tahun', 'bulan', 'hari', 'periode'

    
    public ?string $pasarId = '';

    
    public ?int $selectedTahun = null;

    
    public ?int $selectedBulan = null;

    
    public ?string $startDate = '';

    
    public ?string $endDate = '';

    public function mount(): void
    {
        $this->selectedTahun = (int) date('Y');
        $this->selectedBulan = (int) date('n');
        $this->startDate = date('Y-m-d', strtotime('-30 days'));
        $this->endDate = date('Y-m-d');
    }

    public function updatedTimeframe(): void
    {
        $this->dispatch('chart-data-updated', chartData: $this->getChartData());
    }

    public function updatedPasarId(): void
    {
        $this->dispatch('chart-data-updated', chartData: $this->getChartData());
    }

    public function updatedSelectedTahun(): void
    {
        $this->dispatch('chart-data-updated', chartData: $this->getChartData());
    }

    public function updatedSelectedBulan(): void
    {
        $this->dispatch('chart-data-updated', chartData: $this->getChartData());
    }

    public function updatedStartDate(): void
    {
        $this->dispatch('chart-data-updated', chartData: $this->getChartData());
    }

    public function updatedEndDate(): void
    {
        $this->dispatch('chart-data-updated', chartData: $this->getChartData());
    }

    public function setTimeframe(string $mode): void
    {
        $this->timeframe = $mode;
        $this->dispatch('chart-data-updated', chartData: $this->getChartData());
    }

    public function pollData(): void
    {
        $this->dispatch('chart-data-updated', chartData: $this->getChartData());
    }

    public function getChartData(): array
    {
        return PendapatanCacheHelper::getChartData(
            timeframe: $this->timeframe,
            pasarId: $this->pasarId ?: null,
            tahun: $this->selectedTahun,
            bulan: $this->selectedBulan,
            startDate: $this->startDate,
            endDate: $this->endDate
        );
    }

    public function render()
    {
        // ─── AMBIL DATA PASAR & FILTER SUMMARY DARI CACHE ─────────────────────────
        $allPasars = PasarsCacheHelper::getAllPasar();
        $tahunList = PendapatanCacheHelper::getTahunList();

        // 1. Data Ringkasan Pendapatan Sesuai Filter Aktif
        $filteredSummary = PendapatanCacheHelper::getFilteredSummary(
            timeframe: $this->timeframe,
            pasarId: $this->pasarId ?: null,
            tahun: $this->selectedTahun,
            bulan: $this->selectedBulan,
            startDate: $this->startDate,
            endDate: $this->endDate
        );

        $totalPendapatan = $filteredSummary['total_retribusi'] ?? 0;
        $totalTransaksi = $filteredSummary['total_transaksi'] ?? 0;
        $pasarStats = $filteredSummary['pasar_stats'] ?? collect();

        // 2. Data Pasar & Sarana Prasarana Sesuai Filter Pasar Aktif
        $activePasarId = !empty($this->pasarId) ? (int)$this->pasarId : null;
        $pasarTotals = PasarsCacheHelper::getGlobalTotals($activePasarId);

        if ($activePasarId) {
            $totalPasar = 1;
            $selectedPasarObj = $allPasars->firstWhere('id', $activePasarId);
            $namaFilterPasar = $selectedPasarObj ? $selectedPasarObj->nama_pasar : 'Pasar Terpilih';
        } else {
            $totalPasar = PasarsCacheHelper::getTotalPasar();
            $namaFilterPasar = 'Semua Pasar';
        }

        // 3. Tabel Rekapitulasi Berdasarkan Filter Aktif
        // Map data seluruh pasar (atau pasar yang difilter) dengan transaksi & nominal pada periode/filter ini
        $ringkasanPerPasar = $allPasars->when($activePasarId, function ($collection) use ($activePasarId) {
            return $collection->where('id', $activePasarId);
        })->map(function ($p) use ($pasarStats) {
            $stat = $pasarStats->get($p->id);
            $p->pendapatan_pasars_count = $stat ? (int)$stat->transaksi_count : 0;
            $p->pendapatan_pasars_sum_jumlah_retribusi = $stat ? (float)$stat->total_nominal : 0.0;
            return $p;
        })->sortByDesc('pendapatan_pasars_sum_jumlah_retribusi')->values();

        $chartData = $this->getChartData();

        return view('livewire.dashboard.dashboard', [
            'pasars'            => $allPasars,
            'totalPasar'        => $totalPasar,
            'namaFilterPasar'   => $namaFilterPasar,
            'pasarTotals'       => $pasarTotals,
            'totalPendapatan'   => $totalPendapatan,
            'totalTransaksi'    => $totalTransaksi,
            'ringkasanPerPasar' => $ringkasanPerPasar,
            'tahunList'         => $tahunList,
            'chartData'         => $chartData,
        ])->layout('layouts.dashboard.main');
    }
}
