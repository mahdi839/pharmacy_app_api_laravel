@extends('layouts.admin')

@section('section_label', 'Orders')
@section('section_hint', 'Create an order manually for a guest customer.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Create Order</h1>
            <p class="subtitle">Add a guest customer medicine order.</p>
        </div>
        <a class="btn btn-light" href="{{ route('orders.index') }}">Back to Orders</a>
    </div>

    @include('orders.form', [
        'action' => route('orders.store'),
        'method' => 'POST',
    ])
@endsection
