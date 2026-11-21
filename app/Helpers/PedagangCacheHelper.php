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

    public static function refreshAll(): void
    {
        self::invalidate();
    }
}

