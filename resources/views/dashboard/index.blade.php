@extends('layouts.admin')

@section('section_label', 'Dashboard Overview')
@section('section_hint', 'Business totals and sales performance at a glance.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Dashboard Overview</h1>
            <p class="subtitle">Current inventory, customers, orders, and sales ranking.</p>
        </div>
    </div>

    <section class="stats-grid stats-grid-wide">
        <div class="stat-card">
            <div class="stat-label">All Companies</div>
            <div class="stat-value">{{ number_format($totals['companies']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Products</div>
            <div class="stat-value">{{ number_format($totals['products']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Stocks</div>
            <div class="stat-value">{{ number_format($totals['stocks']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Stock Value</div>
            <div class="stat-value">BDT {{ number_format($totals['stock_value'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Customers</div>
            <div class="stat-value">{{ number_format($totals['customers']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value">{{ number_format($totals['orders']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Sell</div>
            <div class="stat-value">BDT {{ number_format($totals['sell'], 2) }}</div>
        </div>
    </section>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Today Sell</div>
            <div class="stat-value">BDT {{ number_format($salesStats['today'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">This Month Sell</div>
            <div class="stat-value">BDT {{ number_format($salesStats['this_month'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Completed Sell</div>
            <div class="stat-value">BDT {{ number_format($salesStats['completed'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Sell</div>
            <div class="stat-value">BDT {{ number_format($salesStats['pending'], 2) }}</div>
        </div>
    </section>

    <div class="dashboard-grid">
        <section class="panel">
            <div class="panel-heading">Sales Stats</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Orders</th>
                        <th>Total Sell</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dailySales as $sale)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($sale->sale_date)->format('M d, Y') }}</td>
                            <td>{{ number_format($sale->orders_count) }}</td>
                            <td>BDT {{ number_format($sale->total_sell, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="muted">No sales found for the last 7 days.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="panel">
            <div class="panel-heading">Top Customers</div>
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
            <div class="panel-heading">Top Products</div>
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
            <div class="panel-heading">Top Companies</div>
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
