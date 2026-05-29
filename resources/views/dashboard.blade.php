@extends('layouts.admin')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Products</h1>
            <p class="subtitle">{{ $products->count() }} products available for the mobile app API.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('products.create') }}">Add Product</a>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    <section class="grid">
        @foreach ($products as $product)
            <article class="card">
                <div class="image-wrap">
                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
                    <span class="discount">{{ $product['discount'] }}% OFF</span>
                </div>
                <div class="card-body">
                    <h2 class="product-name">{{ $product['name'] }}</h2>
                    <div class="muted">{{ $product['company'] }}</div>
                    <div class="muted">{{ $product['strength'] }} | {{ $product['form'] }}</div>
                    <div class="meta">
                        <span class="price">BDT {{ $product['price'] }}</span>
                        <span class="muted">Stock: {{ $product['stock'] }}</span>
                    </div>
                </div>
            </article>
        @endforeach
    </section>
@endsection
