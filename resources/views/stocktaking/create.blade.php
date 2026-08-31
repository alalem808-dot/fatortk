@extends('layouts.app')
@section('title', 'جلسة جرد جديدة')
@section('page-title')<h6 class="mb-0 fw-bold">إنشاء جلسة جرد جديدة</h6>@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <form action="{{ route('stocktaking.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            المخزن المراد جرده <span class="text-danger">*</span>
                        </label>
                        @if($warehouses->isEmpty())
                            <div class="alert alert-warning py-2">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                لا توجد مخازن متاحة. يرجى إضافة مخزن أولاً.
                            </div>
                        @else
                        <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                            <option value="">— اختر المخزن —</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}"
                                {{ old('warehouse_id', $defaultWarehouse?->id) == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                                @if($wh->is_default) (افتراضي) @endif
                                @if($wh->location) — {{ $wh->location }} @endif
                            </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle text-info"></i>
                            سيتم جرد المنتجات الموجودة في هذا المخزن فقط.
                        </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">اسم الجلسة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               placeholder="مثال: جرد شهر أغسطس 2026"
                               value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">التاريخ <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date', date('Y-m-d')) }}" required>
                        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="اختياري">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" {{ $warehouses->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-play me-1"></i> بدء الجرد
                        </button>
                        <a href="{{ route('stocktaking.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
