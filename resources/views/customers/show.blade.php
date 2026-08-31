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
                    <span class="fw-semibold">{{ number_format($totalInvoiced, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">المدفوع</span>
                    <span class="fw-semibold text-success">{{ number_format($totalPaid, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">المتبقي</span>
                    <span class="fw-bold {{ $totalDue > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($totalDue, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- سداد على المتأخرات الكلية --}}
        @if($totalDue > 0)
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header">
                <h6 class="fw-bold mb-0"><i class="fas fa-money-bill-wave me-2 text-success"></i>سداد على المتأخرات</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2 mb-3" style="font-size:.8rem">
                    <i class="fas fa-info-circle me-1"></i>
                    سيُوزّع المبلغ تلقائياً من الأقدم للأحدث
                </div>
                <form action="{{ route('customers.bulk-payment', $customer) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-bold">المبلغ <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="amount" class="form-control"
                                   min="0.01" step="0.01" max="{{ $totalDue }}"
                                   placeholder="0.00" required>
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="this.previousElementSibling.value='{{ $totalDue }}'"
                                    title="سداد الكل">الكل</button>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">طريقة الدفع <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select form-select-sm" required>
                            @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->code }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">التاريخ <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control form-control-sm"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ملاحظات</label>
                        <input type="text" name="notes" class="form-control form-control-sm"
                               placeholder="اختياري">
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="fas fa-check-circle me-1"></i> تأكيد السداد
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        {{-- كشف الحساب المفصّل --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between">
                <h6 class="fw-bold mb-0"><i class="fas fa-list-alt me-2 text-primary"></i>كشف الحساب</h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('customers.export.pdf', $customer) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </a>
                    <a href="{{ route('customers.export.excel', $customer) }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </a>
                    <a href="{{ route('invoices.create') }}?customer_id={{ $customer->id }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> فاتورة جديدة
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>البيان</th>
                            <th class="text-danger">مدين</th>
                            <th class="text-success">دائن</th>
                            <th>الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledger as $row)
                        <tr class="{{ $row['type'] === 'payment' ? 'table-success bg-opacity-25' : '' }}">
                            <td class="small">{{ \Carbon\Carbon::parse($row['date'])->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ $row['ref'] }}" class="text-decoration-none small">{{ $row['description'] }}</a>
                            </td>
                            <td class="text-danger fw-semibold">
                                {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}
                            </td>
                            <td class="text-success fw-semibold">
                                {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}
                            </td>
                            <td class="{{ $row['balance'] > 0 ? 'text-danger' : 'text-success' }} fw-bold small">
                                {{ number_format($row['balance'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">لا توجد حركات</td></tr>
                        @endforelse
                    </tbody>
                    @if($ledger->count())
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="2" class="fw-bold text-end">الإجمالي</td>
                            <td class="text-danger fw-bold">{{ number_format($ledger->sum('debit'), 2) }}</td>
                            <td class="text-success fw-bold">{{ number_format($ledger->sum('credit'), 2) }}</td>
                            <td class="{{ $ledger->last()['balance'] > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                {{ number_format($ledger->last()['balance'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
