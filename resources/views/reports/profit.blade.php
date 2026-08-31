@extends('layouts.app')
@section('title', 'تقرير الأرباح والخسائر')
@section('page-title')<h6 class="mb-0 fw-bold">تقرير الأرباح والخسائر</h6>@endsection
@section('content')

<form class="d-flex gap-2 mb-4 flex-wrap" method="GET">
    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}" style="width:150px">
    <input type="date" name="to"   class="form-control form-control-sm" value="{{ $to }}"   style="width:150px">
    <button class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i> تطبيق</button>
</form>

{{-- بطاقات الملخص --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card green">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">إجمالي الإيرادات</div>
                    <div class="stat-value text-success">{{ number_format($revenue, 2) }}</div>
                    @if($revenueGrowth !== null)
                    <div class="stat-sub {{ $revenueGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-{{ $revenueGrowth >= 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                        {{ abs($revenueGrowth) }}% مقارنة بالفترة السابقة
                    </div>
                    @endif
                </div>
                <div class="stat-icon bg-success-soft"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card red">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">تكلفة البضاعة (COGS)</div>
                    <div class="stat-value text-danger">{{ number_format($cogs, 2) }}</div>
                    @if($revenue > 0)
                    <div class="stat-sub">{{ round(($cogs/$revenue)*100, 1) }}% من الإيرادات</div>
                    @endif
                </div>
                <div class="stat-icon bg-danger-soft"><i class="fas fa-boxes-stacked"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card yellow">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">إجمالي المصروفات</div>
                    <div class="stat-value text-warning">{{ number_format($expenses, 2) }}</div>
                </div>
                <div class="stat-icon bg-warning-soft"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card {{ $netProfit >= 0 ? 'green' : 'red' }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">صافي الربح</div>
                    <div class="stat-value {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($netProfit, 2) }}
                    </div>
                    @if($revenue > 0)
                    <div class="stat-sub">هامش {{ round(($netProfit/$revenue)*100, 1) }}%</div>
                    @endif
                </div>
                <div class="stat-icon {{ $netProfit >= 0 ? 'bg-success-soft' : 'bg-danger-soft' }}">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- ملخص الأرباح --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header"><h6 class="fw-bold mb-0">قائمة الدخل</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted px-3">إجمالي الإيرادات المحصّلة</td>
                        <td class="text-end fw-semibold text-success px-3">+ {{ number_format($revenue, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted px-3">تكلفة البضاعة المباعة</td>
                        <td class="text-end fw-semibold text-danger px-3">- {{ number_format($cogs, 2) }}</td>
                    </tr>
                    <tr class="table-light">
                        <td class="fw-bold px-3">الربح الإجمالي</td>
                        <td class="text-end fw-bold {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }} px-3">
                            {{ number_format($grossProfit, 2) }}
                            @if($revenue > 0)
                            <span class="text-muted small">({{ round(($grossProfit/$revenue)*100,1) }}%)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted px-3">إجمالي المصروفات</td>
                        <td class="text-end fw-semibold text-danger px-3">- {{ number_format($expenses, 2) }}</td>
                    </tr>
                    <tr style="border-top:2px solid #e2e8f0">
                        <td class="fw-bold fs-6 px-3">صافي الربح</td>
                        <td class="text-end fw-bold fs-6 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} px-3">
                            {{ number_format($netProfit, 2) }}
                        </td>
                    </tr>
                    @if($revenueGrowth !== null)
                    <tr class="table-light">
                        <td class="text-muted small px-3">الفترة السابقة</td>
                        <td class="text-end small px-3">{{ number_format($prevRevenue, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- المصروفات حسب الفئة --}}
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header"><h6 class="fw-bold mb-0">المصروفات حسب الفئة</h6></div>
            <div class="card-body p-0">
                @forelse($expensesByCategory as $cat => $amount)
                @php $pct = $expenses > 0 ? round(($amount/$expenses)*100) : 0; @endphp
                <div class="px-3 py-2 border-bottom">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">{{ $cat }}</span>
                        <span class="fw-semibold text-danger small">{{ number_format($amount, 2) }} ({{ $pct }}%)</span>
                    </div>
                    <div class="progress" style="height:4px">
                        <div class="progress-bar bg-danger" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">لا توجد مصروفات</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- هامش الربح لكل منتج --}}
@if($productMargins->count())
<div class="card border-0 shadow-sm">
    <div class="card-header"><h6 class="fw-bold mb-0"><i class="fas fa-boxes-stacked me-2 text-primary"></i>هامش الربح لكل منتج (أعلى 10)</h6></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>المنتج</th>
                    <th class="text-center">الكمية المباعة</th>
                    <th class="text-center">الإيراد</th>
                    <th class="text-center">التكلفة</th>
                    <th class="text-center">الربح</th>
                    <th class="text-center">هامش %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productMargins as $pm)
                @php $margin = $pm->revenue > 0 ? round(($pm->profit / $pm->revenue) * 100, 1) : 0; @endphp
                <tr>
                    <td class="fw-semibold">{{ $pm->name }}</td>
                    <td class="text-center">{{ number_format($pm->total_qty, 2) }}</td>
                    <td class="text-center">{{ number_format($pm->revenue, 2) }}</td>
                    <td class="text-center text-danger">{{ number_format($pm->cost, 2) }}</td>
                    <td class="text-center fw-bold {{ $pm->profit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($pm->profit, 2) }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $margin >= 30 ? 'bg-success' : ($margin >= 10 ? 'bg-warning text-dark' : 'bg-danger') }}">
                            {{ $margin }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
