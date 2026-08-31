<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
@php
$color = '#1e40af';
$totalReturned = $purchase->returns->sum('total');
@endphp
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: xbriyaz, dejavusans, sans-serif;
    font-size: 10pt;
    color: #1e293b;
    direction: rtl;
    background: #fff;
}
.header-table { width:100%; border-collapse:collapse; margin-bottom:6px; }
.header-table td { vertical-align:top; padding-bottom:8px; }
.logo-img { max-height:65px; max-width:190px; }
.inv-title { font-size:26pt; font-weight:bold; color:{{ $color }}; line-height:1; margin-bottom:6px; }
.inv-number { font-size:10pt; color:#334155; margin-bottom:3px; }
.header-divider { border:none; border-top:3px solid {{ $color }}; margin-bottom:16px; }
.parties-table { width:100%; border-collapse:collapse; margin-bottom:18px; }
.party-label { font-size:8pt; font-weight:bold; color:{{ $color }}; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px; }
.party-name { font-size:11pt; font-weight:bold; color:#0f172a; margin-bottom:3px; }
.party-info { font-size:8.5pt; color:#64748b; line-height:1.7; }
.date-label { font-size:8.5pt; color:#64748b; margin-bottom:4px; line-height:1.7; }
.date-label strong { color:#0f172a; }
.items-table { width:100%; border-collapse:collapse; margin-bottom:20px; font-size:9.5pt; border:1px solid #cbd5e1; }
.items-table thead tr { background:{{ $color }}; color:#fff; }
.items-table th { padding:9px 11px; text-align:right; font-weight:bold; font-size:9pt; }
.items-table th.tc { text-align:center; }
.items-table th.te { text-align:left; }
.items-table tbody td { padding:8px 11px; text-align:right; color:#334155; border-bottom:1px solid #e2e8f0; }
.items-table tbody tr:last-child td { border-bottom:2px solid {{ $color }}; }
.items-table td.tc { text-align:center; }
.items-table td.te { text-align:left; font-weight:bold; color:#0f172a; }
.totals-table { width:44%; border-collapse:collapse; margin-right:auto; margin-left:0; font-size:9.5pt; border:1px solid #e2e8f0; }
.totals-table td { padding:6px 11px; border-bottom:1px solid #e2e8f0; }
.totals-table tr:last-child td { border-bottom:none; }
.totals-table .tl { text-align:right; color:#64748b; }
.totals-table .tr { text-align:left; color:#334155; }
.totals-table .grand td { font-size:11.5pt; font-weight:bold; color:#fff; background:{{ $color }}; border-bottom:none; padding:9px 11px; }
.notes-section { margin-top:30px; }
.notes-title { font-size:9pt; font-weight:bold; color:#0f172a; margin-bottom:3px; }
.notes-text { font-size:9pt; color:#475569; line-height:1.7; }
.status-badge { display:inline-block; padding:3px 10px; border-radius:4px; font-size:8.5pt; font-weight:bold; }
.status-received { background:#dcfce7; color:#15803d; }
.status-pending { background:#fef3c7; color:#b45309; }
.status-cancelled { background:#f1f5f9; color:#64748b; }
@page { margin-bottom: 40px; }
.footer { position:fixed; bottom:0; left:0; right:0; border-top:2px solid {{ $color }}; padding-top:8px; text-align:center; font-size:8.5pt; color:#94a3b8; line-height:1.8; background:#fff; }
.footer strong { color:{{ $color }}; }
</style>
</head>
<body>

<div class="footer">
    <strong>{{ $purchase->tenant->company_name ?? '' }}</strong>
    @if($purchase->tenant->email ?? null) &nbsp;|&nbsp; {{ $purchase->tenant->email }} @endif
    @if($purchase->tenant->phone ?? null) &nbsp;|&nbsp; {{ $purchase->tenant->phone }} @endif
    <br>شكراً لتعاملكم معنا
</div>

{{-- الرأس --}}
<table class="header-table">
    <tr>
        <td style="width:50%; text-align:right; vertical-align:bottom;">
            @if($logo ?? null)
            <img src="{{ $logo }}" class="logo-img" alt="logo">
            @endif
        </td>
        <td style="width:50%; text-align:left; vertical-align:bottom;">
            <div class="inv-title">أمر شراء</div>
            <div class="inv-number">رقم الأمر: <strong>{{ $purchase->reference }}</strong></div>
            <div class="inv-number">
                <span class="status-badge status-{{ $purchase->status }}">{{ $purchase->status_label }}</span>
            </div>
        </td>
    </tr>
</table>

<hr class="header-divider">

{{-- بيانات الطرفين --}}
<table class="parties-table">
    <tr>
        <td style="width:50%; vertical-align:top; padding-left:16px;">
            <div class="party-label">المورد</div>
            <div class="party-name">{{ $purchase->supplier_name ?? 'غير محدد' }}</div>
            @if($purchase->supplier_phone)
                <div class="party-info">{{ $purchase->supplier_phone }}</div>
            @endif
            @if($purchase->supplier)
                @if($purchase->supplier->email)
                    <div class="party-info">{{ $purchase->supplier->email }}</div>
                @endif
            @endif
        </td>
        <td style="width:50%; vertical-align:top; text-align:left;">
            <div class="date-label"><strong>تاريخ الأمر:</strong> {{ $purchase->purchase_date->format('Y-m-d') }}</div>
            @if($purchase->warehouse)
                <div class="date-label"><strong>المخزن:</strong> {{ $purchase->warehouse->name }}</div>
            @endif
            <div class="date-label"><strong>العملة:</strong> {{ $purchase->currency }}</div>
        </td>
    </tr>
</table>

{{-- جدول الأصناف --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width:40%">الصنف</th>
            <th class="tc" style="width:15%">الكمية</th>
            @if($totalReturned > 0)
            <th class="tc" style="width:13%" style="color:#fef08a">مرتجع</th>
            <th class="tc" style="width:13%">الصافي</th>
            @endif
            <th class="tc" style="width:16%">سعر التكلفة</th>
            <th class="te" style="width:{{ $totalReturned > 0 ? '13%' : '29%' }}">الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @php
            $returnedQtys = [];
            foreach ($purchase->returns as $ret) {
                foreach ($ret->items as $ri) {
                    $returnedQtys[$ri->purchase_item_id] =
                        ($returnedQtys[$ri->purchase_item_id] ?? 0) + $ri->quantity;
                }
            }
        @endphp
        @foreach($purchase->items as $item)
        @php
            $retQty = $returnedQtys[$item->id] ?? 0;
            $netQty = $item->quantity - $retQty;
        @endphp
        <tr>
            <td>
                {{ $item->product_name }}
                @if($item->product?->sku)
                    <br><span style="font-size:8pt;color:#94a3b8">SKU: {{ $item->product->sku }}</span>
                @endif
            </td>
            <td class="tc">{{ number_format($item->quantity, 3) }}</td>
            @if($totalReturned > 0)
            <td class="tc" style="color:#b45309">{{ $retQty > 0 ? '- ' . number_format($retQty, 3) : '—' }}</td>
            <td class="tc" style="font-weight:bold; color:{{ $netQty <= 0 ? '#dc2626' : '#15803d' }}">{{ number_format($netQty, 3) }}</td>
            @endif
            <td class="tc">{{ number_format($item->unit_cost, 2) }}</td>
            <td class="te">{{ number_format($item->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- الإجماليات --}}
<div style="width:100%; margin-bottom:20px;">
<table class="totals-table">
    <tr><td class="tl">المجموع الفرعي</td><td class="tr">{{ number_format($purchase->subtotal, 2) }} {{ $purchase->currency }}</td></tr>
    @if(($purchase->tax_amount ?? 0) > 0)
    <tr><td class="tl">الضريبة</td><td class="tr">{{ number_format($purchase->tax_amount, 2) }} {{ $purchase->currency }}</td></tr>
    @endif
    <tr class="grand">
        <td class="tl">الإجمالي الكلي</td>
        <td class="tr">{{ number_format($purchase->subtotal + ($purchase->tax_amount ?? 0), 2) }} {{ $purchase->currency }}</td>
    </tr>
    @if($totalReturned > 0)
    <tr><td class="tl" style="color:#b45309">إجمالي المرتجعات</td><td class="tr" style="color:#b45309">- {{ number_format($totalReturned, 2) }} {{ $purchase->currency }}</td></tr>
    <tr><td class="tl" style="font-weight:bold">الصافي بعد المرتجع</td><td class="tr" style="font-weight:bold">{{ number_format(max(0, $purchase->total), 2) }} {{ $purchase->currency }}</td></tr>
    @endif
    @if(($purchase->paid_amount ?? 0) > 0)
    <tr><td class="tl" style="color:#16a34a">المدفوع</td><td class="tr" style="color:#16a34a">{{ number_format($purchase->paid_amount, 2) }} {{ $purchase->currency }}</td></tr>
    <tr><td class="tl" style="color:#dc2626; font-weight:bold">المتبقي</td><td class="tr" style="color:#dc2626; font-weight:bold">{{ number_format($purchase->remaining_amount, 2) }} {{ $purchase->currency }}</td></tr>
    @endif
</table>
</div>

@if($purchase->notes)
<div class="notes-section">
    <div class="notes-title">ملاحظات</div>
    <div class="notes-text">{{ $purchase->notes }}</div>
</div>
@endif

</body>
</html>
