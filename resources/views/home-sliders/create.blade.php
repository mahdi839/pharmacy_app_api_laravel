@extends('layouts.admin')

@section('section_label', 'Home Sliders')
@section('section_hint', 'Create a product carousel for the mobile home page.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Add Slider</h1>
            <p class="subtitle">Name the row and choose the medicines to show.</p>
        </div>
        <a class="btn btn-outline" href="{{ route('home-sliders.index') }}">Back</a>
    </div>

    @include('home-sliders.form', [
        'action' => route('home-sliders.store'),
        'method' => 'POST',
    ])
@endsection
