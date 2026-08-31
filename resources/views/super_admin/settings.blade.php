@extends('super_admin.layout')
@section('title', 'إعدادات المنصة')
@section('page-title')
<h6 class="mb-0 fw-bold">إعدادات المنصة</h6>
@endsection

@section('content')
<form action="{{ route('super_admin.settings.update') }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
@php $ps = $platformSettings->keyBy('key'); @endphp

<div class="row g-4">

    {{-- ===== العمود الأيسر: حساب المشرف ===== --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-user-shield text-primary me-2"></i>حساب المشرف</h6>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger py-2 small"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <div class="mb-3">
                    <label class="form-label">الاسم</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $admin->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
                </div>
                <hr>
                <p class="text-muted small mb-2">اتركها فارغة إذا لا تريد تغيير كلمة المرور</p>
                <div class="mb-3">
                    <label class="form-label">كلمة المرور الجديدة</label>
                    <input type="password" name="password" class="form-control" minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
        </div>

        {{-- ===== صور المنصة ===== --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-image text-info me-2"></i>صور وشعارات المنصة</h6>
            </div>
            <div class="card-body">

                {{-- لوجو المنصة الرئيسي --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">لوجو المنصة الرئيسي</label>
                    @php $logoUrl = \App\Models\PlatformSetting::imageUrl('platform_logo'); @endphp
                    @if($logoUrl)
                    <div class="mb-2 p-2 border rounded text-center bg-light">
                        <img src="{{ $logoUrl }}" alt="Platform Logo" style="max-height:60px;max-width:200px;object-fit:contain">
                    </div>
                    @else
                    <div class="mb-2 p-2 border rounded text-center bg-light text-muted small">
                        <i class="fas fa-image fa-2x mb-1 d-block text-muted"></i>لا يوجد لوجو حالياً
                    </div>
                    @endif
                    <input type="file" name="platform_logo" class="form-control form-control-sm" accept="image/*">
                    <div class="form-text">PNG أو SVG — يظهر في الصفحة الرئيسية والقوائم</div>
                </div>

                {{-- الصورة المصغرة Favicon --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">الصورة المصغرة (Favicon)</label>
                    @php $faviconUrl = \App\Models\PlatformSetting::imageUrl('platform_favicon'); @endphp
                    @if($faviconUrl)
                    <div class="mb-2 p-2 border rounded text-center bg-light">
                        <img src="{{ $faviconUrl }}" alt="Favicon" style="width:32px;height:32px;object-fit:contain">
                        <span class="small text-muted ms-2">32×32px</span>
                    </div>
                    @else
                    <div class="mb-2 p-2 border rounded text-center bg-light text-muted small">
                        <i class="fas fa-star fa-lg mb-1 d-block text-muted"></i>لا يوجد favicon حالياً
                    </div>
                    @endif
                    <input type="file" name="platform_favicon" class="form-control form-control-sm" accept=".png,.ico,.svg">
                    <div class="form-text">PNG أو ICO — يظهر في تبويب المتصفح (32×32 px موصى به)</div>
                </div>

                {{-- لوجو صفحة تسجيل الدخول --}}
                <div class="mb-2">
                    <label class="form-label fw-semibold">لوجو صفحة تسجيل الدخول</label>
                    @php $loginLogoUrl = \App\Models\PlatformSetting::imageUrl('login_logo'); @endphp
                    @if($loginLogoUrl)
                    <div class="mb-2 p-2 border rounded text-center" style="background:#0f172a">
                        <img src="{{ $loginLogoUrl }}" alt="Login Logo" style="max-height:50px;max-width:180px;object-fit:contain">
                    </div>
                    @else
                    <div class="mb-2 p-2 border rounded text-center" style="background:#0f172a">
                        <span class="text-white-50 small">لا يوجد لوجو — يعرض اسم المنصة نصاً</span>
                    </div>
                    @endif
                    <input type="file" name="login_logo" class="form-control form-control-sm" accept="image/*">
                    <div class="form-text">يظهر أعلى بطاقة تسجيل الدخول على خلفية داكنة</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== العمود الأيمن: إعدادات المنصة ===== --}}
    <div class="col-lg-8">
        {{-- إعدادات عامة --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-cog text-warning me-2"></i>إعدادات المنصة العامة</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">اسم المنصة</label>
                        <input type="text" name="platform_platform_name" class="form-control"
                            value="{{ old('platform_platform_name', $ps['platform_name']->value ?? 'فاتورتك') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">البريد الإلكتروني للدعم</label>
                        <input type="email" name="platform_support_email" class="form-control"
                            value="{{ old('platform_support_email', $ps['support_email']->value ?? '') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- إعدادات واتساب --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="fab fa-whatsapp text-success me-2"></i>إعدادات واتساب</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">رقم واتساب الدعم</label>
                    <div class="input-group">
                        <span class="input-group-text">+</span>
                        <input type="text" name="platform_whatsapp_number" class="form-control"
                            placeholder="مثال: 2499100868681"
                            value="{{ old('platform_whatsapp_number', $ps['whatsapp_number']->value ?? '') }}">
                    </div>
                    <div class="form-text">الرقم بالصيغة الدولية بدون + (مثال: 2499100868681)</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">رسالة الاشتراك الجديد</label>
                        <input type="text" name="platform_whatsapp_subscribe_msg" class="form-control"
                            value="{{ old('platform_whatsapp_subscribe_msg', $ps['whatsapp_subscribe_msg']->value ?? '') }}">
                        <div class="form-text">صفحة الترحيب والتسعير</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">رسالة إنشاء حساب</label>
                        <input type="text" name="platform_whatsapp_register_msg" class="form-control"
                            value="{{ old('platform_whatsapp_register_msg', $ps['whatsapp_register_msg']->value ?? '') }}">
                        <div class="form-text">صفحة تسجيل الدخول</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">رسالة تجديد الاشتراك</label>
                        <input type="text" name="platform_whatsapp_renew_msg" class="form-control"
                            value="{{ old('platform_whatsapp_renew_msg', $ps['whatsapp_renew_msg']->value ?? '') }}">
                        <div class="form-text">صفحة انتهاء الاشتراك</div>
                    </div>
                </div>

                {{-- معاينة الروابط --}}
                @php
                    $number = $ps['whatsapp_number']->value ?? '';
                    $subMsg = $ps['whatsapp_subscribe_msg']->value ?? '';
                    $renMsg = $ps['whatsapp_renew_msg']->value ?? '';
                    $regMsg = $ps['whatsapp_register_msg']->value ?? '';
                @endphp
                @if($number)
                <div class="alert alert-light border mt-3 py-2">
                    <div class="small fw-semibold mb-2 text-muted">معاينة روابط واتساب:</div>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="https://wa.me/{{ $number }}?text={{ rawurlencode($subMsg) }}" target="_blank" class="small text-success">
                            <i class="fab fa-whatsapp me-1"></i>رابط الاشتراك
                        </a>
                        <a href="https://wa.me/{{ $number }}?text={{ rawurlencode($regMsg) }}" target="_blank" class="small text-success">
                            <i class="fab fa-whatsapp me-1"></i>رابط إنشاء الحساب
                        </a>
                        <a href="https://wa.me/{{ $number }}?text={{ rawurlencode($renMsg) }}" target="_blank" class="small text-success">
                            <i class="fab fa-whatsapp me-1"></i>رابط تجديد الاشتراك
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

<div class="mt-4 text-end">
    <button type="submit" class="btn btn-warning fw-bold px-5">
        <i class="fas fa-save me-2"></i>حفظ جميع الإعدادات
    </button>
</div>
</form>
@endsection
