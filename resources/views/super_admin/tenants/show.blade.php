@extends('super_admin.layout')
@section('title', $tenant->company_name)
@section('page-title')
<h6 class="mb-0 fw-bold">{{ $tenant->company_name }}</h6>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">بيانات الشركة</h6>
                <div class="mb-2 small"><span class="text-muted">الاسم:</span> <strong>{{ $tenant->company_name }}</strong></div>
                <div class="mb-2 small"><span class="text-muted">البريد:</span> {{ $tenant->email }}</div>
                <div class="mb-2 small"><span class="text-muted">الهاتف:</span> {{ $tenant->phone ?? '-' }}</div>
                <div class="mb-2 small"><span class="text-muted">النطاق:</span> {{ $tenant->subdomain }}.fatortk.com</div>
                <div class="mb-2 small"><span class="text-muted">تاريخ التسجيل:</span> {{ $tenant->created_at->format('Y-m-d') }}</div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">الإحصائيات</h6>
                @foreach([
                    ['label'=>'المستخدمون',  'value'=>$tenant->users_count],
                    ['label'=>'الفواتير',    'value'=>$tenant->invoices_count],
                    ['label'=>'المنتجات',    'value'=>$tenant->products_count],
                    ['label'=>'العملاء',     'value'=>$tenant->customers_count],
                    ['label'=>'الإيرادات',   'value'=>number_format($revenue,2).' SDG'],
                ] as $s)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">{{ $s['label'] }}</span>
                    <span class="fw-semibold small">{{ $s['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- حذف الحساب --}}
        <div class="card border-0 shadow-sm border-danger">
            <div class="card-body">
                <h6 class="fw-bold text-danger mb-2">منطقة الخطر</h6>
                <p class="text-muted small mb-3">حذف الحساب سيحذف جميع البيانات نهائياً</p>
                <form action="{{ route('super_admin.tenants.delete', $tenant) }}" method="POST"
                    onsubmit="return confirm('هل أنت متأكد من حذف هذا الحساب نهائياً؟')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm w-100"><i class="fas fa-trash"></i> حذف الحساب</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- تعديل الاشتراك --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0">إدارة الاشتراك</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('super_admin.tenants.update', $tenant) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small">حالة الحساب</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="trial"     {{ $tenant->status=='trial'     ?'selected':'' }}>تجريبي</option>
                                <option value="active"    {{ $tenant->status=='active'    ?'selected':'' }}>نشط</option>
                                <option value="suspended" {{ $tenant->status=='suspended' ?'selected':'' }}>موقوف</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">خطة الاشتراك</label>
                            <select name="subscription_plan" class="form-select form-select-sm">
                                <option value="free"       {{ $tenant->subscription_plan=='free'       ?'selected':'' }}>مجاني</option>
                                <option value="basic"      {{ $tenant->subscription_plan=='basic'      ?'selected':'' }}>أساسي - 2,500 SDG</option>
                                <option value="pro"        {{ $tenant->subscription_plan=='pro'        ?'selected':'' }}>احترافي - 6,000 SDG</option>
                                <option value="enterprise" {{ $tenant->subscription_plan=='enterprise' ?'selected':'' }}>مؤسسي - 15,000 SDG</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">تاريخ انتهاء الاشتراك</label>
                            <input type="date" name="subscription_expires_at" class="form-control form-control-sm"
                                value="{{ $tenant->subscription_expires_at?->format('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save"></i> حفظ التغييرات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- مستخدمو الشركة --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0">مستخدمو الشركة</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>الاسم</th><th>البريد</th><th>الدور</th><th>آخر دخول</th><th>الحالة</th></tr>
                    </thead>
                    <tbody>
                        @foreach($tenant->users as $user)
                        <tr>
                            <td class="fw-semibold small">{{ $user->name }}</td>
                            <td class="text-muted small">{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->role=='admin' ? 'bg-danger' : ($user->role=='manager' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                    {{ ['admin'=>'مدير','manager'=>'مشرف','employee'=>'موظف'][$user->role] }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $user->last_login?->format('Y-m-d H:i') ?? 'لم يدخل بعد' }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $user->is_active ? 'نشط' : 'موقوف' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
