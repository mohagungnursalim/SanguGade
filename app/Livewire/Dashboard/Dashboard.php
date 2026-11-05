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
        // ─── AMBIL DATA GLOBAL DARI CACHE ────────────────────────────────────
        $pasars = PasarsCacheHelper::getAllPasar();
        $totalPasar = PasarsCacheHelper::getTotalPasar();
        $pasarTotals = PasarsCacheHelper::getGlobalTotals();

        $totalPendapatan = PendapatanCacheHelper::getTotalNominalAll();
        $totalTransaksi = PendapatanCacheHelper::getTotalTransaksiAll();
        $ringkasanPerPasar = PendapatanCacheHelper::getRingkasanPerPasar();
        $tahunList = PendapatanCacheHelper::getTahunList();

        $chartData = $this->getChartData();

        return view('livewire.dashboard.dashboard', [
            'pasars'           => $pasars,
            'totalPasar'       => $totalPasar,
            'pasarTotals'      => $pasarTotals,
            'totalPendapatan'  => $totalPendapatan,
            'totalTransaksi'   => $totalTransaksi,
            'ringkasanPerPasar'=> $ringkasanPerPasar,
            'tahunList'        => $tahunList,
            'chartData'        => $chartData,
        ])->layout('layouts.dashboard.main');
    }
}
