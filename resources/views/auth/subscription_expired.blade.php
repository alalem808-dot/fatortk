<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انتهى الاشتراك</title>
    @php $favicon = \App\Models\PlatformSetting::imageUrl('platform_favicon'); @endphp
    @if($favicon)<link rel="icon" type="image/png" href="{{ $favicon }}">@endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="card border-0 shadow-sm text-center p-5" style="max-width:480px;width:100%">
        <div class="mb-4">
            <i class="fas fa-clock fa-4x text-warning"></i>
        </div>
        <h4 class="fw-bold mb-2">انتهى اشتراكك</h4>
        <p class="text-muted mb-4">
            لقد انتهت صلاحية اشتراكك في نظام FatorTK.<br>
            تواصل مع الدعم لتجديد الاشتراك والمتابعة.
        </p>
        <div class="d-grid gap-2">
            <a href="{{ \App\Models\PlatformSetting::whatsappUrl('whatsapp_renew_msg') }}"
               target="_blank" class="btn btn-success">
                <i class="fab fa-whatsapp me-1"></i> تواصل معنا على واتساب
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-sign-out-alt me-1"></i> تسجيل الخروج
                </button>
            </form>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
