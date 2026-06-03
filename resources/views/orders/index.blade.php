@extends('layouts.admin')

@section('section_label', 'Orders')
@section('section_hint', 'Orders submitted from the mobile app appear here.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Orders</h1>
            <p class="subtitle">{{ $orders->count() }} customer orders in the system.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('orders.create') }}">Add Order</a>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    <section class="panel">
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            <div class="strong">{{ $order->order_number }}</div>
                            <div class="muted">{{ $order->created_at->format('M d, Y h:i A') }}</div>
                        </td>
                        <td>
                            <div class="strong">{{ $order->customer_name }}</div>
                            <div class="muted">{{ $order->customer_phone }}</div>
                            <div class="muted">{{ $order->customer_address }}</div>
                        </td>
                        <td>
                            @foreach ($order->items as $item)
                                <div>{{ $item->product_name }} x {{ $item->quantity }}</div>
                            @endforeach
                        </td>
                        <td>BDT {{ number_format((float) $order->total, 2) }}</td>
                        <td><span class="status">{{ $order->status }}</span></td>
                        <td><a class="btn btn-light btn-small" href="{{ route('orders.edit', $order) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
