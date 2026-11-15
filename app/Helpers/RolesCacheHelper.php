<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesCacheHelper
{
    protected static int $ttl = 60 * 60 * 24 * 7; // 1 minggu
    protected static string $baseKey = 'cache_roles';

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
     * Invalidasi seluruh cache roles dengan menaikkan nomor versi.
     */
    public static function invalidate(): void
    {
        Cache::add(self::$baseKey . '_version', 1, self::$ttl);
        Cache::increment(self::$baseKey . '_version');
    }

    // ── Public Methods ───────────────────────────────────────────────────────

    /**
     * Ambil semua roles lengkap dengan eager load users dan permissions (Mencegah N+1)
     */
    public static function getAllRolesWithRelations()
    {
        return Cache::remember(self::vKey('all_with_relations'), self::$ttl, function () {
            return Role::with(['users:id,name,email,pasar_id', 'permissions:id,name'])
                ->withCount('users')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Ambil semua permission yang tersedia
     */
    public static function getAllPermissions()
    {
        return Cache::remember(self::vKey('all_permissions'), self::$ttl, function () {
            return Permission::orderBy('name')->get(['id', 'name']);
        });
    }

    /**
     * Invalidate semua cache roles lalu pre-warm entry dasar.
     */
    public static function refreshAll(): void
    {
        self::invalidate();

        self::getAllRolesWithRelations();
        self::getAllPermissions();
    }
}

