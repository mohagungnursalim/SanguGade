<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UsersCacheHelper
{
    protected static int $ttl = 60 * 60 * 24 * 7; // 1 minggu
    protected static string $baseKey = 'cache_users';

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
     */
    public static function vKey(string $suffix): string
    {
        return self::$baseKey . ':v' . self::version() . ':' . $suffix;
    }

    /**
     * Invalidasi seluruh cache users dengan menaikkan nomor versi.
     */
    public static function invalidate(): void
    {
        Cache::add(self::$baseKey . '_version', 1, self::$ttl);
        Cache::increment(self::$baseKey . '_version');
    }

    // ── Public Methods ───────────────────────────────────────────────────────

    /**
     * Ambil users non-admin (OPD, Operator, Bapokting) lengkap dengan relasi (Mencegah N+1).
     * Admin disembunyikan dari Users Management.
     */
    public static function getAllUsersWithRelations()
    {
        return Cache::remember(self::vKey('all_non_admin_with_relations'), self::$ttl, function () {
            return User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->with([
                'roles:id,name',
                'pasar:id,nama_pasar,kecamatan,kelurahan'
            ])
            ->latest('id')
            ->get();
        });
    }

    /**
     * Invalidate semua cache users lalu pre-warm entry dasar.
     */
    public static function refreshAll(): void
    {
        self::invalidate();

        self::getAllUsersWithRelations();
    }
}
