@extends('layouts.app')
@section('title', 'إنشاء مرتجع')
@section('page-title')<h6 class="mb-0 fw-bold">مرتجع فاتورة {{ $invoice->invoice_number }}</h6>@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('returns.store', $invoice) }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">تاريخ المرتجع <span class="text-danger">*</span></label>
                            <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">سبب المرتجع</label>
                            <input type="text" name="reason" class="form-control" placeholder="اختياري">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">اختر البنود المرتجعة</h6>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr><th>الصنف</th><th class="text-center">الكمية الأصلية</th><th class="text-center">السعر</th><th class="text-center" style="width:140px">كمية المرتجع</th></tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $i => $item)
                                <tr>
                                    <td>
                                        {{ $item->description }}
                                        <input type="hidden" name="items[{{ $i }}][invoice_item_id]" value="{{ $item->id }}">
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-center">{{ number_format($item->unit_price, 2) }}</td>
                                    <td>
                                        <input type="number" name="items[{{ $i }}][quantity]"
                                            class="form-control form-control-sm text-center"
                                            min="0" max="{{ $item->quantity }}" step="0.01" value="0">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-danger"><i class="fas fa-undo me-1"></i> تسجيل المرتجع</button>
                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
