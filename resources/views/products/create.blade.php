@extends('layouts.admin')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Create Product</h1>
            <p class="subtitle">Add medicine details for the mobile app product list.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="errors">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form class="form" method="POST" action="{{ route('products.store') }}">
        @csrf
        <div class="form-grid">
            <div class="field">
                <label for="name">Product Name</label>
                <input id="name" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="field">
                <label for="company">Company Name</label>
                <input id="company" name="company" value="{{ old('company') }}" required>
            </div>
            <div class="field">
                <label for="strength">Strength</label>
                <input id="strength" name="strength" value="{{ old('strength') }}" placeholder="500mg" required>
            </div>
            <div class="field">
                <label for="form">Form</label>
                <input id="form" name="form" value="{{ old('form') }}" placeholder="Tablet" required>
            </div>
            <div class="field">
                <label for="price">Price</label>
                <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price') }}" required>
            </div>
            <div class="field">
                <label for="stock">Stock</label>
                <input id="stock" name="stock" type="number" min="0" value="{{ old('stock') }}" required>
            </div>
            <div class="field">
                <label for="discount">Discount Percentage</label>
                <input id="discount" name="discount" type="number" min="0" max="100" value="{{ old('discount', 0) }}">
            </div>
            <div class="field">
                <label for="image">Image URL</label>
                <input id="image" name="image" type="url" value="{{ old('image') }}" placeholder="https://example.com/product.png">
            </div>
            <div class="field field-full">
                <button class="btn btn-primary" type="submit">Create Product</button>
            </div>
        </div>
    </form>
@endsection
