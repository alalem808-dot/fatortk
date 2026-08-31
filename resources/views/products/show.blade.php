@extends('layouts.app')
@section('title', $product->name)
@section('page-title')<h6 class="mb-0 fw-bold">{{ $product->name }}</h6>@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
        <span class="badge bg-{{ $product->status === 'active' ? 'success' : 'secondary' }}">
            {{ $product->status === 'active' ? 'نشط' : 'غير نشط' }}
        </span>
        @if($product->isLowStock())
        <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>مخزون منخفض</span>
        @endif
    </div>
    <div class="d-flex gap-2">
        @can('products.edit')
        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">
            <i class="fas fa-edit me-1"></i>تعديل
        </a>
        @endcan
        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i>رجوع
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- ===== معلومات المنتج ===== --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0">معلومات المنتج</h6></div>
            <div class="card-body">
                @if($product->image)
                <img src="{{ url('storage/'.$product->image) }}" class="img-fluid rounded mb-3" style="max-height:150px;object-fit:contain">
                @endif
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted small">الكود (SKU)</th><td>{{ $product->sku ?? '—' }}</td></tr>
                    <tr><th class="text-muted small">الباركود</th><td>{{ $product->barcode ?? '—' }}</td></tr>
                    <tr><th class="text-muted small">الفئة</th><td>{{ $product->category?->name ?? '—' }}</td></tr>
                    <tr><th class="text-muted small">وحدة القياس</th><td>{{ $product->unit }}</td></tr>
                    <tr><th class="text-muted small">سعر البيع</th><td class="fw-semibold">{{ number_format($product->unit_price, 2) }}</td></tr>
                    <tr><th class="text-muted small">سعر التكلفة</th><td>{{ number_format($product->cost_price, 2) }}</td></tr>
                    <tr><th class="text-muted small">نسبة الضريبة</th><td>{{ $product->tax_rate }}%</td></tr>
                    <tr><th class="text-muted small">حد التنبيه</th><td>{{ $product->min_stock_alert }} {{ $product->unit }}</td></tr>
                </table>
            </div>
        </div>

        {{-- ===== الكميات في كل مخزن ===== --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-warehouse me-2 text-primary"></i>الكميات حسب المخزن
                </h6>
            </div>
            <div class="card-body p-0">
                @if($warehouseStocks->isEmpty())
                <div class="text-center text-muted py-4 small">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    لا توجد بيانات مخزن
                </div>
                @else
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>المخزن</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warehouseStocks as $ws)
                        <tr>
                            <td>
                                <div class="fw-semibold small">{{ $ws->warehouse?->name ?? '—' }}</div>
                                @if($ws->warehouse?->location)
                                <div class="text-muted" style="font-size:.7rem">{{ $ws->warehouse->location }}</div>
                                @endif
                                @if($ws->warehouse?->is_default)
                                <span class="badge bg-primary" style="font-size:.65rem">افتراضي</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold">
                                <span class="{{ $ws->quantity <= $product->min_stock_alert ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($ws->quantity, 2) }}
                                </span>
                                <div class="text-muted" style="font-size:.7rem">{{ $product->unit }}</div>
                            </td>
                            <td class="text-center">
                                @if($ws->quantity <= 0)
                                    <span class="badge bg-danger" style="font-size:.7rem">نفد</span>
                                @elseif($ws->quantity <= $product->min_stock_alert)
                                    <span class="badge bg-warning text-dark" style="font-size:.7rem">منخفض</span>
                                @else
                                    <span class="badge bg-success" style="font-size:.7rem">جيد</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td class="fw-bold small">الإجمالي</td>
                            <td class="text-center fw-bold">{{ number_format($product->stock_quantity, 2) }} {{ $product->unit }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== حركات المخزون ===== --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">آخر حركات المخزون</h6>
                <span class="text-muted small">آخر 20 حركة</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>النوع</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-center">قبل</th>
                            <th class="text-center">بعد</th>
                            <th>المخزن</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->stockMovements as $mov)
                        <tr>
                            <td class="small">{{ $mov->created_at->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge bg-{{ $mov->type === 'in' ? 'success' : ($mov->type === 'out' ? 'danger' : 'warning text-dark') }}" style="font-size:.75rem">
                                    {{ ['in' => 'وارد', 'out' => 'صادر', 'adjustment' => 'تسوية'][$mov->type] ?? $mov->type }}
                                </span>
                            </td>
                            <td class="text-center fw-semibold">{{ number_format($mov->quantity, 2) }}</td>
                            <td class="text-center text-muted small">{{ number_format($mov->quantity_before, 2) }}</td>
                            <td class="text-center text-muted small">{{ number_format($mov->quantity_after, 2) }}</td>
                            <td class="small text-muted">
                                @if($mov->warehouse_id)
                                    {{ \App\Models\Warehouse::find($mov->warehouse_id)?->name ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="small text-muted">{{ $mov->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">لا توجد حركات مسجّلة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
