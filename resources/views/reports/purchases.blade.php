@extends('layouts.app')
@section('title', 'تقرير المشتريات')
@section('page-title')<h6 class="mb-0 fw-bold">تقرير المشتريات</h6>@endsection
@section('content')

<form class="d-flex gap-2 mb-4 flex-wrap" method="GET">
    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}" style="width:150px">
    <input type="date" name="to"   class="form-control form-control-sm" value="{{ $to }}"   style="width:150px">
    <select name="supplier_id" class="form-select form-select-sm" style="width:180px">
        <option value="">كل الموردين</option>
        @foreach($suppliers as $s)
        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
    </select>
    <select name="status" class="form-select form-select-sm" style="width:130px">
        <option value="">كل الحالات</option>
        <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>معلق</option>
        <option value="received"  {{ request('status') === 'received'  ? 'selected' : '' }}>مستلم</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغي</option>
    </select>
    <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-search"></i> تطبيق</button>
</form>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="text-muted small mb-1">إجمالي المشتريات</div>
            <div class="fs-5 fw-bold">{{ number_format($summary['total'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="text-muted small mb-1">المستلمة</div>
            <div class="fs-5 fw-bold text-success">{{ number_format($summary['received'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="text-muted small mb-1">المعلقة</div>
            <div class="fs-5 fw-bold text-warning">{{ number_format($summary['pending'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="text-muted small mb-1">عدد الأوامر</div>
            <div class="fs-5 fw-bold">{{ $summary['count'] }}</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>المرجع</th><th>المورد</th><th>التاريخ</th><th>الإجمالي</th><th>العملة</th><th>الحالة</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($purchases as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->reference }}</td>
                    <td>{{ $p->supplier_name ?? '—' }}</td>
                    <td class="small">{{ $p->purchase_date->format('Y-m-d') }}</td>
                    <td>{{ number_format($p->total, 2) }}</td>
                    <td>{{ $p->currency }}</td>
                    <td><span class="badge bg-{{ $p->status_color }}">{{ $p->status_label }}</span></td>
                    <td><a href="{{ route('purchases.show', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">لا توجد مشتريات في هذه الفترة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
