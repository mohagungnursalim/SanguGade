<?php

namespace App\Helpers;

class TerbilangHelper
{
    protected static array $angka = [
        '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'
    ];

    /**
     * Konversi angka nominal rupiah menjadi kalimat terbilang.
     */
    public static function make(float|int|string|null $nilai): string
    {
        $nilai = (float) $nilai;
        if ($nilai <= 0) {
            return 'Nol Rupiah';
        }

        $hasil = self::penyebut($nilai);
        $clean = preg_replace('/\s+/', ' ', trim($hasil));
        return $clean . ' Rupiah';
    }

    protected static function penyebut(float $nilai): string
    {
        $nilai = floor(abs($nilai));

        if ($nilai < 12) {
            return ' ' . self::$angka[(int)$nilai];
        }

        if ($nilai < 20) {
            return self::penyebut($nilai - 10) . ' Belas';
        }

        if ($nilai < 100) {
            return self::penyebut(floor($nilai / 10)) . ' Puluh' . self::penyebut(fmod($nilai, 10));
        }

        if ($nilai < 200) {
            return ' Seratus' . self::penyebut($nilai - 100);
        }

        if ($nilai < 1000) {
            return self::penyebut(floor($nilai / 100)) . ' Ratus' . self::penyebut(fmod($nilai, 100));
        }

        if ($nilai < 2000) {
            return ' Seribu' . self::penyebut($nilai - 1000);
        }

        if ($nilai < 1000000) {
            return self::penyebut(floor($nilai / 1000)) . ' Ribu' . self::penyebut(fmod($nilai, 1000));
        }

        if ($nilai < 1000000000) {
            return self::penyebut(floor($nilai / 1000000)) . ' Juta' . self::penyebut(fmod($nilai, 1000000));
        }

        if ($nilai < 1000000000000) {
            return self::penyebut(floor($nilai / 1000000000)) . ' Miliar' . self::penyebut(fmod($nilai, 1000000000));
        }

        if ($nilai < 1000000000000000) {
            return self::penyebut(floor($nilai / 1000000000000)) . ' Triliun' . self::penyebut(fmod($nilai, 1000000000000));
        }

        return ' Tak Terhingga';
    }
}
