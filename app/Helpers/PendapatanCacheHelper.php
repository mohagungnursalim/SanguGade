<?php

namespace App\Helpers;

use App\Models\Pasar;
use App\Models\PendapatanPasar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PendapatanCacheHelper
{
    protected static int $ttl = 60 * 60 * 24 * 7; // 1 minggu
    protected static string $baseKey = 'cache_pendapatan';

    /**
     * Ambil waktu update terakhir data pendapatan pasar (cached)
     */
    public static function getLastUpdated(): ?Carbon
    {
        $key = self::$baseKey . '_last_updated';
        return Cache::remember($key, self::$ttl, function () {
            $latest = PendapatanPasar::latest('updated_at')->value('updated_at');
            return $latest ? Carbon::parse($latest) : null;
        });
    }

    /**
     * Ambil total akumulasi pendapatan global (all-time)
     */
    public static function getTotalNominalAll(): float
    {
        $key = self::$baseKey . '_total_nominal_all';
        return (float) Cache::remember($key, self::$ttl, fn() => PendapatanPasar::sum('jumlah_retribusi'));
    }

    /**
     * Ambil total jumlah transaksi SSRD global (all-time)
     */
    public static function getTotalTransaksiAll(): int
    {
        $key = self::$baseKey . '_total_transaksi_all';
        return (int) Cache::remember($key, self::$ttl, fn() => PendapatanPasar::count());
    }

    /**
     * Ambil statistik ringkasan kartu berdasarkan filter (cached progresif)
     */
    public static function getFilteredSummary(
        string $timeframe = 'bulan',
        $pasarId = null,
        $tahun = null,
        $bulan = null,
        $startDate = null,
        $endDate = null
    ): array {
        $tahun = $tahun ?: (int) date('Y');
        $cacheKey = sprintf(
            '%s_summary_%s_%s_%s_%s_%s_%s',
            self::$baseKey,
            $timeframe,
            $pasarId ?: 'all',
            $tahun ?: 'all',
            $bulan ?: 'all',
            $startDate ?: 'none',
            $endDate ?: 'none'
        );

        self::trackCacheKey($cacheKey);

        return Cache::remember($cacheKey, self::$ttl, function () use ($timeframe, $pasarId, $tahun, $bulan, $startDate, $endDate) {
            $query = PendapatanPasar::query();

            if (!empty($pasarId)) {
                $query->where('pasar_id', $pasarId);
            }

            if ($timeframe === 'tahun') {
                // Semua tahun / tren tahunan
            } elseif ($timeframe === 'bulan') {
                $query->where('tahun_retribusi', $tahun);
            } elseif ($timeframe === 'hari') {
                $targetMonth = $bulan ?: (int) date('m');
                $query->whereYear('tanggal_penyetoran', $tahun)
                      ->whereMonth('tanggal_penyetoran', $targetMonth);
            } elseif ($timeframe === 'periode') {
                $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
                $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

                if ($start->gt($end)) {
                    $tmp = $start;
                    $start = $end->copy()->startOfDay();
                    $end = $tmp->copy()->endOfDay();
                }

                $query->whereBetween('tanggal_penyetoran', [$start->toDateString(), $end->toDateString()]);
            }

            $totalRetribusi = (float) $query->sum('jumlah_retribusi');
            $totalTransaksi = (int) $query->count();

            // Rekap per pasar pada filter aktif
            $pasarStatsQuery = (clone $query)->selectRaw('pasar_id, COUNT(*) as transaksi_count, SUM(jumlah_retribusi) as total_nominal')
                ->groupBy('pasar_id');
            $pasarStats = $pasarStatsQuery->get()->keyBy('pasar_id');

            return [
                'total_retribusi' => $totalRetribusi,
                'total_transaksi' => $totalTransaksi,
                'pasar_stats'     => $pasarStats,
            ];
        });
    }

    /**
     * Ambil rekapitulasi ringkasan per pasar (cached)
     */
    public static function getRingkasanPerPasar()
    {
        $key = self::$baseKey . '_ringkasan_per_pasar';
        return Cache::remember($key, self::$ttl, function () {
            return Pasar::select('id', 'nama_pasar', 'kecamatan', 'kelurahan', 'status_pasar')
                ->withCount('pendapatanPasars')
                ->withSum('pendapatanPasars', 'jumlah_retribusi')
                ->orderByDesc('pendapatan_pasars_sum_jumlah_retribusi')
                ->get();
        });
    }

    /**
     * Ambil daftar tahun unik yang tersedia
     */
    public static function getTahunList(): array
    {
        $key = self::$baseKey . '_tahun_list';
        return Cache::remember($key, self::$ttl, function () {
            $list = PendapatanPasar::selectRaw('DISTINCT tahun_retribusi AS th')
                ->orderByDesc('th')
                ->pluck('th')
                ->filter()
                ->values()
                ->all();

            return empty($list) ? [(int)date('Y')] : $list;
        });
    }

    /**
     * Ambil data time-series chart garis untuk Dashboard (progresif cached dengan dynamic key tracking)
     *
     * @param string $timeframe 'tahun' | 'bulan' | 'hari' | 'periode'
     * @param string|int|null $pasarId
     * @param int|null $tahun
     * @param int|null $bulan
     * @param string|null $startDate
     * @param string|null $endDate
     */
    public static function getChartData(
        string $timeframe = 'bulan',
        $pasarId = null,
        $tahun = null,
        $bulan = null,
        $startDate = null,
        $endDate = null
    ): array {
        $tahun = $tahun ?: (int) date('Y');
        $cacheKey = sprintf(
            '%s_chart_%s_%s_%s_%s_%s_%s',
            self::$baseKey,
            $timeframe,
            $pasarId ?: 'all',
            $tahun ?: 'all',
            $bulan ?: 'all',
            $startDate ?: 'none',
            $endDate ?: 'none'
        );

        self::trackCacheKey($cacheKey);

        return Cache::remember($cacheKey, self::$ttl, function () use ($timeframe, $pasarId, $tahun, $bulan, $startDate, $endDate) {
            $query = PendapatanPasar::query();

            if (!empty($pasarId)) {
                $query->where('pasar_id', $pasarId);
            }

            $labels = [];
            $data = [];

            $isSqlite = DB::connection()->getDriverName() === 'sqlite';

            if ($timeframe === 'tahun') {
                // Tampilkan data per tahun (5 tahun terakhir atau data yang ada)
                $query->selectRaw('tahun_retribusi AS period, SUM(jumlah_retribusi) as total')
                    ->groupBy('tahun_retribusi')
                    ->orderBy('tahun_retribusi');

                $results = $query->pluck('total', 'period')->all();
                
                $minYear = !empty($results) ? min(array_keys($results)) : ($tahun - 4);
                $maxYear = !empty($results) ? max(array_keys($results)) : $tahun;
                if ($maxYear - $minYear < 4) {
                    $minYear = $maxYear - 4;
                }

                for ($y = $minYear; $y <= $maxYear; $y++) {
                    $labels[] = (string) $y;
                    $data[] = (float) ($results[$y] ?? 0);
                }
            } elseif ($timeframe === 'bulan') {
                // Tampilkan 12 bulan pada tahun terpilih
                $monthExpr = $isSqlite ? "CAST(strftime('%m', tanggal_penyetoran) AS INTEGER)" : "MONTH(tanggal_penyetoran)";

                $query->where('tahun_retribusi', $tahun)
                    ->selectRaw("{$monthExpr} AS bln, SUM(jumlah_retribusi) AS total")
                    ->groupByRaw($monthExpr)
                    ->orderByRaw($monthExpr);

                $results = $query->pluck('total', 'bln')->all();
                $namaBulan = [
                    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                    5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                ];

                for ($m = 1; $m <= 12; $m++) {
                    $labels[] = $namaBulan[$m];
                    $data[] = (float) ($results[$m] ?? 0);
                }
            } elseif ($timeframe === 'hari') {
                // Tampilkan per hari dalam 1 bulan pada tahun terpilih
                $targetMonth = $bulan ?: (int) date('m');
                $daysInMonth = Carbon::create($tahun, $targetMonth, 1)->daysInMonth;
                $dayExpr = $isSqlite ? "CAST(strftime('%d', tanggal_penyetoran) AS INTEGER)" : "DAY(tanggal_penyetoran)";

                $query->whereYear('tanggal_penyetoran', $tahun)
                    ->whereMonth('tanggal_penyetoran', $targetMonth)
                    ->selectRaw("{$dayExpr} AS tgl, SUM(jumlah_retribusi) AS total")
                    ->groupByRaw($dayExpr)
                    ->orderByRaw($dayExpr);

                $results = $query->pluck('total', 'tgl')->all();

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $labels[] = 'Tgl ' . $d;
                    $data[] = (float) ($results[$d] ?? 0);
                }
            } elseif ($timeframe === 'periode') {
                // Rentang tanggal A ke B
                $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
                $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

                if ($start->gt($end)) {
                    $tmp = $start;
                    $start = $end->copy()->startOfDay();
                    $end = $tmp->copy()->endOfDay();
                }

                $dateExpr = $isSqlite ? "date(tanggal_penyetoran)" : "DATE(tanggal_penyetoran)";

                $query->whereBetween('tanggal_penyetoran', [$start->toDateString(), $end->toDateString()])
                    ->selectRaw("{$dateExpr} AS tgl, SUM(jumlah_retribusi) AS total")
                    ->groupByRaw($dateExpr)
                    ->orderByRaw($dateExpr);

                $results = $query->pluck('total', 'tgl')->all();

                $curr = $start->copy();
                while ($curr->lte($end)) {
                    $dStr = $curr->toDateString();
                    $labels[] = $curr->format('d/m');
                    $data[] = (float) ($results[$dStr] ?? 0);
                    $curr->addDay();
                }
            }

            $totalNominal = array_sum($data);

            return [
                'labels' => $labels,
                'data' => $data,
                'total' => $totalNominal,
                'max' => !empty($data) ? max($data) : 0,
                'avg' => count($data) > 0 ? ($totalNominal / count($data)) : 0,
            ];
        });
    }

    /**
     * Invalidate & refresh semua cache pendapatan
     */
    public static function refreshAll(): void
    {
        Cache::forget(self::$baseKey . '_last_updated');
        Cache::forget(self::$baseKey . '_total_nominal_all');
        Cache::forget(self::$baseKey . '_total_transaksi_all');
        Cache::forget(self::$baseKey . '_ringkasan_per_pasar');
        Cache::forget(self::$baseKey . '_tahun_list');

        // Hapus seluruh dynamic query chart & filter yang tercatat
        $trackerKey = self::$baseKey . '_tracked_keys';
        $trackedKeys = Cache::get($trackerKey, []);

        foreach ($trackedKeys as $k) {
            Cache::forget($k);
        }

        Cache::forget($trackerKey);

        // Pre-warm global dasar
        self::getTotalNominalAll();
        self::getTotalTransaksiAll();
        self::getRingkasanPerPasar();
        self::getTahunList();
    }

    /**
     * Catat key cache dinamis
     */
    public static function trackCacheKey(string $key): void
    {
        $trackerKey = self::$baseKey . '_tracked_keys';
        $tracked = Cache::get($trackerKey, []);

        if (!in_array($key, $tracked)) {
            $tracked[] = $key;
            Cache::put($trackerKey, $tracked, self::$ttl);
        }
    }
}
