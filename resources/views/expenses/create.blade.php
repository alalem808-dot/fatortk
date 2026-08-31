@extends('layouts.app')
@section('title', 'مصروف جديد')
@section('page-title')<h6 class="mb-0 fw-bold">إضافة مصروف جديد</h6>@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">الوصف <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" value="{{ old('description') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" value="{{ old('amount') }}" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">العملة</label>
                            @if($currencies->count())
                            <select name="currency" class="form-select">
                                @foreach($currencies as $c)
                                <option value="{{ $c->code }}" {{ $c->is_base ? 'selected' : '' }}>{{ $c->code }} - {{ $c->name }}</option>
                                @endforeach
                            </select>
                            @else
                            <input type="text" name="currency" class="form-control" value="SDG">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الفئة</label>
                            <select name="category_id" class="form-select">
                                <option value="">بدون فئة</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">نقدي</option>
                                <option value="bank">تحويل بنكي</option>
                                <option value="card">بطاقة</option>
                                <option value="cheque">شيك</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">مرفق</label>
                            <input type="file" name="attachment" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
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
