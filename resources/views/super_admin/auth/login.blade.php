<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Super Admin - فاتورتك</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
<style>
    body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; display: flex; align-items: center; }
    .card { border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
    .super-badge { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; padding: 4px 12px; border-radius: 20px; font-size: .75rem; font-weight: 700; }
</style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="text-center mb-4">
                <h2 class="text-white fw-bold">فاتورتك</h2>
                <span class="super-badge">⚡ Super Admin Panel</span>
            </div>
            <div class="card p-4">
                <h5 class="fw-bold mb-1">دخول المشرف العام</h5>
                <p class="text-muted small mb-4">هذه اللوحة مخصصة لمدير النظام فقط</p>

                @if($errors->any())
                    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                @endif

                <form action="{{ route('super_admin.login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">البريد أو اسم المستخدم</label>
                        <input type="text" name="login" class="form-control" value="{{ old('login') }}" required autofocus placeholder="superadmin أو email@example.com">
                        @error('login')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 fw-bold">دخول</button>
                </form>
            </div>
            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-white-50 small">← العودة لتسجيل دخول المستخدمين</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
