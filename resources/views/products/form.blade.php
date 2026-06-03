@if ($errors->any())
    <div class="errors">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form class="form" method="POST" action="{{ $action }}">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="form-grid">
        <div class="field">
            <label for="name">Product Name</label>
            <input id="name" name="name" value="{{ old('name', $product?->name) }}" required>
        </div>
        <div class="field">
            <label for="company">Company Name</label>
            <input id="company" name="company" value="{{ old('company', $product?->company) }}" required>
        </div>
        <div class="field">
            <label for="strength">Strength</label>
            <input id="strength" name="strength" value="{{ old('strength', $product?->strength) }}" placeholder="500mg" required>
        </div>
        <div class="field">
            <label for="form">Form</label>
            <input id="form" name="form" value="{{ old('form', $product?->form) }}" placeholder="Tablet" required>
        </div>
        <div class="field">
            <label for="price">Price</label>
            <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $product?->price) }}" required>
        </div>
        <div class="field">
            <label for="stock">Stock</label>
            <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product?->stock) }}" required>
        </div>
        <div class="field">
            <label for="discount">Discount Percentage</label>
            <input id="discount" name="discount" type="number" min="0" max="100" value="{{ old('discount', $product?->discount ?? 0) }}">
        </div>
        <div class="field">
            <label for="image">Image URL</label>
            <input id="image" name="image" type="url" value="{{ old('image', $product?->image) }}" placeholder="https://example.com/product.png">
        </div>
        <div class="field field-full">
            <button class="btn btn-primary" type="submit">{{ $product ? 'Update Product' : 'Create Product' }}</button>
        </div>
    </div>
</form>
