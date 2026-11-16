<?php

namespace App\Helpers;

use App\Models\Pasar;
use App\Models\PendapatanPasar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PendapatanCacheHelper
{
    protected static int $ttl = 60 * 60 * 24 * 7; // 1 minggu
    protected static string $baseKey = 'cache_pendapatan';

    // ── Versioned Key ────────────────────────────────────────────────────────

    /**
     * Baca versi cache saat ini. Default ke 1 jika belum pernah diset.
     */
    protected static function version(): int
    {
        return (int) Cache::get(self::$baseKey . '_version', 1);
    }

    /**
     * Bentuk cache key yang sudah mengandung versi aktif.
     * Ketika versi naik, semua derived key otomatis menjadi stale
     * tanpa perlu menghapus satu per satu (invalidasi O(1)).
     */
    protected static function vKey(string $suffix): string
    {
        return self::$baseKey . ':v' . self::version() . ':' . $suffix;
    }

    protected static function scopeSuffix(?int $userId, ?int $pasarId): string
    {
        return 'user_' . ($userId ?? 'all') . '_pasar_' . ($pasarId ?? 'all');
    }

    protected static function applyScope(Builder $query, ?int $userId, ?int $pasarId): void
    {
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if ($pasarId !== null) {
            $query->where('pasar_id', $pasarId);
        }
    }

    /**
     * Invalidasi seluruh cache pendapatan dengan menaikkan nomor versi.
     * Semua key lama menjadi unreachable secara instan.
     * Cache::add memastikan key seed = 1 sebelum increment,
     * sehingga versi selalu meningkat dari nilai yang sudah tersimpan.
     */
    public static function invalidate(): void
    {
        Cache::add(self::$baseKey . '_version', 1, self::$ttl);
        Cache::increment(self::$baseKey . '_version');
    }

    // ── Public Methods ───────────────────────────────────────────────────────

    /**
     * Ambil waktu update terakhir data pendapatan pasar (cached)
     */
    public static function getLastUpdated(): ?Carbon
    {
        return Cache::remember(self::vKey('last_updated'), self::$ttl, function () {
            $latest = PendapatanPasar::latest('updated_at')->value('updated_at');
            return $latest ? Carbon::parse($latest) : null;
        });
    }

    /**
     * Ambil total akumulasi pendapatan global (all-time)
     */
    public static function getTotalNominalAll(?int $userId = null, ?int $pasarId = null): float
    {
        return self::getAllTimeTotals($userId, $pasarId)['total_nominal'];
    }

    /**
     * Ambil total jumlah transaksi SSRD global (all-time)
     */
    public static function getTotalTransaksiAll(?int $userId = null, ?int $pasarId = null): int
    {
        return self::getAllTimeTotals($userId, $pasarId)['total_transaksi'];
    }

    /**
     * Ambil total nominal dan transaksi dalam satu query cached.
     *
     * @return array{total_nominal: float, total_transaksi: int}
     */
    public static function getAllTimeTotals(?int $userId = null, ?int $pasarId = null): array
    {
        return Cache::remember(
            self::vKey('all_time_totals_' . self::scopeSuffix($userId, $pasarId)),
            self::$ttl,
            function () use ($userId, $pasarId) {
                $query = PendapatanPasar::query()
                    ->selectRaw('COALESCE(SUM(jumlah_retribusi), 0) as total_nominal, COUNT(*) as total_transaksi');
                self::applyScope($query, $userId, $pasarId);

                $totals = $query->first();

                return [
                    'total_nominal' => (float) $totals->total_nominal,
                    'total_transaksi' => (int) $totals->total_transaksi,
                ];
            }
        );
    }

    /**
     * Ambil statistik ringkasan kartu berdasarkan filter (cached progresif).
     * Cache key unik per kombinasi parameter filter.
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
        $suffix = sprintf(
            'summary_%s_%s_%s_%s_%s_%s',
            $timeframe,
            $pasarId ?: 'all',
            $tahun ?: 'all',
            $bulan ?: 'all',
            $startDate ?: 'none',
            $endDate ?: 'none'
        );

        return Cache::remember(self::vKey($suffix), self::$ttl, function () use ($timeframe, $pasarId, $tahun, $bulan, $startDate, $endDate) {
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
            $pasarStats = (clone $query)
                ->selectRaw('pasar_id, COUNT(*) as transaksi_count, SUM(jumlah_retribusi) as total_nominal')
                ->groupBy('pasar_id')
                ->get()
                ->keyBy('pasar_id');

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
    public static function getRingkasanPerPasar(?int $userId = null, ?int $pasarId = null)
    {
        $suffix = self::scopeSuffix($userId, $pasarId);

        return Cache::remember(self::vKey('ringkasan_per_pasar_' . $suffix), self::$ttl, function () use ($userId, $pasarId) {
            $totals = PendapatanPasar::query()
                ->selectRaw('pasar_id, COUNT(*) as pendapatan_pasars_count, SUM(jumlah_retribusi) as pendapatan_pasars_sum_jumlah_retribusi')
                ->groupBy('pasar_id');
            self::applyScope($totals, $userId, $pasarId);

            $query = Pasar::query()
                ->leftJoinSub($totals, 'pendapatan_totals', function ($join) {
                    $join->on('pasars.id', '=', 'pendapatan_totals.pasar_id');
                })
                ->select([
                    'pasars.id',
                    'pasars.nama_pasar',
                    'pasars.kecamatan',
                    'pasars.kelurahan',
                    'pasars.status_pasar',
                ])
                ->selectRaw('COALESCE(pendapatan_totals.pendapatan_pasars_count, 0) as pendapatan_pasars_count')
                ->selectRaw('COALESCE(pendapatan_totals.pendapatan_pasars_sum_jumlah_retribusi, 0) as pendapatan_pasars_sum_jumlah_retribusi');

            if ($userId !== null || $pasarId !== null) {
                $query->whereNotNull('pendapatan_totals.pasar_id');
            }

            return $query
                ->orderByDesc('pendapatan_pasars_sum_jumlah_retribusi')
                ->get();
        });
    }

    /**
     * Ambil daftar tahun unik yang tersedia
     */
    public static function getTahunList(?int $userId = null, ?int $pasarId = null): array
    {
        $suffix = self::scopeSuffix($userId, $pasarId);

        return Cache::remember(self::vKey('tahun_list_' . $suffix), self::$ttl, function () use ($userId, $pasarId) {
            $query = PendapatanPasar::selectRaw('DISTINCT tahun_retribusi AS th');
            self::applyScope($query, $userId, $pasarId);

            $list = $query
                ->orderByDesc('th')
                ->pluck('th')
                ->filter()
                ->values()
                ->all();

            return empty($list) ? [(int) date('Y')] : $list;
        });
    }

    /**
     * Ambil data time-series chart garis untuk Dashboard (cached progresif).
     * Cache key unik per kombinasi parameter filter.
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
        $suffix = sprintf(
            'chart_%s_%s_%s_%s_%s_%s',
            $timeframe,
            $pasarId ?: 'all',
            $tahun ?: 'all',
            $bulan ?: 'all',
            $startDate ?: 'none',
            $endDate ?: 'none'
        );

        return Cache::remember(self::vKey($suffix), self::$ttl, function () use ($timeframe, $pasarId, $tahun, $bulan, $startDate, $endDate) {
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
                    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
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
                'data'   => $data,
                'total'  => $totalNominal,
                'max'    => !empty($data) ? max($data) : 0,
                'avg'    => count($data) > 0 ? ($totalNominal / count($data)) : 0,
            ];
        });
    }

    /**
     * Invalidate semua cache pendapatan lalu pre-warm entry global.
     * Invalidasi bersifat O(1) — cukup increment version key.
     */
    public static function refreshAll(): void
    {
        self::invalidate();

        // Pre-warm entry yang paling sering diakses
        self::getAllTimeTotals();
        self::getRingkasanPerPasar();
        self::getTahunList();
    }
}
