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
            <label for="name">Company Name</label>
            <input id="name" name="name" value="{{ old('name', $company?->name) }}" required>
        </div>
        <div class="field">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" value="{{ old('phone', $company?->phone) }}">
        </div>
        <div class="field field-full">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $company?->email) }}">
        </div>
        <div class="field field-full">
            <label for="address">Address</label>
            <textarea id="address" name="address">{{ old('address', $company?->address) }}</textarea>
        </div>
        <div class="field field-full">
            <button class="btn btn-primary" type="submit">{{ $company ? 'Update Company' : 'Create Company' }}</button>
        </div>
    </div>
</form>
