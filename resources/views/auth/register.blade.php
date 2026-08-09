<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - فاتورتك</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card { border: none; border-radius: 20px; box-shadow: 0 25px 60px rgba(0,0,0,.4); }
        .brand-logo { font-size: 2.2rem; font-weight: 900; color: #2563eb; letter-spacing: -1px; }
        .form-control { border-radius: 10px; border: 1px solid #e2e8f0; padding: .75rem 1rem; }
        .form-control:focus { box-shadow: none; border-color: #2563eb; }
        .btn-register { background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; padding: .75rem; font-size: 1rem; font-weight: 600; border-radius: 10px; }
        .btn-register:hover { background: linear-gradient(135deg, #1d4ed8, #1e40af); }
        .form-label { font-weight: 600; font-size: .9rem; color: #1e293b; }
        .small-text { font-size: .85rem; color: #64748b; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="text-center mb-4">
                <div class="brand-logo">فاتورتك</div>
                <p class="text-white-50 mt-1 small">نظام إدارة الفواتير والمخزون</p>
            </div>

            <div class="card p-4">
                <h5 class="fw-bold mb-1">إنشاء حساب جديد 🚀</h5>
                <p class="text-muted small mb-4">ابدأ مجاناً وأدر فواتيرك بسهولة</p>

                @if($errors->any())
                    <div class="alert alert-danger py-2 small">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('register') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">اسم الشركة</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" required>
                        <small class="small-text">مثال: متجر أحمد للإلكترونيات</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">النطاق الفرعي (Subdomain)</label>
                        <div class="input-group">
                            <input type="text" name="subdomain" class="form-control" value="{{ old('subdomain') }}" required>
                            <span class="input-group-text">.fatortk.com</span>
                        </div>
                        <small class="small-text">أحرف وأرقام وشرطات فقط (بدون مسافات)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">اسمك الكامل</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" name="password" class="form-control" required id="passwordInput">
                        <small class="small-text">8 أحرف على الأقل</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-register btn-primary w-100 text-white mb-3">
                        <i class="fas fa-user-plus me-2"></i>إنشاء الحساب
                    </button>
                </form>

                <div class="text-center">
                    <p class="small text-muted mb-0">
                        لديك حساب بالفعل؟
                        <a href="{{ route('login') }}" class="text-primary fw-bold">سجّل دخولك</a>
                    </p>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="{{ url('/') }}" class="text-white-50 small">
                    <i class="fas fa-arrow-right me-1"></i>العودة للصفحة الرئيسية
                </a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
