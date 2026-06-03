@extends('layouts.admin')

@section('section_label', 'Products')
@section('section_hint', 'Update medicine details used by the mobile app.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Edit Product</h1>
            <p class="subtitle">{{ $product->name }} from {{ $product->company }}.</p>
        </div>
        <a class="btn btn-light" href="{{ route('products.index') }}">Back to Products</a>
    </div>

    @include('products.form', [
        'action' => route('products.update', $product),
        'method' => 'PUT',
    ])
@endsection
