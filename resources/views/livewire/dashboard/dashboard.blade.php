<div class="space-y-6" wire:poll.10s="pollData">

    {{-- =====================================================================
         KOP INFORMASI DASHBOARD
    ====================================================================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2.5 bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 rounded-xl">
                    <i class="fa-solid fa-chart-line text-2xl"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-gray-800 dark:text-white">
                        Dashboard Monitoring Sangu Gade
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Sistem Informasi dan Monitoring Pengelolaan Pasar.
                    </p>
                </div>
            </div>
        </div>

        {{-- Badge Status Cache Global --}}
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Diperbarui tiap 10 detik
            </span>
            {{-- <a
                wire:navigate
                href="{{ route('dashboard.pendapatan-pasar') }}"
                class="px-3.5 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition">
                <i class="fa-solid fa-plus mr-1"></i> Kelola SSRD
            </a> --}}
        </div>
    </div>

    {{-- =====================================================================
         4 KARTU STATISTIK UTAMA (DIAMBIL DARI GLOBAL CACHE)
    ====================================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Pendapatan Retribusi --}}
        <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Total Retribusi SSRD</span>
                <span class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-vault"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-gray-900 dark:text-white">
                Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-receipt text-emerald-500"></i>
                <span>{{ number_format($totalTransaksi) }} total transaksi tersimpan</span>
            </p>
        </div>

        {{-- Total Pasar --}}
        <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Pasar Terkelola</span>
                <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-shop"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-gray-900 dark:text-white">
                {{ $totalPasar }} Pasar
            </p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-certificate text-blue-500"></i>
                <span>{{ $pasarTotals->total_sertifikat_ada ?? 0 }} bersertifikat ({{ $pasarTotals->total_sertifikat_tidak ?? 0 }} belum)</span>
            </p>
        </div>

        {{-- Total Pedagang --}}
        <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Jumlah Pedagang</span>
                <span class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-300 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-gray-900 dark:text-white">
                {{ number_format($pasarTotals->total_pedagang ?? 0) }} Orang
            </p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-door-open text-purple-500"></i>
                <span>{{ number_format(($pasarTotals->total_kios ?? 0) + ($pasarTotals->total_los ?? 0)) }} total Kios & Los</span>
            </p>
        </div>

        {{-- Luas Total Lahan --}}
        <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Total Luas Lahan</span>
                <span class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-ruler-combined"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-gray-900 dark:text-white">
                {{ number_format($pasarTotals->total_luas ?? 0) }} m²
            </p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-check-circle text-green-500"></i>
                <span>{{ number_format($pasarTotals->total_baik ?? 0) }} unit sarana kondisi baik</span>
            </p>
        </div>
    </div>

    {{-- =====================================================================
         PANEL GRAFIK PENDAPATAN PASAR (LINE CHART) DENGAN FILTER LENGKAP
    ====================================================================== --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6"
         x-data="chartManager(@js($chartData))"
         x-init="initChart()"
         @chart-data-updated.window="updateChart($event.detail.chartData)"
    >
        {{-- Header Grafik & Tab Timeframe (Tahun / Bulan / Hari / Periode A ke B) --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-5 border-b border-gray-100 dark:border-gray-700">
            <div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-emerald-600 text-lg"></i>
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">
                        Tren Pendapatan Retribusi Pasar
                    </h2>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Pantau pergerakan penerimaan retribusi pasar
                </p>
            </div>

            {{-- Tab Pilihan Timeframe: Pertahun, Perbulan, Perhari, Periode --}}
            <div class="flex items-center p-1 bg-gray-100 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    wire:click="setTimeframe('tahun')"
                    class="px-3 py-1.5 text-xs font-bold rounded-lg transition {{ $timeframe === 'tahun' ? 'bg-white dark:bg-gray-700 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                >
                    <i class="fa-regular fa-calendar mr-1"></i> Per Tahun
                </button>
                <button
                    type="button"
                    wire:click="setTimeframe('bulan')"
                    class="px-3 py-1.5 text-xs font-bold rounded-lg transition {{ $timeframe === 'bulan' ? 'bg-white dark:bg-gray-700 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                >
                    <i class="fa-regular fa-calendar-days mr-1"></i> Per Bulan
                </button>
                <button
                    type="button"
                    wire:click="setTimeframe('hari')"
                    class="px-3 py-1.5 text-xs font-bold rounded-lg transition {{ $timeframe === 'hari' ? 'bg-white dark:bg-gray-700 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                >
                    <i class="fa-solid fa-clock mr-1"></i> Per Hari
                </button>
                <button
                    type="button"
                    wire:click="setTimeframe('periode')"
                    class="px-3 py-1.5 text-xs font-bold rounded-lg transition {{ $timeframe === 'periode' ? 'bg-white dark:bg-gray-700 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                >
                    <i class="fa-solid fa-arrows-left-right mr-1"></i> Periode (A ke B)
                </button>
            </div>
        </div>

        {{-- BARIS FILTER INPUT: FILTER PASAR, TAHUN, BULAN, DAN PERIODE TANGGAL A KE B --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 my-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
            {{-- 1. Filter Pasar --}}
            <div>
                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fa-solid fa-shop text-emerald-600 mr-1"></i> Filter Pasar:
                </label>
                <select
                    wire:model.live="pasarId"
                    class="w-full text-xs py-2 px-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 font-medium"
                >
                    <option value="">Semua Pasar (Konsolidasi)</option>
                    @foreach ($pasars as $p)
                        <option value="{{ $p->id }}">{{ $p->nama_pasar }} ({{ $p->kecamatan }})</option>
                    @endforeach
                </select>
            </div>

            {{-- 2. Filter Tahun (muncul jika mode bulan / hari) --}}
            <div class="{{ $timeframe === 'periode' || $timeframe === 'tahun' ? 'opacity-50 pointer-events-none' : '' }}">
                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fa-regular fa-calendar mr-1 text-blue-600"></i> Pilih Tahun:
                </label>
                <select
                    wire:model.live="selectedTahun"
                    class="w-full text-xs py-2 px-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 font-medium"
                >
                    @foreach ($tahunList as $th)
                        <option value="{{ $th }}">{{ $th }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 3. Filter Bulan (muncul saat mode per hari) --}}
            <div class="{{ $timeframe !== 'hari' ? 'opacity-50 pointer-events-none' : '' }}">
                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fa-regular fa-calendar-days mr-1 text-purple-600"></i> Pilih Bulan (Mode Hari):
                </label>
                <select
                    wire:model.live="selectedBulan"
                    class="w-full text-xs py-2 px-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 font-medium"
                >
                    @foreach ([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ] as $num => $namaBln)
                        <option value="{{ $num }}">{{ $namaBln }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 4. Filter Periode Tanggal A ke B --}}
            <div class="{{ $timeframe !== 'periode' ? 'opacity-50 pointer-events-none' : '' }}">
                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fa-solid fa-arrows-left-right mr-1 text-amber-600"></i> Rentang Tanggal (A ke B):
                </label>
                <div class="flex items-center gap-1.5">
                    <input
                        type="date"
                        wire:model.live="startDate"
                        class="w-1/2 text-[11px] py-1.5 px-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                    />
                    <span class="text-xs text-gray-400 font-bold">-</span>
                    <input
                        type="date"
                        wire:model.live="endDate"
                        class="w-1/2 text-[11px] py-1.5 px-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                    />
                </div>
            </div>
        </div>

        {{-- Ringkasan Mini Chart --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4 px-2">
            <div class="flex items-center gap-4">
                <div>
                    <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider block">Total Pada Periode Ini:</span>
                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-400" x-text="'Rp ' + Number(chartData.total || 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="hidden sm:block border-l border-gray-200 dark:border-gray-700 pl-4">
                    <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider block">Tertinggi:</span>
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300" x-text="'Rp ' + Number(chartData.max || 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="hidden sm:block border-l border-gray-200 dark:border-gray-700 pl-4">
                    <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider block">Rata-rata:</span>
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300" x-text="'Rp ' + Math.round(chartData.avg || 0).toLocaleString('id-ID')"></span>
                </div>
            </div>

            <div wire:loading wire:target="timeframe, pasarId, selectedTahun, selectedBulan, startDate, endDate, setTimeframe" class="text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 font-bold">
                <i class="fas fa-spinner fa-spin"></i> Memperbarui data grafik...
            </div>
        </div>

        {{-- Canvas Chart.js --}}
        <div class="relative w-full h-80" wire:ignore>
            <canvas id="pendapatanLineChart"></canvas>
        </div>
    </div>

    {{-- =====================================================================
         TABEL REKAPITULASI PENDAPATAN PER PASAR (GLOBAL CACHED)
    ====================================================================== --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-ranking-star text-amber-500"></i>
                    Peringkat Akumulasi Retribusi Berdasarkan Pasar
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{--  --}}
                </p>
            </div>
            <a
                wire:navigate
                href="{{ route('dashboard.sarana-prasarana') }}"
                class="inline-flex items-center text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline"
            >
                Kelola Sarana Prasarana <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-900 uppercase text-[10px] tracking-wider font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-3 py-3 w-12 text-center">Rank</th>
                        <th class="px-3 py-3">Nama Pasar</th>
                        <th class="px-3 py-3">Kecamatan</th>
                        <th class="px-3 py-3 text-center">Status Operasional</th>
                        <th class="px-3 py-3 text-center">Jumlah Bukti SSRD</th>
                        <th class="px-3 py-3 text-right">Total Penerimaan</th>
                        <th class="px-3 py-3 text-center w-28">Status Retribusi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($ringkasanPerPasar as $idx => $rPasar)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                            <td class="px-3 py-3 text-center font-bold font-mono">
                                @if ($idx === 0)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-800 text-xs">🥇</span>
                                @elseif ($idx === 1)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-200 text-gray-800 text-xs">🥈</span>
                                @elseif ($idx === 2)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-800/20 text-amber-900 text-xs">🥉</span>
                                @else
                                    {{ $idx + 1 }}
                                @endif
                            </td>
                            <td class="px-3 py-3 font-bold text-gray-900 dark:text-white">
                                {{ $rPasar->nama_pasar }}
                            </td>
                            <td class="px-3 py-3 text-gray-500 dark:text-gray-400">
                                Kec. {{ $rPasar->kecamatan }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if (strtolower($rPasar->status_pasar) === 'mingguan')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                        Mingguan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        Harian
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    {{ $rPasar->pendapatan_pasars_count ?? 0 }} SSRD
                                </span>
                            </td>
                            <td class="px-3 py-3 text-right font-black text-emerald-600 dark:text-emerald-400 text-sm">
                                Rp {{ number_format($rPasar->pendapatan_pasars_sum_jumlah_retribusi ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if (($rPasar->pendapatan_pasars_sum_jumlah_retribusi ?? 0) > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                        Nihil
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada data rekapitulasi pasar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
/**
 * Alpine component untuk mengontrol Chart.js Line Chart
 */
function chartManager(initialData) {
    return {
        chartInstance: null,
        chartData: initialData,

        initChart() {
            const ctx = document.getElementById('pendapatanLineChart');
            if (!ctx) return;

            // Jika canvas sudah punya instance Chart.js sebelumnya, bersihkan agar tidak duplikasi
            const existing = Chart.getChart(ctx);
            if (existing) {
                existing.destroy();
            }

            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';
            const textColor = isDark ? '#9ca3af' : '#4b5563';

            const defaultLabels = (this.chartData && this.chartData.labels && this.chartData.labels.length) 
                ? this.chartData.labels 
                : ['-'];
            const defaultData = (this.chartData && this.chartData.data && this.chartData.data.length) 
                ? this.chartData.data 
                : [0];

            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: defaultLabels,
                    datasets: [{
                        label: 'Pendapatan Retribusi (Rp)',
                        data: defaultData,
                        borderColor: '#059669', // Emerald 600
                        backgroundColor: (context) => {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) return null;
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(5, 150, 105, 0.35)');
                            gradient.addColorStop(1, 'rgba(5, 150, 105, 0.00)');
                            return gradient;
                        },
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.38,
                        pointBackgroundColor: '#059669',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        spanGaps: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 500,
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: '#111827',
                            titleColor: '#ffffff',
                            bodyColor: '#10b981',
                            padding: 10,
                            boxPadding: 4,
                            callbacks: {
                                label: function(context) {
                                    let val = context.raw || 0;
                                    return 'Retribusi: Rp ' + Number(val).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: gridColor,
                            },
                            ticks: {
                                color: textColor,
                                font: { size: 10 }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor,
                            },
                            ticks: {
                                color: textColor,
                                font: { size: 10 },
                                callback: function(value) {
                                    if (value >= 1000000000) {
                                        return (value / 1000000000).toFixed(1) + ' M';
                                    }
                                    if (value >= 1000000) {
                                        return (value / 1000000).toFixed(1) + ' Jt';
                                    }
                                    if (value >= 1000) {
                                        return (value / 1000).toFixed(0) + ' Rb';
                                    }
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        },

        updateChart(newData) {
            if (!newData) return;
            this.chartData = newData;

            const ctx = document.getElementById('pendapatanLineChart');
            if (!this.chartInstance && ctx) {
                this.initChart();
                return;
            }

            if (!this.chartInstance) return;

            const labels = (newData.labels && newData.labels.length) ? newData.labels : ['-'];
            const data = (newData.data && newData.data.length) ? newData.data : [0];

            this.chartInstance.data.labels = labels;
            this.chartInstance.data.datasets[0].data = data;
            this.chartInstance.update();
        }
    };
}
</script>
@endpush
