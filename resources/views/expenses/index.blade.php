@extends('layouts.app')
@section('title', 'المصروفات')
@section('page-title')<span>المصروفات</span>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2 flex-wrap align-items-center" method="GET">
        <select name="category_id" class="form-select form-select-sm" style="width:150px" onchange="this.form.submit()">
            <option value="">كل الفئات</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" style="width:145px">
        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" style="width:145px">
        <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-filter me-1"></i>فلترة</button>
        @if(request()->anyFilled(['from','to','category_id']))
        <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></a>
        @endif
    </form>
    <div class="d-flex gap-2">
        @can('expenses.create')
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#catModal">
            <i class="fas fa-tags me-1"></i> الفئات
        </button>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> مصروف جديد
        </a>
        @endcan
    </div>
</div>

@if(request()->anyFilled(['from','to','category_id']))
<div class="alert alert-info py-2 small mb-3 d-flex align-items-center gap-2">
    <i class="fas fa-info-circle"></i>
    إجمالي المصروفات المفلترة:
    <strong>{{ number_format($total, 2) }}</strong>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>الوصف</th>
                    <th>الفئة</th>
                    <th class="text-center">المبلغ</th>
                    <th>طريقة الدفع</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $e)
                <tr>
                    <td class="text-muted small">{{ $e->expense_date->format('Y-m-d') }}</td>
                    <td class="fw-600">{{ $e->description }}</td>
                    <td>
                        @if($e->category)
                        <span class="badge" style="background:#f1f5f9;color:#475569">{{ $e->category->name }}</span>
                        @else <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center fw-700">
                        {{ number_format($e->amount, 2) }}
                        <span class="text-muted fw-400 small">{{ $e->currency }}</span>
                    </td>
                    <td class="text-muted small">{{ $e->payment_method }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            @can('expenses.edit')
                            <a href="{{ route('expenses.edit', $e) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                                <i class="fas fa-pen"></i>
                            </a>
                            @endcan
                            @can('expenses.delete')
                            <form action="{{ route('expenses.destroy', $e) }}" method="POST"
                                  onsubmit="return confirm('حذف المصروف؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-receipt"></i></div>
                            <h5>لا توجد مصروفات</h5>
                            <p>{{ request()->anyFilled(['from','to','category_id']) ? 'لا توجد نتائج مطابقة' : 'سجّل مصروفاتك لتتبع أرباحك' }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())
    <div class="card-footer bg-white d-flex justify-content-center py-2">
        {{ $expenses->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Modal فئات المصروفات --}}
<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fas fa-tags me-2 text-primary"></i>فئات المصروفات</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @can('expenses.create')
                <form action="{{ route('expense-categories.store') }}" method="POST" class="d-flex gap-2 mb-3">
                    @csrf
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="اسم الفئة الجديدة" required>
                    <button class="btn btn-sm btn-primary px-3">إضافة</button>
                </form>
                @endcan
                <div class="list-group list-group-flush">
                    @forelse($categories as $cat)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="fw-600 small">{{ $cat->name }}</span>
                        @can('expenses.delete')
                        <form action="{{ route('expense-categories.destroy', $cat) }}" method="POST"
                              onsubmit="return confirm('حذف الفئة؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger"><i class="fas fa-times"></i></button>
                        </form>
                        @endcan
                    </div>
                    @empty
                    <div class="text-muted small text-center py-3">لا توجد فئات بعد</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
