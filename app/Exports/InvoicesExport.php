<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $invoices) {}

    public function collection(): Collection { return $this->invoices; }

    public function headings(): array
    {
        return ['رقم الفاتورة', 'العميل', 'التاريخ', 'الاستحقاق', 'المجموع الفرعي', 'الضريبة', 'الخصم', 'الإجمالي', 'المدفوع', 'المتبقي', 'الحالة', 'العملة'];
    }

    public function map($invoice): array
    {
        $labels = ['draft'=>'مسودة','sent'=>'مرسلة','paid'=>'مدفوعة','overdue'=>'متأخرة','cancelled'=>'ملغاة'];
        return [
            $invoice->invoice_number,
            $invoice->customer->name,
            $invoice->invoice_date->format('Y-m-d'),
            $invoice->due_date?->format('Y-m-d') ?? '-',
            $invoice->subtotal,
            $invoice->tax_amount,
            $invoice->discount_amount,
            $invoice->total_amount,
            $invoice->paid_amount,
            $invoice->total_amount - $invoice->paid_amount,
            $labels[$invoice->status] ?? $invoice->status,
            $invoice->currency,
        ];
    }
}
