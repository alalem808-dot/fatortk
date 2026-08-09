@extends('super_admin.layout')
@section('title', 'إنشاء حساب جديد')
@section('page-title')
<h6 class="mb-0 fw-bold">إنشاء حساب جديد</h6>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                <form action="{{ route('super_admin.tenants.store') }}" method="POST">
                    @csrf

                    <h6 class="fw-bold text-muted mb-3 border-bottom pb-2">
                        <i class="fas fa-building me-2 text-primary"></i>بيانات الشركة
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">اسم الشركة <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">النطاق الفرعي <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="subdomain" class="form-control" value="{{ old('subdomain') }}" placeholder="mycompany" required>
                                <span class="input-group-text">.fatortk.com</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">خطة الاشتراك <span class="text-danger">*</span></label>
                            <select name="subscription_plan" class="form-select" required>
                                @foreach($plans as $plan)
                                <option value="{{ $plan->slug }}" {{ old('subscription_plan')==$plan->slug?'selected':'' }}>
                                    {{ $plan->name }} -
                                    {{ $plan->price_monthly > 0 ? number_format($plan->price_monthly).' SDG/شهر' : 'مجاني' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">حالة الحساب</label>
                            <select name="status" class="form-select">
                                <option value="active"  {{ old('status','active')=='active'  ?'selected':'' }}>نشط</option>
                                <option value="trial"   {{ old('status')=='trial'   ?'selected':'' }}>تجريبي</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">تاريخ انتهاء الاشتراك</label>
                            <input type="date" name="subscription_expires_at" class="form-control" value="{{ old('subscription_expires_at') }}">
                        </div>
                    </div>

                    <h6 class="fw-bold text-muted mb-3 border-bottom pb-2">
                        <i class="fas fa-user me-2 text-primary"></i>بيانات مدير الحساب (Admin)
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-at"></i></span>
                                <input type="text" name="username" class="form-control" value="{{ old('username') }}"
                                    placeholder="مثال: ahmed_ali" required pattern="[a-zA-Z0-9_\-]+"
                                    title="حروف إنجليزية وأرقام و _ فقط">
                            </div>
                            <div class="form-text">يُستخدم لتسجيل الدخول - حروف إنجليزية وأرقام فقط</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إنشاء الحساب
                        </button>
                        <a href="{{ route('super_admin.tenants') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
