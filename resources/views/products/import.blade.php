@extends('layouts.app')
@section('title', 'استيراد المنتجات')
@section('page-title')<span>استيراد المنتجات من Excel</span>@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        {{-- بطاقة التعليمات --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i>تعليمات الاستيراد</h6>
                <ul class="small text-muted mb-3">
                    <li>يجب أن يحتوي الملف على الأعمدة التالية بنفس الترتيب والأسماء.</li>
                    <li>الأعمدة الإلزامية: <strong>اسم_المنتج</strong> و <strong>سعر_البيع</strong>.</li>
                    <li>إذا كان الـ SKU موجوداً مسبقاً سيتم تحديث المنتج بدلاً من إنشائه.</li>
                    <li>الفئة غير الموجودة ستُنشأ تلقائياً.</li>
                    <li>قيمة الحالة: <strong>نشط</strong> أو <strong>غير نشط</strong>.</li>
                </ul>
                <a href="{{ route('products.import.template') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i> تحميل ملف المثال
                </a>
            </div>
        </div>

        {{-- بطاقة الأعمدة --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>اسم العمود</th>
                            <th>الوصف</th>
                            <th>إلزامي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['اسم_المنتج',    'اسم المنتج',              true],
                            ['الفئة',         'اسم الفئة',               false],
                            ['sku',           'رمز المنتج الفريد',        false],
                            ['الباركود',      'رقم الباركود',             false],
                            ['سعر_البيع',     'سعر البيع للعميل',         true],
                            ['سعر_التكلفة',   'سعر التكلفة',              false],
                            ['نسبة_الضريبة',  'نسبة الضريبة % (مثال: 15)',false],
                            ['الكمية',        'الكمية الافتتاحية',        false],
                            ['حد_التنبيه',    'حد تنبيه المخزون المنخفض', false],
                            ['الوحدة',        'وحدة القياس (قطعة، كجم...)',false],
                            ['الحالة',        'نشط أو غير نشط',           false],
                        ] as [$col, $desc, $req])
                        <tr>
                            <td><code>{{ $col }}</code></td>
                            <td class="text-muted">{{ $desc }}</td>
                            <td>{!! $req ? '<span class="badge bg-danger">إلزامي</span>' : '<span class="text-muted">—</span>' !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- نموذج الرفع --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fas fa-upload text-primary me-2"></i>رفع الملف</h6>

                @if(session('warning'))
                <div class="alert alert-warning py-2 small">{{ session('warning') }}</div>
                @endif

                <form action="{{ route('products.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">ملف Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
                               accept=".xlsx,.xls,.csv" required>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">الصيغ المدعومة: xlsx, xls, csv — الحجم الأقصى: 5 ميجابايت</div>
                    </div>

                    @if($warehouses->count() > 1)
                    <div class="mb-3">
                        <label class="form-label">المخزن</label>
                        <select name="warehouse_id" class="form-select">
                            <option value="">المخزن الافتراضي</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-import me-1"></i> استيراد
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
