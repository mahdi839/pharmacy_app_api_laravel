@if ($errors->any())
    <div class="errors">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

@php
    $existingItems = $order?->items ?? collect();
    $rowCount = max(5, old('items') ? count(old('items')) : $existingItems->count());
@endphp

<form class="form form-wide" method="POST" action="{{ $action }}">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="form-grid">
        <div class="field">
            <label for="customer_name">Customer Name</label>
            <input id="customer_name" name="customer_name" value="{{ old('customer_name', $order?->customer_name) }}" required>
        </div>
        <div class="field">
            <label for="customer_phone">Customer Phone</label>
            <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $order?->customer_phone) }}" required>
        </div>
        <div class="field field-full">
            <label for="customer_address">Customer Address</label>
            <textarea id="customer_address" name="customer_address" required>{{ old('customer_address', $order?->customer_address) }}</textarea>
        </div>

        @if ($order)
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="field {{ $order ? '' : 'field-full' }}">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes">{{ old('notes', $order?->notes) }}</textarea>
        </div>

        <div class="field field-full">
            <label>Medicines</label>
            @for ($index = 0; $index < $rowCount; $index++)
                @php
                    $existingItem = $existingItems->values()->get($index);
                    $selectedProduct = old("items.$index.product_id", $existingItem?->product_id);
                    $quantity = old("items.$index.quantity", $existingItem?->quantity);
                @endphp
                <div class="item-grid">
                    <select name="items[{{ $index }}][product_id]" {{ $index === 0 ? 'required' : '' }}>
                        <option value="">Select medicine</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) $selectedProduct === (string) $product->id)>
                                {{ $product->name }} - {{ $product->strength }} - BDT {{ number_format($product->effectiveSellPrice(), 2) }}
                            </option>
                        @endforeach
                    </select>
                    <input name="items[{{ $index }}][quantity]" type="number" min="1" value="{{ $quantity }}" placeholder="Qty" {{ $index === 0 ? 'required' : '' }}>
                </div>
            @endfor
        </div>

        <div class="field field-full">
            <button class="btn btn-primary" type="submit">{{ $order ? 'Update Order' : 'Create Order' }}</button>
        </div>
    </div>
</form>
