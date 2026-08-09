<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورتك - نظام إدارة الفواتير والمخزون</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Cairo', 'Segoe UI', sans-serif; overflow-x: hidden; background: #fff; }

        /* NAVBAR */
        .navbar { 
            background: #fff; 
            box-shadow: 0 1px 3px rgba(0,0,0,.05); 
            padding: 1rem 0;
        }
        .navbar-brand { 
            font-size: 1.8rem; 
            font-weight: 900; 
            color: #1e40af !important;
        }
        .navbar-brand i { color: #3b82f6; }
        .nav-link { 
            color: #64748b !important; 
            font-weight: 600; 
            padding: .5rem 1.2rem !important;
            transition: color .3s;
        }
        .nav-link:hover { color: #1e40af !important; }
        .btn-nav { 
            background: #1e40af; 
            color: #fff; 
            padding: .6rem 2rem; 
            border-radius: 8px; 
            font-weight: 700;
            border: none;
        }
        .btn-nav:hover { background: #1e3a8a; color: #fff; }

        /* HERO */
        .hero {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            padding: 100px 0;
            position: relative;
        }
        .hero-content h1 { 
            font-size: 3.5rem; 
            font-weight: 900; 
            color: #0f172a; 
            line-height: 1.2; 
            margin-bottom: 1.5rem;
        }
        .hero-content .highlight { color: #1e40af; }
        .hero-content p { 
            font-size: 1.3rem; 
            color: #475569; 
            margin-bottom: 2.5rem; 
            line-height: 1.7;
        }
        .btn-hero { 
            background: #25D366; 
            color: #fff; 
            padding: 1.2rem 3rem; 
            font-size: 1.2rem; 
            font-weight: 700; 
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(37, 211, 102, .3);
            transition: all .3s;
        }
        .btn-hero:hover { 
            background: #128C7E; 
            transform: translateY(-2px); 
            box-shadow: 0 15px 40px rgba(37, 211, 102, .4);
            color: #fff;
        }
        .hero-image {
            text-align: center;
            font-size: 15rem;
            color: #bfdbfe;
            opacity: .3;
        }

        /* FEATURES */
        .features { 
            padding: 100px 0; 
            background: #fff;
        }
        .section-title { 
            font-size: 2.5rem; 
            font-weight: 900; 
            text-align: center; 
            color: #0f172a; 
            margin-bottom: 1rem;
        }
        .section-subtitle { 
            text-align: center; 
            color: #64748b; 
            font-size: 1.2rem; 
            margin-bottom: 4rem;
        }
        .feature-box {
            background: #fff;
            padding: 2rem;
            border-radius: 16px;
            border: 2px solid #e2e8f0;
            transition: all .3s;
            height: 100%;
        }
        .feature-box:hover {
            border-color: #3b82f6;
            box-shadow: 0 10px 30px rgba(59, 130, 246, .1);
            transform: translateY(-5px);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            background: #eff6ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e40af;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
        }
        .feature-box h5 { 
            font-weight: 700; 
            color: #0f172a; 
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .feature-box p { 
            color: #64748b; 
            line-height: 1.6; 
            margin: 0;
        }

        /* PRICING */
        .pricing { 
            padding: 100px 0; 
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }
        .price-box {
            background: #fff;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.1);
            border: 3px solid #1e40af;
            position: relative;
        }
        .price-badge {
            position: absolute;
            top: -15px;
            right: 50%;
            transform: translateX(50%);
            background: linear-gradient(135deg, #f59e0b, #dc2626);
            color: #fff;
            padding: .5rem 1.5rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: .9rem;
        }
        .price-box h3 { 
            font-size: 2rem; 
            font-weight: 900; 
            color: #0f172a; 
            text-align: center; 
            margin-bottom: 1rem;
        }
        .price-amount {
            text-align: center;
            margin: 2rem 0;
        }
        .price-amount .currency { 
            font-size: 2rem; 
            color: #64748b; 
            vertical-align: super;
        }
        .price-amount .amount { 
            font-size: 5rem; 
            font-weight: 900; 
            color: #1e40af;
        }
        .price-amount .period { 
            display: block; 
            color: #64748b; 
            font-size: 1.2rem; 
            margin-top: .5rem;
        }
        .price-features {
            margin: 2rem 0;
        }
        .price-features li {
            padding: 1rem 0;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
        }
        .price-features li:last-child { border: none; }
        .price-features li i {
            color: #10b981;
            margin-left: 1rem;
            font-size: 1.2rem;
        }
        .btn-price {
            background: #25D366;
            border: none;
            padding: 1.2rem;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 12px;
            width: 100%;
            color: #fff;
        }
        .btn-price:hover { 
            background: #128C7E; 
            color: #fff;
        }

        /* CTA */
        .cta {
            padding: 100px 0;
            background: #1e40af;
            color: #fff;
            text-align: center;
        }
        .cta h2 { 
            font-size: 2.8rem; 
            font-weight: 900; 
            margin-bottom: 1.5rem;
        }
        .cta p { 
            font-size: 1.3rem; 
            margin-bottom: 2.5rem; 
            color: #dbeafe;
        }
        .btn-cta {
            background: #fff;
            color: #1e40af;
            padding: 1.2rem 3rem;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 12px;
            border: none;
        }
        .btn-cta:hover { 
            background: #f1f5f9; 
            color: #1e40af;
        }

        /* FOOTER */
        footer { 
            background: #0f172a; 
            color: #94a3b8; 
            padding: 3rem 0 1.5rem;
        }
        footer h6 { 
            color: #fff; 
            font-weight: 700; 
            margin-bottom: 1rem;
        }
        footer a { 
            color: #94a3b8; 
            text-decoration: none; 
            transition: color .3s;
        }
        footer a:hover { color: #fff; }
        .social-links a {
            display: inline-flex;
            width: 40px;
            height: 40px;
            background: #1e293b;
            border-radius: 8px;
            align-items: center;
            justify-content: center;
            margin-left: .5rem;
            transition: all .3s;
        }
        .social-links a:hover {
            background: #1e40af;
            color: #fff;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 2.2rem; }
            .hero-content p { font-size: 1.1rem; }
            .hero { padding: 60px 0; }
            .features, .pricing, .cta { padding: 60px 0; }
            .section-title { font-size: 2rem; }
            .price-amount .amount { font-size: 3.5rem; }
            .hero-image { font-size: 8rem; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-receipt"></i> فاتورتك
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="#features">المميزات</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">السعر</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">دخول</a></li>
                <li class="nav-item ms-2">
                    <a class="btn btn-nav" href="https://wa.me/249912345678?text=مرحباً، أريد الاشتراك في فاتورتك" target="_blank">
                        <i class="fab fa-whatsapp me-1"></i> تواصل معنا للاشتراك
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content">
                <h1>أدر فواتيرك <span class="highlight">بذكاء</span></h1>
                <p>نظام متكامل لإدارة الفواتير والمخزون والعملاء. وفّر وقتك وركّز على نمو أعمالك</p>
                <a href="https://wa.me/249912345678?text=مرحباً، أريد الاشتراك في فاتورتك" target="_blank" class="btn btn-hero">
                    <i class="fab fa-whatsapp me-2"></i> تواصل معنا للاشتراك
                </a>
            </div>
            <div class="col-lg-6">
                <div class="hero-image">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
    <div class="container">
        <h2 class="section-title">كل ما تحتاجه في مكان واحد</h2>
        <p class="section-subtitle">أدوات قوية لإدارة أعمالك بكفاءة</p>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-file-invoice"></i></div>
                    <h5>فواتير احترافية</h5>
                    <p>أنشئ فواتير احترافية بسهولة مع قوالب قابلة للتخصيص</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-warehouse"></i></div>
                    <h5>إدارة المخزون</h5>
                    <p>تتبع المخزون تلقائياً مع تنبيهات عند نقص المنتجات</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <h5>إدارة العملاء</h5>
                    <p>احفظ بيانات عملائك وتابع سجل معاملاتهم</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                    <h5>تقارير تفصيلية</h5>
                    <p>احصل على تقارير شاملة عن مبيعاتك وأرباحك</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                    <h5>متوافق مع الموبايل</h5>
                    <p>استخدم النظام من أي جهاز في أي وقت</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h5>آمن وموثوق</h5>
                    <p>بيانات آمنة مع نسخ احتياطية يومية</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PRICING -->
<section class="pricing" id="pricing">
    <div class="container">
        <h2 class="section-title">سعر بسيط وواضح</h2>
        <p class="section-subtitle">كل المميزات بسعر واحد</p>
        
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="price-box">
                    <div class="price-badge">⭐ العرض الأفضل</div>
                    
                    <h3>الاشتراك السنوي</h3>
                    
                    <div class="price-amount">
                        <span class="currency">$</span>
                        <span class="amount">400</span>
                        <span class="period">سنوياً</span>
                    </div>
                    
                    <div class="text-center mb-3">
                        <span class="badge bg-success" style="font-size: 1rem; padding: .5rem 1rem;">فقط $33 شهرياً</span>
                    </div>
                    
                    <ul class="price-features list-unstyled">
                        <li><i class="fas fa-check-circle"></i> فواتير غير محدودة</li>
                        <li><i class="fas fa-check-circle"></i> عملاء غير محدودين</li>
                        <li><i class="fas fa-check-circle"></i> منتجات غير محدودة</li>
                        <li><i class="fas fa-check-circle"></i> مستخدمون غير محدودين</li>
                        <li><i class="fas fa-check-circle"></i> تصدير PDF و Excel</li>
                        <li><i class="fas fa-check-circle"></i> إرسال بريد وواتساب</li>
                        <li><i class="fas fa-check-circle"></i> إدارة المخزون الكاملة</li>
                        <li><i class="fas fa-check-circle"></i> قوالب فواتير مخصصة</li>
                        <li><i class="fas fa-check-circle"></i> تقارير متقدمة</li>
                        <li><i class="fas fa-check-circle"></i> نسخ احتياطي يومي</li>
                        <li><i class="fas fa-check-circle"></i> دعم فني 24/7</li>
                        <li><i class="fas fa-check-circle"></i> تحديثات مجانية</li>
                    </ul>
                    
                    <a href="https://wa.me/249912345678?text=مرحباً، أريد الاشتراك في فاتورتك" target="_blank" class="btn btn-price" style="margin-top: 2rem;">
                        <i class="fab fa-whatsapp me-2"></i> تواصل معنا للاشتراك
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta">
    <div class="container">
        <h2>جاهز للبدء؟</h2>
        <p>تواصل معنا عبر واتساب للاشتراك والحصول على حسابك</p>
        <a href="https://wa.me/249912345678?text=مرحباً، أريد الاشتراك في فاتورتك" target="_blank" class="btn btn-cta">
            <i class="fab fa-whatsapp me-2"></i> تواصل عبر واتساب
        </a>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h6><i class="fas fa-receipt me-2"></i>فاتورتك</h6>
                <p>نظام إدارة الفواتير والمخزون الاحترافي</p>
            </div>
            <div class="col-md-4 mb-4">
                <h6>روابط سريعة</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#features">المميزات</a></li>
                    <li class="mb-2"><a href="#pricing">السعر</a></li>
                    <li class="mb-2"><a href="{{ route('login') }}">دخول</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h6>تواصل معنا</h6>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <hr style="border-color: #1e293b; margin: 2rem 0 1rem;">
        <div class="text-center">
            <p class="mb-0">© {{ date('Y') }} فاتورتك. جميع الحقوق محفوظة</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
