<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إيصال بيع #{{ $invoice->invoice_number }}</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap');
    * { box-sizing: border-box; }
    body { font-family: 'Cairo', sans-serif; background: #f1f5f9; }

    .receipt-wrap { max-width: 420px; margin: 2rem auto; }

    .receipt {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,.1);
        overflow: hidden;
    }
    .receipt-header {
        background: linear-gradient(135deg, #1e40af, #2563eb);
        color: #fff;
        padding: 1.5rem;
        text-align: center;
    }
    .receipt-header .company { font-size: 1.1rem; font-weight: 800; }
    .receipt-header .inv-num { font-size: .8rem; opacity: .85; margin-top: .25rem; }
    .receipt-header .check-icon {
        width: 56px; height: 56px; border-radius: 50%;
        background: rgba(255,255,255,.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; margin: 0 auto .75rem;
    }

    .receipt-body { padding: 1.25rem; }

    .info-row {
        display: flex; justify-content: space-between;
        padding: .4rem 0; border-bottom: 1px dashed #e2e8f0;
        font-size: .85rem;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row .lbl { color: #64748b; }
    .info-row .val { font-weight: 700; color: #1e293b; }

    .items-table { width: 100%; font-size: .82rem; margin: .75rem 0; }
    .items-table th {
        background: #f8fafc; padding: .4rem .5rem;
        font-weight: 700; color: #64748b; font-size: .72rem;
        text-transform: uppercase;
    }
    .items-table td { padding: .4rem .5rem; border-bottom: 1px solid #f1f5f9; }
    .items-table tr:last-child td { border-bottom: none; }

    .totals-section { background: #f8fafc; border-radius: 10px; padding: .75rem 1rem; margin-top: .75rem; }
    .total-final {
        background: #1e40af; color: #fff;
        border-radius: 10px; padding: .75rem 1rem;
        display: flex; justify-content: space-between; align-items: center;
        margin-top: .5rem;
    }
    .total-final .lbl { font-size: .85rem; opacity: .9; }
    .total-final .val { font-size: 1.3rem; font-weight: 900; }

    .receipt-footer {
        text-align: center; padding: 1rem;
        border-top: 1px dashed #e2e8f0;
        color: #94a3b8; font-size: .75rem;
    }

    .action-btns { display: flex; gap: .75rem; margin-top: 1rem; }
    .action-btns .btn { flex: 1; border-radius: 10px; font-family: 'Cairo', sans-serif; font-weight: 700; }

    @media print {
        body { background: #fff; }
        .receipt-wrap { margin: 0; max-width: 100%; }
        .receipt { box-shadow: none; border-radius: 0; }
        .action-btns { display: none; }
        .no-print { display: none !important; }
    }
</style>
</head>
<body>

<div class="receipt-wrap">

    {{-- أزرار الإجراءات --}}
    <div class="action-btns no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-1"></i> طباعة
        </button>
        <a href="{{ route('quick-sale.index') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> بيع جديد
        </a>
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">
            <i class="fas fa-eye me-1"></i> الفاتورة
        </a>
    </div>

    <div class="receipt">
        {{-- الرأس --}}
        <div class="receipt-header">
            <div class="check-icon"><i class="fas fa-check"></i></div>
            <div class="company">{{ $invoice->tenant->company_name }}</div>
            <div class="inv-num">إيصال رقم {{ $invoice->invoice_number }}</div>
        </div>

        <div class="receipt-body">
            {{-- معلومات --}}
            <div class="mb-3">
                <div class="info-row">
                    <span class="lbl">التاريخ</span>
                    <span class="val">{{ $invoice->invoice_date->format('Y-m-d') }}</span>
                </div>
                <div class="info-row">
                    <span class="lbl">الوقت</span>
                    <span class="val">{{ $invoice->created_at->format('H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="lbl">العميل</span>
                    <span class="val">
                        @php
                            $customerName = $invoice->customer->name;
                            // استخراج اسم الزبون من الملاحظات إن وُجد
                            if ($invoice->notes && str_contains($invoice->notes, 'اسم الزبون: ')) {
                                preg_match('/اسم الزبون: ([^|]+)/', $invoice->notes, $m);
                                if (!empty($m[1])) $customerName = trim($m[1]);
                            }
                        @endphp
                        {{ $customerName }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="lbl">الموظف</span>
                    <span class="val">{{ auth()->user()->name }}</span>
                </div>
                @if($invoice->payments->first())
                <div class="info-row">
                    <span class="lbl">طريقة الدفع</span>
                    <span class="val">{{ $invoice->payments->first()->payment_method }}</span>
                </div>
                @endif
            </div>

            {{-- البنود --}}
            <table class="items-table">
                <thead>
                    <tr>
                        <th>الصنف</th>
                        <th class="text-center">الكمية</th>
                        <th class="text-center">السعر</th>
                        <th class="text-end">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-center">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-700">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- الإجماليات --}}
            <div class="totals-section">
                <div class="info-row">
                    <span class="lbl">المجموع الفرعي</span>
                    <span class="val">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                <div class="info-row">
                    <span class="lbl">الضريبة</span>
                    <span class="val">{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</span>
                </div>
                @endif
                @if($invoice->discount_amount > 0)
                <div class="info-row">
                    <span class="lbl">الخصم</span>
                    <span class="val text-danger">- {{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}</span>
                </div>
                @endif
            </div>

            <div class="total-final">
                <span class="lbl">الإجمالي الكلي</span>
                <span class="val">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</span>
            </div>

            @if($invoice->payments->first())
            @php $paid = $invoice->payments->sum('amount'); $change = $paid - $invoice->total_amount; @endphp
            <div class="mt-2 p-2 rounded" style="background:#f0fdf4;font-size:.82rem">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">المبلغ المستلم</span>
                    <span class="fw-700 text-success">{{ number_format($paid, 2) }} {{ $invoice->currency }}</span>
                </div>
                @if($change > 0)
                <div class="d-flex justify-content-between mt-1">
                    <span class="text-muted">الباقي للعميل</span>
                    <span class="fw-700 text-primary">{{ number_format($change, 2) }} {{ $invoice->currency }}</span>
                </div>
                @endif
            </div>
            @endif
        </div>

        <div class="receipt-footer">
            @php
                // عرض الملاحظات بدون جزء اسم الزبون
                $displayNotes = $invoice->notes
                    ? trim(preg_replace('/اسم الزبون: [^|]+\|?\s*/', '', $invoice->notes))
                    : null;
            @endphp
            @if($displayNotes)
            <div class="mb-2 p-2 rounded" style="background:#f8fafc;font-size:.8rem;color:#475569;text-align:right">
                <i class="fas fa-note-sticky me-1"></i> {{ $displayNotes }}
            </div>
            @endif
            شكراً لتعاملكم معنا<br>
            {{ $invoice->tenant->company_name }}
            @if($invoice->tenant->phone)
            · {{ $invoice->tenant->phone }}
            @endif
        </div>
    </div>
</div>

</body>
</html>
