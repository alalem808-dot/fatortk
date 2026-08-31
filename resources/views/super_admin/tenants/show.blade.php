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
                                @foreach($plans as $plan)
                                <option value="{{ $plan->slug }}" {{ $tenant->subscription_plan == $plan->slug ? 'selected' : '' }}>
                                    {{ $plan->name }}
                                    @if($plan->price_yearly_usd > 0) — ${{ number_format($plan->price_yearly_usd, 0) }}/سنة @endif
                                    @if(!$plan->is_active) (معطلة) @endif
                                </option>
                                @endforeach
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

        {{-- ميزات المشترك --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0">ميزات المشترك</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-semibold small">محور العملات وأسعار الصرف</div>
                        <div class="text-muted" style="font-size:.8rem">السماح للمشترك باستخدام عملات متعددة وأسعار الصرف في الفواتير والمشتريات</div>
                    </div>
                    <form action="{{ route('super_admin.tenants.toggle-currencies', $tenant) }}" method="POST" class="ms-3">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $tenant->currencies_enabled ? 'btn-success' : 'btn-outline-secondary' }}"
                            onclick="return confirm('{{ $tenant->currencies_enabled ? 'إلغاء تفعيل محور العملات؟' : 'تفعيل محور العملات؟' }}')">
                            <i class="fas fa-{{ $tenant->currencies_enabled ? 'toggle-on' : 'toggle-off' }}"></i>
                            {{ $tenant->currencies_enabled ? 'مفعّل' : 'معطّل' }}
                        </button>
                    </form>
                </div>
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
                        <tr><th>الاسم</th><th>البريد</th><th>الدور</th><th>آخر دخول</th><th>الحالة</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($tenant->users as $user)
                        <tr>
                            <td class="fw-semibold small">{{ $user->name }}</td>
                            <td class="text-muted small">{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->role=='admin' ? 'bg-danger' : ($user->role=='manager' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                    {{ ['admin'=>'مدير','manager'=>'مشرف','employee'=>'موظف','staff'=>'موظف'][$user->role] ?? $user->role }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $user->last_login?->format('Y-m-d H:i') ?? 'لم يدخل بعد' }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $user->is_active ? 'نشط' : 'موقوف' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetModal{{ $user->id }}">
                                    <i class="fas fa-key"></i>
                                </button>

                                <div class="modal fade" id="resetModal{{ $user->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6 class="modal-title">إعادة تعيين كلمة مرور {{ $user->name }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('super_admin.users.reset-password', $user) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">كلمة المرور الجديدة</label>
                                                        <input type="password" name="password" class="form-control" required minlength="8">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">تأكيد كلمة المرور</label>
                                                        <input type="password" name="password_confirmation" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-warning btn-sm">حفظ</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
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
@endsection
