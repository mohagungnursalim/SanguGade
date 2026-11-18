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

    public static function refreshAll(): void
    {
        self::invalidate();
    }
}

