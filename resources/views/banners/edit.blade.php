@extends('layouts.admin')

@section('section_label', 'Banners')
@section('section_hint', 'Replace this mobile banner image.')

@section('content')
    <div class="toolbar">
        <h1 class="title">Edit Banner</h1>
        <a class="btn btn-outline" href="{{ route('banners.index') }}">Back</a>
    </div>

    @include('banners.form', [
        'action' => route('banners.update', $banner),
        'method' => 'PUT',
    ])
@endsection
