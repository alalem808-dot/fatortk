<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; background: #f8fafc; margin: 0; padding: 20px; }
    .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.1); }
    .header { background: #1e293b; padding: 24px; text-align: center; }
    .header h1 { color: #fff; margin: 0; font-size: 24px; }
    .body { padding: 32px; }
    .invoice-box { background: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0; }
    .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
    .row:last-child { border-bottom: none; font-weight: bold; font-size: 16px; }
    .btn { display: inline-block; background: #2563eb; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; margin: 16px 0; }
    .footer { background: #f1f5f9; padding: 16px; text-align: center; font-size: 12px; color: #64748b; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $invoice->tenant->company_name }}</h1>
    </div>
    <div class="body">
        <p>مرحباً <strong>{{ $invoice->customer->name }}</strong>،</p>
        <p>نرسل إليك فاتورتك رقم <strong>{{ $invoice->invoice_number }}</strong>.</p>

        <div class="invoice-box">
            <div class="row"><span>رقم الفاتورة</span><span>{{ $invoice->invoice_number }}</span></div>
            <div class="row"><span>تاريخ الفاتورة</span><span>{{ $invoice->invoice_date->format('Y-m-d') }}</span></div>
            @if($invoice->due_date)
            <div class="row"><span>تاريخ الاستحقاق</span><span>{{ $invoice->due_date->format('Y-m-d') }}</span></div>
            @endif
            <div class="row"><span>المجموع الفرعي</span><span>{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span></div>
            @if($invoice->tax_amount > 0)
            <div class="row"><span>الضريبة</span><span>{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</span></div>
            @endif
            <div class="row"><span>الإجمالي</span><span>{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</span></div>
        </div>

        <div style="text-align:center">
            <a href="{{ route('invoices.public', $invoice->id) }}" class="btn">تحميل الفاتورة PDF</a>
        </div>

        @if($invoice->notes)
        <p style="color:#64748b;font-size:14px"><strong>ملاحظات:</strong> {{ $invoice->notes }}</p>
        @endif
    </div>
    <div class="footer">
        {{ $invoice->tenant->company_name }} | {{ $invoice->tenant->email }} | {{ $invoice->tenant->phone }}
        <br>شكراً لتعاملكم معنا
    </div>
</div>
</body>
</html>
