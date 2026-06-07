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
            <label for="name">Slider Name</label>
            <input id="name" name="name" value="{{ old('name', $slider?->name) }}" placeholder="Trending Medicine" required>
        </div>
        <div class="field">
            <label for="sort_order">Sort Order</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $slider?->sort_order ?? 0) }}">
        </div>
        <div class="field field-full">
            <label for="product_ids">Products</label>
            <select id="product_ids" name="product_ids[]" multiple required>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(in_array((string) $product->id, old('product_ids', $selectedProducts), true))>
                        {{ $product->name }} - {{ $product->companyName() }} - {{ $product->strength }}
                    </option>
                @endforeach
            </select>
            <div class="muted">Selected order controls the mobile carousel order.</div>
        </div>
        <div class="field field-full">
            <label>
                <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $slider?->is_active ?? true))>
                Show this slider on mobile
            </label>
        </div>
        <div class="field field-full">
            <button class="btn btn-primary" type="submit">{{ $slider ? 'Update Slider' : 'Create Slider' }}</button>
        </div>
    </div>
</form>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-wrapper.multi .ts-control { min-height: 42px; border-color: #cfd8e3; border-radius: 6px; padding: 8px 12px; }
        .ts-wrapper.focus .ts-control { border-color: #1a7f5a; box-shadow: 0 0 0 3px rgba(26, 127, 90, 0.12); }
        input[type="checkbox"] { width: auto; height: auto; margin-right: 8px; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
    <script>
        new TomSelect('#product_ids', {
            create: false,
            plugins: ['remove_button', 'drag_drop', 'dropdown_input'],
        });
    </script>
@endpush
