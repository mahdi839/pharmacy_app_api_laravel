@extends('layouts.admin')

@section('section_label', 'Companies')
@section('section_hint', 'Update company details used by products.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Edit Company</h1>
            <p class="subtitle">{{ $company->name }}</p>
        </div>
        <a class="btn btn-light" href="{{ route('companies.index') }}">Back to Companies</a>
    </div>

    @include('companies.form', [
        'action' => route('companies.update', $company),
        'method' => 'PUT',
    ])
@endsection
