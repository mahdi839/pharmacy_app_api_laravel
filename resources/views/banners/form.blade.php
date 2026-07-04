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
        @if ($banner)
            <div class="field field-full">
                <label>Current Banner</label>
                <img src="{{ $banner->displayImage() }}" alt="Current banner" style="max-width: 540px; width: 100%; aspect-ratio: 3 / 1; object-fit: cover; border-radius: 8px;">
            </div>
        @endif

        <div class="field field-full">
            <label for="image">Banner Image — required size: 1080 px width × 360 px height</label>
            <input id="image" name="image" type="file" accept="image/*" @required(!$banner)>
            <div class="muted">Use an exact 3:1 image. Maximum file size: 5 MB.</div>
        </div>

        <div class="field field-full">
            <button class="btn btn-primary" type="submit">{{ $banner ? 'Update Banner' : 'Create Banner' }}</button>
        </div>
    </div>
</form>
