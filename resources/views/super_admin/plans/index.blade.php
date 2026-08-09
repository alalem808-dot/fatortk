@extends('super_admin.layout')
@section('title', 'خطط الاشتراك')
@section('page-title')
<h6 class="mb-0 fw-bold">إدارة خطط الاشتراك</h6>
@endsection

@section('content')
<div class="row g-3">
    @foreach($plans as $plan)
    @php
        $colors = ['free'=>['bg'=>'#f1f5f9','border'=>'#94a3b8','text'=>'#475569'],
                   'basic'=>['bg'=>'#dbeafe','border'=>'#3b82f6','text'=>'#1d4ed8'],
                   'pro'=>['bg'=>'#f3e8ff','border'=>'#9333ea','text'=>'#7e22ce'],
                   'enterprise'=>['bg'=>'#fef3c7','border'=>'#f59e0b','text'=>'#d97706']];
        $c = $colors[$plan->slug] ?? $colors['free'];
    @endphp
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid {{ $c['border'] }} !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1" style="color:{{ $c['text'] }}">{{ $plan->name }}</h5>
                        <span class="badge" style="background:{{ $c['bg'] }};color:{{ $c['text'] }}">{{ $plan->slug }}</span>
                    </div>
                    <span class="badge {{ $plan->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $plan->is_active ? 'نشطة' : 'معطلة' }}
                    </span>
                </div>

                <div class="mb-3">
                    <div class="fw-bold fs-5" style="color:{{ $c['text'] }}">
                        {{ number_format($plan->price_monthly) }} <small class="fw-normal text-muted fs-6">SDG/شهر</small>
                    </div>
                    <div class="text-muted small">{{ number_format($plan->price_yearly) }} SDG/سنة</div>
                </div>

                <div class="mb-3">
                    @foreach([
                        ['label'=>'الفواتير/شهر', 'value'=>$plan->max_invoices_per_month == -1 ? '∞' : $plan->max_invoices_per_month],
                        ['label'=>'العملاء',       'value'=>$plan->max_customers == -1 ? '∞' : $plan->max_customers],
                        ['label'=>'المنتجات',      'value'=>$plan->max_products == -1 ? '∞' : $plan->max_products],
                        ['label'=>'المستخدمون',    'value'=>$plan->max_users == -1 ? '∞' : $plan->max_users],
                        ['label'=>'القوالب',       'value'=>$plan->max_templates == -1 ? '∞' : $plan->max_templates],
                    ] as $item)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted small">{{ $item['label'] }}</span>
                        <span class="fw-semibold small">{{ $item['value'] }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="d-flex flex-wrap gap-1 mb-3">
                    @foreach(['excel_export'=>'Excel','email_send'=>'بريد','stock_management'=>'مخزون','custom_templates'=>'قوالب','api_access'=>'API'] as $feat => $label)
                    <span class="badge {{ $plan->$feat ? 'bg-success' : 'bg-light text-muted' }}" style="font-size:.7rem">
                        {{ $plan->$feat ? '✓' : '✗' }} {{ $label }}
                    </span>
                    @endforeach
                </div>

                <a href="{{ route('super_admin.plans.edit', $plan) }}" class="btn btn-sm w-100" style="background:{{ $c['border'] }};color:#fff">
                    <i class="fas fa-edit"></i> تعديل الخطة
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
