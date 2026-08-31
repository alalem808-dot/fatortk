<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['منتج1', 'إلكترونيات', 'SKU-001', '1234567890', 3500, 2800, 15, 10, 2, 'قطعة', 'نشط'],
            ['منتج2', 'إلكترونيات', 'SKU-002', '',           85,   60,   0,  5,  1, 'قطعة', 'نشط'],
            ['منتج3', 'أثاث',        '',        '',           450,  300,  0,  3,  0, 'قطعة', 'نشط'],
        ];
    }

    public function headings(): array
    {
        return [
            'اسم_المنتج',
            'الفئة',
            'sku',
            'الباركود',
            'سعر_البيع',
            'سعر_التكلفة',
            'نسبة_الضريبة',
            'الكمية',
            'حد_التنبيه',
            'الوحدة',
            'الحالة',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F81BD']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}
