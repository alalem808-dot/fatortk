@extends('layouts.app')
@section('title', isset($template) ? 'تعديل قالب' : 'قالب جديد')
@section('page-title')
<h6 class="mb-0 fw-bold">{{ isset($template) ? 'تعديل قالب' : 'إنشاء قالب جديد' }}</h6>
@endsection

@section('content')
@php
    $action     = isset($template) ? route('templates.update', $template) : route('templates.store');
    $method     = isset($template) ? 'PUT' : 'POST';
    $tName      = old('name',           isset($template) ? $template->name           : '');
    $tPrimary   = old('primary_color',  isset($template) ? $template->primary_color  : '#2563eb');
    $tSecondary = old('secondary_color',isset($template) ? $template->secondary_color: '#64748b');
    $tFont      = old('font_family',    isset($template) ? $template->font_family    : 'Arial');
    $tHeader    = old('header_html',    isset($template) ? $template->header_html    : '');
    $tFooter    = old('footer_html',    isset($template) ? $template->footer_html    : '');
    $tDefault   = old('is_default',     isset($template) ? $template->is_default     : false);
    $tShowLogo  = old('show_logo',      isset($template) ? $template->show_logo      : true);
    $tShowTax   = old('show_tax',       isset($template) ? $template->show_tax       : true);
    $tShowDisc  = old('show_discount',  isset($template) ? $template->show_discount  : true);
    $tShowNotes = old('show_notes',     isset($template) ? $template->show_notes     : true);
@endphp

<form action="{{ $action }}" method="POST">
    @csrf
    @method($method)

    <div class="row g-3">
        <div class="col-md-8">

            {{-- معلومات القالب --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="fw-bold mb-0">معلومات القالب</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">اسم القالب <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $tName }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">اللون الرئيسي</label>
                            <input type="color" name="primary_color" class="form-control form-control-color w-100" value="{{ $tPrimary }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">اللون الثانوي</label>
                            <input type="color" name="secondary_color" class="form-control form-control-color w-100" value="{{ $tSecondary }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">نوع الخط</label>
                            <select name="font_family" class="form-select">
                                <option value="Arial"           {{ $tFont === 'Arial'            ? 'selected' : '' }}>Arial</option>
                                <option value="Tahoma"          {{ $tFont === 'Tahoma'           ? 'selected' : '' }}>Tahoma</option>
                                <option value="Verdana"         {{ $tFont === 'Verdana'          ? 'selected' : '' }}>Verdana</option>
                                <option value="Times New Roman" {{ $tFont === 'Times New Roman'  ? 'selected' : '' }}>Times New Roman</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">خيارات العرض</label>
                            <div class="d-flex flex-wrap gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_logo" id="show_logo" value="1" {{ $tShowLogo ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_logo">الشعار</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_tax" id="show_tax" value="1" {{ $tShowTax ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_tax">الضريبة</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_discount" id="show_discount" value="1" {{ $tShowDisc ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_discount">الخصم</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_notes" id="show_notes" value="1" {{ $tShowNotes ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_notes">الملاحظات</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ترويسة الفاتورة --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="fw-bold mb-0">ترويسة الفاتورة <span class="text-muted fw-normal small">(HTML اختياري)</span></h6>
                </div>
                <div class="card-body">
                    <textarea name="header_html" class="form-control font-monospace" rows="6"
                        placeholder="اتركه فارغاً للاستخدام الافتراضي...">{{ $tHeader }}</textarea>
                    <div class="form-text mt-2">
                        يمكنك استخدام HTML مخصص. المتغيرات المتاحة:
                        <code>&#123;&#123;company_name&#125;&#125;</code>
                        <code>&#123;&#123;invoice_number&#125;&#125;</code>
                    </div>
                </div>
            </div>

            {{-- تذييل الفاتورة --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="fw-bold mb-0">تذييل الفاتورة <span class="text-muted fw-normal small">(HTML اختياري)</span></h6>
                </div>
                <div class="card-body">
                    <textarea name="footer_html" class="form-control font-monospace" rows="4"
                        placeholder="اتركه فارغاً للاستخدام الافتراضي...">{{ $tFooter }}</textarea>
                </div>
            </div>

        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="fw-bold mb-0">الإعدادات</h6>
                </div>
                <div class="card-body">
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1" {{ $tDefault ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_default">تعيين كقالب افتراضي</label>
                    </div>

                    {{-- معاينة الألوان --}}
                    <div class="mb-4">
                        <div class="text-muted small mb-2">معاينة الألوان</div>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="rounded border" style="width:32px;height:32px;background:{{ $tPrimary }}"></div>
                            <span class="small text-muted">رئيسي</span>
                            <div class="rounded border" style="width:32px;height:32px;background:{{ $tSecondary }}"></div>
                            <span class="small text-muted">ثانوي</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ القالب
                        </button>
                        <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
