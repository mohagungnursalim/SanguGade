<?php

namespace App\Helpers;

use App\Models\Pasar;
use Illuminate\Support\Facades\Cache;

class PasarsCacheHelper
{
    protected static int $ttl = 60 * 60 * 24 * 7; // 1 minggu
    protected static string $baseKey = 'cache_pasars';

    /**
     * Ambil total seluruh pasar (cached)
     */
    public static function getTotalPasar(): int
    {
        $key = self::$baseKey . '_total';
        return (int) Cache::remember($key, self::$ttl, fn() => Pasar::count());
    }

    /**
     * Ambil daftar semua pasar untuk dropdown / select / listing (cached)
     */
    public static function getAllPasar()
    {
        $key = self::$baseKey . '_all_list';
        return Cache::remember($key, self::$ttl, function () {
            return Pasar::orderBy('nama_pasar')->get([
                'id',
                'nama_pasar',
                'kecamatan',
                'kelurahan',
                'status_pasar',
                'tahun_pembangunan',
                'luas_lahan',
                'ada_sertifikat',
                'jumlah_pedagang'
            ]);
        });
    }

    /**
     * Ambil data ringkasan sarana prasarana global (cached)
     */
    public static function getGlobalTotals(?int $pasarId = null)
    {
        $key = self::$baseKey . '_global_totals_' . ($pasarId ?: 'all');
        self::trackCacheKey($key);

        return Cache::remember($key, self::$ttl, function () use ($pasarId) {
            $query = Pasar::query();

            if (!empty($pasarId)) {
                $query->where('id', $pasarId);
            }

            return $query->selectRaw('
                COALESCE(SUM(luas_lahan), 0)          AS total_luas,
                SUM(CASE WHEN ada_sertifikat = 1 THEN 1 ELSE 0 END) AS total_sertifikat_ada,
                SUM(CASE WHEN ada_sertifikat = 0 THEN 1 ELSE 0 END) AS total_sertifikat_tidak,
                COALESCE(SUM(jumlah_pedagang), 0)     AS total_pedagang,
                COALESCE(SUM(kios), 0)                AS total_kios,
                COALESCE(SUM(los), 0)                 AS total_los,
                COALESCE(SUM(lapak), 0)               AS total_lapak,
                COALESCE(SUM(ruko), 0)                AS total_ruko,
                COALESCE(SUM(kondisi_baik), 0)        AS total_baik,
                COALESCE(SUM(kondisi_rusak), 0)       AS total_rusak,
                COALESCE(SUM(kondisi_terpakai), 0)    AS total_terpakai,
                COALESCE(SUM(kepala_pasar), 0)        AS total_kepala_pasar,
                COALESCE(SUM(petugas_kebersihan), 0)  AS total_kebersihan,
                COALESCE(SUM(petugas_keamanan), 0)    AS total_keamanan,
                COALESCE(SUM(retribusi), 0)           AS total_retribusi,
                COUNT(*)                              AS total_pasar
            ')->first();
        });
    }

    /**
     * Refresh / invalidate semua cache pasar (progresif)
     */
    public static function refreshAll(): void
    {
        Cache::forget(self::$baseKey . '_total');
        Cache::forget(self::$baseKey . '_all_list');
        Cache::forget(self::$baseKey . '_global_totals');

        // Hapus query-query spesifik yang di-track
        $trackerKey = self::$baseKey . '_tracked_keys';
        $trackedKeys = Cache::get($trackerKey, []);

        foreach ($trackedKeys as $k) {
            Cache::forget($k);
        }

        Cache::forget($trackerKey);

        // Pre-warm cache global kembali
        self::getTotalPasar();
        self::getAllPasar();
        self::getGlobalTotals();
    }

    /**
     * Track dynamic query cache keys
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

