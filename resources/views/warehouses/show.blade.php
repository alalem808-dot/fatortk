@extends('layouts.app')
@section('title', $warehouse->name)
@section('page-title')<h6 class="mb-0 fw-bold">مخزون: {{ $warehouse->name }}</h6>@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        @if($warehouse->location)
        <span class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i>{{ $warehouse->location }}</span>
        @endif
        @if($warehouse->is_default)
        <span class="badge bg-primary">افتراضي</span>
        @endif
        @if(!$warehouse->is_active)
        <span class="badge bg-secondary">معطّل</span>
        @endif
    </div>
    <a href="{{ route('warehouses.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-right me-1"></i> العودة
    </a>
</div>

{{-- ملخص --}}
@php
    $totalItems    = $stocks->total();
    $totalValue    = \App\Models\WarehouseStock::where('warehouse_id', $warehouse->id)
        ->join('products', 'warehouse_stocks.product_id', '=', 'products.id')
        ->sum(\Illuminate\Support\Facades\DB::raw('warehouse_stocks.quantity * products.cost_price'));
    $lowStockCount = \App\Models\WarehouseStock::where('warehouse_id', $warehouse->id)
        ->join('products', 'warehouse_stocks.product_id', '=', 'products.id')
        ->whereColumn('warehouse_stocks.quantity', '<=', 'products.min_stock_alert')
        ->count();
@endphp
<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><div class="text-muted small">إجمالي الأصناف</div><div class="fs-5 fw-bold mt-1">{{ $totalItems }}</div></div>
                <div class="icon" style="background:#dbeafe;color:#2563eb"><i class="fas fa-boxes"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><div class="text-muted small">مخزون منخفض</div><div class="fs-5 fw-bold mt-1 text-warning">{{ $lowStockCount }}</div></div>
                <div class="icon" style="background:#ffedd5;color:#ea580c"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><div class="text-muted small">قيمة المخزون</div><div class="fw-bold mt-1">{{ number_format($totalValue, 2) }}</div></div>
                <div class="icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- بحث --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('warehouses.show', $warehouse) }}" class="d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control"
                       placeholder="ابحث عن منتج بالاسم أو الكود..."
                       value="{{ $search ?? '' }}">
                @if($search)
                <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </div>
            <button type="submit" class="btn btn-primary px-4">بحث</button>
        </form>
        @if($search)
        <div class="text-muted small mt-1">
            نتائج البحث عن "<strong>{{ $search }}</strong>"
        </div>
        @endif
    </div>
</div>

{{-- جدول المخزون --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>المنتج</th>
                    <th>الفئة</th>
                    <th class="text-center">الكمية في المخزن</th>
                    <th class="text-center">إجمالي المخزون</th>
                    <th class="text-center">حد التنبيه</th>
                    <th class="text-center">سعر التكلفة</th>
                    <th class="text-center">قيمة المخزون</th>
                    <th class="text-center">الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $stock)
                @php $product = $stock->product; @endphp
                @if($product)
                <tr class="{{ $stock->quantity <= $product->min_stock_alert ? 'table-warning' : '' }}">
                    <td>
                        <div class="fw-semibold">{{ $product->name }}</div>
                        @if($product->sku)
                        <div class="text-muted" style="font-size:.75rem">SKU: {{ $product->sku }}</div>
                        @endif
                    </td>
                    <td><span class="badge bg-light text-dark">{{ $product->category?->name ?? '—' }}</span></td>
                    <td class="text-center">
                        <span class="fw-bold {{ $stock->quantity <= 0 ? 'text-danger' : ($stock->quantity <= $product->min_stock_alert ? 'text-warning' : 'text-success') }}">
                            {{ number_format($stock->quantity, 2) }}
                        </span>
                        <div class="text-muted" style="font-size:.7rem">{{ $product->unit }}</div>
                    </td>
                    <td class="text-center text-muted small">{{ number_format($product->stock_quantity, 2) }}</td>
                    <td class="text-center text-muted small">{{ $product->min_stock_alert }}</td>
                    <td class="text-center small">{{ number_format($product->cost_price, 2) }}</td>
                    <td class="text-center fw-semibold small">
                        {{ number_format($stock->quantity * $product->cost_price, 2) }}
                    </td>
                    <td class="text-center">
                        @if($stock->quantity <= 0)
                            <span class="badge bg-danger">نفد</span>
                        @elseif($stock->quantity <= $product->min_stock_alert)
                            <span class="badge bg-warning text-dark">منخفض</span>
                        @else
                            <span class="badge bg-success">جيد</span>
                        @endif
                    </td>
                    <td>
                        @can('products.view')
                        <a href="{{ route('products.show', $product) }}"
                           class="btn btn-xs btn-outline-secondary" title="تفاصيل المنتج">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endcan
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        @if($search)
                            لا توجد نتائج للبحث عن "{{ $search }}"
                        @else
                            لا يوجد مخزون مسجّل في هذا المخزن
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($stocks->hasPages())
    <div class="card-footer bg-white">
        {{ $stocks->appends(['search' => $search])->links() }}
    </div>
    @endif
</div>
@endsection
