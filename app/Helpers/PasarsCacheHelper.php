<?php

namespace App\Helpers;

use App\Models\Pasar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PasarsCacheHelper
{
    protected static int $ttl = 60 * 60 * 24 * 7; // 1 minggu
    protected static string $baseKey = 'cache_pasars';

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

    /**
     * Invalidasi seluruh cache pasar dengan menaikkan nomor versi.
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
     * Ambil waktu update terakhir data pasar (cached)
     */
    public static function getLastUpdated(): ?Carbon
    {
        return Cache::remember(self::vKey('last_updated'), self::$ttl, function () {
            $latest = Pasar::latest('updated_at')->value('updated_at');
            return $latest ? Carbon::parse($latest) : null;
        });
    }

    /**
     * Ambil total seluruh pasar (cached)
     */
    public static function getTotalPasar(): int
    {
        return (int) Cache::remember(self::vKey('total'), self::$ttl, fn () => Pasar::count());
    }

    /**
     * Ambil daftar semua pasar untuk dropdown / select / listing (cached)
     */
    public static function getAllPasar()
    {
        return Cache::remember(self::vKey('all_list'), self::$ttl, function () {
            return Pasar::orderBy('nama_pasar')->get([
                'id',
                'nama_pasar',
                'kecamatan',
                'kelurahan',
                'status_pasar',
                'tahun_pembangunan',
                'luas_lahan',
                'ada_sertifikat',
                'jumlah_pedagang',
            ]);
        });
    }

    /**
     * Ambil data ringkasan sarana prasarana global (cached)
     */
    public static function getGlobalTotals(?int $pasarId = null)
    {
        $suffix = 'global_totals_' . ($pasarId ?: 'all');

        return Cache::remember(self::vKey($suffix), self::$ttl, function () use ($pasarId) {
            $query = Pasar::query();

            if (!empty($pasarId)) {
                $query->where('id', $pasarId);
            }

            return $query->selectRaw('
                COALESCE(SUM(luas_lahan), 0)          AS total_luas,
                SUM(CASE WHEN ada_sertifikat = 1 THEN 1 ELSE 0 END) AS total_sertifikat_ada,
                SUM(CASE WHEN ada_sertifikat = 0 THEN 1 ELSE 0 END) AS total_sertifikat_tidak,
                COALESCE(SUM(jumlah_pedagang), 0)     AS total_pedagang,
                COALESCE(SUM(ptt), 0)                 AS total_ptt,
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
     * Invalidate semua cache pasar lalu pre-warm entry global.
     * Invalidasi bersifat O(1) — cukup increment version key.
     */
    public static function refreshAll(): void
    {
        self::invalidate();

        // Pre-warm entry yang paling sering diakses
        self::getTotalPasar();
        self::getAllPasar();
        self::getGlobalTotals();
    }
}
