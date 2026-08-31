@extends('super_admin.layout')
@section('title', 'الإيرادات والمدفوعات')
@section('page-title')<h6 class="mb-0 fw-bold">الإيرادات والمدفوعات</h6>@endsection

@section('content')
{{-- إحصائيات --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">إجمالي الإيرادات</div>
                    <div class="fw-bold fs-5 mt-1">${{ number_format($totalRevenue, 0) }}</div>
                </div>
                <div class="icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-dollar-sign"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">إيرادات هذه السنة</div>
                    <div class="fw-bold fs-5 mt-1">${{ number_format($thisYearRevenue, 0) }}</div>
                </div>
                <div class="icon" style="background:#dbeafe;color:#2563eb"><i class="fas fa-calendar-alt"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">إيرادات هذا الشهر</div>
                    <div class="fw-bold fs-5 mt-1">${{ number_format($thisMonthRevenue, 0) }}</div>
                </div>
                <div class="icon" style="background:#fef3c7;color:#d97706"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">إجمالي المدفوعات</div>
                    <div class="fw-bold fs-5 mt-1">{{ number_format($totalPayments) }}</div>
                </div>
                <div class="icon" style="background:#f3e8ff;color:#9333ea"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- رسم الإيرادات --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0">الإيرادات - آخر 12 شهر (USD)</h6>
            </div>
            <div class="card-body"><canvas id="revenueChart" height="110"></canvas></div>
        </div>
    </div>

    {{-- التجديد القريب --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">يستحق التجديد قريباً</h6>
                <span class="badge bg-warning text-dark">{{ $renewingSoon->count() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($renewingSoon as $t)
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <div>
                        <div class="small fw-semibold">{{ $t->company_name }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $t->subscription_expires_at->format('Y-m-d') }}</div>
                    </div>
                    <span class="badge {{ $t->subscription_expires_at->diffInDays() <= 3 ? 'bg-danger' : 'bg-warning text-dark' }}">
                        {{ $t->subscription_expires_at->diffInDays() }} يوم
                    </span>
                </div>
                @empty
                <div class="text-center text-muted p-4 small">لا توجد اشتراكات قريبة الانتهاء</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- آخر المدفوعات + زر إضافة --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">سجل المدفوعات</h6>
        <div class="d-flex gap-2">
            <a href="{{ route('super_admin.revenue.export') }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-csv me-1"></i>تصدير CSV
            </a>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                <i class="fas fa-plus me-1"></i>تسجيل دفعة
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr><th>الشركة</th><th>الخطة</th><th>المبلغ</th><th>الفترة</th><th>تاريخ الدفع</th><th>تاريخ الانتهاء</th><th>ملاحظات</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($recentPayments as $p)
                <tr>
                    <td class="fw-semibold small">{{ $p->tenant->company_name }}</td>
                    <td><span class="badge badge-{{ $p->plan_slug }}">{{ $p->plan_name }}</span></td>
                    <td class="fw-bold text-success">${{ number_format($p->amount_usd, 0) }}</td>
                    <td class="small">{{ $p->period === 'yearly' ? 'سنوي' : 'شهري' }}</td>
                    <td class="small text-muted">{{ $p->paid_at->format('Y-m-d') }}</td>
                    <td class="small text-muted">{{ $p->expires_at->format('Y-m-d') }}</td>
                    <td class="small text-muted">{{ $p->notes ?? '—' }}</td>
                    <td>
                        <form action="{{ route('super_admin.revenue.destroy', $p->id) }}" method="POST"
                              onsubmit="return confirm('حذف هذه الدفعة؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">لا توجد مدفوعات مسجلة بعد</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal إضافة دفعة --}}
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('super_admin.revenue.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">تسجيل دفعة اشتراك</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">الشركة</label>
                    <select name="tenant_id" class="form-select" required>
                        <option value="">اختر المشترك...</option>
                        @foreach(\App\Models\Tenant::orderBy('company_name')->get() as $t)
                        <option value="{{ $t->id }}">{{ $t->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">المبلغ (USD)</label>
                        <input type="number" name="amount_usd" class="form-control" value="600" min="1" step="0.01" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">الفترة</label>
                        <select name="period" class="form-select">
                            <option value="yearly">سنوي</option>
                            <option value="monthly">شهري</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <label class="form-label">تاريخ الدفع</label>
                        <input type="date" name="paid_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">تاريخ الانتهاء</label>
                        <input type="date" name="expires_at" class="form-control" value="{{ date('Y-m-d', strtotime('+1 year')) }}" required>
                    </div>
                </div>
                <div class="mt-2">
                    <label class="form-label">ملاحظات (اختياري)</label>
                    <input type="text" name="notes" class="form-control" placeholder="رقم الحوالة، طريقة الدفع...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">تسجيل الدفعة</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyRevenue->pluck('month')) !!},
        datasets: [{
            label: 'الإيرادات (USD)',
            data: {!! json_encode($monthlyRevenue->pluck('amount')) !!},
            backgroundColor: 'rgba(37,99,235,.7)',
            borderRadius: 6,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
