@extends('layouts.app')
@section('title', 'تعديل مصروف')
@section('page-title')<h6 class="mb-0 fw-bold">تعديل مصروف</h6>@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">الوصف <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" value="{{ old('description', $expense->description) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">العملة</label>
                            @if($currencies->count())
                            <select name="currency" class="form-select">
                                @foreach($currencies as $c)
                                <option value="{{ $c->code }}" {{ old('currency', $expense->currency)==$c->code?'selected':'' }}>{{ $c->code }} - {{ $c->name }}</option>
                                @endforeach
                            </select>
                            @else
                            <input type="text" name="currency" class="form-control" value="{{ old('currency', $expense->currency) }}">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الفئة</label>
                            <select name="category_id" class="form-select">
                                <option value="">بدون فئة</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $expense->category_id)==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                @foreach(['cash'=>'نقدي','bank'=>'تحويل بنكي','card'=>'بطاقة','cheque'=>'شيك'] as $val=>$lbl)
                                <option value="{{ $val }}" {{ old('payment_method',$expense->payment_method)==$val?'selected':'' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">مرفق جديد</label>
                            <input type="file" name="attachment" class="form-control">
                            @if($expense->attachment)
                            <div class="form-text">مرفق حالي: <a href="{{ url('storage/'.$expense->attachment) }}" target="_blank">عرض</a></div>
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $expense->notes) }}</textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> حفظ</button>
                            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
