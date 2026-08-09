@extends('super_admin.layout')
@section('title', 'تعديل خطة: ' . $plan->name)
@section('page-title')
<h6 class="mb-0 fw-bold">تعديل خطة: {{ $plan->name }}</h6>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('super_admin.plans.update', $plan) }}" method="POST">
                    @csrf @method('PUT')

                    {{-- معلومات أساسية --}}
                    <h6 class="fw-bold text-muted mb-3 border-bottom pb-2">معلومات الخطة</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">اسم الخطة</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الحالة</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">الخطة نشطة</label>
                            </div>
                        </div>
                    </div>

                    {{-- الأسعار --}}
                    <h6 class="fw-bold text-muted mb-3 border-bottom pb-2">الأسعار</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                أدخل 0 للخطة المجانية. الأسعار بالجنيه السوداني (SDG) والدولار الأمريكي (USD)
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">شهري (SDG)</label>
                            <div class="input-group">
                                <input type="number" name="price_monthly" class="form-control"
                                    value="{{ old('price_monthly', $plan->price_monthly) }}" min="0" required>
                                <span class="input-group-text">SDG</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">سنوي (SDG)</label>
                            <div class="input-group">
                                <input type="number" name="price_yearly" class="form-control"
                                    value="{{ old('price_yearly', $plan->price_yearly) }}" min="0" required>
                                <span class="input-group-text">SDG</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">شهري (USD)</label>
                            <div class="input-group">
                                <input type="number" name="price_monthly_usd" class="form-control"
                                    value="{{ old('price_monthly_usd', $plan->price_monthly_usd) }}" min="0" step="0.01" required>
                                <span class="input-group-text">$</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">سنوي (USD)</label>
                            <div class="input-group">
                                <input type="number" name="price_yearly_usd" class="form-control"
                                    value="{{ old('price_yearly_usd', $plan->price_yearly_usd) }}" min="0" step="0.01" required>
                                <span class="input-group-text">$</span>
                            </div>
                        </div>
                    </div>

                    {{-- الحدود --}}
                    <h6 class="fw-bold text-muted mb-3 border-bottom pb-2">
                        الحدود <small class="fw-normal text-info fs-6">(-1 = غير محدود)</small>
                    </h6>
                    <div class="row g-3 mb-4">
                        @foreach([
                            ['name'=>'max_invoices_per_month', 'label'=>'الفواتير / شهر',  'icon'=>'fa-file-invoice'],
                            ['name'=>'max_customers',          'label'=>'العملاء',          'icon'=>'fa-users'],
                            ['name'=>'max_products',           'label'=>'المنتجات',         'icon'=>'fa-boxes'],
                            ['name'=>'max_users',              'label'=>'المستخدمون',       'icon'=>'fa-user-friends'],
                            ['name'=>'max_templates',          'label'=>'القوالب',          'icon'=>'fa-palette'],
                        ] as $field)
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas {{ $field['icon'] }} text-muted me-1"></i>{{ $field['label'] }}
                            </label>
                            <input type="number" name="{{ $field['name'] }}" class="form-control"
                                value="{{ old($field['name'], $plan->{$field['name']}) }}" min="-1" required>
                        </div>
                        @endforeach
                    </div>

                    {{-- الميزات --}}
                    <h6 class="fw-bold text-muted mb-3 border-bottom pb-2">الميزات المتاحة</h6>
                    <div class="row g-3 mb-4">
                        @foreach([
                            ['name'=>'excel_export',     'label'=>'تصدير Excel',          'icon'=>'fa-file-excel',   'color'=>'text-success'],
                            ['name'=>'email_send',       'label'=>'إرسال بريد إلكتروني',  'icon'=>'fa-envelope',     'color'=>'text-primary'],
                            ['name'=>'stock_management', 'label'=>'إدارة المخزون',        'icon'=>'fa-warehouse',    'color'=>'text-warning'],
                            ['name'=>'custom_templates', 'label'=>'قوالب مخصصة',          'icon'=>'fa-palette',      'color'=>'text-purple'],
                            ['name'=>'api_access',       'label'=>'API Access',            'icon'=>'fa-code',         'color'=>'text-danger'],
                        ] as $feat)
                        <div class="col-md-4">
                            <div class="form-check form-switch p-3 border rounded">
                                <input class="form-check-input" type="checkbox" name="{{ $feat['name'] }}"
                                    id="{{ $feat['name'] }}" value="1"
                                    {{ old($feat['name'], $plan->{$feat['name']}) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="{{ $feat['name'] }}">
                                    <i class="fas {{ $feat['icon'] }} {{ $feat['color'] }} me-1"></i>
                                    {{ $feat['label'] }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning fw-bold px-4">
                            <i class="fas fa-save"></i> حفظ التغييرات
                        </button>
                        <a href="{{ route('super_admin.plans') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
