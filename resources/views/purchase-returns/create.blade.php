@extends('layouts.app')
@section('title', 'مرتجع مشتريات')
@section('page-title')
<h6 class="mb-0 fw-bold">مرتجع مشتريات - {{ $purchase->reference }}</h6>
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('purchase-returns.store', $purchase) }}">
            @csrf

            <div class="row g-3 mb-4">
                {{-- تاريخ المرتجع --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">تاريخ المرتجع <span class="text-danger">*</span></label>
                    <input type="date" name="return_date" class="form-control"
                           value="{{ old('return_date', date('Y-m-d')) }}" required>
                </div>

                {{-- المخزن --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        خصم المخزون من
                        <span class="text-muted small">(المخزن الذي سيُخصم منه الكمية)</span>
                    </label>
                    <select name="warehouse_id" class="form-select">
                        <option value="">-- المخزن الافتراضي --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                {{ old('warehouse_id', $defaultWarehouse?->id) == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                                @if($warehouse->is_default)
                                    <span>(افتراضي)</span>
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @if($warehouses->isEmpty())
                        <div class="form-text text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            لا يوجد مخازن — سيتم الخصم من المخزون الإجمالي فقط.
                        </div>
                    @endif
                </div>

                {{-- السبب --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">سبب الإرجاع</label>
                    <input type="text" name="reason" class="form-control"
                           value="{{ old('reason') }}" placeholder="سبب الإرجاع (اختياري)">
                </div>
            </div>

            {{-- معلومات أمر الشراء --}}
            <div class="alert alert-info d-flex gap-3 mb-3 py-2">
                <div><span class="text-muted small">المورد:</span> <strong>{{ $purchase->supplier_name ?? '—' }}</strong></div>
                <div><span class="text-muted small">الإجمالي الحالي:</span> <strong>{{ number_format($purchase->total, 2) }} {{ $purchase->currency }}</strong></div>
                <div><span class="text-muted small">تاريخ الشراء:</span> <strong>{{ $purchase->purchase_date->format('Y-m-d') }}</strong></div>
            </div>

            {{-- جدول البنود --}}
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="returnTable">
                    <thead class="table-light">
                        <tr>
                            <th>المنتج</th>
                            <th class="text-center" style="width:130px">الكمية المشتراة</th>
                            <th class="text-center" style="width:130px">تم إرجاعه سابقاً</th>
                            <th class="text-center" style="width:130px">المتاح للإرجاع</th>
                            <th class="text-center" style="width:120px">سعر التكلفة</th>
                            <th class="text-center" style="width:160px">كمية المرتجع</th>
                            <th class="text-center" style="width:140px">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                        @php
                            $alreadyReturned = \App\Models\PurchaseReturnItem::whereHas(
                                'purchaseReturn',
                                fn($q) => $q->where('purchase_id', $purchase->id)
                            )->where('purchase_item_id', $item->id)->sum('quantity');
                            $available = max(0, $item->quantity - $alreadyReturned);
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->product_name }}</div>
                                @if($item->product)
                                    <div class="text-muted small">SKU: {{ $item->product->sku ?? '—' }}</div>
                                @endif
                                <input type="hidden" name="items[{{ $loop->index }}][purchase_item_id]" value="{{ $item->id }}">
                            </td>
                            <td class="text-center">{{ number_format($item->quantity, 3) }}</td>
                            <td class="text-center text-danger">{{ number_format($alreadyReturned, 3) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $available <= 0 ? 'bg-secondary' : 'bg-success' }}">
                                    {{ number_format($available, 3) }}
                                </span>
                            </td>
                            <td class="text-center">{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-center">
                                @if($available > 0)
                                    <input type="number"
                                           name="items[{{ $loop->index }}][quantity]"
                                           class="form-control form-control-sm return-qty text-center"
                                           min="0" max="{{ $available }}" step="0.001" value="0"
                                           data-unit-cost="{{ $item->unit_cost }}"
                                           {{ $available <= 0 ? 'disabled' : '' }}>
                                @else
                                    <span class="text-muted small">تم الإرجاع بالكامل</span>
                                    <input type="hidden" name="items[{{ $loop->index }}][quantity]" value="0">
                                @endif
                            </td>
                            <td class="text-center fw-semibold item-total" id="total-{{ $loop->index }}">
                                0.00
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end fw-bold">إجمالي المرتجع:</td>
                            <td class="text-center fw-bold text-danger" id="grand-total">0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @error('items')
                <div class="alert alert-danger py-2">{{ $message }}</div>
            @enderror

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-undo me-1"></i> تسجيل المرتجع
                </button>
                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.return-qty');

    function updateTotals() {
        let grandTotal = 0;
        inputs.forEach(function (input, index) {
            const qty      = parseFloat(input.value) || 0;
            const unitCost = parseFloat(input.dataset.unitCost) || 0;
            const lineTotal = qty * unitCost;
            grandTotal += lineTotal;

            const totalCell = document.getElementById('total-' + input.closest('tr').querySelector('input[type="hidden"]')
                .name.match(/\d+/)[0]);
            if (totalCell) totalCell.textContent = lineTotal.toFixed(2);
        });
        document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
    }

    inputs.forEach(input => input.addEventListener('input', updateTotals));
});
</script>
@endpush
@endsection
