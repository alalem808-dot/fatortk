<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(\App\Models\PlatformSetting::get('platform_name','فاتورتك')); ?> - نظام إدارة الأعمال الاحترافي</title>
    <meta name="description" content="نظام متكامل لإدارة الفواتير والمخزون والمشتريات والتقارير. حلول SaaS للشركات الصغيرة والمتوسطة.">
    <?php $favicon = \App\Models\PlatformSetting::imageUrl('platform_favicon'); ?>
    <?php if($favicon): ?><link rel="icon" type="image/png" href="<?php echo e($favicon); ?>"><?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap');

        :root {
            --primary: #1e40af;
            --primary-dark: #1e3a8a;
            --primary-light: #3b82f6;
            --accent: #0ea5e9;
            --success: #10b981;
            --warning: #f59e0b;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Cairo', sans-serif; overflow-x: hidden; background: #fff; color: var(--text-dark); }

        /* ===== NAVBAR ===== */
        .navbar {
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(12px);
            box-shadow: 0 1px 0 var(--border);
            padding: .9rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand img { max-height: 38px; max-width: 140px; object-fit: contain; }
        .navbar-brand .brand-text { font-size: 1.6rem; font-weight: 900; color: var(--primary); }
        .navbar-brand .brand-text i { color: var(--primary-light); }
        .nav-link { color: var(--text-muted) !important; font-weight: 600; padding: .5rem 1rem !important; transition: color .2s; border-radius: 8px; }
        .nav-link:hover { color: var(--primary) !important; background: #eff6ff; }
        .btn-login { border: 2px solid var(--primary); color: var(--primary) !important; border-radius: 8px; padding: .45rem 1.4rem !important; font-weight: 700; transition: all .2s; }
        .btn-login:hover { background: var(--primary); color: #fff !important; }
        .btn-nav-cta { background: #25D366; color: #fff !important; padding: .5rem 1.4rem !important; border-radius: 8px; font-weight: 700; transition: all .2s; }
        .btn-nav-cta:hover { background: #128C7E; color: #fff !important; transform: translateY(-1px); }

        /* ===== HERO ===== */
        .hero {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #e0f2fe 100%);
            padding: 90px 0 80px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -100px; left: -100px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(59,130,246,.12) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -80px; right: -80px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(14,165,233,.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            background: #fff; border: 1.5px solid #bfdbfe;
            color: var(--primary); padding: .4rem 1rem;
            border-radius: 30px; font-size: .85rem; font-weight: 700;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(30,64,175,.08);
        }
        .hero-badge span { width: 8px; height: 8px; background: var(--success); border-radius: 50%; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(1.3)} }
        .hero h1 { font-size: 3.2rem; font-weight: 900; line-height: 1.25; color: var(--text-dark); margin-bottom: 1.3rem; }
        .hero h1 .highlight { color: var(--primary); }
        .hero p.lead { font-size: 1.2rem; color: var(--text-muted); line-height: 1.75; margin-bottom: 2.2rem; }
        .hero-stats {
            display: flex; gap: 2rem; flex-wrap: wrap;
            padding: 1.2rem 1.5rem;
            background: #fff; border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,.06);
            margin-top: 2rem; border: 1px solid var(--border);
        }
        .hero-stat { text-align: center; }
        .hero-stat .num { font-size: 1.6rem; font-weight: 900; color: var(--primary); display: block; }
        .hero-stat .lbl { font-size: .8rem; color: var(--text-muted); font-weight: 600; }
        .btn-hero-primary {
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: #fff; padding: 1rem 2.2rem;
            font-size: 1.1rem; font-weight: 700;
            border-radius: 12px; border: none;
            box-shadow: 0 8px 24px rgba(37,211,102,.3);
            transition: all .3s; text-decoration: none;
            display: inline-flex; align-items: center; gap: .5rem;
        }
        .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(37,211,102,.4); color: #fff; }
        .btn-hero-secondary {
            background: #fff; color: var(--primary);
            padding: 1rem 2rem; font-size: 1.1rem; font-weight: 700;
            border-radius: 12px; border: 2px solid var(--primary);
            transition: all .3s; text-decoration: none;
            display: inline-flex; align-items: center; gap: .5rem;
        }
        .btn-hero-secondary:hover { background: var(--primary); color: #fff; transform: translateY(-2px); }
        .hero-visual {
            position: relative; z-index: 2;
        }
        .hero-visual .dashboard-mock {
            background: #fff; border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.12), 0 4px 12px rgba(0,0,0,.06);
            overflow: hidden; border: 1px solid var(--border);
        }
        .mock-bar { background: linear-gradient(135deg, var(--primary), var(--accent)); padding: .7rem 1.2rem; display: flex; align-items: center; gap: .5rem; }
        .mock-bar span { width: 10px; height: 10px; border-radius: 50%; }
        .mock-bar .r { background: #ff5f57; }
        .mock-bar .y { background: #febc2e; }
        .mock-bar .g { background: #28c840; }
        .mock-body { padding: 1.5rem; }
        .mock-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: .8rem; margin-bottom: 1rem; }
        .mock-stat-box { background: var(--bg-light); border-radius: 10px; padding: .8rem; border: 1px solid var(--border); }
        .mock-stat-box .label { font-size: .65rem; color: var(--text-muted); font-weight: 600; margin-bottom: .3rem; }
        .mock-stat-box .value { font-size: 1.1rem; font-weight: 900; color: var(--primary); }
        .mock-bars { display: flex; gap: .5rem; align-items: flex-end; height: 70px; }
        .mock-bar-item { flex: 1; background: linear-gradient(to top, var(--primary), var(--accent)); border-radius: 4px 4px 0 0; opacity: .8; }
        .mock-table { margin-top: 1rem; }
        .mock-row { display: flex; justify-content: space-between; padding: .4rem .5rem; border-radius: 6px; font-size: .7rem; margin-bottom: .25rem; }
        .mock-row:nth-child(odd) { background: var(--bg-light); }
        .mock-row .badge-paid { background: #d1fae5; color: var(--success); padding: .1rem .5rem; border-radius: 20px; font-size: .65rem; font-weight: 700; }
        .mock-row .badge-pending { background: #fef3c7; color: var(--warning); padding: .1rem .5rem; border-radius: 20px; font-size: .65rem; font-weight: 700; }

        /* ===== SECTION SHARED ===== */
        section { padding: 90px 0; }
        .section-label {
            display: inline-flex; align-items: center; gap: .4rem;
            background: #eff6ff; color: var(--primary);
            padding: .35rem .9rem; border-radius: 20px;
            font-size: .8rem; font-weight: 700; margin-bottom: 1rem;
        }
        .section-title { font-size: 2.3rem; font-weight: 900; color: var(--text-dark); margin-bottom: .8rem; }
        .section-sub { font-size: 1.1rem; color: var(--text-muted); line-height: 1.7; margin-bottom: 3rem; }

        /* ===== FEATURES ===== */
        .features { background: #fff; }
        .feature-card {
            background: #fff; border: 1.5px solid var(--border);
            border-radius: 16px; padding: 1.8rem; height: 100%;
            transition: all .3s; position: relative; overflow: hidden;
        }
        .feature-card::before {
            content: ''; position: absolute; top: 0; right: 0;
            width: 4px; height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--accent));
            opacity: 0; transition: opacity .3s;
        }
        .feature-card:hover { border-color: var(--primary-light); box-shadow: 0 8px 30px rgba(30,64,175,.1); transform: translateY(-4px); }
        .feature-card:hover::before { opacity: 1; }
        .feature-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-radius: 12px; display: flex; align-items: center;
            justify-content: center; color: var(--primary);
            font-size: 1.5rem; margin-bottom: 1.2rem;
            transition: all .3s;
        }
        .feature-card:hover .feature-icon { background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; }
        .feature-card h5 { font-size: 1rem; font-weight: 800; color: var(--text-dark); margin-bottom: .7rem; }
        .feature-card p { color: var(--text-muted); font-size: .92rem; line-height: 1.65; margin: 0; }
        .feature-card .feature-tags { margin-top: 1rem; display: flex; flex-wrap: wrap; gap: .4rem; }
        .feature-tag { font-size: .7rem; font-weight: 700; background: var(--bg-light); color: var(--text-muted); padding: .2rem .6rem; border-radius: 20px; border: 1px solid var(--border); }

        /* ===== HOW IT WORKS ===== */
        .how-it-works { background: var(--bg-light); }
        .step-card {
            background: #fff; border-radius: 16px; padding: 1.8rem;
            border: 1.5px solid var(--border); height: 100%;
            position: relative; transition: all .3s;
        }
        .step-card:hover { border-color: var(--primary-light); box-shadow: 0 8px 30px rgba(30,64,175,.08); }
        .step-number {
            width: 44px; height: 44px; background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.1rem; font-weight: 900; margin-bottom: 1rem;
        }
        .step-card h6 { font-size: 1rem; font-weight: 800; color: var(--text-dark); margin-bottom: .6rem; }
        .step-card p { color: var(--text-muted); font-size: .9rem; line-height: 1.6; margin: 0; }
        .step-connector {
            display: none;
        }
        @media (min-width: 992px) {
            .step-connector {
                display: flex; align-items: center; justify-content: center;
                color: var(--primary-light); font-size: 1.5rem;
            }
        }

        /* ===== MODULES ===== */
        .modules { background: #fff; }
        .module-item {
            display: flex; align-items: flex-start; gap: 1rem;
            padding: 1.2rem 1.4rem; border-radius: 12px;
            border: 1px solid var(--border); transition: all .25s;
            background: #fff;
        }
        .module-item:hover { border-color: var(--primary-light); background: #f0f7ff; }
        .module-icon {
            width: 42px; height: 42px; min-width: 42px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-radius: 10px; display: flex; align-items: center;
            justify-content: center; color: var(--primary); font-size: 1.2rem;
        }
        .module-item h6 { font-size: .92rem; font-weight: 800; color: var(--text-dark); margin-bottom: .25rem; }
        .module-item p { font-size: .82rem; color: var(--text-muted); margin: 0; line-height: 1.5; }

        /* ===== PERMISSIONS ===== */
        .permissions { background: var(--bg-light); }
        .role-card {
            background: #fff; border: 1.5px solid var(--border);
            border-radius: 14px; padding: 1.5rem; height: 100%;
            transition: all .3s;
        }
        .role-card:hover { border-color: var(--primary-light); box-shadow: 0 6px 24px rgba(30,64,175,.08); }
        .role-card .role-icon { font-size: 2rem; margin-bottom: .8rem; }
        .role-card h6 { font-weight: 800; color: var(--text-dark); margin-bottom: .8rem; }
        .role-perm { font-size: .8rem; color: var(--text-muted); padding: .2rem 0; display: flex; align-items: center; gap: .4rem; }
        .role-perm i { color: var(--success); font-size: .75rem; }

        /* ===== PRICING ===== */
        .pricing { background: linear-gradient(135deg, #eff6ff 0%, #e0f2fe 100%); }
        .price-box {
            background: #fff; border-radius: 24px;
            padding: 2.5rem; border: 3px solid var(--primary);
            box-shadow: 0 20px 60px rgba(30,64,175,.15);
            position: relative; max-width: 480px; margin: 0 auto;
        }
        .price-badge {
            position: absolute; top: -16px; right: 50%; transform: translateX(50%);
            background: linear-gradient(135deg, #f59e0b, #dc2626);
            color: #fff; padding: .4rem 1.4rem;
            border-radius: 20px; font-weight: 700; font-size: .85rem;
        }
        .price-box h3 { font-size: 1.8rem; font-weight: 900; color: var(--text-dark); text-align: center; margin-bottom: .5rem; }
        .price-amount { text-align: center; margin: 1.5rem 0; }
        .price-amount .currency { font-size: 1.8rem; color: var(--text-muted); vertical-align: super; }
        .price-amount .amount { font-size: 4.5rem; font-weight: 900; color: var(--primary); }
        .price-amount .period { display: block; color: var(--text-muted); font-size: 1rem; }
        .price-features { margin: 1.5rem 0; }
        .price-feature-row {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: .6rem;
        }
        .price-feature-item { display: flex; align-items: center; gap: .5rem; padding: .5rem; font-size: .88rem; color: #475569; }
        .price-feature-item i { color: var(--success); font-size: .9rem; flex-shrink: 0; }
        .btn-price {
            background: linear-gradient(135deg, #25D366, #128C7E);
            border: none; padding: 1rem;
            font-size: 1.1rem; font-weight: 700;
            border-radius: 12px; width: 100%; color: #fff;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            transition: all .3s; text-decoration: none;
        }
        .btn-price:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,211,102,.35); color: #fff; }

        /* ===== CTA ===== */
        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 50%, #1e3a8a 100%);
            padding: 90px 0; color: #fff; text-align: center;
            position: relative; overflow: hidden;
        }
        .cta-section::before {
            content: ''; position: absolute; top: -50%; left: -10%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        .cta-section h2 { font-size: 2.5rem; font-weight: 900; margin-bottom: 1rem; }
        .cta-section p { font-size: 1.15rem; color: #bfdbfe; margin-bottom: 2rem; line-height: 1.7; }
        .btn-cta { background: #fff; color: var(--primary); padding: 1rem 2.5rem; font-size: 1.1rem; font-weight: 700; border-radius: 12px; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: .5rem; transition: all .3s; }
        .btn-cta:hover { background: #f1f5f9; color: var(--primary); transform: translateY(-2px); }

        /* ===== FOOTER ===== */
        footer { background: #0f172a; color: #94a3b8; padding: 3rem 0 1.5rem; }
        footer h6 { color: #fff; font-weight: 800; margin-bottom: 1rem; }
        footer a { color: #94a3b8; text-decoration: none; transition: color .2s; font-size: .92rem; }
        footer a:hover { color: #fff; }
        footer ul { list-style: none; padding: 0; }
        footer ul li { margin-bottom: .5rem; }
        .social-links { display: flex; gap: .5rem; margin-top: .5rem; }
        .social-links a { width: 38px; height: 38px; background: #1e293b; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all .3s; }
        .social-links a:hover { background: var(--primary); color: #fff; }
        .footer-brand { font-size: 1.4rem; font-weight: 900; color: #fff; margin-bottom: .8rem; display: flex; align-items: center; gap: .4rem; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991px) {
            .hero { padding: 60px 0; }
            .hero h1 { font-size: 2.2rem; }
            .hero p.lead { font-size: 1.05rem; }
            section { padding: 60px 0; }
            .section-title { font-size: 1.9rem; }
            .hero-visual { margin-top: 2.5rem; }
            .hero-stats { gap: 1rem; }
            .price-amount .amount { font-size: 3.5rem; }
            .price-feature-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <?php $platformLogo = \App\Models\PlatformSetting::imageUrl('platform_logo'); ?>
            <?php if($platformLogo): ?>
                <img src="<?php echo e($platformLogo); ?>" alt="<?php echo e(\App\Models\PlatformSetting::get('platform_name','فاتورتك')); ?>">
            <?php else: ?>
                <span class="brand-text"><i class="fas fa-file-invoice-dollar"></i> <?php echo e(\App\Models\PlatformSetting::get('platform_name','فاتورتك')); ?></span>
            <?php endif; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav mx-auto align-items-center gap-1">
                <li class="nav-item"><a class="nav-link" href="#features">المميزات</a></li>
                <li class="nav-item"><a class="nav-link" href="#modules">الوحدات</a></li>
                <li class="nav-item"><a class="nav-link" href="#how">كيف يعمل</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">السعر</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo e(route('login')); ?>" class="nav-link btn-login">دخول</a>
                <a href="<?php echo e(\App\Models\PlatformSetting::whatsappUrl('whatsapp_subscribe_msg')); ?>" target="_blank" class="nav-link btn-nav-cta">
                    <i class="fab fa-whatsapp me-1"></i> اشترك الآن
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ===== HERO ===== -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-badge">
                    <span></span> نظام SaaS متكامل للشركات
                </div>
                <h1>أدر أعمالك بالكامل<br>من مكان <span class="highlight">واحد</span></h1>
                <p class="lead">
                    فواتير + مخزون + مشتريات + موردون + تقارير مالية + صلاحيات متقدمة.<br>
                    كل ما تحتاجه لإدارة شركتك باحترافية وكفاءة.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo e(\App\Models\PlatformSetting::whatsappUrl('whatsapp_subscribe_msg')); ?>" target="_blank" class="btn-hero-primary">
                        <i class="fab fa-whatsapp"></i> ابدأ الآن عبر واتساب
                    </a>
                    <a href="<?php echo e(route('login')); ?>" class="btn-hero-secondary">
                        <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="num">15+</span>
                        <span class="lbl">وحدة متكاملة</span>
                    </div>
                    <div class="hero-stat">
                        <span class="num">100%</span>
                        <span class="lbl">عربي وRTL</span>
                    </div>
                    <div class="hero-stat">
                        <span class="num">∞</span>
                        <span class="lbl">فواتير وعملاء</span>
                    </div>
                    <div class="hero-stat">
                        <span class="num">24/7</span>
                        <span class="lbl">دعم فني</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-visual">
                    <div class="dashboard-mock">
                        <div class="mock-bar">
                            <span class="r"></span><span class="y"></span><span class="g"></span>
                            <small class="text-white ms-auto" style="font-family:monospace;font-size:.7rem;opacity:.8">لوحة تحكم فاتورتك</small>
                        </div>
                        <div class="mock-body">
                            <div class="mock-stats">
                                <div class="mock-stat-box">
                                    <div class="label">الإيرادات</div>
                                    <div class="value">٢٤,٥٠٠</div>
                                </div>
                                <div class="mock-stat-box">
                                    <div class="label">الفواتير</div>
                                    <div class="value">١٢٨</div>
                                </div>
                                <div class="mock-stat-box">
                                    <div class="label">صافي الربح</div>
                                    <div class="value" style="color:var(--success)">٨,٢٠٠</div>
                                </div>
                            </div>
                            <div style="background:var(--bg-light);border-radius:10px;padding:.8rem;border:1px solid var(--border)">
                                <div style="font-size:.7rem;color:var(--text-muted);font-weight:700;margin-bottom:.6rem">إيرادات آخر ٦ أشهر</div>
                                <div class="mock-bars">
                                    <div class="mock-bar-item" style="height:45%"></div>
                                    <div class="mock-bar-item" style="height:62%"></div>
                                    <div class="mock-bar-item" style="height:55%"></div>
                                    <div class="mock-bar-item" style="height:80%"></div>
                                    <div class="mock-bar-item" style="height:70%"></div>
                                    <div class="mock-bar-item" style="height:100%"></div>
                                </div>
                            </div>
                            <div class="mock-table">
                                <div style="font-size:.7rem;color:var(--text-muted);font-weight:700;margin-bottom:.4rem">آخر الفواتير</div>
                                <div class="mock-row"><span>INV-0128 — محمد أحمد</span><span class="badge-paid">مدفوعة</span></div>
                                <div class="mock-row"><span>INV-0127 — شركة النور</span><span class="badge-pending">معلقة</span></div>
                                <div class="mock-row"><span>INV-0126 — خالد العمر</span><span class="badge-paid">مدفوعة</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== FEATURES ===== -->
<section class="features" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label"><i class="fas fa-star"></i> المميزات الرئيسية</div>
            <h2 class="section-title">كل أدوات إدارة أعمالك في منصة واحدة</h2>
            <p class="section-sub">نظام متكامل يغطي دورة العمل الكاملة من الفاتورة حتى تقرير الأرباح</p>
        </div>
        <div class="row g-4">

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    <h5>فواتير احترافية متكاملة</h5>
                    <p>أنشئ فواتير بقوالب قابلة للتخصيص، أرسلها بـ PDF أو واتساب أو بريد إلكتروني. ادعم الخصومات والضرائب وشروط الدفع.</p>
                    <div class="feature-tags">
                        <span class="feature-tag">PDF تلقائي</span>
                        <span class="feature-tag">إرسال واتساب</span>
                        <span class="feature-tag">رابط عام</span>
                        <span class="feature-tag">قوالب مخصصة</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-warehouse"></i></div>
                    <h5>إدارة مخزون متعدد المخازن</h5>
                    <p>تتبع كمياتك في مخازن متعددة، نقل مخزون بين الفروع، تنبيهات المخزون المنخفض، وجرد دوري شامل.</p>
                    <div class="feature-tags">
                        <span class="feature-tag">مخازن متعددة</span>
                        <span class="feature-tag">نقل مخزون</span>
                        <span class="feature-tag">جرد دوري</span>
                        <span class="feature-tag">تنبيهات</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shopping-cart"></i></div>
                    <h5>إدارة المشتريات والموردين</h5>
                    <p>أوامر شراء متكاملة تُضيف للمخزون تلقائياً عند الاستلام، مع تتبع متوسط سعر التكلفة ودفعات الموردين.</p>
                    <div class="feature-tags">
                        <span class="feature-tag">WAC تلقائي</span>
                        <span class="feature-tag">دفعات الموردين</span>
                        <span class="feature-tag">مرتجعات الشراء</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h5>تقارير مالية تفصيلية</h5>
                    <p>تقارير المبيعات والمشتريات والمخزون والعملاء وتقرير الأرباح والخسائر الكامل بتصدير Excel.</p>
                    <div class="feature-tags">
                        <span class="feature-tag">P&L كامل</span>
                        <span class="feature-tag">تصدير Excel</span>
                        <span class="feature-tag">فلترة متقدمة</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-users-cog"></i></div>
                    <h5>صلاحيات متقدمة ومتعددة المستخدمين</h5>
                    <p>أضف موظفيك بصلاحيات دقيقة (مبيعات، مشتريات، مخزن، محاسب) وحدد المخازن والبيانات المتاحة لكل مستخدم.</p>
                    <div class="feature-tags">
                        <span class="feature-tag">5 أدوار جاهزة</span>
                        <span class="feature-tag">صلاحيات مخصصة</span>
                        <span class="feature-tag">عزل البيانات</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-undo-alt"></i></div>
                    <h5>مرتجعات المبيعات والمشتريات</h5>
                    <p>إدارة مرتجعات المبيعات بإعادة المخزون تلقائياً وتعديل الفاتورة، ومرتجعات الشراء بخصم من المخزون.</p>
                    <div class="feature-tags">
                        <span class="feature-tag">مرتجع جزئي</span>
                        <span class="feature-tag">تسوية تلقائية</span>
                        <span class="feature-tag">تحديث فوري</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <h5>تتبع المدفوعات والمستحقات</h5>
                    <p>سجّل دفعات جزئية أو كاملة بطرق دفع متعددة (نقد، تحويل، بطاقة)، وتابع المتأخرات لكل عميل.</p>
                    <div class="feature-tags">
                        <span class="feature-tag">دفع جزئي</span>
                        <span class="feature-tag">طرق دفع متعددة</span>
                        <span class="feature-tag">تتبع المتأخرات</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-receipt"></i></div>
                    <h5>إدارة المصروفات التشغيلية</h5>
                    <p>سجّل مصروفاتك بفئات مخصصة (إيجار، رواتب، كهرباء...) مع العملة وسعر الصرف. تُحسب تلقائياً في تقرير الأرباح.</p>
                    <div class="feature-tags">
                        <span class="feature-tag">فئات مخصصة</span>
                        <span class="feature-tag">دعم عملات</span>
                        <span class="feature-tag">ربط P&L</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h5>SaaS متعدد المستأجرين</h5>
                    <p>كل شركة بيانات معزولة تماماً. نسخ احتياطي يومي، أمان عالٍ، وإمكانية توسع غير محدود.</p>
                    <div class="feature-tags">
                        <span class="feature-tag">عزل تام للبيانات</span>
                        <span class="feature-tag">نسخ احتياطي</span>
                        <span class="feature-tag">Laravel 10</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="how-it-works" id="how">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label"><i class="fas fa-route"></i> كيف تبدأ</div>
            <h2 class="section-title">ابدأ في دقائق، أتقن في يوم</h2>
            <p class="section-sub">خطوات واضحة لإطلاق نظامك وإدارة أعمالك من اليوم الأول</p>
        </div>
        <div class="row g-3 align-items-stretch">
            <div class="col-6 col-lg">
                <div class="step-card">
                    <div class="step-number">١</div>
                    <h6>سجّل حسابك</h6>
                    <p>أدخل اسم شركتك وبريدك وابدأ مباشرة بحساب تجريبي مجاني</p>
                </div>
            </div>
            <div class="col-lg-auto d-none d-lg-flex step-connector">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="col-6 col-lg">
                <div class="step-card">
                    <div class="step-number">٢</div>
                    <h6>أعدّ شركتك</h6>
                    <p>رفع الشعار، العملة، الضريبة، بادئة الفواتير، طرق الدفع</p>
                </div>
            </div>
            <div class="col-lg-auto d-none d-lg-flex step-connector">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="col-6 col-lg">
                <div class="step-card">
                    <div class="step-number">٣</div>
                    <h6>أضف منتجاتك</h6>
                    <p>منتجات، فئات، مخازن، رصيد افتتاحي، حد تنبيه المخزون</p>
                </div>
            </div>
            <div class="col-lg-auto d-none d-lg-flex step-connector">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="col-6 col-lg">
                <div class="step-card">
                    <div class="step-number">٤</div>
                    <h6>أضف العملاء والموردين</h6>
                    <p>قاعدة بيانات كاملة مع تفاصيل التواصل والمعاملات السابقة</p>
                </div>
            </div>
            <div class="col-lg-auto d-none d-lg-flex step-connector">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="col-6 col-lg">
                <div class="step-card">
                    <div class="step-number">٥</div>
                    <h6>ابدأ الفوترة</h6>
                    <p>أنشئ فواتيرك، تابع المدفوعات، راجع التقارير يومياً</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== MODULES ===== -->
<section class="modules" id="modules">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <div class="section-label"><i class="fas fa-cubes"></i> وحدات النظام</div>
                <h2 class="section-title">١٥ وحدة شاملة لكل احتياجاتك</h2>
                <p class="section-sub">من الفاتورة البسيطة حتى تقرير الأرباح والخسائر الكامل، النظام يغطي دورة العمل بالكامل.</p>
                <a href="<?php echo e(\App\Models\PlatformSetting::whatsappUrl('whatsapp_subscribe_msg')); ?>" target="_blank" class="btn-hero-primary d-inline-flex">
                    <i class="fab fa-whatsapp"></i> اطلب عرضاً توضيحياً
                </a>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">

                    <div class="col-6">
                        <div class="module-item">
                            <div class="module-icon"><i class="fas fa-tachometer-alt"></i></div>
                            <div>
                                <h6>لوحة التحكم</h6>
                                <p>إيرادات، أرباح، مخزون منخفض، رسوم بيانية</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="module-item">
                            <div class="module-icon"><i class="fas fa-file-invoice"></i></div>
                            <div>
                                <h6>الفواتير</h6>
                                <p>إنشاء، إرسال، تتبع، PDF، واتساب، بريد</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="module-item">
                            <div class="module-icon"><i class="fas fa-users"></i></div>
                            <div>
                                <h6>العملاء</h6>
                                <p>قاعدة بيانات + سجل المعاملات + كشف حساب</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="module-item">
                            <div class="module-icon"><i class="fas fa-boxes"></i></div>
                            <div>
                                <h6>المنتجات والمخزون</h6>
                                <p>SKU، باركود، مخازن متعددة، حركات</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="module-item">
                            <div class="module-icon"><i class="fas fa-truck"></i></div>
                            <div>
                                <h6>المشتريات</h6>
                                <p>أوامر شراء، استلام، تحديث التكلفة تلقائياً</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="module-item">
                            <div class="module-icon"><i class="fas fa-handshake"></i></div>
                            <div>
                                <h6>الموردون</h6>
                                <p>قاعدة بيانات + دفعات + سجل المشتريات</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="module-item">
                            <div class="module-icon"><i class="fas fa-cash-register"></i></div>
                            <div>
                                <h6>نقطة البيع (POS)</h6>
                                <p>واجهة بيع سريع مع طباعة إيصال</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="module-item">
                            <div class="module-icon"><i class="fas fa-clipboard-list"></i></div>
                            <div>
                                <h6>الجرد الدوري</h6>
                                <p>إنشاء جلسة، فروقات، اعتماد تسوية</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="module-item">
                            <div class="module-icon"><i class="fas fa-file-alt"></i></div>
                            <div>
                                <h6>التقارير المالية</h6>
                                <p>مبيعات، مشتريات، مخزون، أرباح وخسائر</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="module-item">
                            <div class="module-icon"><i class="fas fa-wallet"></i></div>
                            <div>
                                <h6>المصروفات</h6>
                                <p>تشغيلية بفئات مخصصة، تُدرج في P&L</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== PERMISSIONS ===== -->
<section class="permissions">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label"><i class="fas fa-lock"></i> الصلاحيات والأدوار</div>
            <h2 class="section-title">كل موظف يرى فقط ما يحتاجه</h2>
            <p class="section-sub">نظام صلاحيات دقيق يحمي بياناتك ويرفع كفاءة فريقك</p>
        </div>
        <div class="row g-4 justify-content-center">

            <div class="col-6 col-md-4 col-lg-2">
                <div class="role-card text-center">
                    <div class="role-icon">👑</div>
                    <h6>مدير كامل</h6>
                    <div class="role-perm"><i class="fas fa-check"></i> كل الصلاحيات</div>
                    <div class="role-perm"><i class="fas fa-check"></i> كل التقارير</div>
                    <div class="role-perm"><i class="fas fa-check"></i> كل الإعدادات</div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="role-card text-center">
                    <div class="role-icon">💼</div>
                    <h6>مبيعات</h6>
                    <div class="role-perm"><i class="fas fa-check"></i> إنشاء فواتير</div>
                    <div class="role-perm"><i class="fas fa-check"></i> إدارة العملاء</div>
                    <div class="role-perm"><i class="fas fa-check"></i> عرض المنتجات</div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="role-card text-center">
                    <div class="role-icon">🚚</div>
                    <h6>مشتريات</h6>
                    <div class="role-perm"><i class="fas fa-check"></i> أوامر الشراء</div>
                    <div class="role-perm"><i class="fas fa-check"></i> إدارة الموردين</div>
                    <div class="role-perm"><i class="fas fa-check"></i> إدارة المنتجات</div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="role-card text-center">
                    <div class="role-icon">📊</div>
                    <h6>محاسب</h6>
                    <div class="role-perm"><i class="fas fa-check"></i> كل التقارير</div>
                    <div class="role-perm"><i class="fas fa-check"></i> المصروفات</div>
                    <div class="role-perm"><i class="fas fa-check"></i> عرض الفواتير</div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="role-card text-center">
                    <div class="role-icon">📦</div>
                    <h6>مخزن</h6>
                    <div class="role-perm"><i class="fas fa-check"></i> إدارة المنتجات</div>
                    <div class="role-perm"><i class="fas fa-check"></i> إدارة المخازن</div>
                    <div class="role-perm"><i class="fas fa-check"></i> جرد دوري</div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="role-card text-center">
                    <div class="role-icon">⚙️</div>
                    <h6>مخصص</h6>
                    <div class="role-perm"><i class="fas fa-check"></i> صلاحيات يدوية</div>
                    <div class="role-perm"><i class="fas fa-check"></i> مخازن محددة</div>
                    <div class="role-perm"><i class="fas fa-check"></i> عزل البيانات</div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ===== PRICING ===== -->
<section class="pricing" id="pricing">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label"><i class="fas fa-tag"></i> التسعير</div>
            <h2 class="section-title">سعر واضح، كل المميزات مشمولة</h2>
            <p class="section-sub">بدون رسوم مخفية، بدون قيود على الفواتير أو العملاء أو المستخدمين</p>
        </div>

        <div class="price-box">
            <div class="price-badge">⭐ الأفضل قيمة</div>
            <h3>اشتراك سنوي</h3>
            <div class="price-amount">
                <span class="currency">$</span>
                <span class="amount">600</span>
                <span class="period">سنوياً</span>
            </div>

            <div class="price-feature-row">
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> فواتير غير محدودة</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> عملاء غير محدودين</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> منتجات غير محدودة</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> مستخدمون غير محدودين</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> مخازن متعددة</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> إدارة مشتريات كاملة</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> تقارير الأرباح والخسائر</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> مرتجعات المبيعات والشراء</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> نقطة بيع (POS)</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> جرد دوري</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> صلاحيات متقدمة</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> تصدير PDF و Excel</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> إرسال بريد وواتساب</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> قوالب فواتير مخصصة</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> نسخ احتياطي يومي</div>
                <div class="price-feature-item"><i class="fas fa-check-circle"></i> دعم فني 24/7</div>
            </div>

            <a href="<?php echo e(\App\Models\PlatformSetting::whatsappUrl('whatsapp_subscribe_msg')); ?>" target="_blank" class="btn-price mt-4">
                <i class="fab fa-whatsapp"></i> تواصل معنا للاشتراك
            </a>
        </div>
    </div>
</section>

<!-- ===== CTA ===== -->
<section class="cta-section">
    <div class="container" style="position:relative;z-index:2">
        <h2>جاهز تبدأ تدير أعمالك باحتراف؟</h2>
        <p>تواصل معنا الآن عبر واتساب وابدأ استخدام النظام خلال دقائق</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?php echo e(\App\Models\PlatformSetting::whatsappUrl('whatsapp_subscribe_msg')); ?>" target="_blank" class="btn-cta">
                <i class="fab fa-whatsapp" style="color:#25D366"></i> تواصل عبر واتساب
            </a>
            <a href="<?php echo e(route('login')); ?>" class="btn-cta" style="background:transparent;border:2px solid rgba(255,255,255,.4);color:#fff">
                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
            </a>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <?php $footerLogo = \App\Models\PlatformSetting::imageUrl('platform_logo'); ?>
                <?php if($footerLogo): ?>
                    <img src="<?php echo e($footerLogo); ?>" alt="<?php echo e(\App\Models\PlatformSetting::get('platform_name','فاتورتك')); ?>" style="max-height:36px;max-width:130px;object-fit:contain;margin-bottom:.8rem;filter:brightness(0) invert(1)">
                <?php else: ?>
                    <div class="footer-brand"><i class="fas fa-file-invoice-dollar text-blue-400"></i> <?php echo e(\App\Models\PlatformSetting::get('platform_name','فاتورتك')); ?></div>
                <?php endif; ?>
                <p style="font-size:.9rem;line-height:1.7">نظام إدارة الأعمال المتكامل — فواتير، مخزون، مشتريات، وتقارير مالية لشركتك.</p>
                <?php if(\App\Models\PlatformSetting::get('support_email')): ?>
                <div class="mt-2" style="font-size:.85rem">
                    <i class="fas fa-envelope me-1" style="color:var(--primary-light)"></i>
                    <a href="mailto:<?php echo e(\App\Models\PlatformSetting::get('support_email')); ?>"><?php echo e(\App\Models\PlatformSetting::get('support_email')); ?></a>
                </div>
                <?php endif; ?>
                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="<?php echo e(\App\Models\PlatformSetting::whatsappUrl('whatsapp_subscribe_msg')); ?>" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <h6>المنصة</h6>
                <ul>
                    <li><a href="#features">المميزات</a></li>
                    <li><a href="#modules">الوحدات</a></li>
                    <li><a href="#pricing">الأسعار</a></li>
                    <li><a href="#how">كيف تبدأ</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-2">
                <h6>الوحدات</h6>
                <ul>
                    <li><a href="#">الفواتير</a></li>
                    <li><a href="#">المخزون</a></li>
                    <li><a href="#">المشتريات</a></li>
                    <li><a href="#">التقارير</a></li>
                    <li><a href="#">نقطة البيع</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>ابدأ الآن</h6>
                <p style="font-size:.88rem;line-height:1.6;margin-bottom:1rem">تواصل معنا عبر واتساب للحصول على عرض تجريبي أو الاشتراك في النظام.</p>
                <a href="<?php echo e(\App\Models\PlatformSetting::whatsappUrl('whatsapp_subscribe_msg')); ?>" target="_blank"
                   style="display:inline-flex;align-items:center;gap:.5rem;background:#25D366;color:#fff;padding:.6rem 1.4rem;border-radius:8px;font-weight:700;text-decoration:none;font-size:.9rem;transition:all .3s"
                   onmouseover="this.style.background='#128C7E'" onmouseout="this.style.background='#25D366'">
                    <i class="fab fa-whatsapp"></i> واتساب
                </a>
                <a href="<?php echo e(route('login')); ?>"
                   style="display:inline-flex;align-items:center;gap:.5rem;border:1.5px solid #334155;color:#94a3b8;padding:.6rem 1.4rem;border-radius:8px;font-weight:700;text-decoration:none;font-size:.9rem;margin-right:.6rem;transition:all .3s"
                   onmouseover="this.style.color='#fff';this.style.borderColor='#fff'" onmouseout="this.style.color='#94a3b8';this.style.borderColor='#334155'">
                    <i class="fas fa-sign-in-alt"></i> دخول
                </a>
            </div>
        </div>
        <hr style="border-color:#1e293b;margin:2rem 0 1rem">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <p class="mb-0" style="font-size:.85rem">© <?php echo e(date('Y')); ?> <?php echo e(\App\Models\PlatformSetting::get('platform_name','فاتورتك')); ?>. جميع الحقوق محفوظة.</p>
            <p class="mb-0" style="font-size:.8rem;color:#475569">مبني بـ Laravel 10 · PHP 8.2 · MySQL</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php /**PATH C:\xampp8.2\htdocs\fatortk\resources\views/welcome.blade.php ENDPATH**/ ?>