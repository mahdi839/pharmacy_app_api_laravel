@extends('layouts.admin')

@section('section_label', 'Companies')
@section('section_hint', 'Manage medicine company names used when creating products.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Companies</h1>
            <p class="subtitle">{{ $companies->count() }} companies available for product selection.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('companies.create') }}">Add Company</a>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    <section class="panel">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Products</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($companies as $company)
                    <tr>
                        <td>
                            <div class="strong">{{ $company->name }}</div>
                            @if ($company->address)
                                <div class="muted">{{ $company->address }}</div>
                            @endif
                        </td>
                        <td>{{ $company->phone ?: '-' }}</td>
                        <td>{{ $company->email ?: '-' }}</td>
                        <td>{{ $company->products_count }}</td>
                        <td><a class="btn btn-light btn-small" href="{{ route('companies.edit', $company) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">No companies yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
