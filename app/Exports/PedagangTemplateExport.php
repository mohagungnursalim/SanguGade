<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PedagangTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Data Pedagang';
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIK',
            'Alamat Tinggal',
            'Nama Tempat',
            'Jenis Sarana',
            'Ukuran',
            'Tarif per meter (m2)',
            'Total tarif bulanan (Rp)',
            'Jenis Usaha',
            'Titik Kordinat',
            'Alamat Usaha',
            'Keterangan',
            'Pasar',
        ];
    }

    public function array(): array
    {
        // 2 baris contoh agar end user langsung paham format & bisa mengganti
        return [
            [
                'Budi Santoso',
                '7271012345678901',
                'Jl. Mawar No. 12 Palu',
                'Kios A No. 5',
                'Kios',
                6,
                25000,
                150000,
                'Sembako',
                '-0.891234, 119.876543',
                'Pasar Masomba Blok A No 5',
                'Aktif berjualan',
                'Pasar Masomba',
            ],
            [
                'Siti Rahayu',
                '7271029876543210',
                'Jl. Anggrek No. 7 Palu',
                'Los B No. 10',
                'Los',
                4,
                20000,
                80000,
                'Sayuran',
                '-0.892345, 119.877654',
                'Pasar Masomba Blok B No 10',
                'Lapak sudut dekat pintu masuk',
                'Pasar Masomba',
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,  // Nama
            'B' => 20,  // NIK
            'C' => 32,  // Alamat Tinggal
            'D' => 22,  // Nama Tempat
            'E' => 14,  // Jenis Sarana
            'F' => 10,  // Ukuran
            'G' => 22,  // Tarif per meter
            'H' => 26,  // Total tarif bulanan
            'I' => 18,  // Jenis Usaha
            'J' => 28,  // Titik Kordinat
            'K' => 32,  // Alamat Usaha
            'L' => 28,  // Keterangan
            'M' => 22,  // Pasar
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header row style: background hijau tua, teks putih, tebal, center
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF059669'], // emerald-600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC'],
                ],
            ],
        ];

        // Data rows style: zebra striping ringan
        $evenRowStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF0FDF4'], // hijau sangat muda
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE5E7EB'],
                ],
            ],
        ];

        $oddRowStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFFFF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE5E7EB'],
                ],
            ],
        ];

        // Terapkan style zebra ke baris data (2 & 3 karena row 1 = header)
        $sheet->getStyle('A2:M2')->applyFromArray($evenRowStyle);
        $sheet->getStyle('A3:M3')->applyFromArray($oddRowStyle);

        // Kolom numerik: right-align
        foreach (['F', 'G', 'H'] as $col) {
            $sheet->getStyle("{$col}2:{$col}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Tinggi header row
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(22);

        // Freeze baris header agar tidak ikut scroll
        $sheet->freezePane('A2');

        return [
            1 => $headerStyle,
        ];
    }
}

