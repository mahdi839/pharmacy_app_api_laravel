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
