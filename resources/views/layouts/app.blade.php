<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'فاتورتك') - {{ $currentTenant->company_name ?? 'فاتورتك' }}</title>
    @php $favicon = \App\Models\PlatformSetting::imageUrl('platform_favicon'); @endphp
    @if($favicon)<link rel="icon" type="image/png" href="{{ $favicon }}">@endif

    {{-- Google Fonts: Cairo للعربية --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ===== CSS VARIABLES ===== */
        :root {
            --primary:        #2563eb;
            --primary-dark:   #1d4ed8;
            --primary-light:  #dbeafe;
            --secondary:      #64748b;
            --success:        #16a34a;
            --success-light:  #dcfce7;
            --danger:         #dc2626;
            --danger-light:   #fee2e2;
            --warning:        #d97706;
            --warning-light:  #fef3c7;
            --info:           #0891b2;
            --info-light:     #cffafe;
            --sidebar-bg:     #0f172a;
            --sidebar-hover:  #1e293b;
            --sidebar-active: #2563eb;
            --sidebar-text:   #94a3b8;
            --sidebar-width:  260px;
            --topbar-height:  64px;
            --body-bg:        #f1f5f9;
            --card-radius:    14px;
            --font:           'Cairo', 'Segoe UI', Tahoma, sans-serif;
        }

        /* ===== BASE ===== */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            background: var(--body-bg);
            font-family: var(--font);
            color: #1e293b;
            font-size: 14px;
            line-height: 1.6;
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

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
            transition: transform .3s cubic-bezier(.4,0,.2,1);
            overflow: hidden;
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.07);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .sidebar-brand .brand-icon {
            width: 38px; height: 38px;
            background: var(--primary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.1rem; flex-shrink: 0;
        }
        .sidebar-brand .brand-text h5 {
            color: #fff; margin: 0; font-weight: 700; font-size: .95rem; line-height: 1.2;
        }
        .sidebar-brand .brand-text span {
            color: var(--sidebar-text); font-size: .72rem;
        }
        .sidebar-brand img { max-width: 130px; height: 32px; object-fit: contain; border-radius: 6px; }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: .75rem 0; }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); }

        .nav-section {
            padding: .75rem 1.5rem .3rem;
            color: rgba(148,163,184,.5);
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-top: .25rem;
        }
        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: .55rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            border-radius: 0;
            transition: all .18s ease;
            font-size: .88rem;
            font-weight: 500;
            white-space: nowrap;
            position: relative;
        }
        .sidebar .nav-link .nav-icon {
            width: 20px; text-align: center; font-size: .9rem;
            opacity: .7; transition: opacity .18s;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,.06);
        }
        .sidebar .nav-link:hover .nav-icon { opacity: 1; }
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(37,99,235,.15);
            font-weight: 600;
        }
        .sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            right: 0; top: 4px; bottom: 4px;
            width: 3px;
            background: var(--primary);
            border-radius: 3px 0 0 3px;
        }
        .sidebar .nav-link.active .nav-icon { opacity: 1; color: var(--primary); }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.07);
            flex-shrink: 0;
        }
        .sidebar-user {
            display: flex; align-items: center; gap: .65rem;
            padding: .6rem .75rem;
            border-radius: 10px;
            background: rgba(255,255,255,.05);
            margin-bottom: .6rem;
        }
        .sidebar-user .avatar {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .85rem; font-weight: 700; flex-shrink: 0;
        }
        .sidebar-user .user-info .name { color: #e2e8f0; font-size: .82rem; font-weight: 600; }
        .sidebar-user .user-info .role { color: var(--sidebar-text); font-size: .68rem; }
        .btn-logout-sidebar {
            width: 100%; background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2);
            color: #fca5a5; border-radius: 8px; padding: .45rem; font-size: .8rem;
            font-family: var(--font); cursor: pointer; transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: .4rem;
        }
        .btn-logout-sidebar:hover { background: rgba(239,68,68,.2); color: #fecaca; }

        /* ===== OVERLAY ===== */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.6); z-index: 1039;
            backdrop-filter: blur(3px);
        }
        .sidebar-overlay.show { display: block; }

        /* ===== TOPBAR ===== */
        .topbar {
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 0 1.75rem;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .topbar-left { display: flex; align-items: center; gap: .75rem; }
        .topbar-toggle {
            display: none; background: none; border: none;
            color: #64748b; font-size: 1.2rem; padding: .35rem .5rem;
            border-radius: 8px; cursor: pointer; transition: all .2s;
        }
        .topbar-toggle:hover { background: #f1f5f9; color: #1e293b; }
        .topbar-breadcrumb { font-size: .85rem; font-weight: 600; color: #1e293b; }
        .topbar-right { display: flex; align-items: center; gap: .6rem; }

        .topbar-badge-btn {
            position: relative; background: none; border: 1px solid #e2e8f0;
            border-radius: 10px; padding: .4rem .65rem; cursor: pointer;
            color: #64748b; transition: all .2s;
        }
        .topbar-badge-btn:hover { background: #f8fafc; color: #1e293b; border-color: #cbd5e1; }
        .topbar-badge-btn .badge-dot {
            position: absolute; top: 4px; left: 4px;
            width: 7px; height: 7px; border-radius: 50%;
            background: #dc2626; border: 2px solid #fff;
        }
        .topbar-user {
            display: flex; align-items: center; gap: .5rem;
            padding: .4rem .75rem;
            border: 1px solid #e2e8f0; border-radius: 10px;
            font-size: .82rem; color: #374151; font-weight: 600;
            background: #f8fafc;
        }
        .topbar-user .t-avatar {
            width: 28px; height: 28px; border-radius: 7px;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700;
        }
        .btn-topbar-logout {
            background: none; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: .4rem .7rem; color: #64748b; cursor: pointer;
            font-size: .82rem; font-family: var(--font);
            transition: all .2s; display: flex; align-items: center; gap: .35rem;
        }
        .btn-topbar-logout:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .page-body { padding: 1.5rem 1.75rem; flex: 1; }

        /* ===== PAGE HEADER ===== */
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.5rem; flex-wrap: wrap; gap: .75rem;
        }
        .page-header h1, .page-header h6 {
            font-weight: 700; color: #0f172a; margin: 0;
        }

        /* ===== CARDS ===== */
        .card {
            border-radius: var(--card-radius) !important;
            border: 1px solid #e2e8f0 !important;
        }
        .card-header {
            border-bottom: 1px solid #f1f5f9 !important;
            background: #fff !important;
            border-radius: var(--card-radius) var(--card-radius) 0 0 !important;
            padding: 1rem 1.25rem !important;
            font-weight: 600;
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: #fff;
            border-radius: var(--card-radius);
            padding: 1.25rem 1.35rem;
            border: 1px solid #e2e8f0;
            transition: all .2s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0; left: 0;
            height: 3px;
        }
        .stat-card.blue::before   { background: linear-gradient(90deg, var(--primary), #60a5fa); }
        .stat-card.green::before  { background: linear-gradient(90deg, var(--success), #4ade80); }
        .stat-card.red::before    { background: linear-gradient(90deg, var(--danger), #f87171); }
        .stat-card.yellow::before { background: linear-gradient(90deg, var(--warning), #fbbf24); }
        .stat-card.purple::before { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
        .stat-card.teal::before   { background: linear-gradient(90deg, var(--info), #22d3ee); }
        .stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.07); transform: translateY(-1px); }

        .stat-card .stat-label { color: #64748b; font-size: .78rem; font-weight: 500; margin-bottom: .35rem; }
        .stat-card .stat-value { font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1.1; }
        .stat-card .stat-sub   { font-size: .75rem; color: #94a3b8; margin-top: .2rem; }
        .stat-card .stat-icon  {
            width: 44px; height: 44px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem;
        }

        /* ===== TABLES ===== */
        .table { font-size: .875rem; }
        .table > thead > tr > th {
            font-weight: 600; font-size: .75rem; color: #64748b;
            text-transform: uppercase; letter-spacing: .05em;
            background: #f8fafc; border-bottom: 2px solid #e2e8f0;
            padding: .75rem 1rem; white-space: nowrap;
        }
        .table > tbody > tr > td {
            padding: .75rem 1rem; vertical-align: middle;
            border-bottom: 1px solid #f1f5f9; color: #374151;
        }
        .table > tbody > tr:hover > td { background: #f8fafc; }
        .table > tbody > tr:last-child > td { border-bottom: none; }

        /* ===== BADGES ===== */
        .badge {
            font-weight: 600; font-size: .7rem;
            padding: .3em .65em; border-radius: 6px;
            letter-spacing: .02em;
        }
        .badge-status {
            padding: .35em .8em; font-size: .72rem; border-radius: 20px;
        }
        .badge-draft        { background: #f1f5f9; color: #475569; }
        .badge-sent         { background: #dbeafe; color: #1d4ed8; }
        .badge-paid         { background: #dcfce7; color: #15803d; }
        .badge-partially_paid { background: #cffafe; color: #0e7490; }
        .badge-overdue      { background: #fee2e2; color: #dc2626; }
        .badge-cancelled    { background: #f1f5f9; color: #64748b; }
        .badge-returned     { background: #fef3c7; color: #b45309; }
        .badge-pending      { background: #fef3c7; color: #b45309; }
        .badge-received     { background: #dcfce7; color: #15803d; }

        /* ===== BUTTONS ===== */
        .btn {
            font-family: var(--font); font-weight: 600;
            border-radius: 9px; transition: all .18s ease;
        }
        .btn-primary {
            background: var(--primary); border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(37,99,235,.25);
        }
        .btn-primary:hover {
            background: var(--primary-dark); border-color: var(--primary-dark);
            box-shadow: 0 4px 14px rgba(37,99,235,.35); transform: translateY(-1px);
        }
        .btn-success { box-shadow: 0 2px 8px rgba(22,163,74,.2); }
        .btn-danger  { box-shadow: 0 2px 8px rgba(220,38,38,.2); }
        .btn-sm { font-size: .8rem; padding: .4rem .85rem; border-radius: 7px; }
        .btn-xs { padding: .22rem .5rem; font-size: .72rem; border-radius: 6px; }
        .btn-whatsapp { background: #25D366; color: #fff; border: none; }
        .btn-whatsapp:hover { background: #128C7E; color: #fff; box-shadow: 0 2px 8px rgba(37,211,102,.3); }

        /* ===== FORMS ===== */
        .form-control, .form-select {
            font-family: var(--font); border-radius: 9px;
            border: 1px solid #e2e8f0; font-size: .875rem;
            transition: border-color .15s, box-shadow .15s;
            color: #1e293b;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }
        .form-label { font-weight: 600; font-size: .82rem; color: #374151; margin-bottom: .35rem; }
        .form-text  { font-size: .75rem; color: #94a3b8; }

        /* ===== ALERTS ===== */
        .alert {
            border-radius: 10px; border: none;
            font-size: .875rem; font-weight: 500;
            display: flex; align-items: flex-start; gap: .65rem;
        }
        .alert-success { background: #f0fdf4; color: #166534; border-right: 4px solid #16a34a !important; }
        .alert-danger  { background: #fef2f2; color: #991b1b; border-right: 4px solid #dc2626 !important; }
        .alert-warning { background: #fffbeb; color: #92400e; border-right: 4px solid #d97706 !important; }
        .alert-info    { background: #eff6ff; color: #1e40af; border-right: 4px solid #2563eb !important; }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center; padding: 4rem 2rem;
            color: #94a3b8;
        }
        .empty-state .empty-icon {
            width: 70px; height: 70px; border-radius: 50%;
            background: #f1f5f9; margin: 0 auto 1.25rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem; color: #cbd5e1;
        }
        .empty-state h5 { color: #475569; font-weight: 700; margin-bottom: .5rem; }
        .empty-state p  { font-size: .875rem; margin-bottom: 1.25rem; }

        /* ===== PAGINATION ===== */
        .pagination { gap: .2rem; }
        .page-link {
            border-radius: 8px !important; border: 1px solid #e2e8f0;
            color: #374151; font-size: .82rem; padding: .4rem .75rem;
            font-family: var(--font); font-weight: 600;
            transition: all .15s;
        }
        .page-link:hover { background: var(--primary-light); border-color: var(--primary); color: var(--primary); }
        .page-item.active .page-link { background: var(--primary); border-color: var(--primary); color: #fff; }
        .page-item.disabled .page-link { opacity: .5; }

        /* ===== MODAL ===== */
        .modal-content { border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
        .modal-header { border-bottom: 1px solid #f1f5f9; padding: 1.25rem 1.5rem; }
        .modal-footer { border-top: 1px solid #f1f5f9; padding: 1rem 1.5rem; }
        .modal-title  { font-weight: 700; font-size: .95rem; }

        /* ===== DROPDOWN ===== */
        .dropdown-menu {
            border-radius: 12px; border: 1px solid #e2e8f0;
            box-shadow: 0 8px 30px rgba(0,0,0,.1); padding: .4rem;
        }
        .dropdown-item { border-radius: 8px; font-size: .85rem; padding: .5rem .9rem; font-weight: 500; }
        .dropdown-item:hover { background: var(--primary-light); color: var(--primary); }

        /* ===== PROGRESS ===== */
        .progress { border-radius: 10px; background: #e2e8f0; }
        .progress-bar { border-radius: 10px; }

        /* ===== BOTTOM NAV (Mobile) ===== */
        .bottom-nav {
            display: none; position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            z-index: 1030;
            box-shadow: 0 -4px 20px rgba(0,0,0,.08);
            padding-bottom: env(safe-area-inset-bottom);
        }
        .bottom-nav a, .bottom-nav button {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: .5rem .25rem; color: #94a3b8;
            text-decoration: none; font-size: .6rem; font-weight: 600;
            gap: .2rem; transition: color .2s;
            background: none; border: none; cursor: pointer;
            font-family: var(--font);
        }
        .bottom-nav a i, .bottom-nav button i { font-size: 1.15rem; }
        .bottom-nav a.active { color: var(--primary); }
        .bottom-nav a.active i { color: var(--primary); }
        .bottom-nav .btn-logout-mobile { color: #ef4444 !important; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(100%); }
            .sidebar.open { transform: translateX(0); box-shadow: 0 0 40px rgba(0,0,0,.3); }
            .main-content { margin-right: 0; }
            .topbar-toggle { display: block; }
            .bottom-nav { display: flex; }
            .page-body { padding: 1rem 1rem 5rem; }
            .topbar { padding: 0 1rem; }
        }
        @media (max-width: 575px) {
            .page-body { padding: .75rem .75rem 5rem; }
            .table-responsive { font-size: .8rem; }
            .stat-card .stat-value { font-size: 1.3rem; }
        }

        /* ===== UTILITIES ===== */
        .text-muted   { color: #64748b !important; }
        .border-light { border-color: #f1f5f9 !important; }
        .shadow-sm    { box-shadow: 0 1px 4px rgba(0,0,0,.06) !important; }
        .fw-700       { font-weight: 700; }
        .fw-800       { font-weight: 800; }
        .rounded-xl   { border-radius: 14px !important; }
        .bg-primary-soft { background: var(--primary-light) !important; color: var(--primary) !important; }
        .bg-success-soft { background: var(--success-light) !important; color: var(--success) !important; }
        .bg-danger-soft  { background: var(--danger-light) !important; color: var(--danger) !important; }
        .bg-warning-soft { background: var(--warning-light) !important; color: var(--warning) !important; }
        .gap-section { margin-bottom: 1.5rem; }
        .section-title { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; margin-bottom: .75rem; }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- ===== SIDEBAR ===== --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        @if(isset($currentTenant) && $currentTenant->logo)
            <img src="{{ url('storage/' . $currentTenant->logo) }}" alt="logo">
        @elseif(\App\Models\PlatformSetting::imageUrl('platform_logo'))
            <img src="{{ \App\Models\PlatformSetting::imageUrl('platform_logo') }}" alt="{{ \App\Models\PlatformSetting::get('platform_name','فاتورتك') }}">
        @else
            <div class="brand-icon"><i class="fas fa-file-invoice"></i></div>
        @endif
        <div class="brand-text">
            <h5>{{ Str::limit($currentTenant->company_name ?? 'فاتورتك', 18) }}</h5>
            <span>نظام الفواتير</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie nav-icon"></i> لوحة التحكم
        </a>

        @canany(['invoices.view','customers.view','returns.view','quick_sale.access'])
        <div class="nav-section">المبيعات</div>
        @can('quick_sale.access')
        <a href="{{ route('quick-sale.index') }}" class="nav-link {{ request()->routeIs('quick-sale.*') ? 'active' : '' }}">
            <i class="fas fa-bolt nav-icon"></i> بيع مباشر
        </a>
        @endcan
        @can('invoices.view')
        <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice nav-icon"></i> الفواتير
        </a>
        @endcan
        @can('customers.view')
        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="fas fa-users nav-icon"></i> العملاء
        </a>
        @endcan
        @can('returns.view')
        <a href="{{ route('returns.index') }}" class="nav-link {{ request()->routeIs('returns.*') ? 'active' : '' }}">
            <i class="fas fa-undo nav-icon"></i> المرتجعات
        </a>
        @endcan
        @endcanany

        @canany(['purchases.view','suppliers.view'])
        <div class="nav-section">المشتريات</div>
        @can('purchases.view')
        <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.index') || request()->routeIs('purchases.show') || request()->routeIs('purchases.create') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart nav-icon"></i> أوامر الشراء
        </a>
        <a href="{{ route('purchase-returns.index') }}" class="nav-link {{ request()->routeIs('purchase-returns.*') ? 'active' : '' }}">
            <i class="fas fa-rotate-left nav-icon"></i> مرتجعات المشتريات
        </a>
        @endcan
        @can('suppliers.view')
        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <i class="fas fa-truck nav-icon"></i> الموردون
        </a>
        @endcan
        @endcanany

        @canany(['products.view','warehouses.view','stocktaking.view','settings.view'])
        <div class="nav-section">المخزون</div>
        @can('products.view')
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="fas fa-boxes-stacked nav-icon"></i> المنتجات
        </a>
        @endcan
        @can('warehouses.view')
        <a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
            <i class="fas fa-warehouse nav-icon"></i> المخازن
        </a>
        @endcan
        @can('settings.view')
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="fas fa-tags nav-icon"></i> الفئات والوحدات
        </a>
        @endcan
        @can('stocktaking.view')
        <a href="{{ route('stocktaking.index') }}" class="nav-link {{ request()->routeIs('stocktaking.*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list nav-icon"></i> الجرد
        </a>
        @endcan
        @endcanany

        @can('expenses.view')
        <div class="nav-section">المالية</div>
        <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            <i class="fas fa-receipt nav-icon"></i> المصروفات
        </a>
        @endcan

        @canany(['reports.view_sales','reports.view_profit','reports.view_stock'])
        <div class="nav-section">التقارير</div>
        @can('reports.view_sales')
        <a href="{{ route('reports.sales') }}" class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
            <i class="fas fa-chart-bar nav-icon"></i> تقرير المبيعات
        </a>
        <a href="{{ route('reports.purchases') }}" class="nav-link {{ request()->routeIs('reports.purchases') ? 'active' : '' }}">
            <i class="fas fa-cart-arrow-down nav-icon"></i> تقرير المشتريات
        </a>
        <a href="{{ route('reports.customers') }}" class="nav-link {{ request()->routeIs('reports.customers') ? 'active' : '' }}">
            <i class="fas fa-chart-pie nav-icon"></i> تقرير العملاء
        </a>
        @endcan
        @can('reports.view_profit')
        <a href="{{ route('reports.profit') }}" class="nav-link {{ request()->routeIs('reports.profit') ? 'active' : '' }}">
            <i class="fas fa-chart-line nav-icon"></i> الأرباح والخسائر
        </a>
        @endcan
        @can('reports.view_stock')
        <a href="{{ route('reports.stock') }}" class="nav-link {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
            <i class="fas fa-layer-group nav-icon"></i> تقرير المخزون
        </a>
        @endcan
        @endcanany

        @canany(['users.view','settings.view'])
        <div class="nav-section">الإعدادات</div>
        @can('users.view')
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="fas fa-users-gear nav-icon"></i> المستخدمون
        </a>
        @endcan
        @can('settings.view')
        <a href="{{ route('templates.index') }}" class="nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}">
            <i class="fas fa-palette nav-icon"></i> قوالب الفواتير
        </a>
        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <i class="fas fa-gear nav-icon"></i> الإعدادات
        </a>
        <a href="{{ route('backups.index') }}" class="nav-link {{ request()->routeIs('backups.*') ? 'active' : '' }}">
            <i class="fas fa-database nav-icon"></i> النسخ الاحتياطي
        </a>
        @endcan
        @endcanany
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-info">
                <div class="name">{{ Str::limit(auth()->user()->name, 16) }}</div>
                <div class="role">{{ auth()->user()->role ?? 'مستخدم' }}</div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button class="btn-logout-sidebar">
                <i class="fas fa-arrow-right-from-bracket"></i> تسجيل الخروج
            </button>
        </form>
    </div>
</aside>

{{-- ===== MAIN ===== --}}
<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" onclick="openSidebar()"><i class="fas fa-bars"></i></button>
            <div class="topbar-breadcrumb">{!! $__env->yieldContent('page-title') !!}</div>
        </div>
        <div class="topbar-right">
            @php
                $lowStockCount = \App\Models\Product::where('tenant_id', auth()->user()->tenant_id)
                    ->whereColumn('stock_quantity', '<=', 'min_stock_alert')->count();
            @endphp
            @if($lowStockCount > 0)
            <a href="{{ route('reports.stock') }}" class="topbar-badge-btn" title="{{ $lowStockCount }} منتج منخفض المخزون">
                <i class="fas fa-bell" style="font-size:.95rem;"></i>
                <span class="badge-dot"></span>
            </a>
            @endif
            <div class="topbar-user d-none d-md-flex">
                <div class="t-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</div>
                <span>{{ Str::limit(auth()->user()->name, 14) }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0 d-none d-md-block">
                @csrf
                <button class="btn-topbar-logout"><i class="fas fa-arrow-right-from-bracket"></i> خروج</button>
            </form>
        </div>
    </div>

    <div class="page-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-circle-check fa-fw mt-1 flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-circle-xmark fa-fw mt-1 flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-triangle-exclamation fa-fw mt-1 flex-shrink-0"></i>
            <div>
                @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
                @endforeach
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </div>
</div>

{{-- ===== BOTTOM NAV ===== --}}
<nav class="bottom-nav">
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-chart-pie"></i> الرئيسية
    </a>
    @can('quick_sale.access')
    <a href="{{ route('quick-sale.index') }}" class="{{ request()->routeIs('quick-sale.*') ? 'active' : '' }}">
        <i class="fas fa-bolt"></i> بيع مباشر
    </a>
    @endcan
    @can('invoices.view')
    <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
        <i class="fas fa-file-invoice"></i> الفواتير
    </a>
    @else
    <span style="flex:1;opacity:.3;display:flex;flex-direction:column;align-items:center;padding:.5rem;font-size:.6rem;gap:.2rem;color:#94a3b8;">
        <i class="fas fa-file-invoice" style="font-size:1.1rem;"></i> الفواتير
    </span>
    @endcan
    @can('customers.view')
    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
        <i class="fas fa-users"></i> العملاء
    </a>
    @else
    <span style="flex:1;opacity:.3;display:flex;flex-direction:column;align-items:center;padding:.5rem;font-size:.6rem;gap:.2rem;color:#94a3b8;">
        <i class="fas fa-users" style="font-size:1.1rem;"></i> العملاء
    </span>
    @endcan
    @can('products.view')
    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
        <i class="fas fa-boxes-stacked"></i> المنتجات
    </a>
    @else
    <span style="flex:1;opacity:.3;display:flex;flex-direction:column;align-items:center;padding:.5rem;font-size:.6rem;gap:.2rem;color:#94a3b8;">
        <i class="fas fa-boxes-stacked" style="font-size:1.1rem;"></i> المنتجات
    </span>
    @endcan
    <a href="#" onclick="openSidebar(); return false;">
        <i class="fas fa-grid-2"></i> المزيد
    </a>
    <form action="{{ route('logout') }}" method="POST" class="m-0 d-flex" style="flex:1">
        @csrf
        <button type="submit" class="btn-logout-mobile">
            <i class="fas fa-arrow-right-from-bracket"></i> خروج
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
document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
    link.addEventListener('click', function() {
        if (window.innerWidth < 992) closeSidebar();
    });
});
// Auto-dismiss alerts after 5s
setTimeout(() => {
    document.querySelectorAll('.alert.alert-success, .alert.alert-danger').forEach(el => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
        if (bsAlert) bsAlert.close();
    });
}, 5000);
</script>
@stack('scripts')
</body>
</html>
