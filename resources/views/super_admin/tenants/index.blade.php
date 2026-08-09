@extends('super_admin.layout')
@section('title', 'الشركات والحسابات')
@section('page-title')
<h6 class="mb-0 fw-bold">الشركات والحسابات</h6>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <a href="{{ route('super_admin.tenants.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>إضافة شركة جديدة
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث بالاسم أو البريد..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">كل الحالات</option>
                    <option value="active"    {{ request('status')=='active'    ?'selected':'' }}>نشط</option>
                    <option value="trial"     {{ request('status')=='trial'     ?'selected':'' }}>تجريبي</option>
                    <option value="suspended" {{ request('status')=='suspended' ?'selected':'' }}>موقوف</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="plan" class="form-select form-select-sm">
                    <option value="">كل الخطط</option>
                    <option value="free"       {{ request('plan')=='free'       ?'selected':'' }}>مجاني</option>
                    <option value="basic"      {{ request('plan')=='basic'      ?'selected':'' }}>أساسي</option>
                    <option value="pro"        {{ request('plan')=='pro'        ?'selected':'' }}>احترافي</option>
                    <option value="enterprise" {{ request('plan')=='enterprise' ?'selected':'' }}>مؤسسي</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary">بحث</button>
                <a href="{{ route('super_admin.tenants') }}" class="btn btn-sm btn-outline-secondary">إعادة</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>الشركة</th><th>البريد</th><th>المستخدمون</th><th>الفواتير</th><th>الخطة</th><th>الحالة</th><th>تاريخ التسجيل</th><th>إجراءات</th></tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                <tr>
                    <td>
                        <a href="{{ route('super_admin.tenants.show', $tenant) }}" class="fw-semibold text-decoration-none">{{ $tenant->company_name }}</a>
                        <div class="text-muted" style="font-size:.75rem">{{ $tenant->subdomain }}.fatortk.com</div>
                    </td>
                    <td class="text-muted small">{{ $tenant->email }}</td>
                    <td class="text-center"><span class="badge bg-light text-dark">{{ $tenant->users_count }}</span></td>
                    <td class="text-center"><span class="badge bg-light text-dark">{{ $tenant->invoices_count }}</span></td>
                    <td><span class="badge badge-{{ $tenant->subscription_plan }}">{{ ['free'=>'مجاني','basic'=>'أساسي','pro'=>'احترافي','enterprise'=>'مؤسسي'][$tenant->subscription_plan] }}</span></td>
                    <td><span class="badge badge-{{ $tenant->status }}">{{ ['active'=>'نشط','trial'=>'تجريبي','suspended'=>'موقوف'][$tenant->status] }}</span></td>
                    <td class="text-muted small">{{ $tenant->created_at->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('super_admin.tenants.show', $tenant) }}" class="btn btn-xs btn-outline-primary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">لا توجد حسابات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $tenants->withQueryString()->links() }}</div>
</div>
@endsection
