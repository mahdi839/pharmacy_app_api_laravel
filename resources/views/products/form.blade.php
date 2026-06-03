@if ($errors->any())
    <div class="errors">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form class="form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
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
            <input id="company_search" type="search" placeholder="Search company">
            <select id="company" name="company_id" required>
                <option value="">Select company</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected((string) old('company_id', $product?->company_id) === (string) $company->id)>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
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
            <label for="image">Product Image</label>
            <input id="image" name="image" type="file" accept="image/*">
            @if ($product?->image)
                <div class="muted">Current image is already saved.</div>
            @endif
        </div>
        <div class="field field-full">
            <button class="btn btn-primary" type="submit">{{ $product ? 'Update Product' : 'Create Product' }}</button>
        </div>
    </div>
</form>

<script>
    const companySearch = document.getElementById('company_search');
    const companySelect = document.getElementById('company');
    const companyOptions = Array.from(companySelect.options);

    companySearch?.addEventListener('input', () => {
        const keyword = companySearch.value.trim().toLowerCase();

        companyOptions.forEach((option) => {
            if (! option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = ! option.textContent.toLowerCase().includes(keyword);
        });
    });
</script>
