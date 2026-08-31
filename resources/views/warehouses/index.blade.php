@extends('layouts.app')
@section('title', 'المخازن')
@section('page-title')<span>إدارة المخازن</span>@endsection

@section('content')

{{-- ===== قائمة المخازن ===== --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="fas fa-warehouse me-2 text-primary"></i>المخازن</h6>
        @can('warehouses.manage')
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
            <i class="fas fa-plus me-1"></i> مخزن جديد
        </button>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الموقع</th>
                    <th class="text-center">عدد الأصناف</th>
                    <th class="text-center">الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $wh)
                <tr>
                    <td>
                        <a href="{{ route('warehouses.show', $wh) }}" class="fw-700 text-decoration-none" style="color:var(--primary)">
                            {{ $wh->name }}
                        </a>
                        @if($wh->is_default)
                        <span class="badge bg-primary ms-1" style="font-size:.65rem">افتراضي</span>
                        @endif
                        @if($wh->location)
                        <div class="text-muted" style="font-size:.75rem"><i class="fas fa-map-marker-alt me-1"></i>{{ $wh->location }}</div>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $wh->location ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">{{ $wh->stocks_count }}</span>
                    </td>
                    <td class="text-center">
                        @can('warehouses.manage')
                        <form action="{{ route('warehouses.toggle', $wh) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-xs {{ $wh->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                {{ $wh->is_active ? 'نشط' : 'معطّل' }}
                            </button>
                        </form>
                        @else
                        <span class="badge {{ $wh->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $wh->is_active ? 'نشط' : 'معطّل' }}
                        </span>
                        @endcan
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('warehouses.show', $wh) }}" class="btn btn-xs btn-outline-secondary" title="عرض المخزون">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('warehouses.manage')
                            @if(!$wh->is_default)
                            <form action="{{ route('warehouses.default', $wh) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-xs btn-outline-warning" title="تعيين كافتراضي">
                                    <i class="fas fa-star"></i>
                                </button>
                            </form>
                            @endif
                            <button class="btn btn-xs btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editWarehouseModal"
                                    data-id="{{ $wh->id }}"
                                    data-name="{{ $wh->name }}"
                                    data-location="{{ $wh->location }}"
                                    data-notes="{{ $wh->notes }}"
                                    title="تعديل">
                                <i class="fas fa-pen"></i>
                            </button>
                            @if(!$wh->is_default)
                            <form action="{{ route('warehouses.destroy', $wh) }}" method="POST"
                                  onsubmit="return confirm('حذف المخزن {{ $wh->name }}؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-warehouse"></i></div>
                            <h5>لا توجد مخازن</h5>
                            <p>أضف مخزناً لتتمكن من إدارة المخزون</p>
                            @can('warehouses.manage')
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                                <i class="fas fa-plus me-1"></i> إضافة مخزن
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== نقل مخزون ===== --}}
@if($warehouses->count() >= 2)
@can('warehouses.manage')
<div class="card border-0 shadow-sm">
    <div class="card-header">
        <h6 class="fw-bold mb-0"><i class="fas fa-arrows-left-right me-2 text-primary"></i>نقل مخزون بين المخازن</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('warehouses.transfer') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small fw-600">من مخزن</label>
                    <select name="from_warehouse_id" class="form-select form-select-sm" required>
                        @foreach($warehouses->where('is_active', true) as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-600">إلى مخزن</label>
                    <select name="to_warehouse_id" class="form-select form-select-sm" required>
                        @foreach($warehouses->where('is_active', true) as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-600">المنتج</label>
                    <select name="product_id" class="form-select form-select-sm" required>
                        <option value="">-- اختر منتجاً --</option>
                        @foreach(\App\Models\Product::where('tenant_id', auth()->user()->tenant_id)->where('status','active')->orderBy('name')->get() as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->stock_quantity }} {{ $p->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-600">الكمية</label>
                    <input type="number" name="quantity" class="form-control form-control-sm" min="0.001" step="0.001" required placeholder="0.000">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-600">ملاحظات</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="اختياري">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary btn-sm">
                        <i class="fas fa-arrows-left-right me-1"></i> تنفيذ النقل
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan
@endif

{{-- ===== Modal إضافة مخزن ===== --}}
@can('warehouses.manage')
<div class="modal fade" id="addWarehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>إضافة مخزن جديد</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('warehouses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم المخزن <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: المخزن الرئيسي" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الموقع</label>
                        <input type="text" name="location" class="form-control" placeholder="مثال: الطابق الأول - المبنى A">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="اختياري"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_default" value="1" class="form-check-input" id="isDefaultNew">
                        <label class="form-check-label" for="isDefaultNew">تعيين كمخزن افتراضي</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Modal تعديل مخزن ===== --}}
<div class="modal fade" id="editWarehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fas fa-pen me-2 text-primary"></i>تعديل المخزن</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editWarehouseForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم المخزن <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editWhName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الموقع</label>
                        <input type="text" name="location" id="editWhLocation" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" id="editWhNotes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
document.getElementById('editWarehouseModal')?.addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    const id  = btn.dataset.id;
    document.getElementById('editWarehouseForm').action = `/warehouses/${id}`;
    document.getElementById('editWhName').value     = btn.dataset.name     || '';
    document.getElementById('editWhLocation').value = btn.dataset.location || '';
    document.getElementById('editWhNotes').value    = btn.dataset.notes    || '';
});
</script>
@endpush

@endsection
