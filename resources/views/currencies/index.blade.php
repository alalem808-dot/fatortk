@extends('layouts.app')
@section('title', 'العملات وأسعار الصرف')
@section('page-title')<h6 class="mb-0 fw-bold">العملات وأسعار الصرف</h6>@endsection
@section('content')
<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0">العملات المضافة</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>الرمز</th><th>الاسم</th><th>الرمز</th><th>سعر الصرف الحالي</th><th>الأساسية</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($currencies as $c)
                        <tr>
                            <td class="fw-bold">{{ $c->code }}</td>
                            <td>{{ $c->name }}</td>
                            <td>{{ $c->symbol }}</td>
                            <td>
                                @if($c->is_base)
                                    <span class="badge bg-success">عملة أساسية</span>
                                @else
                                    {{ number_format($c->latest_rate, 4) }}
                                    <button class="btn btn-xs btn-outline-secondary ms-1" data-bs-toggle="collapse"
                                        data-bs-target="#rateForm{{ $c->id }}" style="font-size:.7rem;padding:1px 6px;">
                                        تحديث
                                    </button>
                                    <div class="collapse mt-2" id="rateForm{{ $c->id }}">
                                        <form action="{{ route('currencies.rate', $c) }}" method="POST" class="d-flex gap-2">
                                            @csrf
                                            <input type="number" name="rate" class="form-control form-control-sm" placeholder="السعر الجديد" step="0.000001" min="0.000001" required style="width:130px">
                                            <input type="date" name="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required style="width:140px">
                                            <button class="btn btn-sm btn-primary">حفظ</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($c->is_base)
                                    <span class="badge bg-primary"><i class="fas fa-star"></i> أساسية</span>
                                @else
                                    <form action="{{ route('currencies.base', $c) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-xs btn-outline-primary" style="font-size:.7rem;padding:1px 6px;">تعيين أساسية</button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                @if(!$c->is_base)
                                <form action="{{ route('currencies.destroy', $c) }}" method="POST" onsubmit="return confirm('حذف العملة؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">لا توجد عملات مضافة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0">إضافة عملة جديدة</h6></div>
            <div class="card-body">
                <form action="{{ route('currencies.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">رمز العملة <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="USD, SDG, SAR..." maxlength="10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الاسم <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="دولار أمريكي" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرمز</label>
                        <input type="text" name="symbol" class="form-control" placeholder="$" maxlength="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سعر الصرف مقابل الأساسية <span class="text-danger">*</span></label>
                        <input type="number" name="rate" class="form-control" value="1" step="0.000001" min="0.000001" required>
                        <div class="form-text">إذا كانت هذه أول عملة ستصبح الأساسية تلقائياً</div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_base" id="isBase" value="1">
                        <label class="form-check-label" for="isBase">تعيينها كعملة أساسية</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">إضافة</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
