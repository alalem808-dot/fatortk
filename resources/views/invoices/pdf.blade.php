<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
@php
$isAr  = $invoice->language !== 'en';
$dir   = $isAr ? 'rtl' : 'ltr';
$color = $invoice->template->primary_color ?? '#1e40af';
$showTax  = $invoice->template?->show_tax      ?? true;
$showDisc = $invoice->template?->show_discount ?? true;
$showNote = $invoice->template?->show_notes    ?? true;
$as = $isAr ? 'right' : 'left';
$ae = $isAr ? 'left'  : 'right';

$t = $isAr ? [
    'inv'=>'فاتورة','no'=>'رقم الفاتورة','to'=>'فاتورة إلى',
    'date'=>'تاريخ الإصدار','due'=>'تاريخ الاستحقاق','cur'=>'العملة',
    'desc'=>'الوصف','qty'=>'الكمية','price'=>'سعر الوحدة','tax'=>'الضريبة','total'=>'الإجمالي',
    'sub'=>'المجموع الفرعي','taxamt'=>'الضريبة','disc'=>'الخصم','grand'=>'الإجمالي الكلي',
    'paid'=>'المدفوع','bal'=>'المتبقي','notes'=>'ملاحظات','terms'=>'الشروط والأحكام',
    'taxno'=>'الرقم الضريبي','thanks'=>'شكراً لتعاملكم معنا',
] : [
    'inv'=>'INVOICE','no'=>'Invoice No.','to'=>'Bill To',
    'date'=>'Issue Date','due'=>'Due Date','cur'=>'Currency',
    'desc'=>'Description','qty'=>'Qty','price'=>'Unit Price','tax'=>'Tax','total'=>'Total',
    'sub'=>'Subtotal','taxamt'=>'Tax Amount','disc'=>'Discount','grand'=>'Grand Total',
    'paid'=>'Paid','bal'=>'Balance Due','notes'=>'Notes','terms'=>'Terms & Conditions',
    'taxno'=>'Tax No.','thanks'=>'Thank you for your business',
];
@endphp
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: xbriyaz, dejavusans, sans-serif;
    font-size: 10pt;
    color: #1e293b;
    direction: {{ $dir }};
    background: #fff;
}

/* ===== HEADER ===== */
.header-table { width:100%; border-collapse:collapse; margin-bottom:6px; }
.header-table td { vertical-align:top; padding-bottom:8px; }

.logo-img { max-height:65px; max-width:190px; }

.company-block { padding:10px 14px; border-{{ $as }}:3px solid {{ $color }}; background:#f8fafc; margin-bottom:18px; display:inline-block; }
.company-name { font-size:12pt; font-weight:bold; color:{{ $color }}; margin-bottom:4px; }
.company-info { font-size:8.5pt; color:#475569; line-height:1.8; }
.company-info span { color:#94a3b8; margin-{{ $ae }}:4px; }

.inv-title {
    font-size:30pt;
    font-weight:bold;
    color:{{ $color }};
    line-height:1;
    margin-bottom:6px;
    font-family: xbriyaz;
}
.inv-number { font-size:10.5pt; color:#334155; margin-bottom:3px; font-family: xbriyaz; }

.header-divider {
    border:none;
    border-top:3px solid {{ $color }};
    margin-bottom:16px;
}

/* ===== PARTIES ===== */
.parties-table { width:100%; border-collapse:collapse; margin-bottom:18px; }
.party-label {
    font-size:8pt;
    font-weight:bold;
    color:{{ $color }};
    text-transform:uppercase;
    letter-spacing:1px;
    margin-bottom:4px;
}
.party-name { font-size:11.5pt; font-weight:bold; color:#0f172a; margin-bottom:3px; }
.party-info { font-size:8.5pt; color:#64748b; line-height:1.7; }
.date-label { font-size:8.5pt; color:#64748b; margin-bottom:4px; line-height:1.7; }
.date-label strong { color:#0f172a; }

/* ===== ITEMS TABLE ===== */
.items-table {
    width:100%;
    border-collapse:collapse;
    margin-bottom:20px;
    font-size:9.5pt;
    border:1px solid #cbd5e1;
}
.items-table thead tr { background:{{ $color }}; color:#fff; }
.items-table th {
    padding:9px 11px;
    text-align:{{ $as }};
    font-weight:bold;
    font-size:9pt;
    border-right:1px solid rgba(255,255,255,0.3);
    border-left:1px solid rgba(255,255,255,0.3);
}
.items-table th.tc { text-align:center; }
.items-table th.te { text-align:{{ $ae }}; }

.items-table tbody td {
    padding:8px 11px;
    text-align:{{ $as }};
    color:#334155;
    border-bottom:1px solid #cbd5e1;
    border-right:1px solid #cbd5e1;
    border-left:1px solid #cbd5e1;
}
.items-table tbody tr:last-child td { border-bottom:2px solid {{ $color }}; }
.items-table td.tc { text-align:center; }
.items-table td.te { text-align:{{ $ae }}; font-weight:bold; color:#0f172a; }

/* ===== TOTALS ===== */
.totals-wrap { width:100%; margin-bottom:28px; }
.totals-table {
    width:44%;
    border-collapse:collapse;
    margin-{{ $as }}:auto;
    margin-{{ $ae }}:0;
    font-size:9.5pt;
    border:1px solid #e2e8f0;
}
.totals-table td { padding:6px 11px; border-bottom:1px solid #e2e8f0; }
.totals-table tr:last-child td { border-bottom:none; }
.totals-table .tl { text-align:{{ $as }}; color:#64748b; }
.totals-table .tr { text-align:{{ $ae }}; color:#334155; }
.totals-table .grand td {
    font-size:11.5pt;
    font-weight:bold;
    color:#fff;
    background:{{ $color }};
    border-bottom:none;
    padding:9px 11px;
}
.totals-table .balance td {
    font-weight:bold;
    color:#dc2626;
    background:#fff5f5;
}

/* ===== SIGNATURE ===== */
.signature-section { width:100%; margin-top:30px; border-collapse:collapse; }
.signature-box { width:45%; vertical-align:top; }
.signature-label { font-size:8pt; color:#64748b; margin-bottom:6px; }
.signature-line { border-top:1px solid #cbd5e1; margin-top:8px; padding-top:5px; }
.signer-name { font-size:9.5pt; font-weight:bold; color:#0f172a; }
.signer-title { font-size:8.5pt; color:#64748b; }

/* ===== NOTES ===== */
.notes-section { margin-top:50px; margin-bottom:50px; }
.notes-title { font-size:9pt; font-weight:bold; color:#0f172a; margin-bottom:3px; }
.notes-text { font-size:9pt; color:#475569; line-height:1.7; margin-bottom:10px; }

/* ===== FOOTER ===== */
@page { margin-bottom: 40px; }
.footer {
    position:fixed;
    bottom:0;
    left:0;
    right:0;
    border-top:2px solid {{ $color }};
    padding-top:8px;
    text-align:center;
    font-size:8.5pt;
    color:#94a3b8;
    line-height:1.8;
    background:#fff;
}
.footer strong { color:{{ $color }}; }
</style>
</head>
<body>

{{-- ===== FOOTER (fixed) ===== --}}
<div class="footer">
    <strong>{{ $invoice->tenant->company_name }}</strong>
    @if($invoice->tenant->email) &nbsp;|&nbsp; {{ $invoice->tenant->email }} @endif
    @if($invoice->tenant->phone) &nbsp;|&nbsp; {{ $invoice->tenant->phone }} @endif
    <br>{{ $t['thanks'] }}
</div>

{{-- ===== HEADER: logo + invoice title ===== --}}
<table class="header-table">
    <tr>
        <td style="width:50%; text-align:{{ $as }}; vertical-align:bottom;">
            @if($logo)
            <img src="{{ $logo }}" class="logo-img" alt="logo">
            @endif
        </td>
        <td style="width:50%; text-align:{{ $ae }}; vertical-align:bottom;">
            <div class="inv-title">{{ $t['inv'] }}</div>
            <div class="inv-number">{{ $t['no'] }}: <strong>{{ $invoice->invoice_number }}</strong></div>
        </td>
    </tr>
</table>

<hr class="header-divider">

{{-- ===== COMPANY INFO (below divider) ===== --}}
<!--<div class="company-block">
    <div class="company-name">{{ $invoice->tenant->company_name }}</div>
    @if($invoice->tenant->address)<div class="company-info"><span>&#9679;</span>{{ $invoice->tenant->address }}</div>@endif
    @if($invoice->tenant->phone)<div class="company-info"><span>&#9679;</span>{{ $invoice->tenant->phone }}</div>@endif
    @if($invoice->tenant->email)<div class="company-info"><span>&#9679;</span>{{ $invoice->tenant->email }}</div>@endif
    @if($invoice->tenant->tax_number)<div class="company-info"><span>&#9679;</span>{{ $t['taxno'] }}: {{ $invoice->tenant->tax_number }}</div>@endif
</div>-->
{{-- ===== PARTIES ===== --}}
<table class="parties-table">
    <tr>
        <td style="width:52%; vertical-align:top; padding-{{ $ae }}:16px;">
            <div class="party-label">{{ $t['to'] }}</div>
            <div class="party-name">{{ $invoice->customer->name }}</div>
            @if($invoice->customer->email)<div class="party-info">{{ $invoice->customer->email }}</div>@endif
            @if($invoice->customer->phone)<div class="party-info">{{ $invoice->customer->phone }}</div>@endif
            @if($invoice->customer->address)<div class="party-info">{{ $invoice->customer->address }}</div>@endif
            @if($invoice->customer->tax_number)<div class="party-info">{{ $t['taxno'] }}: {{ $invoice->customer->tax_number }}</div>@endif
        </td>
        <td style="width:48%; vertical-align:top; text-align:{{ $ae }};">
            <div class="date-label"><strong>{{ $t['date'] }}:</strong> {{ $invoice->invoice_date->format('Y-m-d') }}</div>
            @if($invoice->due_date)
            <div class="date-label"><strong>{{ $t['due'] }}:</strong> {{ $invoice->due_date->format('Y-m-d') }}</div>
            @endif
            <div class="date-label"><strong>{{ $t['cur'] }}:</strong> {{ $invoice->currency }}</div>
        </td>
    </tr>
</table>

{{-- ===== ITEMS ===== --}}
<table class="items-table">
<thead>
<tr>
    <th style="width:40%">{{ $t['desc'] }}</th>
    <th class="tc" style="width:11%">{{ $t['qty'] }}</th>
    <th class="tc" style="width:16%">{{ $t['price'] }}</th>
    @if($showTax)<th class="tc" style="width:10%">{{ $t['tax'] }}</th>@endif
    <th class="te" style="width:{{ $showTax ? '23%' : '33%' }}">{{ $t['total'] }}</th>
</tr>
</thead>
<tbody>
@foreach($invoice->items as $item)
<tr>
    <td>{{ $item->description }}</td>
    <td class="tc">{{ $item->quantity }}</td>
    <td class="tc">{{ number_format($item->unit_price, 2) }}</td>
    @if($showTax)<td class="tc">{{ $item->tax_rate }}%</td>@endif
    <td class="te">{{ number_format($item->total, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>

{{-- ===== TOTALS ===== --}}
<div class="totals-wrap">
<table class="totals-table">
    <tr><td class="tl">{{ $t['sub'] }}</td><td class="tr">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td></tr>
    @if($showTax)
    <tr><td class="tl">{{ $t['taxamt'] }}</td><td class="tr">{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</td></tr>
    @endif
    @if($showDisc && $invoice->discount_amount > 0)
    <tr><td class="tl">{{ $t['disc'] }}</td><td class="tr" style="color:#dc2626">- {{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}</td></tr>
    @endif
    <tr class="grand">
        <td class="tl">{{ $t['grand'] }}</td>
        <td class="tr">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
    </tr>
    @if($invoice->paid_amount > 0)
    <tr><td class="tl">{{ $t['paid'] }}</td><td class="tr" style="color:#16a34a">{{ number_format($invoice->paid_amount, 2) }} {{ $invoice->currency }}</td></tr>
    <tr class="balance"><td class="tl">{{ $t['bal'] }}</td><td class="tr">{{ number_format($invoice->remaining_amount, 2) }} {{ $invoice->currency }}</td></tr>
    @endif
</table>
</div>

{{-- ===== NOTES ===== --}}
@if(($showNote && $invoice->notes) || $invoice->terms_conditions)
<div class="notes-section">
    @if($showNote && $invoice->notes)
    <div class="notes-title">{{ $t['notes'] }}</div>
    <div class="notes-text">{{ $invoice->notes }}</div>
    @endif
    @if($invoice->terms_conditions)
    <div class="notes-title">{{ $t['terms'] }}</div>
    <div class="notes-text">{{ $invoice->terms_conditions }}</div>
    @endif
</div>
@endif

{{-- ===== SIGNATURE & STAMP ===== --}}
@php
$tenant = $invoice->tenant;
$hasSignature = $tenant->signature_image || $tenant->stamp_image || $tenant->signer_name;
@endphp
@if($hasSignature)
@php
$stampPath = $tenant->stamp_image ? public_path('storage/' . ltrim($tenant->stamp_image, '/')) : '';
$stampSrc  = ($stampPath && file_exists($stampPath)) ? 'data:image/png;base64,'.base64_encode(file_get_contents($stampPath)) : '';
@endphp
<table style="width:100%; border-collapse:collapse; margin-top:30px;">
    <tr>
        {{-- يسار: الختم --}}
        <td style="width:50%; vertical-align:middle; text-align:rightu;">
            @if($stampSrc)
            <img src="{{ $stampSrc }}" style="max-height:130px; max-width:130px; opacity:0.9;">
            @endif
        </td>
        {{-- يمين: التوقيع + الاسم + الصفة --}}
        <td style="width:50%; vertical-align:bottom;">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="text-align:center;">
                        @if($tenant->signature_image)
                        <img src="{{ $tenant->signature_image }}" style="max-height:65px; max-width:180px; display:block; margin-bottom:4px; margin-left:auto;">
                        @endif
                        @if($tenant->signer_name)<div class="signer-name" style="text-align:right;">{{ $tenant->signer_name }}</div>@endif
                        @if($tenant->signer_title)<div class="signer-title" style="text-align:right;">{{ $tenant->signer_title }}</div>@endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif

</body>
</html>
