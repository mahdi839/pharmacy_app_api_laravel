@extends('layouts.admin')

@section('section_label', 'Notifications')
@section('section_hint', 'The notification is sent to every logged-in customer\'s phone as soon as you submit.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Send Notification</h1>
            <p class="subtitle">Will be sent to {{ number_format($deviceCount) }} customer phone(s).</p>
        </div>
        <a class="btn btn-outline" href="{{ route('notifications.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="errors">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form class="form" method="POST" action="{{ route('notifications.store') }}" enctype="multipart/form-data"
          onsubmit="this.querySelector('button[type=submit]').disabled = true;">
        @csrf

        <div class="form-grid">
            <div class="field field-full">
                <label for="title">Title</label>
                <input id="title" name="title" value="{{ old('title') }}" maxlength="120" required placeholder="e.g. Napa Extra now 10% off">
            </div>

            <div class="field field-full">
                <label for="body">Message</label>
                <textarea id="body" name="body" maxlength="1000" required placeholder="e.g. Offer valid till Friday. Order now from the app.">{{ old('body') }}</textarea>
            </div>

            <div class="field field-full">
                <label for="image">Image (optional)</label>
                <input id="image" name="image" type="file" accept="image/png,image/jpeg">
                <div class="muted">JPG or PNG, maximum 1 MB. A wide image (2:1, e.g. 1024 × 512 px) looks best. If you link a product and leave this empty, the product's image is used.</div>
            </div>

            <div class="field field-full">
                <label for="product_search">Linked Product (optional)</label>
                <input id="product_search" type="search" placeholder="Type to filter the product list" autocomplete="off">
                <select id="product_id" name="product_id" size="6" style="height: auto; padding: 6px;">
                    <option value="" @selected(old('product_id') === null || old('product_id') === '')>— No product (tapping opens the app's home page) —</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                            {{ $product->name }} — {{ $product->strength }} {{ $product->form }}
                        </option>
                    @endforeach
                </select>
                <div class="muted">When a customer taps the notification, the app opens this product.</div>
            </div>

            <div class="field field-full">
                <button class="btn btn-primary" type="submit">Send Notification</button>
            </div>
        </div>
    </form>

    <script>
        (() => {
            const search = document.getElementById('product_search');
            const select = document.getElementById('product_id');

            search.addEventListener('input', () => {
                const term = search.value.trim().toLowerCase();

                Array.from(select.options).forEach((option) => {
                    option.hidden = option.value !== '' && term !== '' && !option.text.toLowerCase().includes(term);
                });
            });
        })();
    </script>
@endsection
