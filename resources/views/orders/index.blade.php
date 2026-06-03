@extends('layouts.admin')

@section('section_label', 'Orders')
@section('section_hint', 'Orders submitted from the mobile app appear here.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Orders</h1>
            <p class="subtitle">{{ $orders->count() }} orders matched your current view.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('orders.create') }}">Add Order</a>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    <form class="filters" method="GET" action="{{ route('orders.index') }}">
        <div class="field">
            <label for="search">Search</label>
            <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Order number, customer name, phone">
        </div>
        <div class="field">
            <label for="from">From Date</label>
            <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="field">
            <label for="to">To Date</label>
            <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All Status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Filter</button>
    </form>

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
                        <td>
                            <form class="inline-form" method="POST" action="{{ route('orders.status', $order) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-light btn-small" href="{{ route('orders.edit', $order) }}">Edit</a>
                                <a class="btn btn-outline btn-small" href="{{ route('orders.invoice', $order) }}" target="_blank">Print</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
