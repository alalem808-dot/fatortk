@extends('layouts.app')
@section('title', 'تقرير المخزون')
@section('page-title')
<h6 class="mb-0 fw-bold">تقرير المخزون</h6>
@endsection

@section('content')

{{-- ملخص إجمالي --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">إجمالي المنتجات</div>
                    <div class="fs-4 fw-bold mt-1">{{ $summary['total_products'] }}</div>
                </div>
                <div class="icon" style="background:#dbeafe;color:#2563eb"><i class="fas fa-boxes"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">مخزون منخفض</div>
                    <div class="fs-4 fw-bold mt-1 text-warning">{{ $summary['low_stock'] }}</div>
                </div>
                <div class="icon" style="background:#ffedd5;color:#ea580c"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">نفد المخزون</div>
                    <div class="fs-4 fw-bold mt-1 text-danger">{{ $summary['out_of_stock'] }}</div>
                </div>
                <div class="icon" style="background:#fee2e2;color:#dc2626"><i class="fas fa-ban"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">قيمة المخزون الكلية</div>
                    <div class="fw-bold mt-1 fs-6">{{ number_format($summary['total_value'], 2) }}</div>
                </div>
                <div class="icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- فلتر المخازن --}}
@if($warehouses->count() > 1)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex align-items-center gap-3">
            <label class="fw-semibold text-nowrap mb-0">عرض مخزن:</label>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('reports.stock') }}"
                   class="btn btn-sm {{ !$selectedWarehouseId ? 'btn-primary' : 'btn-outline-secondary' }}">
                    الكل
                </a>
                @foreach($warehouses as $wh)
                    <a href="{{ route('reports.stock', ['warehouse_id' => $wh->id]) }}"
                       class="btn btn-sm {{ $selectedWarehouseId == $wh->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="fas fa-warehouse me-1"></i>{{ $wh->name }}
                        @if($wh->is_default)
                            <span class="badge bg-light text-dark ms-1">افتراضي</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </form>
    </div>
</div>
@endif

{{-- ==========================================
     عرض حسب مخزن محدد
     ========================================== --}}
@if($selectedWarehouseId)
    @php
        $selectedWarehouse = $warehouses->firstWhere('id', $selectedWarehouseId);
        $stocks = $warehouseStocks[$selectedWarehouseId] ?? collect();
    @endphp
    @if($selectedWarehouse)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-warehouse me-2 text-primary"></i>
                        {{ $selectedWarehouse->name }}
                        @if($selectedWarehouse->is_default)
                            <span class="badge bg-primary ms-2">افتراضي</span>
                        @endif
                    </h6>
                    @if($selectedWarehouse->location)
                        <div class="text-muted small mt-1"><i class="fas fa-map-marker-alt me-1"></i>{{ $selectedWarehouse->location }}</div>
                    @endif
                </div>
                <div class="text-end">
                    <div class="text-muted small">إجمالي القيمة</div>
                    <div class="fw-bold">
                        {{ number_format($stocks->sum(fn($s) => $s->quantity * ($s->product->cost_price ?? 0)), 2) }}
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>المنتج</th>
                            <th>الفئة</th>
                            <th class="text-center">الكمية في المخزن</th>
                            <th class="text-center">الكمية الكلية</th>
                            <th class="text-center">حد التنبيه</th>
                            <th class="text-center">سعر التكلفة</th>
                            <th class="text-center">قيمة المخزون</th>
                            <th class="text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                        @php $product = $stock->product; @endphp
                        @if($product)
                        <tr class="{{ $product->isLowStock() ? 'table-warning' : '' }}">
                            <td class="fw-semibold">{{ $product->name }}</td>
                            <td><span class="badge bg-light text-dark">{{ $product->category?->name ?? '—' }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ $stock->quantity <= 0 ? 'bg-danger' : 'bg-info text-dark' }}">
                                    {{ number_format($stock->quantity, 2) }} {{ $product->unit }}
                                </span>
                            </td>
                            <td class="text-center text-muted small">{{ number_format($product->stock_quantity, 2) }}</td>
                            <td class="text-center text-muted small">{{ $product->min_stock_alert }}</td>
                            <td class="text-center">{{ number_format($product->cost_price, 2) }}</td>
                            <td class="text-center fw-semibold">{{ number_format($stock->quantity * $product->cost_price, 2) }}</td>
                            <td class="text-center">
                                @if($stock->quantity <= 0)
                                    <span class="badge bg-danger">نفد</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning text-dark">منخفض</span>
                                @else
                                    <span class="badge bg-success">جيد</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">لا يوجد مخزون في هذا المخزن</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

{{-- ==========================================
     عرض الكل — الكمية الكلية أولاً ثم مخزن بمخزن
     ========================================== --}}
@else

    {{-- ===== قسم 1: الكمية الكلية لكل منتج ===== --}}
    <h6 class="fw-bold mb-3 mt-2">
        <i class="fas fa-boxes me-2 text-primary"></i>الكمية الكلية لكل منتج (جميع المخازن)
    </h6>
    <div class="card border-0 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>المنتج</th>
                        <th>الفئة</th>
                        @foreach($warehouses as $wh)
                            <th class="text-center small">{{ $wh->name }}</th>
                        @endforeach
                        <th class="text-center">الإجمالي</th>
                        <th class="text-center">حد التنبيه</th>
                        <th class="text-center">مبيع 30ي</th>
                        <th class="text-center">آخر بيع</th>
                        <th class="text-center">القيمة الكلية</th>
                        <th class="text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="{{ $product->isLowStock() ? 'table-warning' : '' }}">
                        <td class="fw-semibold">{{ $product->name }}</td>
                        <td><span class="badge bg-light text-dark">{{ $product->category?->name ?? '—' }}</span></td>
                        @foreach($warehouses as $wh)
                            @php
                                $whStockQty = ($warehouseStocks[$wh->id] ?? collect())
                                    ->firstWhere('product_id', $product->id)?->quantity ?? 0;
                            @endphp
                            <td class="text-center small {{ $whStockQty > 0 ? '' : 'text-muted' }}">
                                {{ $whStockQty > 0 ? number_format($whStockQty, 2) : '—' }}
                            </td>
                        @endforeach
                        <td class="text-center">
                            <span class="badge {{ $product->stock_quantity <= 0 ? 'bg-danger' : ($product->isLowStock() ? 'bg-warning text-dark' : 'bg-success') }}">
                                {{ number_format($product->stock_quantity, 2) }} {{ $product->unit }}
                            </span>
                        </td>
                        <td class="text-center text-muted small">{{ $product->min_stock_alert }}</td>
                        <td class="text-center small">
                            @php $sold30 = $soldQtys[$product->id] ?? 0; @endphp
                            <span class="{{ $sold30 > 0 ? 'text-success fw-semibold' : 'text-muted' }}">{{ number_format($sold30, 2) }}</span>
                        </td>
                        <td class="text-center small">
                            @php $lastSold = $lastSoldDates[$product->id] ?? null; @endphp
                            @if($lastSold)
                                @php $daysAgo = now()->diffInDays($lastSold); @endphp
                                <span class="{{ $daysAgo > 60 ? 'text-danger' : ($daysAgo > 30 ? 'text-warning' : 'text-success') }}">
                                    {{ $lastSold }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center fw-semibold">{{ number_format($product->stock_quantity * $product->cost_price, 2) }}</td>
                        <td class="text-center">
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
                    <tr><td colspan="{{ 6 + $warehouses->count() }}" class="text-center text-muted py-5">لا توجد منتجات</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="{{ 2 + $warehouses->count() }}">الإجمالي</td>
                        <td class="text-center">{{ number_format($products->sum('stock_quantity'), 2) }}</td>
                        <td></td>
                        <td class="text-center">{{ number_format($soldQtys->sum(), 2) }}</td>
                        <td></td>
                        <td class="text-center">{{ number_format($summary['total_value'], 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ===== قسم 2: تفاصيل كل مخزن ===== --}}
    @if($warehouses->count() > 0)
    <h6 class="fw-bold mb-3 mt-2">
        <i class="fas fa-layer-group me-2 text-secondary"></i>المخزون حسب المخزن
    </h6>

    @foreach($warehouses as $warehouse)
    @php
        $wStocks      = $warehouseStocks[$warehouse->id] ?? collect();
        $warehouseVal = $wStocks->sum(fn($s) => $s->quantity * ($s->product->cost_price ?? 0));
        $totalQty     = $wStocks->sum('quantity');
    @endphp
    <div class="card border-0 shadow-sm mb-3">
        {{-- رأس المخزن --}}
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3"
             data-bs-toggle="collapse" data-bs-target="#warehouse-{{ $warehouse->id }}"
             style="cursor:pointer;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-warehouse text-primary"></i>
                <span class="fw-bold">{{ $warehouse->name }}</span>
                @if($warehouse->is_default)
                    <span class="badge bg-primary">افتراضي</span>
                @endif
                @if(!$warehouse->is_active)
                    <span class="badge bg-secondary">معطّل</span>
                @endif
                @if($warehouse->location)
                    <span class="text-muted small"><i class="fas fa-map-marker-alt mx-1"></i>{{ $warehouse->location }}</span>
                @endif
            </div>
            <div class="d-flex gap-3 align-items-center">
                <div class="text-center">
                    <div class="text-muted" style="font-size:0.7rem">عدد الأصناف</div>
                    <div class="fw-bold">{{ $wStocks->count() }}</div>
                </div>
                <div class="text-center">
                    <div class="text-muted" style="font-size:0.7rem">إجمالي الكمية</div>
                    <div class="fw-bold">{{ number_format($totalQty, 2) }}</div>
                </div>
                <div class="text-center">
                    <div class="text-muted" style="font-size:0.7rem">قيمة المخزون</div>
                    <div class="fw-bold text-success">{{ number_format($warehouseVal, 2) }}</div>
                </div>
                <a href="{{ route('reports.stock', ['warehouse_id' => $warehouse->id]) }}"
                   class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation()">
                    <i class="fas fa-eye"></i>
                </a>
                <i class="fas fa-chevron-down text-muted"></i>
            </div>
        </div>

        {{-- تفاصيل المخزن (قابل للطي) --}}
        <div class="collapse show" id="warehouse-{{ $warehouse->id }}">
            @if($wStocks->isEmpty())
                <div class="card-body text-center text-muted py-3">
                    <i class="fas fa-inbox me-2"></i>لا يوجد مخزون مسجّل في هذا المخزن
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light" style="font-size:0.85rem">
                        <tr>
                            <th>المنتج</th>
                            <th>الفئة</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-center">سعر التكلفة</th>
                            <th class="text-center">قيمة المخزون</th>
                            <th class="text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wStocks as $stock)
                        @php $product = $stock->product; @endphp
                        @if($product)
                        <tr class="{{ $product->isLowStock() ? 'table-warning' : '' }}">
                            <td class="fw-semibold small">{{ $product->name }}</td>
                            <td><span class="badge bg-light text-dark">{{ $product->category?->name ?? '—' }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ $stock->quantity <= 0 ? 'bg-danger' : ($product->isLowStock() ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ number_format($stock->quantity, 2) }} {{ $product->unit }}
                                </span>
                            </td>
                            <td class="text-center small">{{ number_format($product->cost_price, 2) }}</td>
                            <td class="text-center fw-semibold small">{{ number_format($stock->quantity * $product->cost_price, 2) }}</td>
                            <td class="text-center">
                                @if($stock->quantity <= 0)
                                    <span class="badge bg-danger" style="font-size:0.7rem">نفد</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning text-dark" style="font-size:0.7rem">منخفض</span>
                                @else
                                    <span class="badge bg-success" style="font-size:0.7rem">جيد</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endforeach
    @endif

@endif

@endsection
