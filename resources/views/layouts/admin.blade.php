<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @stack('styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f5f7fb; color: #1f2933; }
        a { color: inherit; text-decoration: none; }
        .shell { min-height: 100vh; display: grid; grid-template-columns: 260px 1fr; }
        .sidebar { background: #12372f; color: #fff; padding: 22px 18px; display: flex; flex-direction: column; gap: 22px; }
        .brand { font-size: 20px; font-weight: 800; line-height: 1.2; }
        .brand span { display: block; color: #9ad8bf; font-size: 12px; font-weight: 700; margin-top: 6px; text-transform: uppercase; }
        .menu { display: flex; flex-direction: column; gap: 8px; }
        .menu-link { border-radius: 6px; color: #d7f2e8; display: block; font-weight: 700; padding: 12px 13px; }
        .menu-link:hover, .menu-link.active { background: #1a7f5a; color: #fff; }
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
        .inline-form { margin: 0; display: flex; gap: 8px; align-items: center; }
        .actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 16px; }
        .stat-card { background: #fff; border: 1px solid #e3e8ef; border-radius: 8px; padding: 16px; }
        .stat-label { color: #667085; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .stat-value { color: #173d31; font-size: 24px; font-weight: 900; margin-top: 8px; }
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
            .form-grid, .item-grid, .filters, .stats-grid { grid-template-columns: 1fr; }
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
                <a class="menu-link {{ request()->routeIs('orders.*') || request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('orders.index') }}">Orders</a>
                <a class="menu-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">Customers</a>
                <a class="menu-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.sales') }}">Sales Report</a>
                <a class="menu-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">Companies</a>
                <a class="menu-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Products</a>
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
