<?php

namespace App\Helpers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoriesCacheHelper
{
    protected static $ttl = 60 * 60 * 24 * 7; // 1 minggu
    protected static $baseKey = 'cache_categories';

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
    public static function vKey(string $suffix): string
    {
        return self::$baseKey . ':v' . self::version() . ':' . $suffix;
    }

    /**
     * Invalidasi seluruh cache kategori dengan menaikkan nomor versi.
     * Cache::add menjamin key terseed ke 1 sebelum increment,
     * sehingga versi selalu meningkat dari nilai yang sudah tersimpan.
     */
    public static function invalidate(): void
    {
        Cache::add(self::$baseKey . '_version', 1, self::$ttl);
        Cache::increment(self::$baseKey . '_version');
    }

    // ── Public Methods ───────────────────────────────────────────────────────

    /**
     * Ambil total semua kategori (cached)
     */
    public static function getTotalCategories()
    {
        return Cache::remember(self::vKey('total'), self::$ttl, fn () => Category::count());
    }

    /**
     * Invalidate semua cache kategori lalu pre-warm entry dasar.
     * Invalidasi bersifat O(1) — cukup increment version key.
     */
    public static function refreshAll(): void
    {
        self::invalidate();

        // Pre-warm entry yang paling sering diakses
        self::getTotalCategories();
    }
}
