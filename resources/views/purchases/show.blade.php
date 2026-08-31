@extends('layouts.app')
@section('title', 'أمر شراء - ' . $purchase->reference)
@section('page-title')
<h6 class="mb-0 fw-bold">أمر الشراء: {{ $purchase->reference }}</h6>
@endsection

@section('content')
<div class="row g-3">
    {{-- ===== العمود الرئيسي ===== --}}
    <div class="col-md-8">

        {{-- الأصناف --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">الأصناف</h6>
                <div class="d-flex gap-2">
                    <span class="badge bg-{{ $purchase->status_color }} fs-6">{{ $purchase->status_label }}</span>
                    @if(isset($purchase->payment_status))
                    <span class="badge bg-{{ $purchase->payment_status_color }}">{{ $purchase->payment_status_label }}</span>
                    @endif
                </div>
            </div>
            <div class="table-responsive">
                <table class="table mb-0" style="min-width: 600px;">
                    <thead class="table-light">
                        <tr>
                            <th>الصنف</th>
                            <th class="text-center" style="width:90px;">الكمية</th>
                            @if($purchase->returns->count() > 0)
                            <th class="text-center" style="width:90px;">مرتجع</th>
                            <th class="text-center" style="width:90px;">الصافي</th>
                            @endif
                            <th class="text-center" style="width:110px;">سعر التكلفة</th>
                            <th class="text-center" style="width:110px;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $returnedQtys = [];
                            foreach ($purchase->returns as $ret) {
                                foreach ($ret->items as $ri) {
                                    $returnedQtys[$ri->purchase_item_id] =
                                        ($returnedQtys[$ri->purchase_item_id] ?? 0) + $ri->quantity;
                                }
                            }
                        @endphp
                        @foreach($purchase->items as $item)
                        @php
                            $retQty = $returnedQtys[$item->id] ?? 0;
                            $netQty = $item->quantity - $retQty;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->product_name }}</div>
                                @if($item->product?->sku)
                                    <div class="text-muted small">SKU: {{ $item->product->sku }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                            @if($purchase->returns->count() > 0)
                            <td class="text-center">
                                @if($retQty > 0)
                                    <span class="badge bg-warning text-dark">-{{ number_format($retQty, 2) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold {{ $netQty <= 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($netQty, 2) }}
                            </td>
                            @endif
                            <td class="text-center">{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-center fw-semibold">{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">المجموع الفرعي</td>
                            <td class="text-center fw-bold">{{ number_format($purchase->subtotal, 2) }}</td>
                        </tr>
                        @if($purchase->tax_amount > 0)
                        <tr>
                            <td colspan="3" class="text-end text-muted">الضريبة</td>
                            <td class="text-center">{{ number_format($purchase->tax_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="table-primary">
                            <td colspan="3" class="text-end fw-bold fs-6">الإجمالي</td>
                            <td class="text-center fw-bold fs-6">{{ number_format($purchase->total, 2) }} {{ $purchase->currency }}</td>
                        </tr>
                        @if(($purchase->paid_amount ?? 0) > 0)
                        <tr>
                            <td colspan="3" class="text-end text-success">المدفوع</td>
                            <td class="text-center text-success fw-semibold">{{ number_format($purchase->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end text-danger">المتبقي</td>
                            <td class="text-center text-danger fw-semibold">{{ number_format($purchase->remaining_amount, 2) }}</td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ===== دفعات الموردين ===== --}}
        @if($purchase->status === 'received')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-money-bill-wave me-2 text-success"></i>
                    دفعات المورد
                    @php $totalPaid = $purchase->payments->sum('amount') @endphp
                    @if($totalPaid > 0)
                        <span class="badge bg-success ms-2">مدفوع: {{ number_format($totalPaid, 2) }}</span>
                    @endif
                </h6>
            </div>

            {{-- جدول الدفعات --}}
            @if($purchase->payments->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th>المرجع</th>
                            <th>الملاحظات</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->payments as $pmt)
                        <tr>
                            <td>{{ $pmt->payment_date->format('Y-m-d') }}</td>
                            <td class="fw-semibold text-success">{{ number_format($pmt->amount, 2) }}</td>
                            <td>{{ $pmt->payment_method }}</td>
                            <td class="text-muted small">{{ $pmt->reference_number ?? '—' }}</td>
                            <td class="text-muted small">{{ $pmt->notes ?? '—' }}</td>
                            <td>
                                @can('purchases.edit')
                                <form action="{{ route('supplier-payments.destroy', $pmt) }}" method="POST"
                                      onsubmit="return confirm('حذف الدفعة؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="card-body text-center text-muted py-3">
                    <i class="fas fa-inbox me-2"></i> لا توجد دفعات مسجّلة بعد
                </div>
            @endif

            {{-- فورم إضافة دفعة --}}
            @if($purchase->remaining_amount > 0.001)
            @can('purchases.edit')
            <div class="card-footer bg-light border-0">
                <h6 class="fw-semibold mb-3">
                    <i class="fas fa-plus-circle text-success me-1"></i>
                    إضافة دفعة جديدة
                    <span class="text-muted fw-normal small">(المتبقي: {{ number_format($purchase->remaining_amount, 2) }} {{ $purchase->currency }})</span>
                </h6>
                <form action="{{ route('supplier-payments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small">المبلغ <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control form-control-sm @error('amount') is-invalid @enderror"
                                   min="0.01" step="0.01" max="{{ $purchase->remaining_amount }}"
                                   value="{{ old('amount', number_format($purchase->remaining_amount, 2, '.', '')) }}" required>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">طريقة الدفع <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-sm @error('payment_method') is-invalid @enderror" required>
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm->code }}">{{ $pm->name }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-sm"
                                   value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">رقم المرجع</label>
                            <input type="text" name="reference_number" class="form-control form-control-sm"
                                   value="{{ old('reference_number') }}" placeholder="اختياري">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">ملاحظات</label>
                            <input type="text" name="notes" class="form-control form-control-sm"
                                   value="{{ old('notes') }}" placeholder="اختياري">
                        </div>
                        <div class="col-12 mt-1">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-save me-1"></i> تسجيل الدفعة
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endcan
            @else
                <div class="card-footer bg-light border-0 text-center text-success fw-semibold py-2">
                    <i class="fas fa-check-circle me-1"></i> تم سداد كامل المبلغ
                </div>
            @endif
        </div>
        @endif

        {{-- المرتجعات --}}
        @if($purchase->returns->count() > 0)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-undo me-2 text-danger"></i>المرتجعات ({{ $purchase->returns->count() }})</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>التاريخ</th><th>المرجع</th><th>السبب</th><th class="text-center">الإجمالي</th></tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->returns as $ret)
                        <tr>
                            <td>{{ $ret->return_date->format('Y-m-d') }}</td>
                            <td><code>{{ $ret->reference }}</code></td>
                            <td class="text-muted small">{{ $ret->reason ?? '—' }}</td>
                            <td class="text-center text-danger fw-semibold">{{ number_format($ret->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- ===== العمود الجانبي ===== --}}
    <div class="col-md-4">

        {{-- بيانات الطلب --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">بيانات الطلب</h6>
                <div class="mb-2 d-flex justify-content-between">
                    <span class="text-muted small">المورد</span>
                    <span class="fw-semibold small">{{ $purchase->supplier_name ?? '—' }}</span>
                </div>
                @if($purchase->supplier_phone)
                <div class="mb-2 d-flex justify-content-between">
                    <span class="text-muted small">الهاتف</span>
                    <span class="small">{{ $purchase->supplier_phone }}</span>
                </div>
                @endif
                <div class="mb-2 d-flex justify-content-between">
                    <span class="text-muted small">التاريخ</span>
                    <span class="small">{{ $purchase->purchase_date->format('Y-m-d') }}</span>
                </div>
                @if($purchase->warehouse)
                <div class="mb-2 d-flex justify-content-between">
                    <span class="text-muted small">المخزن</span>
                    <span class="small fw-semibold">
                        <i class="fas fa-warehouse me-1 text-primary"></i>
                        {{ $purchase->warehouse->name }}
                    </span>
                </div>
                @endif
                <div class="mb-2 d-flex justify-content-between">
                    <span class="text-muted small">العملة</span>
                    <span class="small">{{ $purchase->currency }}</span>
                </div>
                @if($purchase->notes)
                <div class="mt-2 pt-2 border-top">
                    <span class="text-muted small">ملاحظات:</span>
                    <div class="small mt-1">{{ $purchase->notes }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- ملخص مالي --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">الملخص المالي</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">الإجمالي الأصلي</span>
                    <span class="fw-bold">{{ number_format($purchase->total, 2) }}</span>
                </div>
                @if($purchase->returned_amount > 0)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small text-warning">إجمالي المرتجعات</span>
                    <span class="text-warning fw-semibold">- {{ number_format($purchase->returned_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 border-top pt-2">
                    <span class="text-muted small fw-bold">الصافي بعد المرتجعات</span>
                    <span class="fw-bold">{{ number_format($purchase->net_total, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">المدفوع</span>
                    <span class="text-success fw-semibold">{{ number_format($purchase->paid_amount ?? 0, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2">
                    <span class="text-muted small">المتبقي</span>
                    <span class="fw-bold {{ $purchase->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($purchase->remaining_amount, 2) }}
                    </span>
                </div>
                @php
                    $base = $purchase->net_total > 0 ? $purchase->net_total : $purchase->total;
                    $pct  = $base > 0 ? min(100, ($purchase->paid_amount ?? 0) / $base * 100) : 0;
                @endphp
                <div class="progress mt-2" style="height:6px">
                    <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                </div>
                <div class="text-muted small mt-1 text-center">{{ number_format($pct, 0) }}% مدفوع</div>
            </div>
        </div>

        {{-- تحديث الحالة --}}
        @if($purchase->status !== 'received')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">تحديث الحالة</h6>
                <form action="{{ route('purchases.status', $purchase) }}" method="POST">
                    @method('PATCH') @csrf
                    <div class="mb-3">
                        <select name="status" class="form-select">
                            <option value="pending"   {{ $purchase->status=='pending'?'selected':'' }}>معلق</option>
                            <option value="received"  {{ $purchase->status=='received'?'selected':'' }}>مستلم (يضاف للمخزون)</option>
                            <option value="cancelled" {{ $purchase->status=='cancelled'?'selected':'' }}>ملغي</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">تحديث</button>
                </form>
            </div>
        </div>
        @endif

        {{-- أزرار الإجراءات --}}
        <div class="d-grid gap-2">
            <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
            </a>
            <a href="{{ route('purchases.pdf', $purchase) }}" class="btn btn-outline-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> تحميل PDF
            </a>
            @if($purchase->supplier_phone || $purchase->supplier?->phone)
            <a href="{{ route('purchases.whatsapp', $purchase) }}" class="btn btn-whatsapp btn-sm" target="_blank">
                <i class="fab fa-whatsapp me-1"></i> إرسال واتساب
            </a>
            @endif
            @if($purchase->status === 'received')
            <a href="{{ route('purchase-returns.create', $purchase) }}" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-undo me-1"></i> تسجيل مرتجع
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
