<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - فاتورتك</title>
    @php $favicon = \App\Models\PlatformSetting::imageUrl('platform_favicon'); @endphp
    @if($favicon)<link rel="icon" type="image/png" href="{{ $favicon }}">@endif
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
        .input-group-text { background: #f8fafc; border-left: none; }
        .form-control { border-right: none; }
        .form-control:focus { box-shadow: none; border-color: #ced4da; }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control { border-color: #2563eb; }
        .btn-login { background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; padding: .75rem; font-size: 1rem; font-weight: 600; border-radius: 10px; }
        .btn-login:hover { background: linear-gradient(135deg, #1d4ed8, #1e40af); }
        .divider { position: relative; text-align: center; margin: 1rem 0; }
        .divider::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e2e8f0; }
        .divider span { background: #fff; padding: 0 .75rem; color: #94a3b8; font-size: .8rem; position: relative; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-2">
                @php $loginLogo = \App\Models\PlatformSetting::imageUrl('login_logo'); @endphp
                @if($loginLogo)
                    <img src="{{ $loginLogo }}" alt="{{ \App\Models\PlatformSetting::get('platform_name','فاتورتك') }}"
                         style="max-height:120px;max-width:220px;object-fit:contain" class="mb-2">
                @else
                    <div class="brand-logo">{{ \App\Models\PlatformSetting::get('platform_name','فاتورتك') }}</div>
                @endif
                
            </div>

            <div class="card p-4">
                <h5 class="fw-bold mb-1">مرحباً  </h5>
                <p class="text-muted small mb-4">سجّل دخولك للمتابعة</p>

                @if($errors->any())
                    <div class="alert alert-danger py-2 small">
                        <i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">اسم المستخدم</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="login" class="form-control"
                                value="{{ old('login') }}"
                                placeholder="email@example.com أو username"
                                required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">كلمة المرور</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control"
                                placeholder="••••••••" required id="passwordInput">
                            <button type="button" class="btn btn-outline-secondary border-start-0"
                                onclick="togglePass()">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label small" for="remember">تذكرني</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-login btn-primary w-100 text-white">
                        <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول
                    </button>
                </form>

                <div class="divider mt-4"><span>لا تملك حساباً؟</span></div>

                <a href="{{ \App\Models\PlatformSetting::whatsappUrl('whatsapp_register_msg') }}"
                   target="_blank"
                   class="btn w-100 mt-2 fw-semibold"
                   style="background:#25D366;color:#fff;border-radius:10px;padding:.65rem">
                    <i class="fab fa-whatsapp me-2 fs-5"></i>تواصل معنا لإنشاء حساب
                </a>
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
<script>
function togglePass() {
    const input = document.getElementById('passwordInput');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</body>
</html>
