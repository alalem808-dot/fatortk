@extends('super_admin.layout')
@section('title', 'لوحة التحكم')
@section('page-title')
<h6 class="mb-0 fw-bold">لوحة تحكم المشرف العام</h6>
@endsection

@section('content')
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'إجمالي الحسابات',  'value'=>$stats['total_tenants'],  'icon'=>'fa-building',     'bg'=>'#dbeafe','ic'=>'#2563eb'],
        ['label'=>'حسابات نشطة',      'value'=>$stats['active_tenants'], 'icon'=>'fa-check-circle', 'bg'=>'#dcfce7','ic'=>'#16a34a'],
        ['label'=>'حسابات تجريبية',   'value'=>$stats['trial_tenants'],  'icon'=>'fa-clock',        'bg'=>'#fef9c3','ic'=>'#ca8a04'],
        ['label'=>'موقوفة',           'value'=>$stats['suspended'],      'icon'=>'fa-ban',           'bg'=>'#fee2e2','ic'=>'#dc2626'],
        ['label'=>'إجمالي الفواتير',  'value'=>$stats['total_invoices'], 'icon'=>'fa-file-invoice',  'bg'=>'#f3e8ff','ic'=>'#9333ea'],
        ['label'=>'إجمالي المستخدمين','value'=>$stats['total_users'],    'icon'=>'fa-users',         'bg'=>'#e0f2fe','ic'=>'#0284c7'],
        ['label'=>'جديد هذا الشهر',   'value'=>$stats['new_this_month'], 'icon'=>'fa-star',          'bg'=>'#fef3c7','ic'=>'#d97706'],
    ] as $s)
    <div class="col-md col-sm-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">{{ $s['label'] }}</div>
                    <div class="fw-bold mt-1 fs-5">{{ $s['value'] }}</div>
                </div>
                <div class="icon" style="background:{{ $s['bg'] }};color:{{ $s['ic'] }}">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-3">
    {{-- رسم نمو الحسابات --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0">نمو الحسابات - آخر 6 أشهر</h6>
            </div>
            <div class="card-body">
                <canvas id="growthChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- توزيع الخطط --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between">
                <h6 class="fw-bold mb-0">توزيع خطط الاشتراك</h6>
                <a href="{{ route('super_admin.plans') }}" class="btn btn-xs btn-outline-warning">إدارة الخطط</a>
            </div>
            <div class="card-body">
                <canvas id="planChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- آخر الحسابات --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between">
        <h6 class="fw-bold mb-0">آخر الحسابات المسجلة</h6>
        <div class="d-flex gap-2">
            <a href="{{ route('super_admin.tenants.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> حساب جديد
            </a>
            <a href="{{ route('super_admin.tenants') }}" class="btn btn-sm btn-outline-secondary">عرض الكل</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>الشركة</th><th>البريد</th><th>الخطة</th><th>الحالة</th><th>التاريخ</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($recentTenants as $tenant)
                <tr>
                    <td>
                        <a href="{{ route('super_admin.tenants.show', $tenant) }}" class="fw-semibold text-decoration-none">{{ $tenant->company_name }}</a>
                        <div class="text-muted" style="font-size:.72rem">{{ $tenant->subdomain }}.fatortk.com</div>
                    </td>
                    <td class="text-muted small">{{ $tenant->email }}</td>
                    <td><span class="badge badge-{{ $tenant->subscription_plan }}">{{ ['free'=>'مجاني','basic'=>'أساسي','pro'=>'احترافي','enterprise'=>'مؤسسي'][$tenant->subscription_plan] }}</span></td>
                    <td><span class="badge badge-{{ $tenant->status }}">{{ ['active'=>'نشط','trial'=>'تجريبي','suspended'=>'موقوف'][$tenant->status] }}</span></td>
                    <td class="text-muted small">{{ $tenant->created_at->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('super_admin.tenants.show', $tenant) }}" class="btn btn-xs btn-outline-secondary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('growthChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($growthData->pluck('month')) !!},
        datasets: [{
            label: 'حسابات جديدة',
            data: {!! json_encode($growthData->pluck('count')) !!},
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,.1)',
            tension: 0.4,
            fill: true,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('planChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($planStats->keys()->map(fn($k) => ['free'=>'مجاني','basic'=>'أساسي','pro'=>'احترافي','enterprise'=>'مؤسسي'][$k] ?? $k)) !!},
        datasets: [{
            data: {!! json_encode($planStats->values()) !!},
            backgroundColor: ['#94a3b8','#3b82f6','#9333ea','#f59e0b'],
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
