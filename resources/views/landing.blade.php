<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
@php $waNumber = '249122372020'; @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورتك - نظام إدارة الفواتير والمخزون</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; overflow-x: hidden; }

        /* ===== NAVBAR ===== */
        .navbar { background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .navbar-brand { font-size: 1.5rem; font-weight: 900; color: #2563eb !important; }
        .nav-link { color: #475569 !important; font-weight: 500; transition: color .2s; }
        .nav-link:hover { color: #2563eb !important; }

        /* ===== HERO ===== */
        .hero {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #fff;
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(37, 99, 235, .1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(20px); }
        }
        .hero-content { position: relative; z-index: 1; }
        .hero h1 { font-size: 3.5rem; font-weight: 900; line-height: 1.2; margin-bottom: 1.5rem; }
        .hero p { font-size: 1.2rem; color: #cbd5e1; margin-bottom: 2rem; }
        .btn-primary-lg { background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; padding: 1rem 2.5rem; font-size: 1.1rem; font-weight: 600; border-radius: 12px; transition: all .3s; }
        .btn-primary-lg:hover { background: linear-gradient(135deg, #1d4ed8, #1e40af); transform: translateY(-2px); box-shadow: 0 10px 25px rgba(37, 99, 235, .3); color: #fff; }

        /* ===== FEATURES ===== */
        .features { padding: 80px 0; background: #f8fafc; }
        .feature-card {
            background: #fff;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            transition: all .3s;
            border: 1px solid #e2e8f0;
        }
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,.1);
            border-color: #2563eb;
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.8rem;
            margin: 0 auto 1.5rem;
        }
        .feature-card h5 { font-weight: 700; margin-bottom: 1rem; color: #1e293b; }
        .feature-card p { color: #64748b; font-size: .95rem; }

        /* ===== PRICING ===== */
        .pricing { padding: 80px 0; }
        .pricing h2 { font-size: 2.8rem; font-weight: 900; margin-bottom: 1rem; text-align: center; }
        .pricing-subtitle { text-align: center; color: #64748b; margin-bottom: 3rem; font-size: 1.1rem; }
        .price-card {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 2.5rem;
            text-align: center;
            transition: all .3s;
            position: relative;
        }
        .price-card:hover { border-color: #2563eb; box-shadow: 0 20px 40px rgba(37, 99, 235, .15); }
        .price-card.popular {
            border-color: #2563eb;
            background: linear-gradient(135deg, #f0f9ff, #fff);
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(37, 99, 235, .2);
        }
        .popular-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            padding: .5rem 1.5rem;
            border-radius: 20px;
            font-size: .85rem;
            font-weight: 700;
        }
        .price-card h4 { font-weight: 700; margin-bottom: .5rem; margin-top: 1rem; }
        .price-card .subtitle { color: #64748b; font-size: .9rem; margin-bottom: 1.5rem; }
        .price-amount { font-size: 2.8rem; font-weight: 900; color: #2563eb; margin: 1rem 0; }
        .price-period { color: #64748b; font-size: .95rem; margin-bottom: 1.5rem; }
        .price-features { text-align: right; margin: 2rem 0; }
        .price-features li { list-style: none; padding: .6rem 0; color: #475569; font-size: .95rem; }
        .price-features li::before { content: '✓ '; color: #16a34a; font-weight: 700; margin-left: .5rem; }
        .price-card .btn { border-radius: 10px; padding: .75rem 1.5rem; font-weight: 600; }

        /* ===== STATS ===== */
        .stats { padding: 60px 0; background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff; }
        .stat-item { text-align: center; }
        .stat-number { font-size: 2.5rem; font-weight: 900; color: #2563eb; }
        .stat-label { color: #cbd5e1; margin-top: .5rem; }

        /* ===== CTA ===== */
        .cta {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            padding: 60px 0;
            text-align: center;
            border-radius: 20px;
            margin: 80px 0;
        }
        .cta h2 { font-size: 2.2rem; font-weight: 900; margin-bottom: 1.5rem; }
        .cta p { font-size: 1.1rem; margin-bottom: 2rem; color: #e0e7ff; }

        /* ===== FOOTER ===== */
        footer { background: #1e293b; color: #cbd5e1; padding: 3rem 0; text-align: center; }
        footer a { color: #93c5fd; text-decoration: none; }
        footer a:hover { color: #fff; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.2rem; }
            .hero p { font-size: 1rem; }
            .hero { padding: 50px 0; }
            .features, .pricing { padding: 50px 0; }
            .price-card.popular { transform: scale(1); }
            .cta h2 { font-size: 1.8rem; }
            .pricing h2 { font-size: 2rem; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-receipt me-2"></i>فاتورتك</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#features">المميزات</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">الأسعار</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">دخول</a></li>
                <li class="nav-item"><a class="btn btn-primary btn-sm ms-2" href="https://wa.me/{{ $waNumber }}?text=%D9%85%D8%B1%D8%AD%D8%A8%D8%A7%D9%8B%20%D8%A3%D8%B1%D9%8A%D8%AF%20%D8%A5%D9%86%D8%B4%D8%A7%D8%A1%20%D8%AD%D8%B3%D8%A7%D8%A8%20%D9%81%D9%8A%20%D9%81%D8%A7%D8%AA%D9%88%D8%B1%D8%AA%D9%83" target="_blank"><i class="fab fa-whatsapp me-1"></i>ابدأ الآن</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1>إدارة فواتيرك بسهولة واحترافية</h1>
                <p>نظام متكامل وموثوق لإدارة الفواتير والمخزون والعملاء بكفاءة عالية</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="https://wa.me/{{ $waNumber }}?text=%D9%85%D8%B1%D8%AD%D8%A8%D8%A7%D9%8B%20%D8%A3%D8%B1%D9%8A%D8%AF%20%D8%A5%D9%86%D8%B4%D8%A7%D8%A1%20%D8%AD%D8%B3%D8%A7%D8%A8%20%D9%81%D9%8A%20%D9%81%D8%A7%D8%AA%D9%88%D8%B1%D8%AA%D9%83" target="_blank" class="btn btn-primary-lg"><i class="fab fa-whatsapp me-2"></i>تواصل لإنشاء حساب</a>
                    <a href="#features" class="btn btn-outline-light btn-lg">تعرف أكثر</a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div style="font-size: 6rem; color: rgba(255,255,255,.1);">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
    <div class="container">
        <h2 class="text-center fw-bold mb-5" style="font-size: 2.5rem;">المميزات الرئيسية</h2>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-file-invoice"></i></div>
                    <h5>فواتير احترافية</h5>
                    <p>أنشئ فواتير احترافية بسهولة مع قوالب قابلة للتخصيص</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-warehouse"></i></div>
                    <h5>إدارة المخزون</h5>
                    <p>تتبع المخزون تلقائياً وتنبيهات عند نقص المنتجات</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <h5>إدارة العملاء</h5>
                    <p>احفظ بيانات عملائك وتابع سجل معاملاتهم</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                    <h5>تقارير متقدمة</h5>
                    <p>احصل على تقارير تفصيلية عن مبيعاتك وأرباحك</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                    <h5>متوافق مع الموبايل</h5>
                    <p>استخدم النظام من أي جهاز في أي وقت</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-envelope"></i></div>
                    <h5>إرسال الفواتير</h5>
                    <p>أرسل الفواتير عبر البريد والواتساب مباشرة</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-lock"></i></div>
                    <h5>آمن وموثوق</h5>
                    <p>بيانات آمنة مع نسخ احتياطية يومية</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-headset"></i></div>
                    <h5>دعم فني</h5>
                    <p>فريق دعم متاح لمساعدتك في أي وقت</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="stats">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">5000+</div>
                    <div class="stat-label">مستخدم نشط</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">50K+</div>
                    <div class="stat-label">فاتورة شهرية</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">99.9%</div>
                    <div class="stat-label">توفر الخدمة</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">دعم فني</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PRICING -->
<section class="pricing" id="pricing">
    <div class="container">
        <h2>خطط الاشتراك</h2>
        <p class="pricing-subtitle">اختر الخطة المناسبة لاحتياجاتك</p>
        <div class="row g-4 justify-content-center">
            @forelse($plans as $plan)
                <div class="col-md-6 col-lg-3">
                    <div class="price-card {{ $loop->iteration === 3 ? 'popular' : '' }}">
                        @if($loop->iteration === 3)
                            <div class="popular-badge">⭐ الأكثر مبيعاً</div>
                        @endif
                        <h4>{{ $plan->name }}</h4>
                        <div class="price-amount">
                            @if($plan->price_monthly == 0)
                                مجاني
                            @else
                                {{ number_format($plan->price_monthly) }}
                                <small style="font-size:1rem">SDG</small>
                            @endif
                        </div>
                        <div class="price-period">
                            @if($plan->price_monthly > 0)
                                شهرياً
                                @if($plan->price_monthly_usd > 0)
                                    <span class="badge bg-light text-dark ms-1">${{ $plan->price_monthly_usd }}</span>
                                @endif
                            @endif
                        </div>
                        <a href="https://wa.me/{{ $waNumber }}?text=%D9%85%D8%B1%D8%AD%D8%A8%D8%A7%D9%8B%20%D8%A3%D8%B1%D9%8A%D8%AF%20%D8%A7%D9%84%D8%A7%D8%B4%D8%AA%D8%B1%D8%A7%D9%83%20%D9%81%D9%8A%20%D8%AE%D8%B7%D8%A9%20{{ urlencode($plan->name) }}"
                           target="_blank" class="btn {{ $loop->iteration === 3 ? 'btn-primary' : 'btn-outline-primary' }} w-100 mb-3">
                            <i class="fab fa-whatsapp me-1"></i>اشترك الآن
                        </a>
                        <ul class="price-features">
                            <li>{{ $plan->max_invoices_per_month == -1 ? 'فواتير غير محدودة' : $plan->max_invoices_per_month . ' فاتورة/شهر' }}</li>
                            <li>{{ $plan->max_customers == -1 ? 'عملاء غير محدودين' : $plan->max_customers . ' عميل' }}</li>
                            <li>{{ $plan->max_users == -1 ? 'مستخدمون غير محدودين' : $plan->max_users . ' مستخدمين' }}</li>
                            @if($plan->excel_export)<li>تصدير Excel</li>@endif
                            @if($plan->email_send)<li>إرسال بريد إلكتروني</li>@endif
                            @if($plan->stock_management)<li>إدارة المخزون</li>@endif
                            @if($plan->api_access)<li>API Access</li>@endif
                        </ul>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">لا توجد خطط متاحة</div>
            @endforelse
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta">
    <div class="container">
        <h2>جاهز للبدء؟</h2>
        <p>انضم إلى آلاف المستخدمين الذين يديرون فواتيرهم بكفاءة</p>
        <a href="https://wa.me/{{ $waNumber }}?text=%D9%85%D8%B1%D8%AD%D8%A8%D8%A7%D9%8B%20%D8%A3%D8%B1%D9%8A%D8%AF%20%D8%A5%D9%86%D8%B4%D8%A7%D8%A1%20%D8%AD%D8%B3%D8%A7%D8%A8%20%D9%81%D9%8A%20%D9%81%D8%A7%D8%AA%D9%88%D8%B1%D8%AA%D9%83" target="_blank" class="btn btn-light btn-lg fw-bold"><i class="fab fa-whatsapp me-2"></i>تواصل معنا الآن</a>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="container">
        <p class="mb-2"><strong>فاتورتك</strong> - نظام إدارة الفواتير والمخزون</p>
        <p class="small mb-3">جميع الحقوق محفوظة © {{ date('Y') }}</p>
        <div>
            <a href="#" class="me-3"><i class="fab fa-facebook"></i></a>
            <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
            <a href="#" class="me-3"><i class="fab fa-whatsapp"></i></a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
