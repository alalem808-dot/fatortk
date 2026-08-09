<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>الأسعار - فاتورتك</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    body { background: #f8fafc; }
    .navbar { background: #1e293b; }
    .hero { background: linear-gradient(135deg,#1e293b,#0f172a); color:#fff; padding:60px 0 40px; }
    .plan-card { border-radius:16px; border:2px solid #e2e8f0; transition:all .3s; }
    .plan-card:hover { transform:translateY(-4px); box-shadow:0 20px 40px rgba(0,0,0,.1); }
    .plan-card.popular { border-color:#2563eb; }
    .popular-badge { background:#2563eb; color:#fff; font-size:.75rem; padding:4px 12px; border-radius:20px; }
    .price-amount { font-size:2.5rem; font-weight:800; }
    .price-period { font-size:.9rem; color:#64748b; }
    .feature-item { padding:8px 0; border-bottom:1px solid #f1f5f9; font-size:.9rem; }
    .feature-item:last-child { border:none; }
    .check { color:#16a34a; }
    .cross { color:#dc2626; }
</style>
</head>
<body>
<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold fs-4" href="/">فاتورتك</a>
    <div class="d-flex gap-2">
        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">دخول</a>
        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">ابدأ مجاناً</a>
    </div>
</nav>

<div class="hero text-center">
    <h1 class="fw-bold mb-3">أسعار بسيطة وشفافة</h1>
    <p class="text-white-50 mb-0">ابدأ مجاناً، وقم بالترقية عند الحاجة</p>
    <p class="text-white-50 small">جميع الأسعار بالجنيه السوداني (SDG)</p>
</div>

<div class="container py-5">
    <div class="row g-4 justify-content-center">

        {{-- مجاني --}}
        <div class="col-md-3">
            <div class="plan-card card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1">مجاني</h5>
                    <p class="text-muted small mb-3">للأفراد والمشاريع الناشئة</p>
                    <div class="price-amount">0</div>
                    <div class="price-period mb-4">SDG / شهر</div>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary w-100 mb-4">ابدأ مجاناً</a>
                    @foreach(['10 فواتير/شهر','20 عميل','30 منتج','مستخدم واحد','تصدير PDF','إرسال واتساب','قالب واحد'] as $f)
                    <div class="feature-item"><i class="fas fa-check check me-2"></i>{{ $f }}</div>
                    @endforeach
                    @foreach(['تصدير Excel','إرسال بريد','إدارة المخزون'] as $f)
                    <div class="feature-item text-muted"><i class="fas fa-times cross me-2"></i>{{ $f }}</div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- أساسي --}}
        <div class="col-md-3">
            <div class="plan-card card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1">أساسي</h5>
                    <p class="text-muted small mb-3">للمحلات والحرفيين</p>
                    <div class="price-amount">2,500</div>
                    <div class="price-period mb-1">SDG / شهر</div>
                    <div class="text-success small mb-3">أو 25,000 SDG/سنة (وفر 17%)</div>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary w-100 mb-4">ابدأ الآن</a>
                    @foreach(['100 فاتورة/شهر','200 عميل','300 منتج','3 مستخدمين','تصدير PDF وExcel','إرسال بريد وواتساب','إدارة المخزون','3 قوالب مخصصة'] as $f)
                    <div class="feature-item"><i class="fas fa-check check me-2"></i>{{ $f }}</div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- احترافي --}}
        <div class="col-md-3">
            <div class="plan-card card popular h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h5 class="fw-bold mb-0">احترافي</h5>
                        <span class="popular-badge">الأكثر مبيعاً ⭐</span>
                    </div>
                    <p class="text-muted small mb-3">للشركات المتوسطة</p>
                    <div class="price-amount text-primary">6,000</div>
                    <div class="price-period mb-1">SDG / شهر</div>
                    <div class="text-success small mb-3">أو 60,000 SDG/سنة (وفر 17%)</div>
                    <a href="{{ route('register') }}" class="btn btn-primary w-100 mb-4">ابدأ الآن</a>
                    @foreach(['فواتير غير محدودة','عملاء غير محدودين','منتجات غير محدودة','10 مستخدمين','كل ميزات الأساسي','قوالب غير محدودة','API Access','تقارير متقدمة','نسخ احتياطي يومي'] as $f)
                    <div class="feature-item"><i class="fas fa-check check me-2"></i>{{ $f }}</div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- مؤسسي --}}
        <div class="col-md-3">
            <div class="plan-card card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1">مؤسسي</h5>
                    <p class="text-muted small mb-3">للشركات الكبيرة</p>
                    <div class="price-amount">15,000</div>
                    <div class="price-period mb-1">SDG / شهر</div>
                    <div class="text-success small mb-3">أو 150,000 SDG/سنة (وفر 17%)</div>
                    <a href="{{ route('register') }}" class="btn btn-outline-dark w-100 mb-4">تواصل معنا</a>
                    @foreach(['كل ميزات الاحترافي','مستخدمون غير محدودين','دعم مخصص وأولوية','تدريب وإعداد','نسخ احتياطي فوري','تقارير مخصصة','SLA مضمون'] as $f)
                    <div class="feature-item"><i class="fas fa-check check me-2"></i>{{ $f }}</div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- مقارنة --}}
    <div class="mt-5">
        <h4 class="fw-bold text-center mb-4">مقارنة تفصيلية</h4>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 text-center">
                    <thead class="table-dark">
                        <tr><th class="text-end">الميزة</th><th>مجاني</th><th>أساسي</th><th class="table-primary">احترافي</th><th>مؤسسي</th></tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['الفواتير/شهر','10','100','غير محدود','غير محدود'],
                            ['العملاء','20','200','غير محدود','غير محدود'],
                            ['المنتجات','30','300','غير محدود','غير محدود'],
                            ['المستخدمين','1','3','10','غير محدود'],
                            ['تصدير PDF','✅','✅','✅','✅'],
                            ['تصدير Excel','❌','✅','✅','✅'],
                            ['إرسال واتساب','✅','✅','✅','✅'],
                            ['إرسال بريد','❌','✅','✅','✅'],
                            ['إدارة المخزون','❌','✅','✅','✅'],
                            ['قوالب مخصصة','1','3','غير محدود','غير محدود'],
                            ['API Access','❌','❌','✅','✅'],
                            ['الدعم الفني','مجتمع','بريد','أولوية','مخصص'],
                        ] as $row)
                        <tr>
                            <td class="text-end fw-semibold">{{ $row[0] }}</td>
                            <td>{{ $row[1] }}</td><td>{{ $row[2] }}</td>
                            <td class="table-primary fw-semibold">{{ $row[3] }}</td>
                            <td>{{ $row[4] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white-50 text-center py-4 mt-5">
    <div>فاتورتك &copy; {{ date('Y') }} - نظام إدارة الفواتير والمخزون</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
