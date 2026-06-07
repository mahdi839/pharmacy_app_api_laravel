@extends('layouts.admin')

@section('section_label', 'Home Sliders')
@section('section_hint', 'Choose product rows that appear at the top of the mobile home page.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Home Sliders</h1>
            <p class="subtitle">{{ $sliders->count() }} slider rows configured for the mobile app.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('home-sliders.create') }}">Add Slider</a>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    <section class="panel">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Products</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sliders as $slider)
                    <tr>
                        <td class="strong">{{ $slider->name }}</td>
                        <td>{{ $slider->products_count }}</td>
                        <td>{{ $slider->sort_order }}</td>
                        <td><span class="status">{{ $slider->is_active ? 'active' : 'hidden' }}</span></td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-light btn-small" href="{{ route('home-sliders.edit', $slider) }}">Edit</a>
                                <form method="POST" action="{{ route('home-sliders.destroy', $slider) }}" onsubmit="return confirm('Delete this slider?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline btn-small" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">No home sliders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
