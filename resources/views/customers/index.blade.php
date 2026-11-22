@extends('layouts.admin')

@section('section_label', 'Customers')
@section('section_hint', 'Unique guest customers grouped by phone number.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Customers</h1>
            <p class="subtitle">{{ number_format($stats['customers']) }} unique customers from orders.</p>
        </div>
    </div>

    @php
        $statCards = [
            ['label' => 'Total Customers', 'value' => number_format($stats['customers']), 'theme' => 'sapphire',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>'],
            ['label' => 'Repeat Customers', 'value' => number_format($stats['repeat_customers']), 'theme' => 'violet',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg>'],
            ['label' => 'New This Month', 'value' => number_format($stats['new_this_month']), 'theme' => 'teal',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>'],
            ['label' => 'Total Orders', 'value' => number_format($stats['orders']), 'theme' => 'amber',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/></svg>'],
            ['label' => 'Total Revenue', 'value' => 'BDT '.number_format($stats['revenue'], 2), 'theme' => 'emerald',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>'],
            ['label' => 'Avg. Spend / Customer', 'value' => 'BDT '.number_format($stats['avg_spend'], 2), 'theme' => 'rose',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M19 7V6a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h14a2 2 0 0 1 2 2v1"/><path d="M3 6v12a2 2 0 0 0 2 2h16v-6h-4a2 2 0 0 1 0-4h4"/></svg>'],
        ];
    @endphp

    <section class="stats-grid stats-grid-3">
        @foreach ($statCards as $stat)
            <div class="stat-card stat-card-premium stat-card-{{ $stat['theme'] }}">
                <div class="stat-top">
                    <div class="stat-label">{{ $stat['label'] }}</div>
                    <span class="card-icon">{!! $stat['icon'] !!}</span>
                </div>
                <div class="stat-value">{{ $stat['value'] }}</div>
            </div>
        @endforeach
    </section>

    <form class="filters filters-simple" method="GET" action="{{ route('customers.index') }}">
        <div class="field">
            <label for="search">Search Customer</label>
            <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Customer name or phone">
        </div>
        <div class="actions">
            <button class="btn btn-primary" type="submit">Search</button>
            <a class="btn btn-outline" href="{{ route('customers.index') }}">Reset</a>
        </div>
    </form>

    <section class="panel">
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Last Order</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td>
                            <div class="strong">{{ $customer['name'] }}</div>
                            <div class="muted">{{ $customer['phone'] }}</div>
                        </td>
                        <td>{{ $customer['address'] }}</td>
                        <td>{{ $customer['orders_count'] }}</td>
                        <td>BDT {{ number_format($customer['total_spent'], 2) }}</td>
                        <td>{{ $customer['last_order_at']->format('M d, Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">No customers yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @include('partials.pagination', ['paginator' => $customers, 'label' => 'customers'])
@endsection
