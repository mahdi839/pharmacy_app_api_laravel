{{-- Usage: @include('partials.pagination', ['paginator' => $items, 'label' => 'products']) --}}
@if ($paginator->total() > 0)
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $window = collect(range(max(1, $current - 2), min($last, $current + 2)));
    @endphp

    <nav class="pager" aria-label="Pagination">
        <div class="pager-info">
            Showing <strong>{{ number_format($paginator->firstItem()) }}</strong>&ndash;<strong>{{ number_format($paginator->lastItem()) }}</strong>
            of <strong>{{ number_format($paginator->total()) }}</strong> {{ $label ?? 'results' }}
        </div>

        @if ($paginator->hasPages())
            <div class="pager-links">
                @if ($paginator->onFirstPage())
                    <span class="pager-link is-disabled">&lsaquo; Prev</span>
                @else
                    <a class="pager-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo; Prev</a>
                @endif

                @if ($window->first() > 1)
                    <a class="pager-link" href="{{ $paginator->url(1) }}">1</a>
                    @if ($window->first() > 2)
                        <span class="pager-gap">&hellip;</span>
                    @endif
                @endif

                @foreach ($window as $page)
                    @if ($page === $current)
                        <span class="pager-link is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pager-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($window->last() < $last)
                    @if ($window->last() < $last - 1)
                        <span class="pager-gap">&hellip;</span>
                    @endif
                    <a class="pager-link" href="{{ $paginator->url($last) }}">{{ $last }}</a>
                @endif

                @if ($paginator->hasMorePages())
                    <a class="pager-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &rsaquo;</a>
                @else
                    <span class="pager-link is-disabled">Next &rsaquo;</span>
                @endif
            </div>
        @endif
    </nav>
@endif
