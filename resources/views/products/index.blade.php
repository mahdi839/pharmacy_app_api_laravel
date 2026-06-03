@extends('layouts.admin')

@section('section_label', 'Products')
@section('section_hint', 'Products added here appear in the React Native mobile app.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Products</h1>
            <p class="subtitle">{{ $products->count() }} medicines available for the mobile app.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('products.create') }}">Add Product</a>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    <section class="panel">
        <table>
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Company</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Discount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <img class="thumb" src="{{ $product->displayImage() }}" alt="{{ $product->name }}">
                                <div>
                                    <div class="strong">{{ $product->name }}</div>
                                    <div class="muted">{{ $product->strength }} | {{ $product->form }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $product->company }}</td>
                        <td>BDT {{ number_format((float) $product->price, 2) }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>{{ $product->discount }}%</td>
                        <td><a class="btn btn-light btn-small" href="{{ route('products.edit', $product) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">No products yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
