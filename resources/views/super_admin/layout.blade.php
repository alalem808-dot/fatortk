<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Super Admin') - فاتورتك</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    body { background: #f1f5f9; font-family: 'Segoe UI', Tahoma, sans-serif; }
    .sidebar { width: 240px; min-height: 100vh; background: #0f172a; position: fixed; top: 0; right: 0; z-index: 100; }
    .sidebar .brand { padding: 1.25rem 1.5rem; border-bottom: 1px solid #1e293b; }
    .sidebar .brand .title { color: #fff; font-weight: 800; font-size: 1.1rem; }
    .sidebar .super-badge { background: linear-gradient(135deg,#f59e0b,#d97706); color:#fff; font-size:.65rem; padding:2px 8px; border-radius:10px; font-weight:700; }
    .sidebar .nav-link { color: #94a3b8; padding: .55rem 1.5rem; display: flex; align-items: center; gap: .6rem; transition: all .2s; font-size: .9rem; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: #1e293b; }
    .sidebar .nav-link i { width: 16px; text-align: center; }
    .sidebar .section-label { color: #475569; font-size: .7rem; padding: .75rem 1.5rem .25rem; text-transform: uppercase; }
    .main-content { margin-right: 240px; padding: 1.5rem; }
    .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: .75rem 1.5rem; margin: -1.5rem -1.5rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
    .stat-card { background: #fff; border-radius: 12px; padding: 1.25rem; border: 1px solid #e2e8f0; }
    .stat-card .icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .badge-active    { background:#dcfce7; color:#15803d; }
    .badge-trial     { background:#fef9c3; color:#ca8a04; }
    .badge-suspended { background:#fee2e2; color:#dc2626; }
    .badge-free      { background:#f1f5f9; color:#64748b; }
    .badge-basic     { background:#dbeafe; color:#1d4ed8; }
    .badge-pro       { background:#f3e8ff; color:#9333ea; }
    .badge-enterprise{ background:#fef3c7; color:#d97706; }
</style>
</head>
<body>
<div class="sidebar">
    <div class="brand">
        <div class="title">فاتورتك</div>
        <span class="super-badge">⚡ Super Admin</span>
    </div>
    <nav class="mt-2">
        <a href="{{ route('super_admin.dashboard') }}" class="nav-link {{ request()->routeIs('super_admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> لوحة التحكم
        </a>
        <div class="section-label">إدارة النظام</div>
        <a href="{{ route('super_admin.tenants') }}" class="nav-link {{ request()->routeIs('super_admin.tenants*') ? 'active' : '' }}">
            <i class="fas fa-building"></i> الشركات والحسابات
        </a>
        <a href="{{ route('super_admin.plans') }}" class="nav-link {{ request()->routeIs('super_admin.plans*') ? 'active' : '' }}">
            <i class="fas fa-crown"></i> خطط الاشتراك
        </a>
        <div class="section-label">الإعدادات</div>
        <a href="{{ route('super_admin.settings') }}" class="nav-link {{ request()->routeIs('super_admin.settings') ? 'active' : '' }}">
            <i class="fas fa-user-shield"></i> إعدادات الحساب
        </a>
    </nav>
</div>

<div class="main-content">
    <div class="topbar">
        <div>@yield('page-title')</div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small">
                <i class="fas fa-user-shield text-warning me-1"></i>
                {{ auth('super_admin')->user()->name }}
            </span>
            <form action="{{ route('super_admin.logout') }}" method="POST" class="m-0">
                @csrf
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @yield('content')
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')
</body>
</html>
