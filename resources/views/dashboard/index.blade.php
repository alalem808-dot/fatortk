@extends('layouts.app')
@section('title', 'لوحة التحكم')
@section('page-title')
<h6 class="mb-0 fw-bold">لوحة التحكم</h6>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-2 col-sm-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">إجمالي الفواتير</div>
                    <div class="fs-4 fw-bold mt-1">{{ $stats['total_invoices'] }}</div>
                </div>
                <div class="icon" style="background:#dbeafe;color:#2563eb"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">مدفوعة</div>
                    <div class="fs-4 fw-bold mt-1 text-success">{{ $stats['paid_invoices'] }}</div>
                </div>
                <div class="icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">متأخرة</div>
                    <div class="fs-4 fw-bold mt-1 text-danger">{{ $stats['overdue_invoices'] }}</div>
                </div>
                <div class="icon" style="background:#fee2e2;color:#dc2626"><i class="fas fa-exclamation-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">إجمالي الإيرادات</div>
                    <div class="fs-5 fw-bold mt-1">{{ number_format($stats['total_revenue'], 2) }}</div>
                </div>
                <div class="icon" style="background:#fef9c3;color:#ca8a04"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">العملاء</div>
                    <div class="fs-4 fw-bold mt-1">{{ $stats['total_customers'] }}</div>
                </div>
                <div class="icon" style="background:#f3e8ff;color:#9333ea"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">مخزون منخفض</div>
                    <div class="fs-4 fw-bold mt-1 {{ $stats['low_stock_count'] > 0 ? 'text-warning' : '' }}">{{ $stats['low_stock_count'] }}</div>
                </div>
                <div class="icon" style="background:#ffedd5;color:#ea580c"><i class="fas fa-boxes"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0">الإيرادات - آخر 6 أشهر</h6>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between">
                <h6 class="fw-bold mb-0">منتجات منخفضة المخزون</h6>
                <a href="{{ route('reports.stock') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
            </div>
            <div class="card-body p-0">
                @forelse($lowStockProducts as $product)
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <span class="small">{{ $product->name }}</span>
                    <span class="badge bg-warning text-dark">{{ $product->stock_quantity }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">لا توجد منتجات منخفضة</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between">
        <h6 class="fw-bold mb-0">آخر الفواتير</h6>
        <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> فاتورة جديدة
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الفاتورة</th><th>العميل</th><th>التاريخ</th><th>المبلغ</th><th>الحالة</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentInvoices as $invoice)
                    <tr>
                        <td><a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">{{ $invoice->invoice_number }}</a></td>
                        <td>{{ $invoice->customer->name }}</td>
                        <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                        <td>{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                        <td>
                            <span class="badge badge-{{ $invoice->status }}">
                                @php $labels = ['draft'=>'مسودة','sent'=>'مرسلة','paid'=>'مدفوعة','overdue'=>'متأخرة','cancelled'=>'ملغاة'] @endphp
                                {{ $labels[$invoice->status] ?? $invoice->status }}
                            </span>
                        </td>
                        <td><a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary">عرض</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">لا توجد فواتير بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('revenueChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartData->pluck('month')) !!},
        datasets: [{
            label: 'الإيرادات',
            data: {!! json_encode($chartData->pluck('revenue')) !!},
            backgroundColor: '#2563eb',
            borderRadius: 6,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
@endpush
