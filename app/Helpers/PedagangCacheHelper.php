<?php

namespace App\Helpers;

use App\Models\Pedagang;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PedagangCacheHelper
{
    protected static int $ttl = 60 * 60 * 24 * 7; // 1 minggu
    protected static string $baseKey = 'cache_pedagang';

    protected static function version(): int
    {
        return (int) Cache::get(self::$baseKey . '_version', 1);
    }

    protected static function vKey(string $suffix): string
    {
        return self::$baseKey . ':v' . self::version() . ':' . $suffix;
    }

    public static function invalidate(): void
    {
        Cache::add(self::$baseKey . '_version', 1, self::$ttl);
        Cache::increment(self::$baseKey . '_version');
    }

    public static function getAll(?int $pasarId = null): Collection
    {
        $suffix = 'all_' . ($pasarId ?? 'all');
        $key = self::vKey($suffix);

        return Cache::remember($key, self::$ttl, function () use ($pasarId) {
            $query = Pedagang::with('pasar:id,nama_pasar')->latest('id');
            if ($pasarId !== null) {
                $query->where('pasar_id', $pasarId);
            }
            return $query->get();
        });
    }

    /**
     * Ambil data statistik komposisi jenis usaha pedagang (cached)
     *
     * @return array{labels: array<string>, data: array<int>}
     */
    public static function getJenisUsahaStats(?int $pasarId = null): array
    {
        $suffix = 'jenis_usaha_' . ($pasarId ?? 'all');
        $key = self::vKey($suffix);

        return Cache::remember($key, self::$ttl, function () use ($pasarId) {
            $query = Pedagang::query();
            if ($pasarId !== null) {
                $query->where('pasar_id', $pasarId);
            }

            $raw = $query->selectRaw('COALESCE(NULLIF(TRIM(jenis_usaha), ""), "Lainnya") as jenis, COUNT(*) as total')
                ->groupBy('jenis')
                ->orderByDesc('total')
                ->get();

            // Ambil Top 5, sisanya gabung ke "Lainnya"
            $top = $raw->take(5);
            $others = $raw->slice(5)->sum('total');

            $labels = [];
            $data = [];

            foreach ($top as $item) {
                $labels[] = $item->jenis;
                $data[] = (int) $item->total;
            }

            if ($others > 0) {
                $labels[] = 'Lainnya';
                $data[] = (int) $others;
            }

            if (empty($labels)) {
                $labels = ['Belum Ada Data'];
                $data = [0];
            }

            return ['labels' => $labels, 'data' => $data];
        });
    }

    /**
     * Ambil data statistik proporsi sarana berdagang (cached)
     *
     * @return array{labels: array<string>, data: array<int>}
     */
    public static function getJenisSaranaStats(?int $pasarId = null): array
    {
        $suffix = 'jenis_sarana_' . ($pasarId ?? 'all');
        $key = self::vKey($suffix);

        return Cache::remember($key, self::$ttl, function () use ($pasarId) {
            $query = Pedagang::query();
            if ($pasarId !== null) {
                $query->where('pasar_id', $pasarId);
            }

            $raw = $query->selectRaw('COALESCE(NULLIF(TRIM(jenis_sarana), ""), "Lainnya") as sarana, COUNT(*) as total')
                ->groupBy('sarana')
                ->orderByDesc('total')
                ->get();

            $labels = [];
            $data = [];

            foreach ($raw as $item) {
                $labels[] = $item->sarana;
                $data[] = (int) $item->total;
            }

            if (empty($labels)) {
                $labels = ['Belum Ada Data'];
                $data = [0];
            }

            return ['labels' => $labels, 'data' => $data];
        });
    }

    /**
     * Ambil statistik pedagang aktif vs keluar per pasar (soft-deleted dalam 30 hari terakhir)
     *
     * @return array{labels: array<string>, aktif: array<int>, keluar: array<int>}
     */
    public static function getStatusPedagangStats(?int $pasarId = null): array
    {
        $suffix = 'status_pedagang_bar_' . ($pasarId ?? 'all');
        $key = self::vKey($suffix);

        return Cache::remember($key, self::$ttl, function () use ($pasarId) {
            $pasars = PasarsCacheHelper::getAllPasar()
                ->when($pasarId !== null, fn ($c) => $c->where('id', $pasarId))
                ->values();

            $counts = \Illuminate\Support\Facades\DB::table('pedagangs')
                ->when($pasarId !== null, fn ($q) => $q->where('pasar_id', $pasarId))
                ->selectRaw('
                    pasar_id,
                    SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) AS aktif_count,
                    SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) AS keluar_count
                ')
                ->groupBy('pasar_id')
                ->get()
                ->keyBy('pasar_id');

            $labels = [];
            $aktif = [];
            $keluar = [];

            foreach ($pasars as $pasar) {
                $row = $counts->get($pasar->id);
                $labels[] = $pasar->nama_pasar;
                $aktif[] = $row ? (int) $row->aktif_count : 0;
                $keluar[] = $row ? (int) $row->keluar_count : 0;
            }

            if (empty($labels)) {
                return [
                    'labels' => ['Belum Ada Data'],
                    'aktif' => [0],
                    'keluar' => [0],
                ];
            }

            return [
                'labels' => $labels,
                'aktif' => $aktif,
                'keluar' => $keluar,
            ];
        });
    }

    public static function refreshAll(): void
    {
        self::invalidate();
    }
}

