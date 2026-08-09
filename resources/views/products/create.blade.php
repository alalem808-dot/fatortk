@extends('layouts.app')
@section('title', isset($product) ? 'تعديل منتج' : 'منتج جديد')
@section('page-title')
<h6 class="mb-0 fw-bold">{{ isset($product) ? 'تعديل: '.$product->name : 'إضافة منتج جديد' }}</h6>
@endsection

@section('content')
@php
    $action = isset($product) ? route('products.update', $product) : route('products.store');
    $method = isset($product) ? 'PUT' : 'POST';
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf @method($method)

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0">معلومات المنتج</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">اسم المنتج <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}" placeholder="رمز المنتج">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الباركود</label>
                            <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $product->barcode ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">الوصف</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الفئة</label>
                            <select name="category_id" class="form-select">
                                <option value="">بدون فئة</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                <a href="{{ route('categories.index') }}" target="_blank">
                                    <i class="fas fa-plus-circle"></i> إدارة الفئات
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">وحدة القياس</label>
                            <select name="unit" class="form-select">
                                @forelse($units as $unit)
                                <option value="{{ $unit->name }}" {{ old('unit', $product->unit ?? '') == $unit->name ? 'selected' : '' }}>{{ $unit->name }}{{ $unit->symbol ? ' ('.$unit->symbol.')' : '' }}</option>
                                @empty
                                <option value="piece">قطعة</option>
                                @endforelse
                            </select>
                            <div class="form-text">
                                <a href="{{ route('categories.index') }}" target="_blank">
                                    <i class="fas fa-plus-circle"></i> إدارة وحدات القياس
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0">التسعير</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">سعر البيع (SDG) <span class="text-danger">*</span></label>
                            <input type="number" name="unit_price" class="form-control" value="{{ old('unit_price', $product->unit_price ?? '') }}" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">سعر التكلفة (SDG)</label>
                            <input type="number" name="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price ?? '') }}" min="0" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">نسبة الضريبة %</label>
                            <input type="number" name="tax_rate" class="form-control" value="{{ old('tax_rate', $product->tax_rate ?? 0) }}" min="0" max="100" step="0.01">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0">المخزون</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">الكمية الحالية</label>
                            <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">حد التنبيه (أدنى كمية)</label>
                            <input type="number" name="min_stock_alert" class="form-control" value="{{ old('min_stock_alert', $product->min_stock_alert ?? 5) }}" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0">الصورة</h6></div>
                <div class="card-body text-center">
                    @if(isset($product) && $product->image)
                        <img src="{{ url('storage/'.$product->image) }}" class="img-fluid rounded mb-3" style="max-height:150px">
                    @endif
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0">الحالة</h6></div>
                <div class="card-body">
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $product->status ?? 'active') === 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ old('status', $product->status ?? '') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ</button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
