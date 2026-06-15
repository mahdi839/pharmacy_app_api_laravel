@extends('layouts.admin')

@section('section_label', 'Products')
@section('section_hint', 'Products added here appear in the React Native mobile app.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Products</h1>
            <p class="subtitle">{{ $totals['products'] }} medicines match the current filters.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('products.create') }}">Add Product</a>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="errors">{{ session('error') }}</div>
    @endif

    <form class="filters" method="GET" action="{{ route('products.index') }}">
        <div class="field">
            <label for="name">Product Name</label>
            <input id="name" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Search medicine">
        </div>
        <div class="field">
            <label for="company">Company Name</label>
            <input id="company" name="company" value="{{ $filters['company'] ?? '' }}" placeholder="Search company">
        </div>
        <div class="field">
            <label for="from">Start Date</label>
            <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="field">
            <label for="to">End Date</label>
            <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="actions">
            <button class="btn btn-primary" type="submit">Search</button>
            <a class="btn btn-outline" href="{{ route('products.index') }}">Reset</a>
        </div>
    </form>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Product</div>
            <div class="stat-value">{{ number_format($totals['products']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Stock</div>
            <div class="stat-value">{{ number_format($totals['stock']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Stock Value</div>
            <div class="stat-value">BDT {{ number_format($totals['stock_value'], 2) }}</div>
        </div>
       
    </section>

    <section class="panel">
        <table>
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Company</th>
                    <th>Purchase Price</th>
                    <th>MRP Rate</th>
                    <th>Sell Price</th>
                    <th>Stock</th>
                    <th>Stock Value</th>
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
                        <td>{{ $product->companyName() }}</td>
                        <td>BDT {{ number_format((float) $product->purchase_price, 2) }}</td>
                        <td>{{ $product->mrp_rate === null ? '-' : 'BDT '.number_format((float) $product->mrp_rate, 2) }}</td>
                        <td>BDT {{ number_format($product->effectiveSellPrice(), 2) }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>BDT {{ number_format($product->stockValue(), 2) }}</td>
                        <td>{{ $product->discount }}%</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-light btn-small" href="{{ route('products.edit', $product) }}">Edit</a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger-soft btn-small" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="muted">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
