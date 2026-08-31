@extends('super_admin.layout')
@section('title', 'إدارة العملات')
@section('page-title')<h6 class="mb-0 fw-bold">إدارة العملات</h6>@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="fas fa-coins me-2 text-warning"></i>العملات المتاحة للمشتركين</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-1"></i> إضافة عملة
                </button>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>الرمز</th>
                            <th>الاسم</th>
                            <th>الرمز المختصر</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currencies as $cur)
                        <tr>
                            <td><code class="px-2 py-1 rounded" style="background:#f1f5f9">{{ $cur->code }}</code></td>
                            <td>
                                <form action="{{ route('super_admin.currencies.update', $cur) }}" method="POST" class="d-flex gap-2 align-items-center">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" value="{{ $cur->name }}" class="form-control form-control-sm" style="width:160px" required>
                                    <input type="text" name="symbol" value="{{ $cur->symbol }}" class="form-control form-control-sm" style="width:70px" placeholder="رمز">
                                    <button class="btn btn-xs btn-outline-primary" title="حفظ"><i class="fas fa-check"></i></button>
                                </form>
                            </td>
                            <td><span class="text-muted">{{ $cur->symbol }}</span></td>
                            <td>
                                <form action="{{ route('super_admin.currencies.toggle', $cur) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-xs {{ $cur->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                        {{ $cur->is_active ? 'مفعّلة' : 'معطّلة' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('super_admin.currencies.destroy', $cur) }}" method="POST" onsubmit="return confirm('حذف العملة {{ $cur->code }}؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">لا توجد عملات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>ملاحظة</h6>
                <p class="text-muted small mb-0">
                    العملات المفعّلة هنا ستظهر للمشتركين كقائمة في إعداداتهم لاختيار العملة الافتراضية.
                    العملات المعطّلة لن تظهر لأي مشترك.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Modal إضافة عملة --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">إضافة عملة جديدة</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('super_admin.currencies.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">رمز العملة <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="مثال: KWD" required maxlength="10">
                        <div class="form-text">حروف إنجليزية كبيرة — يُستخدم كمعرّف فريد</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الاسم <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: دينار كويتي" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرمز المختصر</label>
                        <input type="text" name="symbol" class="form-control" placeholder="مثال: د.ك" maxlength="10">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
