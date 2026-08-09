@extends('layouts.app')
@section('title', 'المنتجات والمخزون')
@section('page-title')
<h6 class="mb-0 fw-bold">المنتجات والمخزون</h6>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2 flex-wrap" method="GET">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث بالاسم أو SKU..." value="{{ request('search') }}" style="width:200px">
        <select name="category_id" class="form-select form-select-sm" style="width:150px">
            <option value="">كل الفئات</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <div class="form-check form-check-inline mt-1">
            <input class="form-check-input" type="checkbox" name="low_stock" id="low_stock" value="1" {{ request('low_stock')?'checked':'' }}>
            <label class="form-check-label small" for="low_stock">مخزون منخفض فقط</label>
        </div>
        <button class="btn btn-sm btn-outline-secondary">بحث</button>
    </form>
    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> منتج جديد
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>المنتج</th><th>الفئة</th><th>SKU</th>
                    <th>سعر البيع</th><th>سعر التكلفة</th>
                    <th>المخزون</th><th>الحالة</th><th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($product->image)
                                <img src="{{ url('storage/'.$product->image) }}" width="36" height="36" class="rounded" style="object-fit:cover">
                            @else
                                <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:36px;height:36px">
                                    <i class="fas fa-box text-muted"></i>
                                </div>
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                @if($product->barcode)<div class="text-muted" style="font-size:.75rem">{{ $product->barcode }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark">{{ $product->category?->name ?? '-' }}</span></td>
                    <td class="text-muted small">{{ $product->sku ?? '-' }}</td>
                    <td class="fw-semibold">{{ number_format($product->unit_price, 2) }} <small class="text-muted">SDG</small></td>
                    <td class="text-muted">{{ number_format($product->cost_price, 2) }}</td>
                    <td>
                        <span class="badge {{ $product->isLowStock() ? 'bg-danger' : 'bg-success' }}">
                            {{ $product->stock_quantity }} {{ $product->unit }}
                        </span>
                        @if($product->isLowStock())
                            <i class="fas fa-exclamation-triangle text-warning ms-1" title="مخزون منخفض"></i>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $product->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $product->status === 'active' ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-xs btn-outline-primary" title="تعديل"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-xs btn-outline-warning" title="تعديل المخزون"
                                data-bs-toggle="modal" data-bs-target="#stockModal"
                                data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-qty="{{ $product->stock_quantity }}">
                                <i class="fas fa-boxes"></i>
                            </button>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('حذف المنتج؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">لا توجد منتجات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $products->withQueryString()->links() }}</div>
</div>

{{-- Modal تعديل المخزون --}}
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">تعديل المخزون - <span id="modalProductName"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">نوع الحركة</label>
                        <select name="type" class="form-select" required>
                            <option value="in">إضافة للمخزون</option>
                            <option value="out">خصم من المخزون</option>
                            <option value="adjustment">تعديل الرصيد</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الكمية</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                        <div class="form-text">الرصيد الحالي: <strong id="currentQty"></strong></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="notes" class="form-control" placeholder="سبب التعديل...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('stockModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('modalProductName').textContent = btn.dataset.name;
    document.getElementById('currentQty').textContent = btn.dataset.qty;
    document.getElementById('stockForm').action = `/products/${btn.dataset.id}/stock`;
});
</script>
@endpush
