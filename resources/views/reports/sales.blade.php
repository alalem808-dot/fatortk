@extends('layouts.app')
@section('title', 'تقرير المبيعات')
@section('page-title')
<h6 class="mb-0 fw-bold">تقرير المبيعات</h6>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">من تاريخ</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">إلى تاريخ</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-primary">عرض التقرير</button>
                <a href="{{ request()->fullUrlWithQuery(['export'=>'excel']) }}" class="btn btn-sm btn-success ms-1">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach([
        ['label'=>'إجمالي الفواتير','value'=>$summary['count'],'icon'=>'fa-file-invoice','color'=>'#dbeafe','icolor'=>'#2563eb'],
        ['label'=>'إجمالي المبيعات','value'=>number_format($summary['total'],2).' SDG','icon'=>'fa-coins','color'=>'#fef9c3','icolor'=>'#ca8a04'],
        ['label'=>'المحصّل','value'=>number_format($summary['paid'],2).' SDG','icon'=>'fa-check-circle','color'=>'#dcfce7','icolor'=>'#16a34a'],
        ['label'=>'المعلّق','value'=>number_format($summary['pending'],2).' SDG','icon'=>'fa-clock','color'=>'#fee2e2','icolor'=>'#dc2626'],
        ['label'=>'الضريبة','value'=>number_format($summary['tax'],2).' SDG','icon'=>'fa-percent','color'=>'#f3e8ff','icolor'=>'#9333ea'],
    ] as $stat)
    <div class="col-md col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">{{ $stat['label'] }}</div>
                    <div class="fw-bold mt-1">{{ $stat['value'] }}</div>
                </div>
                <div class="icon" style="background:{{ $stat['color'] }};color:{{ $stat['icolor'] }}"><i class="fas {{ $stat['icon'] }}"></i></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>رقم الفاتورة</th><th>العميل</th><th>التاريخ</th><th>الاستحقاق</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th><th></th></tr>
            </thead>
            <tbody>
                @php $labels = ['draft'=>'مسودة','sent'=>'مرسلة','paid'=>'مدفوعة','overdue'=>'متأخرة','cancelled'=>'ملغاة'] @endphp
                @forelse($invoices as $invoice)
                <tr>
                    <td><a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">{{ $invoice->invoice_number }}</a></td>
                    <td>{{ $invoice->customer->name }}</td>
                    <td class="small">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                    <td class="small">{{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="text-success">{{ number_format($invoice->paid_amount, 2) }}</td>
                    <td class="{{ $invoice->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($invoice->remaining_amount, 2) }}</td>
                    <td><span class="badge badge-{{ $invoice->status }}">{{ $labels[$invoice->status] }}</span></td>
                    <td>
                        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-xs btn-outline-danger" target="_blank"><i class="fas fa-file-pdf"></i></a>
                        <a href="{{ route('invoices.whatsapp', $invoice) }}" class="btn btn-xs btn-whatsapp" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-5">لا توجد فواتير في هذه الفترة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
