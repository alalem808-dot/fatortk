@extends('super_admin.layout')
@section('title', 'إحصائيات الاستخدام')
@section('page-title')<h6 class="mb-0 fw-bold">إحصائيات الاستخدام</h6>@endsection

@section('content')
{{-- إجماليات --}}
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'إجمالي الفواتير',   'value'=>$totals['invoices'],  'icon'=>'fa-file-invoice',  'bg'=>'#dbeafe','ic'=>'#2563eb'],
        ['label'=>'إجمالي العملاء',    'value'=>$totals['customers'], 'icon'=>'fa-users',          'bg'=>'#dcfce7','ic'=>'#16a34a'],
        ['label'=>'إجمالي المنتجات',   'value'=>$totals['products'],  'icon'=>'fa-boxes',          'bg'=>'#fef3c7','ic'=>'#d97706'],
        ['label'=>'إجمالي المستخدمين', 'value'=>$totals['users'],     'icon'=>'fa-user-friends',   'bg'=>'#f3e8ff','ic'=>'#9333ea'],
    ] as $s)
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">{{ $s['label'] }}</div>
                    <div class="fw-bold fs-5 mt-1">{{ number_format($s['value']) }}</div>
                </div>
                <div class="icon" style="background:{{ $s['bg'] }};color:{{ $s['ic'] }}"><i class="fas {{ $s['icon'] }}"></i></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    {{-- Top 10 بالفواتير --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0">🏆 أكثر 10 مشتركين بالفواتير</h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>#</th><th>الشركة</th><th>الفواتير</th></tr></thead>
                    <tbody>
                        @foreach($topByInvoices as $i => $t)
                        <tr>
                            <td class="text-muted small">{{ $i+1 }}</td>
                            <td class="fw-semibold small">{{ $t->company_name }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px">
                                        <div class="progress-bar bg-primary" style="width:{{ $topByInvoices->first()->invoices_count > 0 ? ($t->invoices_count / $topByInvoices->first()->invoices_count * 100) : 0 }}%"></div>
                                    </div>
                                    <span class="small fw-bold">{{ $t->invoices_count }}</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top 10 بالعملاء --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0">🏆 أكثر 10 مشتركين بالعملاء</h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>#</th><th>الشركة</th><th>العملاء</th></tr></thead>
                    <tbody>
                        @foreach($topByCustomers as $i => $t)
                        <tr>
                            <td class="text-muted small">{{ $i+1 }}</td>
                            <td class="fw-semibold small">{{ $t->company_name }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px">
                                        <div class="progress-bar bg-success" style="width:{{ $topByCustomers->first()->customers_count > 0 ? ($t->customers_count / $topByCustomers->first()->customers_count * 100) : 0 }}%"></div>
                                    </div>
                                    <span class="small fw-bold">{{ $t->customers_count }}</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- جدول تفصيلي --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">تفصيل استخدام المشتركين</h6>
        <a href="{{ route('super_admin.usage_stats.export') }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-file-csv me-1"></i>تصدير CSV
        </a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr><th>الشركة</th><th>الخطة</th><th>الحالة</th><th>الفواتير</th><th>العملاء</th><th>المنتجات</th><th>المستخدمون</th><th>آخر دخول</th></tr>
            </thead>
            <tbody>
                @foreach($usageData as $t)
                <tr>
                    <td class="fw-semibold small">
                        <a href="{{ route('super_admin.tenants.show', $t) }}" class="text-decoration-none">{{ $t->company_name }}</a>
                    </td>
                    <td><span class="badge badge-{{ $t->subscription_plan }}">{{ $t->subscription_plan }}</span></td>
                    <td><span class="badge badge-{{ $t->status }}">{{ ['active'=>'نشط','trial'=>'تجريبي','suspended'=>'موقوف'][$t->status] }}</span></td>
                    <td class="small">{{ $t->invoices_count }}</td>
                    <td class="small">{{ $t->customers_count }}</td>
                    <td class="small">{{ $t->products_count }}</td>
                    <td class="small">{{ $t->users_count }}</td>
                    <td class="small text-muted">{{ isset($lastLogins[$t->id]) ? \Carbon\Carbon::parse($lastLogins[$t->id])->diffForHumans() : 'لم يدخل' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $usageData->links() }}</div>
</div>
@endsection
