@extends('layouts.app')
@section('title', 'الفواتير')
@section('page-title')
<h6 class="mb-0 fw-bold">الفواتير</h6>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex gap-2" method="GET">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث..." value="{{ request('search') }}">
        <select name="status" class="form-select form-select-sm" style="width:140px">
            <option value="">كل الحالات</option>
            <option value="draft" {{ request('status')=='draft'?'selected':'' }}>مسودة</option>
            <option value="sent" {{ request('status')=='sent'?'selected':'' }}>مرسلة</option>
            <option value="paid" {{ request('status')=='paid'?'selected':'' }}>مدفوعة</option>
            <option value="overdue" {{ request('status')=='overdue'?'selected':'' }}>متأخرة</option>
        </select>
        <button class="btn btn-sm btn-outline-secondary">بحث</button>
    </form>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> فاتورة جديدة
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>رقم الفاتورة</th><th>العميل</th><th>التاريخ</th><th>الاستحقاق</th>
                    <th>المبلغ</th><th>المدفوع</th><th>الحالة</th><th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                @php $labels = ['draft'=>'مسودة','sent'=>'مرسلة','paid'=>'مدفوعة','overdue'=>'متأخرة','cancelled'=>'ملغاة'] @endphp
                <tr>
                    <td><a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">{{ $invoice->invoice_number }}</a></td>
                    <td>{{ $invoice->customer->name }}</td>
                    <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                    <td>{{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                    <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                    <td><span class="badge badge-{{ $invoice->status }}">{{ $labels[$invoice->status] }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-xs btn-outline-secondary" title="عرض"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-xs btn-outline-primary" title="تعديل"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-xs btn-outline-danger" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                            <a href="{{ route('invoices.whatsapp', $invoice) }}" class="btn btn-xs btn-whatsapp" title="واتساب" target="_blank"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">لا توجد فواتير</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $invoices->withQueryString()->links() }}</div>
</div>
@endsection
