@extends('super_admin.layout')
@section('title', 'خطط الاشتراك')
@section('page-title')
<h6 class="mb-0 fw-bold">إدارة خطط الاشتراك</h6>
@endsection

@section('content')

@php
$activePlans   = $plans->where('is_active', true);
$inactivePlans = $plans->where('is_active', false);
$planColors = [
    'free'       => ['bg'=>'#f1f5f9','border'=>'#94a3b8','text'=>'#475569'],
    'basic'      => ['bg'=>'#dbeafe','border'=>'#3b82f6','text'=>'#1d4ed8'],
    'pro'        => ['bg'=>'#dcfce7','border'=>'#16a34a','text'=>'#15803d'],
    'enterprise' => ['bg'=>'#fef3c7','border'=>'#f59e0b','text'=>'#d97706'],
];
@endphp

{{-- الخطط النشطة --}}
<div class="mb-2">
    <h6 class="text-muted fw-bold mb-3"><i class="fas fa-check-circle text-success me-2"></i>الخطط النشطة</h6>
</div>
<div class="row g-3 mb-5">
    @forelse($activePlans as $plan)
    @php $c = $planColors[$plan->slug] ?? ['bg'=>'#f3e8ff','border'=>'#9333ea','text'=>'#7e22ce']; @endphp
    <div class="col-md-4">
        <div class="card border-0 shadow h-100" style="border-top: 4px solid {{ $c['border'] }} !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1" style="color:{{ $c['text'] }}">{{ $plan->name }}</h5>
                        <span class="badge" style="background:{{ $c['bg'] }};color:{{ $c['text'] }}">{{ $plan->slug }}</span>
                    </div>
                    <span class="badge bg-success">نشطة</span>
                </div>

                {{-- السعر --}}
                <div class="mb-3 p-3 rounded" style="background:{{ $c['bg'] }}">
                    @if($plan->price_yearly_usd > 0)
                        <div class="fw-bold fs-3" style="color:{{ $c['text'] }}">${{ number_format($plan->price_yearly_usd, 0) }}</div>
                        <div class="text-muted small">سنوياً (USD)</div>
                    @else
                        <div class="fw-bold fs-4 text-muted">مجاني</div>
                    @endif
                </div>

                {{-- الحدود --}}
                <div class="mb-3">
                    @foreach([
                        ['label'=>'الفواتير/شهر', 'value'=> $plan->max_invoices_per_month == -1 ? '∞' : $plan->max_invoices_per_month],
                        ['label'=>'العملاء',       'value'=> $plan->max_customers == -1 ? '∞' : $plan->max_customers],
                        ['label'=>'المنتجات',      'value'=> $plan->max_products == -1 ? '∞' : $plan->max_products],
                        ['label'=>'المستخدمون',    'value'=> $plan->max_users == -1 ? '∞' : $plan->max_users],
                        ['label'=>'القوالب',       'value'=> $plan->max_templates == -1 ? '∞' : $plan->max_templates],
                    ] as $item)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted small">{{ $item['label'] }}</span>
                        <span class="fw-semibold small">{{ $item['value'] }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- الميزات --}}
                <div class="d-flex flex-wrap gap-1 mb-3">
                    @foreach(['excel_export'=>'Excel','email_send'=>'بريد','stock_management'=>'مخزون','custom_templates'=>'قوالب','api_access'=>'API'] as $feat => $label)
                    <span class="badge {{ $plan->$feat ? 'bg-success' : 'bg-light text-muted' }}" style="font-size:.7rem">
                        {{ $plan->$feat ? '✓' : '✗' }} {{ $label }}
                    </span>
                    @endforeach
                </div>

                <a href="{{ route('super_admin.plans.edit', $plan) }}"
                   class="btn btn-sm w-100 fw-bold"
                   style="background:{{ $c['border'] }};color:#fff">
                    <i class="fas fa-edit me-1"></i> تعديل الخطة
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-warning">لا توجد خطط نشطة.</div>
    </div>
    @endforelse
</div>

{{-- الخطط المعطلة --}}
@if($inactivePlans->count())
<div class="mb-2">
    <h6 class="text-muted fw-bold mb-3">
        <i class="fas fa-ban text-secondary me-2"></i>الخطط المعطلة
        <small class="fw-normal text-muted">(غير مستخدمة حالياً — يمكن تفعيلها مستقبلاً)</small>
    </h6>
</div>
<div class="row g-3">
    @foreach($inactivePlans as $plan)
    @php $c = $planColors[$plan->slug] ?? ['bg'=>'#f1f5f9','border'=>'#94a3b8','text'=>'#475569']; @endphp
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 opacity-60">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-semibold small text-muted">{{ $plan->name }}</div>
                        <span class="badge bg-light text-muted" style="font-size:.65rem">{{ $plan->slug }}</span>
                    </div>
                    <span class="badge bg-secondary">معطلة</span>
                </div>
                <a href="{{ route('super_admin.plans.edit', $plan) }}" class="btn btn-xs btn-outline-secondary w-100 mt-2">
                    <i class="fas fa-edit me-1"></i>تعديل / تفعيل
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
