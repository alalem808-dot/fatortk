@extends('layouts.app')
@section('title', $supplier->name)
@section('page-title')<h6 class="mb-0 fw-bold">{{ $supplier->name }}</h6>@endsection
@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">بيانات المورد</h6>
                @foreach([['الهاتف',$supplier->phone],['البريد',$supplier->email],['العنوان',$supplier->address],['الرقم الضريبي',$supplier->tax_number]] as [$label,$val])
                @if($val)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">{{ $label }}</span>
                    <span class="small">{{ $val }}</span>
                </div>
                @endif
                @endforeach
                @if($supplier->notes)
                <div class="mt-2 p-2 bg-light rounded small">{{ $supplier->notes }}</div>
                @endif
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary w-100">تعديل</a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">الإحصائيات المالية</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">إجمالي المشتريات</span>
                    <span class="fw-bold">{{ number_format($stats['total'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">المدفوع للمورد</span>
                    <span class="fw-bold text-success">{{ number_format($stats['paid'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">المرتجعات</span>
                    <span class="fw-bold text-info">{{ number_format($stats['returned'], 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">عدد الأوامر</span>
                    <span class="badge bg-secondary">{{ $stats['count'] }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">معلقة</span>
                    <span class="badge bg-warning text-dark">{{ $stats['pending'] }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">الرصيد المستحق</span>
                    <span class="fw-bold {{ $stats['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($stats['balance'], 2) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- دفعة سريعة للمورد --}}
        @if($stats['balance'] > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="fw-bold mb-0"><i class="fas fa-money-bill-wave me-2 text-success"></i>دفعة للمورد</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning py-2 mb-3" style="font-size:.8rem">
                    <i class="fas fa-triangle-exclamation me-1"></i>
                    الرصيد المستحق: <strong>{{ number_format($stats['balance'], 2) }}</strong>
                </div>
                {{-- نجد أول أمر شراء مستلم وغير مدفوع بالكامل --}}
                @php
                    $unpaidPurchase = $supplier->purchases
                        ->where('status', 'received')
                        ->filter(fn($p) => $p->remaining_amount > 0.001)
                        ->sortBy('purchase_date')
                        ->first();
                @endphp
                @if($unpaidPurchase)
                <form action="{{ route('supplier-payments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="purchase_id" value="{{ $unpaidPurchase->id }}">
                    <div class="mb-2">
                        <label class="form-label small fw-bold">المبلغ <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="amount" class="form-control"
                                   min="0.01" step="0.01"
                                   max="{{ $unpaidPurchase->remaining_amount }}"
                                   placeholder="0.00" required>
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="this.previousElementSibling.value='{{ $unpaidPurchase->remaining_amount }}'"
                                    title="الكل">الكل</button>
                        </div>
                        <div class="form-text">أمر الشراء: {{ $unpaidPurchase->reference }} — متبقي: {{ number_format($unpaidPurchase->remaining_amount, 2) }}</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">طريقة الدفع <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select form-select-sm" required>
                            @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->code }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">التاريخ <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control form-control-sm"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="fas fa-check-circle me-1"></i> تسجيل الدفعة
                    </button>
                </form>
                @else
                <div class="text-muted small text-center py-2">لا توجد أوامر شراء مستلمة غير مسددة</div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-list-alt me-2 text-primary"></i>كشف الحساب</h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('suppliers.export.pdf', $supplier) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </a>
                    <a href="{{ route('suppliers.export.excel', $supplier) }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </a>
                    <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> أمر شراء</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>البيان</th>
                            <th>النوع</th>
                            <th class="text-danger">مدين</th>
                            <th class="text-success">دائن</th>
                            <th>الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledger as $row)
                        @php
                            $rowClass = match($row['type']) {
                                'return'  => 'table-info',
                                'payment' => 'table-success',
                                default   => '',
                            };
                            $typeLabel = match($row['type']) {
                                'purchase' => '<span class="badge bg-primary">شراء</span>',
                                'return'   => '<span class="badge bg-info text-dark">مرتجع</span>',
                                'payment'  => '<span class="badge bg-success">دفعة</span>',
                                default    => '',
                            };
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="small">{{ \Carbon\Carbon::parse($row['date'])->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ $row['ref'] }}" class="text-decoration-none small">{{ $row['description'] }}</a>
                            </td>
                            <td>{!! $typeLabel !!}</td>
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
                        <tr><td colspan="6" class="text-center text-muted py-4">لا توجد حركات</td></tr>
                        @endforelse
                    </tbody>
                    @if($ledger->count())
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="fw-bold text-end">الإجمالي</td>
                            <td class="text-danger fw-bold">{{ number_format($ledger->sum('debit'), 2) }}</td>
                            <td class="text-success fw-bold">{{ number_format($ledger->sum('credit'), 2) }}</td>
                            <td class="{{ ($ledger->last()['balance'] ?? 0) > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                {{ number_format($ledger->last()['balance'] ?? 0, 2) }}
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
