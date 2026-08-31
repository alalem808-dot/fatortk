<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class LedgerExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private Collection $ledger,
        private string $name,
        private float $totalDebit,
        private float $totalCredit,
        private float $balance
    ) {}

    public function collection(): Collection { return $this->ledger; }

    public function title(): string { return 'كشف حساب'; }

    public function headings(): array
    {
        return ['التاريخ', 'البيان', 'مدين', 'دائن', 'الرصيد'];
    }

    public function map($row): array
    {
        return [
            is_object($row['date']) ? $row['date']->format('Y-m-d') : $row['date'],
            $row['description'],
            $row['debit']  > 0 ? number_format($row['debit'],  2) : '',
            $row['credit'] > 0 ? number_format($row['credit'], 2) : '',
            number_format($row['balance'], 2),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->ledger->count() + 3;

        // إضافة صف الاسم
        $sheet->insertNewRowBefore(1, 2);
        $sheet->setCellValue('A1', 'كشف حساب: ' . $this->name);
        $sheet->setCellValue('A2', 'إجمالي المديونية: ' . number_format($this->totalDebit, 2) . '   |   إجمالي المدفوع: ' . number_format($this->totalCredit, 2) . '   |   الرصيد: ' . number_format($this->balance, 2));
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');

        return [
            1  => ['font' => ['bold' => true, 'size' => 13], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1e40af']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 13]],
            2  => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'dbeafe']]],
            3  => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'f1f5f9']]],
        ];
    }
}
