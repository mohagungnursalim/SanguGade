<?php

namespace App\Services;

use ZipArchive;

class XlsxExportService
{
    /**
     * Generate an Excel (.xlsx) file without external libraries.
     */
    public static function create(array $headers, array $rows, string $filePath): bool
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        // [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // _rels/.rels
        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
        $zip->addFromString('_rels/.rels', $rootRels);

        // xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>'
            . '<sheet name="Data Pedagang" sheetId="1" r:id="rId1"/>'
            . '</sheets>'
            . '</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // xl/worksheets/sheet1.xml
        $sheetData = '<sheetData>';
        $rowIndex = 1;

        // Header Row
        $sheetData .= '<row r="' . $rowIndex . '">';
        $colIdx = 0;
        foreach ($headers as $header) {
            $cellRef = self::colName($colIdx) . $rowIndex;
            $escaped = htmlspecialchars((string)$header, ENT_XML1, 'UTF-8');
            $sheetData .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $escaped . '</t></is></c>';
            $colIdx++;
        }
        $sheetData .= '</row>';
        $rowIndex++;

        // Data Rows
        foreach ($rows as $row) {
            $sheetData .= '<row r="' . $rowIndex . '">';
            $colIdx = 0;
            foreach ($row as $val) {
                $cellRef = self::colName($colIdx) . $rowIndex;
                if (is_numeric($val) && !preg_match('/^0\d+/', (string)$val)) {
                    $sheetData .= '<c r="' . $cellRef . '"><v>' . (float)$val . '</v></c>';
                } else {
                    $escaped = htmlspecialchars((string)($val ?? ''), ENT_XML1, 'UTF-8');
                    $sheetData .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $escaped . '</t></is></c>';
                }
                $colIdx++;
            }
            $sheetData .= '</row>';
            $rowIndex++;
        }
        $sheetData .= '</sheetData>';

        $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $sheetData
            . '</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $worksheet);

        return $zip->close();
    }

    private static function colName(int $idx): string
    {
        $letters = '';
        while ($idx >= 0) {
            $letters = chr($idx % 26 + 65) . $letters;
            $idx = intdiv($idx, 26) - 1;
        }
        return $letters;
    }
}

