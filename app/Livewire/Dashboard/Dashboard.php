<?php

namespace App\Livewire\Dashboard;

use App\Helpers\PasarsCacheHelper;
use App\Helpers\PedagangCacheHelper;
use App\Helpers\PendapatanCacheHelper;
use App\Models\Pedagang;
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
        $this->dispatchChartsUpdated();
    }

    public function updatedPasarId(): void
    {
        $this->dispatchChartsUpdated();
    }

    public function updatedSelectedTahun(): void
    {
        $this->dispatchChartsUpdated();
    }

    public function updatedSelectedBulan(): void
    {
        $this->dispatchChartsUpdated();
    }

    public function updatedStartDate(): void
    {
        $this->dispatchChartsUpdated();
    }

    public function updatedEndDate(): void
    {
        $this->dispatchChartsUpdated();
    }

    public function setTimeframe(string $mode): void
    {
        $this->timeframe = $mode;
        $this->dispatchChartsUpdated();
    }

    public function pollData(): void
    {
        $this->dispatchChartsUpdated();
    }

    protected function dispatchChartsUpdated(): void
    {
        $activePasarId = !empty($this->pasarId) ? (int)$this->pasarId : null;
        $allCharts = $this->getAllChartsData($activePasarId);

        $this->dispatch('chart-data-updated', chartData: $allCharts['pendapatan']);
        $this->dispatch('extra-charts-updated', charts: $allCharts);
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

    public function getAllChartsData(?int $activePasarId = null): array
    {
        $allPasars = PasarsCacheHelper::getAllPasar();
        $filteredSummary = PendapatanCacheHelper::getFilteredSummary(
            timeframe: $this->timeframe,
            pasarId: $this->pasarId ?: null,
            tahun: $this->selectedTahun,
            bulan: $this->selectedBulan,
            startDate: $this->startDate,
            endDate: $this->endDate
        );
        $pasarStats = $filteredSummary['pasar_stats'] ?? collect();

        // 1. Ranking PAD Pasar (Bar Chart)
        $rankingList = $allPasars->when($activePasarId, function ($collection) use ($activePasarId) {
            return $collection->where('id', $activePasarId);
        })->map(function ($p) use ($pasarStats) {
            $stat = $pasarStats->get($p->id);
            return [
                'nama' => $p->nama_pasar,
                'total' => $stat ? (float)$stat->total_nominal : 0.0,
            ];
        })->sortByDesc('total')->values();

        $rankingLabels = $rankingList->pluck('nama')->toArray();
        $rankingData = $rankingList->pluck('total')->toArray();

        // 2. Jenis Usaha Pedagang (Pie / Donut Chart)
        $jenisUsahaData = PedagangCacheHelper::getJenisUsahaStats($activePasarId);

        // 3. Proporsi Sarana Berdagang (Donut Chart)
        $jenisSaranaData = PedagangCacheHelper::getJenisSaranaStats($activePasarId);

        // 4. Kondisi Fisik Sarana & Bangunan Pasar (Pie Chart)
        $kondisiSaranaData = PasarsCacheHelper::getKondisiSaranaStats($activePasarId);

        // 5. Status Pedagang: Aktif vs Keluar (Soft-deleted <= 30 hari)
        $statusPedagangData = PedagangCacheHelper::getStatusPedagangStats($activePasarId);

        return [
            'pendapatan'     => $this->getChartData(),
            'rankingPasar'   => ['labels' => $rankingLabels, 'data' => $rankingData],
            'jenisUsaha'     => $jenisUsahaData,
            'jenisSarana'    => $jenisSaranaData,
            'kondisiSarana'  => $kondisiSaranaData,
            'statusPedagang' => $statusPedagangData,
        ];
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
        $totalPedagang = Pedagang::query()
            ->when($activePasarId, fn ($query) => $query->where('pasar_id', $activePasarId))
            ->count();

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
        $allChartsData = $this->getAllChartsData($activePasarId);

        // Ambil timestamp update terakhir dari database (pendapatan atau pasar)
        $latestPendapatanUpdate = PendapatanCacheHelper::getLastUpdated();
        $latestPasarUpdate = PasarsCacheHelper::getLastUpdated();

        $lastUpdated = null;
        if ($latestPendapatanUpdate && $latestPasarUpdate) {
            $lastUpdated = $latestPendapatanUpdate->gt($latestPasarUpdate) ? $latestPendapatanUpdate : $latestPasarUpdate;
        } else {
            $lastUpdated = $latestPendapatanUpdate ?: ($latestPasarUpdate ?: now());
        }

        return view('livewire.dashboard.dashboard', [
            'pasars'            => $allPasars,
            'totalPasar'        => $totalPasar,
            'namaFilterPasar'   => $namaFilterPasar,
            'pasarTotals'       => $pasarTotals,
            'totalPedagang'     => $totalPedagang,
            'totalPendapatan'   => $totalPendapatan,
            'totalTransaksi'    => $totalTransaksi,
            'ringkasanPerPasar' => $ringkasanPerPasar,
            'tahunList'         => $tahunList,
            'chartData'         => $chartData,
            'allChartsData'     => $allChartsData,
            'lastUpdated'       => $lastUpdated,
        ])->layout('layouts.dashboard.main');
    }
}
