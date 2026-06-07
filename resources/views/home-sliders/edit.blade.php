@extends('layouts.admin')

@section('section_label', 'Home Sliders')
@section('section_hint', 'Update this mobile home carousel.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Edit Slider</h1>
            <p class="subtitle">{{ $slider->name }}</p>
        </div>
        <a class="btn btn-outline" href="{{ route('home-sliders.index') }}">Back</a>
    </div>

    @include('home-sliders.form', [
        'action' => route('home-sliders.update', $slider),
        'method' => 'PUT',
    ])
@endsection
