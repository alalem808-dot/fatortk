@extends('layouts.app')
@section('title', 'الفئات ووحدات القياس')
@section('page-title')
<h6 class="mb-0 fw-bold">الفئات ووحدات القياس</h6>
@endsection

@section('content')
<div class="row g-4">

    {{-- الفئات --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-tags me-2 text-primary"></i>فئات المنتجات</h6>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> إضافة فئة
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>اسم الفئة</th>
                            <th class="text-center">المنتجات</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td class="fw-semibold">{{ $cat->name }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark">{{ $cat->products_count }}</span></td>
                            <td class="text-center">
                                <button class="btn btn-xs btn-outline-primary"
                                    onclick="editCategory({{ $cat->id }}, '{{ $cat->name }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('هل أنت متأكد؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">لا توجد فئات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- وحدات القياس --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-ruler me-2 text-success"></i>وحدات القياس</h6>
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                    <i class="fas fa-plus"></i> إضافة وحدة
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>اسم الوحدة</th>
                            <th>الرمز</th>
                            <th class="text-center">المنتجات</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                        <tr>
                            <td class="fw-semibold">{{ $unit->name }}</td>
                            <td><span class="badge bg-light text-dark">{{ $unit->symbol ?? '-' }}</span></td>
                            <td class="text-center"><span class="badge bg-light text-dark">{{ $unit->products_count }}</span></td>
                            <td class="text-center">
                                <button class="btn btn-xs btn-outline-primary"
                                    onclick="editUnit({{ $unit->id }}, '{{ $unit->name }}', '{{ $unit->symbol }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('units.destroy', $unit) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('هل أنت متأكد؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">لا توجد وحدات قياس</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal إضافة فئة --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title fw-bold">إضافة فئة جديدة</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="form-label">اسم الفئة <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required autofocus>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary btn-sm">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal تعديل فئة --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title fw-bold">تعديل الفئة</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="editCategoryForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <label class="form-label">اسم الفئة <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="editCategoryName" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary btn-sm">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal إضافة وحدة --}}
<div class="modal fade" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title fw-bold">إضافة وحدة قياس</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الوحدة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: كيلوغرام">
                    </div>
                    <div>
                        <label class="form-label">الرمز</label>
                        <input type="text" name="symbol" class="form-control" placeholder="مثال: كغ">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success btn-sm">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal تعديل وحدة --}}
<div class="modal fade" id="editUnitModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title fw-bold">تعديل وحدة القياس</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="editUnitForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الوحدة <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editUnitName" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">الرمز</label>
                        <input type="text" name="symbol" id="editUnitSymbol" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary btn-sm">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function editCategory(id, name) {
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editCategoryForm').action = '/categories/' + id;
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
function editUnit(id, name, symbol) {
    document.getElementById('editUnitName').value = name;
    document.getElementById('editUnitSymbol').value = symbol;
    document.getElementById('editUnitForm').action = '/units/' + id;
    new bootstrap.Modal(document.getElementById('editUnitModal')).show();
}
</script>
@endpush
