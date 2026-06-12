@extends('layouts.admin')

@section('section_label', 'Customers')
@section('section_hint', 'Unique guest customers grouped by phone number.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Customers</h1>
            <p class="subtitle">{{ $customers->count() }} unique customers from orders.</p>
        </div>
    </div>

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
@endsection
