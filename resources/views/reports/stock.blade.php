@extends('layouts.app')
@section('title', 'تقرير المخزون')
@section('page-title')
<h6 class="mb-0 fw-bold">تقرير المخزون</h6>
@endsection

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><div class="text-muted small">إجمالي المنتجات</div><div class="fs-4 fw-bold mt-1">{{ $summary['total_products'] }}</div></div>
                <div class="icon" style="background:#dbeafe;color:#2563eb"><i class="fas fa-boxes"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><div class="text-muted small">مخزون منخفض</div><div class="fs-4 fw-bold mt-1 text-warning">{{ $summary['low_stock'] }}</div></div>
                <div class="icon" style="background:#ffedd5;color:#ea580c"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><div class="text-muted small">قيمة المخزون (بالتكلفة)</div><div class="fw-bold mt-1">{{ number_format($summary['total_value'], 2) }} SDG</div></div>
                <div class="icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>المنتج</th><th>الفئة</th><th>الكمية</th><th>حد التنبيه</th><th>سعر البيع</th><th>سعر التكلفة</th><th>قيمة المخزون</th><th>الحالة</th></tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="{{ $product->isLowStock() ? 'table-warning' : '' }}">
                    <td class="fw-semibold">{{ $product->name }}</td>
                    <td><span class="badge bg-light text-dark">{{ $product->category?->name ?? '-' }}</span></td>
                    <td>
                        <span class="badge {{ $product->stock_quantity <= 0 ? 'bg-danger' : ($product->isLowStock() ? 'bg-warning text-dark' : 'bg-success') }}">
                            {{ $product->stock_quantity }} {{ $product->unit }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $product->min_stock_alert }}</td>
                    <td>{{ number_format($product->unit_price, 2) }} SDG</td>
                    <td>{{ number_format($product->cost_price, 2) }} SDG</td>
                    <td class="fw-semibold">{{ number_format($product->stock_quantity * $product->cost_price, 2) }} SDG</td>
                    <td>
                        @if($product->stock_quantity <= 0)
                            <span class="badge bg-danger">نفد المخزون</span>
                        @elseif($product->isLowStock())
                            <span class="badge bg-warning text-dark">منخفض</span>
                        @else
                            <span class="badge bg-success">جيد</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">لا توجد منتجات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
