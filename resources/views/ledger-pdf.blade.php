<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: xbriyaz, sans-serif; font-size: 12px; color: #1e293b; direction: rtl; }

    .header { background: #1e40af; color: #fff; padding: 14px 18px; margin-bottom: 14px; }
    .header h2 { font-size: 16px; margin-bottom: 3px; }
    .header p  { font-size: 10px; opacity: .85; }

    .stats { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .stats td { width: 33%; padding: 10px; text-align: center; border: 1px solid #e2e8f0; }
    .stats .s-lbl { font-size: 9px; color: #64748b; margin-bottom: 3px; }
    .stats .s-val { font-weight: bold; font-size: 13px; }
    .stat-blue  { background: #dbeafe; color: #1e40af; }
    .stat-green { background: #dcfce7; color: #15803d; }
    .stat-red   { background: #fee2e2; color: #dc2626; }
    .stat-ok    { background: #dcfce7; color: #15803d; }

    table.main { width: 100%; border-collapse: collapse; font-size: 10px; }
    table.main thead th { background: #1e40af; color: #fff; padding: 7px 8px; text-align: right; border: 1px solid #1e3a8a; }
    table.main tbody tr:nth-child(even) { background: #f8fafc; }
    table.main tbody td { padding: 6px 8px; border: 1px solid #e2e8f0; }
    table.main tfoot td { background: #f1f5f9; font-weight: bold; padding: 7px 8px; border: 1px solid #cbd5e1; }

    .badge { padding: 2px 6px; border-radius: 8px; font-size: 9px; font-weight: bold; }
    .b-invoice  { background: #dbeafe; color: #1e40af; }
    .b-payment  { background: #dcfce7; color: #15803d; }
    .b-return   { background: #fef3c7; color: #b45309; }
    .b-purchase { background: #ede9fe; color: #7c3aed; }

    .pos { color: #dc2626; }
    .neg { color: #16a34a; }

    .footer { margin-top: 18px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 6px; }
</style>
</head>
<body>

<div class="header">
    <h2>كشف حساب — {{ $entityName }}</h2>
    <p>{{ $companyName }} &nbsp;|&nbsp; تاريخ الطباعة: {{ now()->format('Y-m-d') }}</p>
</div>

<table class="stats">
    <tr>
        <td class="stat-blue">
            <div class="s-lbl">إجمالي المديونية</div>
            <div class="s-val">{{ number_format($totalDebit, 2) }}</div>
        </td>
        <td class="stat-green">
            <div class="s-lbl">إجمالي المدفوع</div>
            <div class="s-val">{{ number_format($totalCredit, 2) }}</div>
        </td>
        <td class="{{ $balance > 0 ? 'stat-red' : 'stat-ok' }}">
            <div class="s-lbl">الرصيد المستحق</div>
            <div class="s-val">{{ number_format(abs($balance), 2) }} {{ $balance > 0 ? '(مدين)' : '(دائن)' }}</div>
        </td>
    </tr>
</table>

<table class="main">
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:13%">التاريخ</th>
            <th style="width:38%">البيان</th>
            <th style="width:10%">النوع</th>
            <th style="width:12%">مدين</th>
            <th style="width:12%">دائن</th>
            <th style="width:11%">الرصيد</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ledger as $i => $row)
        @php
            $typeLabels = ['invoice'=>'فاتورة','payment'=>'دفعة','return'=>'مرتجع','purchase'=>'مشتريات'];
            $typeClass  = ['invoice'=>'b-invoice','payment'=>'b-payment','return'=>'b-return','purchase'=>'b-purchase'];
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ is_object($row['date']) ? $row['date']->format('Y-m-d') : $row['date'] }}</td>
            <td>{{ $row['description'] }}</td>
            <td><span class="badge {{ $typeClass[$row['type']] ?? '' }}">{{ $typeLabels[$row['type']] ?? $row['type'] }}</span></td>
            <td>{{ $row['debit']  > 0 ? number_format($row['debit'],  2) : '—' }}</td>
            <td>{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
            <td class="{{ $row['balance'] > 0 ? 'pos' : 'neg' }}">{{ number_format(abs($row['balance']), 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">الإجمالي</td>
            <td>{{ number_format($totalDebit, 2) }}</td>
            <td>{{ number_format($totalCredit, 2) }}</td>
            <td class="{{ $balance > 0 ? 'pos' : 'neg' }}">{{ number_format(abs($balance), 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="footer">تم إنشاء هذا الكشف بواسطة نظام فاتورتك</div>
</body>
</html>
