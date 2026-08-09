@extends('layouts.app')
@section('title', isset($customer) ? 'تعديل عميل' : 'عميل جديد')
@section('page-title')
<h6 class="mb-0 fw-bold">{{ isset($customer) ? 'تعديل: '.$customer->name : 'إضافة عميل جديد' }}</h6>
@endsection

@section('content')
@php
    $action = isset($customer) ? route('customers.update', $customer) : route('customers.store');
    $method = isset($customer) ? 'PUT' : 'POST';
@endphp

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form action="{{ $action }}" method="POST">
                    @csrf @method($method)
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">الاسم <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رقم واتساب</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
                                <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $customer->whatsapp_number ?? '') }}" placeholder="249912345678">
                            </div>
                            <div class="form-text">بصيغة دولية بدون + مثال: 249912345678</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">المدينة</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الرقم الضريبي</label>
                            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $customer->tax_number ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">العنوان</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address ?? '') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $customer->notes ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
