@extends('layouts.admin')

@section('section_label', 'Dashboard Overview')
@section('section_hint', 'Business totals and sales performance at a glance.')

@section('content')
    @php
        $icons = [
            'building' => '<svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>',
            'pill' => '<svg viewBox="0 0 24 24"><path d="m10.5 20.5 10-10a4.95 4.95 0 0 0-7-7l-10 10a4.95 4.95 0 0 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>',
            'boxes' => '<svg viewBox="0 0 24 24"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
            'wallet' => '<svg viewBox="0 0 24 24"><path d="M19 7V6a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h14a2 2 0 0 1 2 2v1"/><path d="M3 6v12a2 2 0 0 0 2 2h16v-6h-4a2 2 0 0 1 0-4h4"/></svg>',
            'users' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>',
            'orders' => '<svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/></svg>',
            'sales' => '<svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>',
            'clock' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
            'check' => '<svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>',
            'chart' => '<svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><rect x="7" y="12" width="3" height="5"/><rect x="12" y="8" width="3" height="9"/><rect x="17" y="5" width="3" height="12"/></svg>',
        ];

        $mainStats = [
            ['label' => 'All Companies', 'value' => number_format($totals['companies']), 'icon' => 'building', 'theme' => 'emerald'],
            ['label' => 'Total Products', 'value' => number_format($totals['products']), 'icon' => 'pill', 'theme' => 'sapphire'],
            ['label' => 'Total Stocks', 'value' => number_format($totals['stocks']), 'icon' => 'boxes', 'theme' => 'amber'],
            ['label' => 'Total Stock Value', 'value' => 'BDT '.number_format($totals['stock_value'], 2), 'icon' => 'wallet', 'theme' => 'violet'],
            ['label' => 'Total Customers', 'value' => number_format($totals['customers']), 'icon' => 'users', 'theme' => 'rose'],
            ['label' => 'Total Orders', 'value' => number_format($totals['orders']), 'icon' => 'orders', 'theme' => 'teal'],
            ['label' => 'Total Sell', 'value' => 'BDT '.number_format($totals['sell'], 2), 'icon' => 'sales', 'theme' => 'indigo'],
        ];

        $salesCards = [
            ['label' => 'Today Sell', 'value' => 'BDT '.number_format($salesStats['today'], 2), 'icon' => 'clock', 'theme' => 'emerald'],
            ['label' => 'This Month Sell', 'value' => 'BDT '.number_format($salesStats['this_month'], 2), 'icon' => 'chart', 'theme' => 'sapphire'],
            ['label' => 'Completed Sell', 'value' => 'BDT '.number_format($salesStats['completed'], 2), 'icon' => 'check', 'theme' => 'indigo'],
            ['label' => 'Pending Sell', 'value' => 'BDT '.number_format($salesStats['pending'], 2), 'icon' => 'orders', 'theme' => 'amber'],
        ];

        $statusTotal = (int) $statusBreakdown->sum('count');
        $pieOffset = 0;
        $maxDailySell = max(1, (float) $dailySales->max('total_sell'));
    @endphp

    <div class="toolbar">
        <div>
            <h1 class="title">Dashboard Overview</h1>
            <p class="subtitle">Current inventory, customers, orders, and sales ranking.</p>
        </div>
    </div>

    <section class="stats-grid stats-grid-wide">
        @foreach ($mainStats as $stat)
            <div class="stat-card stat-card-premium stat-card-{{ $stat['theme'] }}">
                <div class="stat-top">
                    <div class="stat-label">{{ $stat['label'] }}</div>
                    <span class="card-icon">{!! $icons[$stat['icon']] !!}</span>
                </div>
                <div class="stat-value">{{ $stat['value'] }}</div>
            </div>
        @endforeach
    </section>

    <section class="stats-grid">
        @foreach ($salesCards as $stat)
            <div class="stat-card stat-card-premium stat-card-{{ $stat['theme'] }}">
                <div class="stat-top">
                    <div class="stat-label">{{ $stat['label'] }}</div>
                    <span class="card-icon">{!! $icons[$stat['icon']] !!}</span>
                </div>
                <div class="stat-value">{{ $stat['value'] }}</div>
            </div>
        @endforeach
    </section>

    <div class="dashboard-grid">
        <section class="panel chart-panel">
            <h2 class="chart-title">Order Status Mix</h2>
            <div class="pie-wrap">
                <svg class="pie-chart" viewBox="0 0 120 120" role="img" aria-label="Order status pie chart">
                    <circle class="pie-bg" cx="60" cy="60" r="45"></circle>
                    @if ($statusTotal > 0)
                        @foreach ($statusBreakdown as $status)
                            @if ($status['count'] > 0)
                                @php
                                    $percent = round(($status['count'] / $statusTotal) * 100, 3);
                                    $dashOffset = -$pieOffset;
                                    $pieOffset += $percent;
                                @endphp
                                <circle
                                    class="pie-segment"
                                    cx="60"
                                    cy="60"
                                    r="45"
                                    pathLength="100"
                                    stroke="{{ $status['color'] }}"
                                    stroke-dasharray="{{ $percent }} {{ 100 - $percent }}"
                                    stroke-dashoffset="{{ $dashOffset }}"
                                ></circle>
                            @endif
                        @endforeach
                    @endif
                    <circle cx="60" cy="60" r="28" fill="#fff"></circle>
                    <text x="60" y="58">{{ number_format($statusTotal) }}</text>
                    <text x="60" y="72" style="font-size: 8px; fill: #667085;">Orders</text>
                </svg>
                <div class="legend">
                    @foreach ($statusBreakdown as $status)
                        <div class="legend-row">
                            <span class="legend-name"><span class="legend-dot" style="background: {{ $status['color'] }}"></span>{{ $status['label'] }}</span>
                            <strong>{{ number_format($status['count']) }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="panel chart-panel">
            <h2 class="chart-title">Last 7 Days Sell</h2>
            <div class="bar-chart">
                @forelse ($dailySales as $sale)
                    @php $width = max(5, ((float) $sale->total_sell / $maxDailySell) * 100); @endphp
                    <div class="bar-row">
                        <span class="muted">{{ \Illuminate\Support\Carbon::parse($sale->sale_date)->format('M d') }}</span>
                        <span class="bar-track"><span class="bar-fill" style="width: {{ $width }}%"></span></span>
                        <strong>BDT {{ number_format($sale->total_sell, 2) }}</strong>
                    </div>
                @empty
                    <div class="muted">No sales found for the last 7 days.</div>
                @endforelse
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading"><span class="panel-icon">{!! $icons['users'] !!}</span>Top Customers</div>
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Orders</th>
                        <th>Total Sell</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topCustomers as $customer)
                        <tr>
                            <td>
                                <div class="strong">{{ $customer->name }}</div>
                                <div class="muted">{{ $customer->phone }}</div>
                            </td>
                            <td>{{ number_format($customer->orders_count) }}</td>
                            <td>BDT {{ number_format($customer->total_sell, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="muted">No customer sales yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="panel">
            <div class="panel-heading"><span class="panel-icon">{!! $icons['pill'] !!}</span>Top Products</div>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Total Sell</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topProducts as $product)
                        <tr>
                            <td>{{ $product->product_name }}</td>
                            <td>{{ number_format($product->quantity_sold) }}</td>
                            <td>BDT {{ number_format($product->total_sell, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="muted">No product sales yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="panel">
            <div class="panel-heading"><span class="panel-icon">{!! $icons['building'] !!}</span>Top Companies</div>
            <table>
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Qty</th>
                        <th>Total Sell</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topCompanies as $company)
                        <tr>
                            <td>{{ $company->company }}</td>
                            <td>{{ number_format($company->quantity_sold) }}</td>
                            <td>BDT {{ number_format($company->total_sell, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="muted">No company sales yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection
