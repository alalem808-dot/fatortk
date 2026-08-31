@extends('layouts.app')
@section('title', 'تقرير المبيعات')
@section('page-title')
<h6 class="mb-0 fw-bold">تقرير المبيعات</h6>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">من تاريخ</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">إلى تاريخ</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">الموظف</label>
                <select name="created_by" class="form-select form-select-sm">
                    <option value="">كل الموظفين</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('created_by') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
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

{{-- ملخص عام --}}
<div class="row g-3 mb-3">
    @foreach([
        ['label'=>'إجمالي الفواتير','value'=>$summary['count'],'icon'=>'fa-file-invoice','color'=>'#dbeafe','icolor'=>'#2563eb'],
        ['label'=>'إجمالي المبيعات','value'=>number_format($summary['total'],2),'icon'=>'fa-coins','color'=>'#fef9c3','icolor'=>'#ca8a04'],
        ['label'=>'المحصّل','value'=>number_format($summary['paid'],2),'icon'=>'fa-check-circle','color'=>'#dcfce7','icolor'=>'#16a34a'],
        ['label'=>'المعلّق','value'=>number_format($summary['pending'],2),'icon'=>'fa-clock','color'=>'#fee2e2','icolor'=>'#dc2626'],
        ['label'=>'الضريبة','value'=>number_format($summary['tax'],2),'icon'=>'fa-percent','color'=>'#f3e8ff','icolor'=>'#9333ea'],
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

<div class="row g-3 mb-3">
    {{-- إيرادات الموظفين --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-users me-2 text-primary"></i>إيرادات الموظفين</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>الموظف</th><th>عدد</th><th>الإجمالي</th></tr>
                    </thead>
                    <tbody>
                        @forelse($byEmployee as $emp)
                        <tr>
                            <td class="fw-semibold small">{{ $emp['name'] }}</td>
                            <td><span class="badge bg-light text-dark">{{ $emp['count'] }}</span></td>
                            <td class="small">{{ number_format($emp['total'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">لا بيانات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- إيرادات طرق الدفع --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-credit-card me-2 text-primary"></i>طرق الدفع</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>طريقة الدفع</th><th>عدد</th><th>الإجمالي</th></tr>
                    </thead>
                    <tbody>
                        @forelse($byPaymentMethod as $pm)
                        <tr>
                            <td class="fw-semibold small">{{ $pm['name'] }}</td>
                            <td><span class="badge bg-light text-dark">{{ $pm['count'] }}</span></td>
                            <td class="text-success fw-semibold small">{{ number_format($pm['total'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">لا توجد مدفوعات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- أكثر المنتجات مبيعاً --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-star me-2 text-warning"></i>أكثر المنتجات مبيعاً</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>المنتج</th><th>كمية</th><th>إيراد</th></tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $tp)
                        <tr>
                            <td class="fw-semibold small">{{ $tp->name }}</td>
                            <td class="small">{{ number_format($tp->total_qty, 2) }}</td>
                            <td class="text-primary fw-semibold small">{{ number_format($tp->total_revenue, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">لا بيانات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- العملاء المتأخرون --}}
@if($overdueCustomers->count())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-clock me-2 text-danger"></i>عملاء بمتأخرات</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr><th>العميل</th><th>إجمالي المتأخر</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($overdueCustomers as $oc)
                <tr>
                    <td class="fw-semibold">{{ $oc->name }}</td>
                    <td class="text-danger fw-bold">{{ number_format($oc->total_due, 2) }}</td>
                    <td><a href="{{ route('customers.show', $oc) }}" class="btn btn-xs btn-outline-primary">كشف حساب</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- جدول الفواتير --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0">تفاصيل الفواتير</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>رقم الفاتورة</th><th>العميل</th><th>الموظف</th><th>التاريخ</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th><th></th></tr>
            </thead>
            <tbody>
                @php $labels = ['draft'=>'مسودة','sent'=>'مرسلة','paid'=>'مدفوعة','partially_paid'=>'جزئي','overdue'=>'متأخرة','cancelled'=>'ملغاة','returned'=>'مرتجعة'] @endphp
                @forelse($invoices as $invoice)
                <tr>
                    <td><a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">{{ $invoice->invoice_number }}</a></td>
                    <td>{{ $invoice->customer->name }}</td>
                    <td class="small text-muted">{{ $invoice->creator->name ?? '—' }}</td>
                    <td class="small">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                    <td>{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="text-success">{{ number_format($invoice->paid_amount, 2) }}</td>
                    <td class="{{ $invoice->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($invoice->remaining_amount, 2) }}</td>
                    <td><span class="badge badge-{{ $invoice->status }}">{{ $labels[$invoice->status] ?? $invoice->status }}</span></td>
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
