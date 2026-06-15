<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @stack('styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f5f7fb; color: #1f2933; font-size: 14px; }
        a { color: inherit; text-decoration: none; }
        .shell { min-height: 100vh; display: grid; grid-template-columns: 260px 1fr; }
        .sidebar { background: linear-gradient(180deg, #102f2a 0%, #12372f 55%, #0e2a24 100%); color: #fff; padding: 22px 18px; display: flex; flex-direction: column; gap: 22px; }
        .brand { font-size: 20px; font-weight: 800; line-height: 1.2; }
        .brand span { display: block; color: #9ad8bf; font-size: 12px; font-weight: 700; margin-top: 6px; text-transform: uppercase; }
        .menu { display: flex; flex-direction: column; gap: 8px; }
        .menu-link { border-radius: 6px; color: #d7f2e8; display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; line-height: 1.25; padding: 11px 12px; }
        .menu-link:hover, .menu-link.active { background: #1a7f5a; color: #fff; }
        .menu-icon { align-items: center; background: rgba(255,255,255,.1); border-radius: 6px; display: inline-flex; height: 28px; justify-content: center; width: 28px; }
        .menu-icon svg, .card-icon svg, .panel-icon svg { height: 17px; width: 17px; stroke: currentColor; stroke-width: 2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
        .sidebar-footer { margin-top: auto; }
        .logout-form { margin: 0; }
        .content { min-width: 0; }
        .topbar { background: #fff; border-bottom: 1px solid #e3e8ef; min-height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 16px 28px; }
        .page-label { color: #667085; font-size: 13px; font-weight: 700; }
        .admin-name { color: #344054; font-size: 14px; font-weight: 700; }
        .page { padding: 28px; }
        .toolbar { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
        .title { margin: 0; font-size: 26px; line-height: 1.2; }
        .subtitle { margin: 6px 0 0; color: #667085; }
        .btn { border: 0; border-radius: 6px; padding: 10px 14px; font-weight: 800; cursor: pointer; display: inline-block; font-size: 14px; text-align: center; }
        .btn-primary { background: #1a7f5a; color: #fff; }
        .btn-light { background: #e8f5ef; color: #14543e; }
        .btn-outline { background: #fff; color: #344054; border: 1px solid #cfd8e3; }
        .btn-danger { background: #b42318; color: #fff; width: 100%; }
        .btn-danger-soft { background: #fff1f0; color: #b42318; border: 1px solid #f4b4ad; }
        .btn-small { padding: 7px 10px; font-size: 12px; }
        .notice { background: #e8f5ef; border: 1px solid #b8dccd; color: #14543e; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; }
        .errors { background: #fff0f0; border: 1px solid #f0b8b8; color: #8a1f1f; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; }
        .panel { background: #fff; border: 1px solid #e3e8ef; border-radius: 8px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #edf1f5; padding: 13px 14px; text-align: left; vertical-align: middle; }
        th { background: #f8fafc; color: #475467; font-size: 12px; text-transform: uppercase; }
        tr:last-child td { border-bottom: 0; }
        .muted { color: #667085; font-size: 13px; }
        .strong { font-weight: 800; }
        .status { display: inline-block; border-radius: 999px; background: #e8f5ef; color: #14543e; padding: 5px 10px; font-size: 12px; font-weight: 800; text-transform: capitalize; }
        .filters { background: #fff; border: 1px solid #e3e8ef; border-radius: 8px; padding: 16px; margin-bottom: 16px; display: grid; grid-template-columns: 2fr repeat(3, 1fr) auto; gap: 12px; align-items: end; }
        .filters-simple { grid-template-columns: minmax(260px, 1fr) auto; }
        .inline-form { margin: 0; display: flex; gap: 8px; align-items: center; }
        .actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .actions form { margin: 0; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 16px; }
        .stats-grid-wide { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .stat-card { background: #fff; border: 1px solid #e3e8ef; border-radius: 8px; box-shadow: 0 12px 28px rgba(15, 23, 42, .05); padding: 16px; }
        .stat-card-premium { border: 0; box-shadow: 0 18px 34px rgba(15, 23, 42, .14); color: #fff; position: relative; overflow: hidden; }
        .stat-card-premium::after { background: rgba(255, 255, 255, .16); border-radius: 50%; content: ""; height: 96px; position: absolute; right: -34px; top: -34px; width: 96px; }
        .stat-card-premium .stat-label, .stat-card-premium .stat-value { color: #fff; }
        .stat-card-premium .card-icon { background: rgba(255, 255, 255, .18); color: #fff; }
        .stat-card-emerald { background: linear-gradient(135deg, #047857 0%, #10b981 100%); }
        .stat-card-sapphire { background: linear-gradient(135deg, #1d4ed8 0%, #38bdf8 100%); }
        .stat-card-amber { background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%); }
        .stat-card-violet { background: linear-gradient(135deg, #6d28d9 0%, #a78bfa 100%); }
        .stat-card-rose { background: linear-gradient(135deg, #be123c 0%, #fb7185 100%); }
        .stat-card-teal { background: linear-gradient(135deg, #0f766e 0%, #2dd4bf 100%); }
        .stat-card-indigo { background: linear-gradient(135deg, #3730a3 0%, #818cf8 100%); }
        .stat-top { align-items: center; display: flex; gap: 10px; justify-content: space-between; }
        .card-icon { align-items: center; background: #e8f5ef; border-radius: 8px; color: #14543e; display: inline-flex; height: 36px; justify-content: center; width: 36px; }
        .stat-label { color: #667085; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .stat-value { color: #173d31; font-size: 22px; font-weight: 900; line-height: 1.2; margin-top: 12px; overflow-wrap: anywhere; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .panel-heading { align-items: center; border-bottom: 1px solid #edf1f5; display: flex; gap: 10px; font-size: 14px; font-weight: 900; padding: 14px 16px; }
        .panel-icon { align-items: center; background: #f1f5f9; border-radius: 8px; color: #173d31; display: inline-flex; height: 32px; justify-content: center; width: 32px; }
        .chart-panel { padding: 18px; }
        .chart-title { color: #173d31; font-size: 15px; font-weight: 900; margin: 0 0 16px; }
        .pie-wrap { align-items: center; display: grid; gap: 18px; grid-template-columns: 170px 1fr; }
        .pie-chart { aspect-ratio: 1; display: block; filter: drop-shadow(0 18px 22px rgba(15, 23, 42, .08)); width: 170px; }
        .pie-chart .pie-bg { fill: none; stroke: #edf2f7; stroke-width: 18; }
        .pie-chart .pie-segment { fill: none; stroke-linecap: butt; stroke-width: 18; transform: rotate(-90deg); transform-origin: 50% 50%; }
        .pie-chart text { fill: #173d31; font-size: 11px; font-weight: 900; text-anchor: middle; }
        .legend { display: grid; gap: 9px; }
        .legend-row { align-items: center; display: flex; gap: 9px; justify-content: space-between; }
        .legend-name { align-items: center; display: flex; gap: 8px; text-transform: capitalize; }
        .legend-dot { border-radius: 50%; height: 10px; width: 10px; }
        .bar-chart { display: grid; gap: 12px; }
        .bar-row { display: grid; gap: 8px; grid-template-columns: 94px 1fr 110px; align-items: center; }
        .bar-track { background: #eef2f7; border-radius: 999px; height: 10px; overflow: hidden; }
        .bar-fill { background: linear-gradient(90deg, #1a7f5a, #34d399); border-radius: inherit; height: 100%; min-width: 5px; }
        .product-cell { display: flex; align-items: center; gap: 12px; min-width: 240px; }
        .thumb { width: 54px; height: 54px; border-radius: 6px; object-fit: cover; background: #eef4f1; border: 1px solid #e3e8ef; }
        .form { background: #fff; border: 1px solid #e3e8ef; border-radius: 8px; padding: 22px; max-width: 880px; }
        .form-wide { max-width: 100%; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field-full { grid-column: 1 / -1; }
        label { font-size: 13px; font-weight: 800; color: #344054; }
        input, select, textarea { border: 1px solid #cfd8e3; border-radius: 6px; padding: 0 12px; font-size: 14px; width: 100%; background: #fff; }
        input, select { height: 42px; }
        textarea { min-height: 90px; padding-top: 10px; resize: vertical; }
        .item-grid { display: grid; grid-template-columns: 1fr 120px; gap: 10px; align-items: end; margin-bottom: 10px; }
        @media (max-width: 820px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: static; }
            .menu { flex-direction: row; flex-wrap: wrap; }
            .sidebar-footer { margin-top: 0; }
            .topbar, .toolbar { align-items: flex-start; flex-direction: column; }
            .page { padding: 20px; }
            .form-grid, .item-grid, .filters, .stats-grid, .stats-grid-wide, .dashboard-grid { grid-template-columns: 1fr; }
            .pie-wrap { grid-template-columns: 1fr; justify-items: center; }
            .legend { width: 100%; }
            .bar-row { grid-template-columns: 70px 1fr; }
            .bar-row strong { grid-column: 2; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                Med Bangladesh
                <span>Mobile App Manager</span>
            </div>

            <nav class="menu">
                <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10Z"/><path d="M13 21h8V11h-8v10Z"/><path d="M13 3v6h8V3h-8Z"/><path d="M3 21h8v-6H3v6Z"/></svg></span>Dashboard Overview</a>
                <a class="menu-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></span>Orders</a>
                <a class="menu-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>Customers</a>
                <a class="menu-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.sales') }}"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg></span>Sales Report</a>
                <a class="menu-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 13v.01"/><path d="M9 17v.01"/></svg></span>Companies</a>
                <a class="menu-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="m10.5 20.5 10-10a4.95 4.95 0 0 0-7-7l-10 10a4.95 4.95 0 0 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg></span>Products</a>
                <a class="menu-link {{ request()->routeIs('home-sliders.*') ? 'active' : '' }}" href="{{ route('home-sliders.index') }}"><span class="menu-icon"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg></span>Home Sliders</a>
            </nav>

            <div class="sidebar-footer">
                <form class="logout-form" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-danger" type="submit">Logout</button>
                </form>
            </div>
        </aside>

        <div class="content">
            <header class="topbar">
                <div>
                    <div class="page-label">@yield('section_label', 'Dashboard')</div>
                    <div class="subtitle">@yield('section_hint', 'Manage data used by the mobile app.')</div>
                </div>
                <div class="admin-name">{{ auth()->user()->name ?? 'Admin' }}</div>
            </header>

            <main class="page">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
