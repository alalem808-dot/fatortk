@extends('layouts.app')
@section('title', 'الفواتير')
@section('page-title')<span>الفواتير</span>@endsection

@section('content')
@php
$statusLabels = ['draft'=>'مسودة','sent'=>'مرسلة','paid'=>'مدفوعة','partially_paid'=>'جزئي','overdue'=>'متأخرة','cancelled'=>'ملغاة','returned'=>'مرتجعة'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2 flex-wrap" method="GET">
        <div class="input-group input-group-sm" style="width:220px">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0"
                   placeholder="بحث برقم الفاتورة أو العميل..." value="{{ request('search') }}">
        </div>
        <select name="status" class="form-select form-select-sm" style="width:150px">
            <option value="">كل الحالات</option>
            @foreach($statusLabels as $val => $lbl)
            <option value="{{ $val }}" {{ request('status')==$val?'selected':'' }}>{{ $lbl }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-filter me-1"></i>فلترة
        </button>
        @if(request('search') || request('status'))
        <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-danger">
            <i class="fas fa-times"></i>
        </a>
        @endif
    </form>
    @can('invoices.create')
    <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> فاتورة جديدة
    </a>
    @endcan
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>العميل</th>
                    <th>التاريخ</th>
                    <th>الاستحقاق</th>
                    <th>الإجمالي</th>
                    <th>المدفوع</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td>
                        <a href="{{ route('invoices.show', $invoice) }}" class="fw-700 text-decoration-none" style="color:var(--primary)">
                            {{ $invoice->invoice_number }}
                        </a>
                    </td>
                    <td>
                        <div class="fw-600">{{ $invoice->customer->name }}</div>
                    </td>
                    <td class="text-muted small">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                    <td class="text-muted small">
                        @if($invoice->due_date)
                            <span class="{{ $invoice->due_date->isPast() && !in_array($invoice->status, ['paid','cancelled','returned']) ? 'text-danger fw-600' : '' }}">
                                {{ $invoice->due_date->format('Y-m-d') }}
                            </span>
                        @else —
                        @endif
                    </td>
                    <td class="fw-700">
                        {{ number_format($invoice->total_amount, 2) }}
                        <span class="text-muted small fw-400">{{ $invoice->currency }}</span>
                    </td>
                    <td>
                        @if($invoice->paid_amount > 0)
                        <span class="text-success fw-600">{{ number_format($invoice->paid_amount, 2) }}</span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-status badge-{{ $invoice->status }}">
                            {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-xs btn-outline-secondary" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('invoices.edit')
                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                                <i class="fas fa-pen"></i>
                            </a>
                            @endcan
                            <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-xs btn-outline-danger" title="PDF" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            <a href="{{ route('invoices.whatsapp', $invoice) }}" class="btn btn-xs btn-whatsapp" title="واتساب" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-file-invoice"></i></div>
                            <h5>لا توجد فواتير</h5>
                            <p>{{ request('search') || request('status') ? 'لا توجد نتائج مطابقة للبحث' : 'ابدأ بإنشاء أول فاتورة' }}</p>
                            @if(!request('search') && !request('status'))
                            @can('invoices.create')
                            <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> فاتورة جديدة
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
    @if($invoices->hasPages())
    <div class="card-footer bg-white d-flex justify-content-center py-2">
        {{ $invoices->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
