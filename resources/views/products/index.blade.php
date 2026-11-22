@extends('layouts.admin')

@section('section_label', 'Products')
@section('section_hint', 'Products added here appear in the React Native mobile app.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Products</h1>
            <p class="subtitle">{{ number_format($totals['products']) }} medicines match the current filters.</p>
        </div>
        <div class="actions">
            <a class="btn btn-outline" href="{{ route('products.pdf', array_filter($filters)) }}">Download PDF</a>
            <a class="btn btn-primary" href="{{ route('products.create') }}">Add Product</a>
        </div>
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

    @php
        $statCards = [
            ['label' => 'Total Products', 'value' => number_format($totals['products']), 'theme' => 'emerald',
             'icon' => '<svg viewBox="0 0 24 24"><path d="m10.5 20.5 10-10a4.95 4.95 0 0 0-7-7l-10 10a4.95 4.95 0 0 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>'],
            ['label' => 'Companies', 'value' => number_format($totals['companies']), 'theme' => 'indigo',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>'],
            ['label' => 'Total Stock', 'value' => number_format($totals['stock']), 'theme' => 'amber',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>'],
            ['label' => 'Total Stock Value', 'value' => 'BDT '.number_format($totals['stock_value'], 2), 'theme' => 'rose',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M19 7V6a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h14a2 2 0 0 1 2 2v1"/><path d="M3 6v12a2 2 0 0 0 2 2h16v-6h-4a2 2 0 0 1 0-4h4"/></svg>'],
        ];
    @endphp

    <section class="stats-grid">
        @foreach ($statCards as $stat)
            <div class="stat-card stat-card-premium stat-card-{{ $stat['theme'] }}">
                <div class="stat-top">
                    <div class="stat-label">{{ $stat['label'] }}</div>
                    <span class="card-icon">{!! $stat['icon'] !!}</span>
                </div>
                <div class="stat-value">{{ $stat['value'] }}</div>
            </div>
        @endforeach
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

    @include('partials.pagination', ['paginator' => $products, 'label' => 'products'])
@endsection
