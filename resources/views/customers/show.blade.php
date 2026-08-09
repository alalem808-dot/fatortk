@extends('layouts.app')
@section('title', $customer->name)
@section('page-title')
<h6 class="mb-0 fw-bold">{{ $customer->name }}</h6>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h6 class="fw-bold mb-0">بيانات العميل</h6>
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                </div>
                <div class="mb-2"><i class="fas fa-user text-muted me-2"></i>{{ $customer->name }}</div>
                @if($customer->email)<div class="mb-2"><i class="fas fa-envelope text-muted me-2"></i>{{ $customer->email }}</div>@endif
                @if($customer->phone)<div class="mb-2"><i class="fas fa-phone text-muted me-2"></i>{{ $customer->phone }}</div>@endif
                @if($customer->whatsapp_number)
                <div class="mb-2">
                    <i class="fab fa-whatsapp text-success me-2"></i>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->whatsapp_number) }}" target="_blank" class="text-success">{{ $customer->whatsapp_number }}</a>
                </div>
                @endif
                @if($customer->address)<div class="mb-2"><i class="fas fa-map-marker-alt text-muted me-2"></i>{{ $customer->address }}</div>@endif
                @if($customer->tax_number)<div class="mb-2"><i class="fas fa-id-card text-muted me-2"></i>{{ $customer->tax_number }}</div>@endif
                @if($customer->notes)<div class="mt-3 p-2 bg-light rounded small">{{ $customer->notes }}</div>@endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">ملخص مالي</h6>
                @php
                    $totalInvoiced = $customer->invoices->sum('total_amount');
                    $totalPaid     = $customer->invoices->sum('paid_amount');
                    $totalDue      = $totalInvoiced - $totalPaid;
                @endphp
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">إجمالي الفواتير</span>
                    <span class="fw-semibold">{{ number_format($totalInvoiced, 2) }} SDG</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">المدفوع</span>
                    <span class="fw-semibold text-success">{{ number_format($totalPaid, 2) }} SDG</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">المتبقي</span>
                    <span class="fw-bold {{ $totalDue > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($totalDue, 2) }} SDG</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between">
                <h6 class="fw-bold mb-0">الفواتير</h6>
                <a href="{{ route('invoices.create') }}?customer_id={{ $customer->id }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> فاتورة جديدة
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>رقم الفاتورة</th><th>التاريخ</th><th>المبلغ</th><th>المدفوع</th><th>الحالة</th><th></th></tr>
                    </thead>
                    <tbody>
                        @php $labels = ['draft'=>'مسودة','sent'=>'مرسلة','paid'=>'مدفوعة','overdue'=>'متأخرة','cancelled'=>'ملغاة'] @endphp
                        @forelse($customer->invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">{{ $invoice->invoice_number }}</a></td>
                            <td class="small">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($invoice->total_amount, 2) }}</td>
                            <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td><span class="badge badge-{{ $invoice->status }}">{{ $labels[$invoice->status] }}</span></td>
                            <td>
                                <a href="{{ route('invoices.whatsapp', $invoice) }}" class="btn btn-xs btn-whatsapp" target="_blank" title="إرسال واتساب"><i class="fab fa-whatsapp"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">لا توجد فواتير</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
