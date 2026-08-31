@extends('layouts.app')
@section('title', 'المنتجات')
@section('page-title')<span>المنتجات والمخزون</span>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2 flex-wrap align-items-center" method="GET">
        <div class="input-group input-group-sm" style="width:210px">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0"
                   placeholder="بحث بالاسم أو SKU..." value="{{ request('search') }}">
        </div>
        <select name="category_id" class="form-select form-select-sm" style="width:150px">
            <option value="">كل الفئات</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" name="low_stock" id="low_stock"
                   value="1" {{ request('low_stock')?'checked':'' }}>
            <label class="form-check-label small" for="low_stock">منخفض فقط</label>
        </div>
        <button class="btn btn-sm btn-outline-secondary">بحث</button>
        @if(request('search') || request('category_id') || request('low_stock'))
        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></a>
        @endif
    </form>
    @can('products.create')
    <div class="d-flex gap-2">
        <a href="{{ route('products.import') }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-import me-1"></i> استيراد Excel
        </a>
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> منتج جديد
        </a>
    </div>
    @endcan
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>الفئة</th>
                    <th>SKU</th>
                    <th class="text-center">سعر البيع</th>
                    <th class="text-center">التكلفة</th>
                    <th class="text-center">المخزون</th>
                    <th class="text-center">الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="{{ $product->isLowStock() && $product->stock_quantity > 0 ? 'table-warning bg-opacity-50' : ($product->stock_quantity <= 0 ? 'table-danger bg-opacity-25' : '') }}">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($product->image)
                                <img src="{{ url('storage/'.$product->image) }}" width="38" height="38"
                                     class="rounded" style="object-fit:cover;border:1px solid #e2e8f0">
                            @else
                                <div class="rounded d-flex align-items-center justify-content-center"
                                     style="width:38px;height:38px;background:var(--primary-light);color:var(--primary);font-size:1rem;flex-shrink:0">
                                    <i class="fas fa-box"></i>
                                </div>
                            @endif
                            <div>
                                @can('products.view')
                                <a href="{{ route('products.show', $product) }}" class="fw-700 text-decoration-none" style="color:var(--primary)">
                                    {{ $product->name }}
                                </a>
                                @else
                                <span class="fw-700">{{ $product->name }}</span>
                                @endcan
                                @if($product->barcode)
                                <div class="text-muted" style="font-size:.72rem">{{ $product->barcode }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($product->category)
                        <span class="badge" style="background:#f1f5f9;color:#475569">{{ $product->category->name }}</span>
                        @else <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $product->sku ?? '—' }}</td>
                    <td class="text-center fw-700">
                        {{ number_format($product->unit_price, 2) }}
                    </td>
                    <td class="text-center text-muted small">{{ number_format($product->cost_price, 2) }}</td>
                    <td class="text-center">
                        <span class="badge badge-status {{ $product->stock_quantity <= 0 ? 'bg-danger text-white' : ($product->isLowStock() ? 'bg-warning text-dark' : 'bg-success text-white') }}">
                            {{ number_format($product->stock_quantity, 2) }} {{ $product->unit }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $product->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $product->status === 'active' ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @can('products.view')
                            <a href="{{ route('products.show', $product) }}" class="btn btn-xs btn-outline-info" title="تفاصيل المخازن">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                            @can('products.edit')
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                                <i class="fas fa-pen"></i>
                            </a>
                            <button class="btn btn-xs btn-outline-warning" title="تعديل المخزون"
                                    data-bs-toggle="modal" data-bs-target="#stockModal"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-qty="{{ $product->stock_quantity }}">
                                <i class="fas fa-boxes-stacked"></i>
                            </button>
                            @endcan
                            @can('products.delete')
                            <form action="{{ route('products.destroy', $product) }}" method="POST"
                                  onsubmit="return confirm('حذف المنتج؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-boxes-stacked"></i></div>
                            <h5>لا توجد منتجات</h5>
                            <p>{{ request()->anyFilled(['search','category_id','low_stock']) ? 'لا توجد نتائج مطابقة' : 'ابدأ بإضافة منتجاتك' }}</p>
                            @if(!request()->anyFilled(['search','category_id','low_stock']))
                            @can('products.create')
                            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> منتج جديد
                            </a>
                            @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-white d-flex justify-content-center py-2">
        {{ $products->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Modal تعديل المخزون --}}
@can('products.edit')
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-boxes-stacked me-2 text-primary"></i>
                    تعديل المخزون — <span id="modalProductName"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-warehouse me-1"></i>
                        الرصيد الحالي: <strong id="currentQty"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع الحركة <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="in">إضافة للمخزون ↑</option>
                            <option value="out">خصم من المخزون ↓</option>
                            <option value="adjustment">تعديل الرصيد مباشرة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الكمية <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="0.001" step="0.001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="notes" class="form-control" placeholder="سبب التعديل...">
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
document.getElementById('stockModal')?.addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('modalProductName').textContent = btn.dataset.name;
    document.getElementById('currentQty').textContent       = btn.dataset.qty;
    document.getElementById('stockForm').action = `/products/${btn.dataset.id}/stock`;
});
</script>
@endpush

@endsection
