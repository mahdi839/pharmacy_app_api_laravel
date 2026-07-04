@extends('layouts.admin')

@section('section_label', 'Banners')
@section('section_hint', 'Upload a 1080 × 360 px mobile banner.')

@section('content')
    <div class="toolbar">
        <h1 class="title">Add Banner</h1>
        <a class="btn btn-outline" href="{{ route('banners.index') }}">Back</a>
    </div>

    @include('banners.form', [
        'action' => route('banners.store'),
        'method' => 'POST',
    ])
@endsection
