@extends('layouts.app')
@section('title', 'أوامر الشراء')
@section('page-title')<span>أوامر الشراء</span>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2 flex-wrap" method="GET">
        <div class="input-group input-group-sm" style="width:220px">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0"
                   placeholder="بحث بالمورد أو المرجع..." value="{{ request('search') }}">
        </div>
        <select name="status" class="form-select form-select-sm" style="width:140px" onchange="this.form.submit()">
            <option value="">كل الحالات</option>
            <option value="pending"   {{ request('status')=='pending'?'selected':'' }}>معلق</option>
            <option value="received"  {{ request('status')=='received'?'selected':'' }}>مستلم</option>
            <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>ملغي</option>
        </select>
        <button class="btn btn-sm btn-outline-secondary">بحث</button>
        @if(request('search') || request('status'))
        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></a>
        @endif
    </form>
    @can('purchases.create')
    <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> أمر شراء جديد
    </a>
    @endcan
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>المرجع</th>
                    <th>المورد</th>
                    <th>المخزن</th>
                    <th>التاريخ</th>
                    <th>الإجمالي</th>
                    <th>المدفوع</th>
                    <th>حالة الدفع</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td>
                        <a href="{{ route('purchases.show', $purchase) }}" class="fw-700 text-decoration-none" style="color:var(--primary)">
                            {{ $purchase->reference }}
                        </a>
                    </td>
                    <td>
                        <div class="fw-600">{{ $purchase->supplier_name ?? '—' }}</div>
                        @if($purchase->supplier_phone)
                        <div class="text-muted" style="font-size:.72rem">{{ $purchase->supplier_phone }}</div>
                        @endif
                    </td>
                    <td class="small text-muted">
                        @if($purchase->warehouse)
                        <i class="fas fa-warehouse me-1"></i>{{ $purchase->warehouse->name }}
                        @else —
                        @endif
                    </td>
                    <td class="text-muted small">{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                    <td class="fw-700">{{ number_format($purchase->total, 2) }}</td>
                    <td>
                        @if(($purchase->paid_amount ?? 0) > 0)
                        <span class="text-success fw-600">{{ number_format($purchase->paid_amount, 2) }}</span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if(isset($purchase->payment_status))
                        <span class="badge badge-status badge-{{ $purchase->payment_status }}">
                            {{ $purchase->payment_status_label }}
                        </span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-status badge-{{ $purchase->status }}">
                            {{ $purchase->status_label }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-xs btn-outline-secondary" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('purchases.delete')
                            @if($purchase->status !== 'received')
                            <form action="{{ route('purchases.destroy', $purchase) }}" method="POST"
                                  onsubmit="return confirm('حذف أمر الشراء؟')">
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
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-shopping-cart"></i></div>
                            <h5>لا توجد أوامر شراء</h5>
                            <p>{{ request('search') || request('status') ? 'لا توجد نتائج مطابقة' : 'ابدأ بإنشاء أول أمر شراء' }}</p>
                            @if(!request('search') && !request('status'))
                            @can('purchases.create')
                            <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> أمر شراء جديد
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
    @if($purchases->hasPages())
    <div class="card-footer bg-white d-flex justify-content-center py-2">
        {{ $purchases->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
