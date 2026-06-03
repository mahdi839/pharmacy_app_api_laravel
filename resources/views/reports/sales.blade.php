@extends('layouts.admin')

@section('section_label', 'Sales Report')
@section('section_hint', 'Track order revenue, discounts, item volume, and status mix.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Sales Report</h1>
            <p class="subtitle">{{ $from->format('M d, Y') }} to {{ $to->format('M d, Y') }}</p>
        </div>
    </div>

    <form class="filters" method="GET" action="{{ route('reports.sales') }}">
        <div class="field">
            <label for="from">From Date</label>
            <input id="from" name="from" type="date" value="{{ $from->toDateString() }}">
        </div>
        <div class="field">
            <label for="to">To Date</label>
            <input id="to" name="to" type="date" value="{{ $to->toDateString() }}">
        </div>
        <button class="btn btn-primary" type="submit">View Report</button>
    </form>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Net Sales</div>
            <div class="stat-value">BDT {{ number_format($stats['net_sales'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Gross Sales</div>
            <div class="stat-value">BDT {{ number_format($stats['gross_sales'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Discounts</div>
            <div class="stat-value">BDT {{ number_format($stats['discount_total'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Items Sold</div>
            <div class="stat-value">{{ $stats['items_sold'] }}</div>
        </div>
    </section>

    <section class="panel">
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Orders</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statusCounts as $status => $count)
                    <tr>
                        <td><span class="status">{{ $status }}</span></td>
                        <td>{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
