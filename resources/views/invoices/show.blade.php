@extends('layouts.app')
@section('title', $invoice->language == 'ar' ? 'فاتورة ' . $invoice->invoice_number : 'Invoice ' . $invoice->invoice_number)
@section('page-title')
<h6 class="mb-0 fw-bold">{{ $invoice->language == 'ar' ? 'فاتورة رقم:' : 'Invoice #' }} {{ $invoice->invoice_number }}</h6>
@endsection

@section('content')
@php
$isArabic = $invoice->language == 'ar';
$labels = $isArabic
    ? ['draft'=>'مسودة','sent'=>'مرسلة','paid'=>'مدفوعة','overdue'=>'متأخرة','cancelled'=>'ملغاة']
    : ['draft'=>'Draft','sent'=>'Sent','paid'=>'Paid','overdue'=>'Overdue','cancelled'=>'Cancelled'];
$statusColors = ['draft'=>'secondary','sent'=>'primary','paid'=>'success','overdue'=>'warning','cancelled'=>'dark'];
$txt = $isArabic ? [
    'invoice'=>'فاتورة','bill_to'=>'فاتورة إلى','invoice_date'=>'تاريخ الفاتورة',
    'due_date'=>'تاريخ الاستحقاق','description'=>'الوصف','quantity'=>'الكمية',
    'price'=>'السعر','tax'=>'الضريبة','total'=>'الإجمالي','subtotal'=>'المجموع الفرعي',
    'tax_amount'=>'الضريبة','discount'=>'الخصم','grand_total'=>'الإجمالي الكلي',
    'paid'=>'المدفوع','remaining'=>'المتبقي','notes'=>'ملاحظات','terms'=>'الشروط والأحكام',
    'payments'=>'المدفوعات','no_payments'=>'لا توجد مدفوعات','register_payment'=>'تسجيل دفع',
    'amount'=>'المبلغ','cash'=>'نقدي','bank'=>'تحويل بنكي','card'=>'بطاقة','cheque'=>'شيك',
    'edit'=>'تعديل','download_pdf'=>'تحميل PDF','send_whatsapp'=>'إرسال واتساب',
    'send_email'=>'إرسال بريد','delete'=>'حذف','confirm_delete'=>'هل أنت متأكد؟',
    'cancel'=>'إلغاء','send'=>'إرسال',
] : [
    'invoice'=>'INVOICE','bill_to'=>'Bill To','invoice_date'=>'Invoice Date',
    'due_date'=>'Due Date','description'=>'Description','quantity'=>'Qty',
    'price'=>'Unit Price','tax'=>'Tax','total'=>'Total','subtotal'=>'Subtotal',
    'tax_amount'=>'Tax Amount','discount'=>'Discount','grand_total'=>'Grand Total',
    'paid'=>'Paid','remaining'=>'Balance Due','notes'=>'Notes','terms'=>'Terms & Conditions',
    'payments'=>'Payments','no_payments'=>'No payments yet','register_payment'=>'Add Payment',
    'amount'=>'Amount','cash'=>'Cash','bank'=>'Bank Transfer','card'=>'Card','cheque'=>'Cheque',
    'edit'=>'Edit','download_pdf'=>'Download PDF','send_whatsapp'=>'Send WhatsApp',
    'send_email'=>'Send Email','delete'=>'Delete','confirm_delete'=>'Are you sure?',
    'cancel'=>'Cancel','send'=>'Send',
];
@endphp

{{-- أزرار الإجراءات --}}
<div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-edit"></i> {{ $txt['edit'] }}
    </a>
    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-danger" target="_blank">
        <i class="fas fa-file-pdf"></i> {{ $txt['download_pdf'] }}
    </a>
    <a href="{{ route('invoices.whatsapp', $invoice) }}" class="btn btn-sm btn-whatsapp" target="_blank">
        <i class="fab fa-whatsapp"></i> {{ $txt['send_whatsapp'] }}
    </a>
    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#emailModal">
        <i class="fas fa-envelope"></i> {{ $txt['send_email'] }}
    </button>

    <div class="dropdown">
        <button class="btn btn-sm btn-{{ $statusColors[$invoice->status] }} dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-tag"></i> {{ $labels[$invoice->status] }}
        </button>
        <ul class="dropdown-menu">
            @foreach($labels as $val => $lbl)
            @if($val !== $invoice->status)
            <li>
                <form action="{{ route('invoices.status', $invoice) }}" method="POST" class="m-0">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $val }}">
                    <button class="dropdown-item">{{ $lbl }}</button>
                </form>
            </li>
            @endif
            @endforeach
        </ul>
    </div>

    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline ms-auto"
        onsubmit="return confirm('{{ $txt['confirm_delete'] }}')">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i> {{ $txt['delete'] }}</button>
    </form>
</div>

<div class="row g-3">
    {{-- الفاتورة الرئيسية --}}
    <div class="col-md-8">
        <div class="card border-0 shadow" style="border-radius:16px;overflow:hidden;">

            {{-- شريط اللون العلوي --}}
            <div style="height:6px;background:linear-gradient(90deg,#1e40af,#3b82f6);"></div>

            <div class="card-body p-4" @if(!$isArabic) dir="ltr" @endif>

                {{-- رأس الفاتورة: شعار + بيانات الشركة + رقم الفاتورة --}}
                <div class="row align-items-start mb-4 pb-4 border-bottom g-3">
                    <div class="col-12 col-md-7 d-flex align-items-center gap-3">
                        @if($invoice->tenant->logo)
                            <img src="{{ url('storage/'.$invoice->tenant->logo) }}" height="60"
                                style="border-radius:10px;object-fit:contain;border:1px solid #e2e8f0;flex-shrink:0;" alt="Logo">
                        @else
                            <div class="d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                style="width:60px;height:60px;border-radius:10px;background:linear-gradient(135deg,#1e40af,#3b82f6);font-size:1.8rem;">
                                {{ Str::upper(Str::substr($invoice->tenant->company_name, 0, 1)) }}
                            </div>
                        @endif
                        <div style="min-width:0;">
                            <h5 class="fw-bold mb-1 text-primary text-truncate">{{ $invoice->tenant->company_name }}</h5>
                            @if($invoice->tenant->address)
                            <div class="text-muted small text-truncate"><i class="fas fa-map-marker-alt me-1"></i>{{ $invoice->tenant->address }}</div>
                            @endif
                            @if($invoice->tenant->phone)
                            <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $invoice->tenant->phone }}</div>
                            @endif
                            @if($invoice->tenant->email)
                            <div class="text-muted small text-truncate"><i class="fas fa-envelope me-1"></i>{{ $invoice->tenant->email }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-5 text-md-end">
                        <h2 class="fw-black text-primary mb-2">{{ $txt['invoice'] }}</h2>
                        <div class="bg-light rounded px-3 py-2 d-inline-block">
                            <div class="text-muted small">{{ $isArabic ? 'رقم الفاتورة' : 'Invoice No.' }}</div>
                            <div class="fw-bold fs-5"># {{ $invoice->invoice_number }}</div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-{{ $statusColors[$invoice->status] }} fs-6 px-3 py-2">
                                {{ $labels[$invoice->status] }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- بيانات العميل والتواريخ --}}
                <div class="row mb-4 g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background:#f8faff;border:1px solid #dbeafe;">
                            <div class="text-primary small fw-bold mb-2 text-uppercase">{{ $txt['bill_to'] }}</div>
                            <div class="fw-bold fs-5 mb-1">{{ $invoice->customer->name }}</div>
                            @if($invoice->customer->email)
                            <div class="small text-muted"><i class="fas fa-envelope me-1"></i>{{ $invoice->customer->email }}</div>
                            @endif
                            @if($invoice->customer->phone)
                            <div class="small text-muted"><i class="fas fa-phone me-1"></i>{{ $invoice->customer->phone }}</div>
                            @endif
                            @if($invoice->customer->address)
                            <div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $invoice->customer->address }}</div>
                            @endif
                            @if($invoice->customer->tax_number)
                            <div class="small text-muted"><i class="fas fa-id-card me-1"></i>{{ $invoice->customer->tax_number }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded h-100" style="background:#f8faff;border:1px solid #dbeafe;">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted small">{{ $txt['invoice_date'] }}</span>
                                <span class="fw-semibold small">{{ $invoice->invoice_date->format('Y-m-d') }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="text-muted small">{{ $txt['due_date'] }}</span>
                                <span class="fw-semibold small">{{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- جدول البنود --}}
                <div class="table-responsive mb-4">
                    <table class="table align-middle" style="border-radius:10px;overflow:hidden;">
                        <thead style="background:linear-gradient(90deg,#1e40af,#3b82f6);color:#fff;">
                            <tr>
                                <th class="py-3">#</th>
                                <th>{{ $txt['description'] }}</th>
                                <th class="text-center">{{ $txt['quantity'] }}</th>
                                <th class="text-center">{{ $txt['price'] }}</th>
                                <th class="text-center">{{ $txt['tax'] }}</th>
                                <th class="text-end">{{ $txt['total'] }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $i => $item)
                            <tr class="{{ $loop->even ? 'table-light' : '' }}">
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $item->description }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-center">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ $item->tax_rate }}%</span>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- الإجماليات --}}
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted">{{ $txt['subtotal'] }}</td>
                                <td class="text-end">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ $txt['tax_amount'] }}</td>
                                <td class="text-end">{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</td>
                            </tr>
                            @if($invoice->discount_amount > 0)
                            <tr>
                                <td class="text-muted">{{ $txt['discount'] }}</td>
                                <td class="text-end text-danger">- {{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}</td>
                            </tr>
                            @endif
                            <tr style="background:linear-gradient(90deg,#1e40af,#3b82f6);color:#fff;border-radius:8px;">
                                <td class="fw-bold fs-5 py-3 px-3" style="border-radius:8px 0 0 8px;">{{ $txt['grand_total'] }}</td>
                                <td class="fw-bold fs-5 text-end py-3 px-3" style="border-radius:0 8px 8px 0;">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ $txt['paid'] }}</td>
                                <td class="text-end text-success fw-semibold">{{ number_format($invoice->paid_amount, 2) }} {{ $invoice->currency }}</td>
                            </tr>
                            <tr class="table-warning">
                                <td class="fw-bold">{{ $txt['remaining'] }}</td>
                                <td class="text-end fw-bold text-danger">{{ number_format($invoice->remaining_amount, 2) }} {{ $invoice->currency }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($invoice->notes)
                <div class="mt-3 p-3 rounded" style="background:#fffbeb;border:1px solid #fde68a;">
                    <div class="small fw-bold mb-1 text-warning-emphasis"><i class="fas fa-sticky-note me-1"></i>{{ $txt['notes'] }}</div>
                    <div class="small">{{ $invoice->notes }}</div>
                </div>
                @endif

                @if($invoice->terms_conditions)
                <div class="mt-2 p-3 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <div class="small fw-bold mb-1 text-success"><i class="fas fa-file-contract me-1"></i>{{ $txt['terms'] }}</div>
                    <div class="small">{{ $invoice->terms_conditions }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- الشريط الجانبي: المدفوعات --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <h6 class="fw-bold mb-0"><i class="fas fa-money-bill-wave me-2 text-success"></i>{{ $txt['payments'] }}</h6>
            </div>
            <div class="card-body p-0">
                @forelse($invoice->payments as $payment)
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <div>
                        <div class="fw-semibold text-success">{{ number_format($payment->amount, 2) }} {{ $invoice->currency }}</div>
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ $payment->payment_date->format('Y-m-d') }} &bull;
                            {{ $txt[$payment->payment_method] ?? $payment->payment_method }}
                        </div>
                    </div>
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">
                    <i class="fas fa-inbox d-block mb-2 fs-4"></i>{{ $txt['no_payments'] }}
                </div>
                @endforelse
            </div>
            @if($invoice->remaining_amount > 0)
            <div class="card-footer bg-white" style="border-radius:0 0 16px 16px;">
                <form action="{{ route('payments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                    <div class="row g-2">
                        <div class="col-7">
                            <input type="number" name="amount" class="form-control form-control-sm"
                                placeholder="{{ $txt['amount'] }}" max="{{ $invoice->remaining_amount }}" step="0.01" required>
                        </div>
                        <div class="col-5">
                            <button class="btn btn-sm btn-success w-100">{{ $txt['register_payment'] }}</button>
                        </div>
                        <div class="col-12">
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="cash">{{ $txt['cash'] }}</option>
                                <option value="bank">{{ $txt['bank'] }}</option>
                                <option value="card">{{ $txt['card'] }}</option>
                                <option value="cheque">{{ $txt['cheque'] }}</option>
                            </select>
                        </div>
                        <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal إرسال البريد --}}
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">{{ $txt['send_email'] }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('invoices.email', $invoice) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $invoice->customer->email }}" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $txt['cancel'] }}</button>
                    <button type="submit" class="btn btn-primary">{{ $txt['send'] }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
