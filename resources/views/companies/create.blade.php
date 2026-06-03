@extends('layouts.admin')

@section('section_label', 'Companies')
@section('section_hint', 'Add a company before selecting it on a product.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Create Company</h1>
            <p class="subtitle">Add a medicine manufacturer or supplier.</p>
        </div>
        <a class="btn btn-light" href="{{ route('companies.index') }}">Back to Companies</a>
    </div>

    @include('companies.form', [
        'action' => route('companies.store'),
        'method' => 'POST',
    ])
@endsection
