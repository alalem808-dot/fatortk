@extends('layouts.app')
@section('title', $stocktaking->name)
@section('page-title')<h6 class="mb-0 fw-bold">{{ $stocktaking->name }}</h6>@endsection
@section('content')

<div class="d-flex gap-2 mb-3 align-items-center flex-wrap">
    <span class="badge {{ $stocktaking->status === 'confirmed' ? 'bg-success' : 'bg-warning text-dark' }} fs-6">
        {{ $stocktaking->status === 'confirmed' ? 'مؤكد' : 'مسودة' }}
    </span>
    <span class="text-muted small"><i class="fas fa-calendar me-1"></i>{{ $stocktaking->date->format('Y-m-d') }}</span>
    @if($stocktaking->warehouse)
    <span class="badge bg-primary">
        <i class="fas fa-warehouse me-1"></i>{{ $stocktaking->warehouse->name }}
    </span>
    @endif
    @if($stocktaking->status === 'draft')
    <form action="{{ route('stocktaking.confirm', $stocktaking) }}" method="POST" class="ms-auto"
        onsubmit="return confirm('اعتماد الجرد وتطبيق التسويات على مخزن {{ $stocktaking->warehouse?->name }}؟ لا يمكن التراجع.')">
        @csrf
        <button class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i> اعتماد الجرد</button>
    </form>
    @endif
</div>

@if($stocktaking->status === 'draft')
<form action="{{ route('stocktaking.update', $stocktaking) }}" method="POST">
    @csrf
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0">أصناف الجرد ({{ $stocktaking->items->count() }})</h6>
            @if($stocktaking->warehouse)
            <div class="text-muted small mt-1">
                <i class="fas fa-warehouse me-1"></i>مخزن: {{ $stocktaking->warehouse->name }}
            </div>
            @endif
        </div>
        <div class="d-flex gap-2 align-items-center">
            {{-- بحث سريع --}}
            <input type="text" id="itemSearch" class="form-control form-control-sm" style="width:200px"
                   placeholder="بحث عن منتج..." oninput="filterItems(this.value)">
            @if($stocktaking->status === 'draft')
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> حفظ الكميات</button>
            @endif
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" id="itemsTable">
            <thead class="table-light">
                <tr>
                    <th>المنتج</th>
                    <th class="text-center">وحدة القياس</th>
                    <th class="text-center">كمية النظام</th>
                    <th class="text-center">الكمية الفعلية</th>
                    <th class="text-center">الفرق</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stocktaking->items as $item)
                <tr class="item-row" data-name="{{ strtolower($item->product->name ?? '') }}">
                    <td class="fw-semibold">
                        {{ $item->product->name ?? '—' }}
                        @if($item->product?->sku)
                        <span class="text-muted small d-block">SKU: {{ $item->product->sku }}</span>
                        @endif
                    </td>
                    <td class="text-center text-muted small">{{ $item->product?->unit ?? '—' }}</td>
                    <td class="text-center">{{ number_format($item->system_qty, 2) }}</td>
                    <td class="text-center" style="width:160px">
                        @if($stocktaking->status === 'draft')
                        <input type="number" name="items[{{ $item->id }}][actual_qty]"
                            value="{{ $item->actual_qty }}" class="form-control form-control-sm text-center actual-input"
                            min="0" step="0.001" required
                            oninput="calcDiff(this, {{ $item->system_qty }}, {{ $item->id }})">
                        @else
                        {{ number_format($item->actual_qty, 2) }}
                        @endif
                    </td>
                    <td class="text-center fw-bold" id="diff_{{ $item->id }}">
                        @php $diff = $item->difference; @endphp
                        <span class="{{ $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted') }}">
                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($stocktaking->status === 'draft')
</form>
@endif

@if($stocktaking->notes)
<div class="alert alert-light mt-3 small"><i class="fas fa-sticky-note me-1"></i> {{ $stocktaking->notes }}</div>
@endif

@push('scripts')
<script>
function filterItems(val) {
    const q = val.toLowerCase();
    document.querySelectorAll('#itemsTable .item-row').forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
}

function calcDiff(input, systemQty, itemId) {
    const actual = parseFloat(input.value) || 0;
    const diff = actual - systemQty;
    const cell = document.getElementById('diff_' + itemId);
    if (cell) {
        const span = cell.querySelector('span') || cell;
        const formatted = (diff > 0 ? '+' : '') + diff.toFixed(2);
        cell.innerHTML = `<span class="${diff > 0 ? 'text-success' : diff < 0 ? 'text-danger' : 'text-muted'}">${formatted}</span>`;
    }
}
</script>
@endpush
@endsection
