<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'فاتورتك') - {{ $currentTenant->company_name ?? 'فاتورتك' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --topbar-height: 60px;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-text: #94a3b8;
        }

        * { box-sizing: border-box; }
        body { background: #f1f5f9; font-family: 'Segoe UI', Tahoma, sans-serif; margin: 0; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; right: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
            overflow: hidden;
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #334155;
            flex-shrink: 0;
        }
        .sidebar-brand h5 { color: #fff; margin: 0; font-weight: 700; font-size: 1rem; }
        .sidebar-brand img { max-width: 140px; height: 35px; object-fit: contain; }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: .5rem 0; }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }

        .nav-section {
            padding: .5rem 1.25rem .25rem;
            color: #475569;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-top: .5rem;
        }
        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: .6rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .65rem;
            border-radius: 0;
            transition: all .2s;
            font-size: .9rem;
            white-space: nowrap;
        }
        .sidebar .nav-link i { width: 18px; text-align: center; font-size: .95rem; }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: var(--sidebar-hover);
            border-right: 3px solid #3b82f6;
        }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #334155;
            flex-shrink: 0;
        }
        .sidebar-footer .user-name { color: #cbd5e1; font-size: .85rem; }
        .sidebar-footer .btn-logout {
            color: #94a3b8;
            background: none;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: .35rem .75rem;
            font-size: .8rem;
            width: 100%;
            margin-top: .5rem;
            transition: all .2s;
        }
        .sidebar-footer .btn-logout:hover { background: #334155; color: #fff; }

        /* ===== OVERLAY ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1039;
            backdrop-filter: blur(2px);
        }
        .sidebar-overlay.show { display: block; }

        /* ===== TOPBAR ===== */
        .topbar {
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .topbar-toggle {
            display: none;
            background: none;
            border: none;
            color: #475569;
            font-size: 1.25rem;
            padding: .25rem .5rem;
            border-radius: 8px;
            cursor: pointer;
        }
        .topbar-toggle:hover { background: #f1f5f9; }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .page-body { padding: 1.5rem; flex: 1; }

        /* ===== CARDS & COMPONENTS ===== */
        .stat-card { background: #fff; border-radius: 12px; padding: 1.25rem; border: 1px solid #e2e8f0; }
        .stat-card .icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .badge-draft { background: #e2e8f0; color: #475569; }
        .badge-sent { background: #dbeafe; color: #1d4ed8; }
        .badge-paid { background: #dcfce7; color: #15803d; }
        .badge-overdue { background: #fee2e2; color: #dc2626; }
        .badge-cancelled { background: #f1f5f9; color: #64748b; }
        .table th { font-weight: 600; font-size: .85rem; color: #64748b; text-transform: uppercase; }
        .btn-whatsapp { background: #25D366; color: #fff; border: none; }
        .btn-whatsapp:hover { background: #128C7E; color: #fff; }

        /* ===== BOTTOM NAV (Mobile) ===== */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            z-index: 1030;
            box-shadow: 0 -2px 12px rgba(0,0,0,.08);
        }
        .bottom-nav a, .bottom-nav button {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: .5rem .25rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: .65rem;
            gap: .2rem;
            transition: color .2s;
            background: none;
            border: none;
            cursor: pointer;
        }
        .bottom-nav a i, .bottom-nav button i { font-size: 1.1rem; }
        .bottom-nav a.active, .bottom-nav a:hover { color: #3b82f6; }
        .bottom-nav a.active i { color: #3b82f6; }
        .bottom-nav .btn-logout-mobile { color: #ef4444 !important; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-right: 0; }
            .topbar-toggle { display: block; }
            .bottom-nav { display: flex; }
            .page-body { padding: 1rem; padding-bottom: 5rem; }
        }

        @media (max-width: 575px) {
            .topbar { padding: 0 1rem; }
            .page-body { padding: .75rem; padding-bottom: 5rem; }
            .table-responsive { font-size: .82rem; }
            .btn-sm { font-size: .75rem; padding: .3rem .6rem; }
            .card { border-radius: 10px; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- Sidebar --}}
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        @if(isset($currentTenant) && $currentTenant->logo)
            <img src="{{ url('storage/' . $currentTenant->logo) }}" class="mb-2 d-block">
        @endif
        <h5>{{ $currentTenant->company_name ?? 'فاتورتك' }}</h5>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> لوحة التحكم
        </a>
        <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i> الفواتير
        </a>
        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> العملاء
        </a>
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="fas fa-boxes"></i> المنتجات والمخزون
        </a>
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> الفئات ووحدات القياس
        </a>

        <div class="nav-section">التقارير</div>
        <a href="{{ route('reports.sales') }}" class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> تقرير المبيعات
        </a>
        <a href="{{ route('reports.stock') }}" class="nav-link {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
            <i class="fas fa-warehouse"></i> تقرير المخزون
        </a>
        <a href="{{ route('reports.customers') }}" class="nav-link {{ request()->routeIs('reports.customers') ? 'active' : '' }}">
            <i class="fas fa-user-chart"></i> تقرير العملاء
        </a>

        <div class="nav-section">الإعدادات</div>
        <a href="{{ route('templates.index') }}" class="nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}">
            <i class="fas fa-palette"></i> قوالب الفواتير
        </a>
        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <i class="fas fa-cog"></i> الإعدادات
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-name"><i class="fas fa-user-circle me-1"></i>{{ auth()->user()->name }}</div>
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button class="btn-logout"><i class="fas fa-sign-out-alt me-1"></i> تسجيل الخروج</button>
        </form>
    </div>
</div>

{{-- Main --}}
<div class="main-content">
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="topbar-toggle" onclick="openSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div>{!! $__env->yieldContent('page-title') !!}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small d-none d-md-inline">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" class="m-0 d-none d-md-block">
                @csrf
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-sign-out-alt"></i> <span class="d-none d-lg-inline">خروج</span>
                </button>
            </form>
        </div>
    </div>

    <div class="page-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        @yield('content')
    </div>
</div>

{{-- Bottom Nav (Mobile) --}}
<nav class="bottom-nav">
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-chart-pie"></i> الرئيسية
    </a>
    <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
        <i class="fas fa-file-invoice"></i> الفواتير
    </a>
    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
        <i class="fas fa-users"></i> العملاء
    </a>
    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
        <i class="fas fa-boxes"></i> المنتجات
    </a>
    <a href="#" onclick="openSidebar(); return false;">
        <i class="fas fa-ellipsis-h"></i> المزيد
    </a>
    <form action="{{ route('logout') }}" method="POST" class="m-0 d-flex" style="flex:1">
        @csrf
        <button type="submit" class="btn-logout-mobile">
            <i class="fas fa-sign-out-alt"></i> خروج
        </button>
    </form>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
        document.body.style.overflow = '';
    }
    // إغلاق الـ sidebar عند الضغط على أي رابط في الموبايل
    document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) closeSidebar();
        });
    });
</script>
@stack('scripts')
</body>
</html>
