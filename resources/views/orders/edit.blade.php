@extends('layouts.admin')

@section('section_label', 'Orders')
@section('section_hint', 'Update customer order status or medicine items.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Edit Order</h1>
            <p class="subtitle">{{ $order->order_number }} for {{ $order->customer_name }}.</p>
        </div>
        <a class="btn btn-light" href="{{ route('orders.index') }}">Back to Orders</a>
    </div>

    @include('orders.form', [
        'action' => route('orders.update', $order),
        'method' => 'PUT',
    ])
@endsection
