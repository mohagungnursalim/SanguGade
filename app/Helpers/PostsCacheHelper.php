<?php

namespace App\Helpers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PostsCacheHelper
{
    protected static $ttl = 60 * 60 * 24 * 7; // 1 minggu
    protected static $baseKey = 'cache_posts';

    // ── User-scoped Key ──────────────────────────────────────────────────────

    /**
     * Base key per user (semua cache terisolasi per user).
     */
    protected static function userKey(): string
    {
        $userId = Auth::id() ?? 'guest';
        return self::$baseKey . "_user_{$userId}";
    }

    // ── Versioned Key (per user) ─────────────────────────────────────────────

    /**
     * Key yang menyimpan nomor versi untuk user saat ini.
     */
    protected static function versionKey(): string
    {
        return self::userKey() . '_version';
    }

    /**
     * Baca versi cache saat ini untuk user aktif. Default ke 1.
     */
    protected static function version(): int
    {
        return (int) Cache::get(self::versionKey(), 1);
    }

    /**
     * Bentuk cache key yang sudah mengandung versi aktif untuk user ini.
     * Ketika versi naik, semua derived key otomatis menjadi stale
     * tanpa perlu menghapus satu per satu (invalidasi O(1)).
     */
    protected static function vKey(string $suffix): string
    {
        return self::userKey() . ':v' . self::version() . ':' . $suffix;
    }

    /**
     * Invalidasi seluruh cache posts user aktif dengan menaikkan nomor versi.
     * Cache::add memastikan key seed = 1 sebelum increment,
     * sehingga versi selalu meningkat dari nilai yang sudah tersimpan.
     */
    public static function invalidate(): void
    {
        Cache::add(self::versionKey(), 1, self::$ttl);
        Cache::increment(self::versionKey());
    }

    // ── Public Methods ───────────────────────────────────────────────────────

    /**
     * Ambil total posts milik user saat ini (cached)
     */
    public static function getTotalPosts()
    {
        return Cache::remember(
            self::vKey('total'),
            self::$ttl,
            fn () => Post::where('user_id', Auth::id())->count()
        );
    }

    /**
     * Invalidate semua cache posts user lalu pre-warm entry dasar.
     * Invalidasi bersifat O(1) — cukup increment version key.
     */
    public static function refreshAll(): void
    {
        self::invalidate();

        // Pre-warm entry yang paling sering diakses
        self::getTotalPosts();
    }
}
