@extends('layouts.app')
@section('title', 'تعديل المستخدم')
@section('page-title')<h6 class="mb-0 fw-bold">تعديل: {{ $user->name }}</h6>@endsection

@section('content')
<form action="{{ route('users.update', $user) }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-3">
        {{-- البيانات الأساسية --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0">البيانات الأساسية</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- أزرار الاختصار --}}
                    <hr>
                    <div class="mb-2 small text-muted fw-semibold">اختصارات الصلاحيات:</div>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="selectPreset('all')">مدير كامل</button>
                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="selectPreset('sales')">مبيعات</button>
                        <button type="button" class="btn btn-xs btn-outline-warning" onclick="selectPreset('purchasing')">مشتريات</button>
                        <button type="button" class="btn btn-xs btn-outline-info" onclick="selectPreset('accountant')">محاسب</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectPreset('warehouse')">مخزن</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectPreset('none')">مسح الكل</button>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> حفظ التعديلات</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100 mt-2">إلغاء</a>
                </div>
            </div>
        </div>

        {{-- الصلاحيات --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">الصلاحيات</h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="checkAll(true)">تحديد الكل</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAll(false)">إلغاء الكل</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($permissionGroups as $group => $perms)
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold small">{{ $group }}</span>
                                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleGroup('{{ Str::slug($group) }}')">تبديل</button>
                                </div>
                                @foreach($perms as $perm => $label)
                                <div class="form-check">
                                    <input class="form-check-input perm-check group-{{ Str::slug($group) }}"
                                        type="checkbox" name="permissions[]"
                                        value="{{ $perm }}" id="perm_{{ str_replace('.','_',$perm) }}"
                                        data-perm="{{ $perm }}"
                                        {{ in_array($perm, old('permissions', $userPermissions)) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="perm_{{ str_replace('.','_',$perm) }}">{{ $label }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- المخازن المسموح بها --}}
            @if(isset($warehouses) && $warehouses->count() > 0)
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-warehouse me-2 text-primary"></i>
                        المخازن المسموح بها
                        <span class="text-muted fw-normal small ms-1">(اتركه فارغاً للسماح بكل المخازن)</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($warehouses as $wh)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="warehouses[]" value="{{ $wh->id }}" id="wh_{{ $wh->id }}"
                                       {{ in_array($wh->id, old('warehouses', $userWarehouseIds ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="wh_{{ $wh->id }}">
                                    {{ $wh->name }}
                                    @if($wh->is_default)<span class="badge bg-primary ms-1" style="font-size:0.65rem">افتراضي</span>@endif
                                    @if($wh->location)<span class="text-muted small d-block">{{ $wh->location }}</span>@endif
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="form-text mt-2">
                        <i class="fas fa-info-circle text-info"></i>
                        فواتير البيع ستُخصم من المخزن الأول المحدد، وأوامر الشراء ستُضاف إليه.
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const presets = {
    all: @json(array_keys(array_merge(...array_values($permissionGroups)))),
    none: [],
    sales: ['invoices.view','invoices.create','invoices.edit','invoices.send','customers.view','customers.create','customers.edit','products.view','returns.view','returns.create'],
    purchasing: ['purchases.view','purchases.create','purchases.edit','suppliers.view','suppliers.create','suppliers.edit','products.view','products.create','products.edit'],
    accountant: ['invoices.view','invoices.create','invoices.edit','invoices.send','invoices.export','customers.view','expenses.view','expenses.create','expenses.edit','reports.view_sales','reports.view_profit','returns.view'],
    warehouse: ['products.view','products.edit','stocktaking.view','stocktaking.create','stocktaking.confirm','warehouses.view','warehouses.manage','reports.view_stock'],
};

function selectPreset(name) {
    const selected = presets[name] || [];
    document.querySelectorAll('.perm-check').forEach(cb => {
        cb.checked = selected.includes(cb.dataset.perm);
    });
}

function checkAll(val) {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = val);
}

function toggleGroup(group) {
    const boxes = document.querySelectorAll('.group-' + group);
    const allChecked = [...boxes].every(b => b.checked);
    boxes.forEach(b => b.checked = !allChecked);
}
</script>
@endpush
