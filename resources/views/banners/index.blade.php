@extends('layouts.admin')

@section('section_label', 'Banners')
@section('section_hint', 'Manage promotional images shown below mobile search.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Mobile Banners</h1>
            <p class="subtitle">{{ $banners->count() }} banner images configured.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('banners.create') }}">Add Banner</a>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    <section class="panel">
        <table>
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Size</th>
                    <th>Added</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($banners as $banner)
                    <tr>
                        <td><img src="{{ $banner->displayImage() }}" alt="Mobile banner" style="width: 240px; aspect-ratio: 3 / 1; object-fit: cover; border-radius: 6px;"></td>
                        <td>1080 × 360 px</td>
                        <td>{{ $banner->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-light btn-small" href="{{ route('banners.edit', $banner) }}">Edit</a>
                                <form method="POST" action="{{ route('banners.destroy', $banner) }}" onsubmit="return confirm('Delete this banner?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline btn-small" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted">No banners yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
