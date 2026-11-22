@extends('layouts.admin')

@section('section_label', 'Products')
@section('section_hint', 'Products added here appear in the React Native mobile app.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Products</h1>
            <p class="subtitle">{{ number_format($totals['products']) }} medicines match the current filters.</p>
        </div>
        <div class="actions">
            <a class="btn btn-outline" id="pdf-download" href="{{ route('products.pdf', array_filter($filters)) }}" data-progress-url="{{ url('/products/pdf/progress') }}">Download PDF</a>
            <a class="btn btn-primary" href="{{ route('products.create') }}">Add Product</a>
        </div>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="errors">{{ session('error') }}</div>
    @endif

    <form class="filters" method="GET" action="{{ route('products.index') }}">
        <div class="field">
            <label for="name">Product Name</label>
            <input id="name" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Search medicine">
        </div>
        <div class="field">
            <label for="company">Company Name</label>
            <input id="company" name="company" value="{{ $filters['company'] ?? '' }}" placeholder="Search company">
        </div>
        <div class="field">
            <label for="from">Start Date</label>
            <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="field">
            <label for="to">End Date</label>
            <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="actions">
            <button class="btn btn-primary" type="submit">Search</button>
            <a class="btn btn-outline" href="{{ route('products.index') }}">Reset</a>
        </div>
    </form>

    @php
        $statCards = [
            ['label' => 'Total Products', 'value' => number_format($totals['products']), 'theme' => 'emerald',
             'icon' => '<svg viewBox="0 0 24 24"><path d="m10.5 20.5 10-10a4.95 4.95 0 0 0-7-7l-10 10a4.95 4.95 0 0 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>'],
            ['label' => 'Companies', 'value' => number_format($totals['companies']), 'theme' => 'indigo',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>'],
            ['label' => 'Total Stock', 'value' => number_format($totals['stock']), 'theme' => 'amber',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>'],
            ['label' => 'Total Stock Value', 'value' => 'BDT '.number_format($totals['stock_value'], 2), 'theme' => 'rose',
             'icon' => '<svg viewBox="0 0 24 24"><path d="M19 7V6a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h14a2 2 0 0 1 2 2v1"/><path d="M3 6v12a2 2 0 0 0 2 2h16v-6h-4a2 2 0 0 1 0-4h4"/></svg>'],
        ];
    @endphp

    <section class="stats-grid">
        @foreach ($statCards as $stat)
            <div class="stat-card stat-card-premium stat-card-{{ $stat['theme'] }}">
                <div class="stat-top">
                    <div class="stat-label">{{ $stat['label'] }}</div>
                    <span class="card-icon">{!! $stat['icon'] !!}</span>
                </div>
                <div class="stat-value">{{ $stat['value'] }}</div>
            </div>
        @endforeach
    </section>

    <section class="panel">
        <table>
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Company</th>
                    <th>Purchase Price</th>
                    <th>MRP Rate</th>
                    <th>Sell Price</th>
                    <th>Stock</th>
                    <th>Stock Value</th>
                    <th>Discount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <img class="thumb" src="{{ $product->displayImage() }}" alt="{{ $product->name }}">
                                <div>
                                    <div class="strong">{{ $product->name }}</div>
                                    <div class="muted">{{ $product->strength }} | {{ $product->form }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $product->companyName() }}</td>
                        <td>BDT {{ number_format((float) $product->purchase_price, 2) }}</td>
                        <td>{{ $product->mrp_rate === null ? '-' : 'BDT '.number_format((float) $product->mrp_rate, 2) }}</td>
                        <td>BDT {{ number_format($product->effectiveSellPrice(), 2) }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>BDT {{ number_format($product->stockValue(), 2) }}</td>
                        <td>{{ $product->discount }}%</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-light btn-small" href="{{ route('products.edit', $product) }}">Edit</a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger-soft btn-small" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="muted">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @include('partials.pagination', ['paginator' => $products, 'label' => 'products'])

    <div class="pdf-progress" id="pdf-progress" role="dialog" aria-modal="true" aria-labelledby="pdf-progress-title" hidden>
        <div class="pdf-progress-card">
            <div class="pdf-progress-head">
                <span class="pdf-progress-icon">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg>
                </span>
                <div>
                    <div class="pdf-progress-title" id="pdf-progress-title">Preparing product PDF</div>
                    <div class="pdf-progress-status" id="pdf-progress-status" aria-live="polite">Starting&hellip;</div>
                </div>
                <div class="pdf-progress-percent" id="pdf-progress-percent">0%</div>
            </div>
            <div class="pdf-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="pdf-progress-track">
                <div class="pdf-progress-bar" id="pdf-progress-bar"></div>
            </div>
            <div class="pdf-progress-actions">
                <a class="btn btn-light btn-small" id="pdf-progress-direct" href="#" hidden>Try direct download</a>
                <button class="btn btn-outline btn-small" type="button" id="pdf-progress-close">Cancel</button>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .pdf-progress { align-items: center; background: rgba(15, 23, 42, .45); backdrop-filter: blur(3px); display: flex; inset: 0; justify-content: center; padding: 16px; position: fixed; z-index: 50; }
        .pdf-progress[hidden], .pdf-progress [hidden] { display: none !important; }
        .pdf-progress-card { background: #fff; border-radius: 14px; box-shadow: 0 30px 60px rgba(15, 23, 42, .25); max-width: 440px; padding: 22px; width: 100%; }
        .pdf-progress-head { align-items: center; display: flex; gap: 12px; margin-bottom: 18px; }
        .pdf-progress-icon { align-items: center; background: linear-gradient(135deg, #047857 0%, #10b981 100%); border-radius: 10px; color: #fff; display: inline-flex; flex: none; height: 42px; justify-content: center; width: 42px; }
        .pdf-progress-icon svg { fill: none; height: 20px; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2; width: 20px; }
        .pdf-progress-title { color: #173d31; font-size: 16px; font-weight: 900; }
        .pdf-progress-status { color: #667085; font-size: 13px; margin-top: 2px; }
        .pdf-progress-percent { color: #047857; font-size: 20px; font-variant-numeric: tabular-nums; font-weight: 900; margin-left: auto; }
        .pdf-progress-track { background: #e8f5ef; border-radius: 999px; height: 10px; overflow: hidden; }
        .pdf-progress-bar { background: linear-gradient(90deg, #047857, #10b981, #34d399); background-size: 200% 100%; border-radius: 999px; height: 100%; transition: width .35s ease; width: 0; animation: pdf-shimmer 1.6s linear infinite; }
        .pdf-progress.is-error .pdf-progress-bar { animation: none; background: #ef4444; }
        .pdf-progress.is-error .pdf-progress-percent { color: #b42318; }
        .pdf-progress.is-done .pdf-progress-bar { animation: none; }
        .pdf-progress-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 18px; }
        @keyframes pdf-shimmer { from { background-position: 200% 0; } to { background-position: 0 0; } }
        @media (prefers-reduced-motion: reduce) { .pdf-progress-bar { animation: none; transition: none; } }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            var link = document.getElementById('pdf-download');
            var modal = document.getElementById('pdf-progress');
            if (!link || !modal || !window.fetch || !window.ReadableStream || !window.URL || !URL.createObjectURL) {
                return; // Old browser: the plain link still downloads the PDF.
            }

            var el = function (id) { return document.getElementById(id); };
            var bar = el('pdf-progress-bar'), track = el('pdf-progress-track'), percentEl = el('pdf-progress-percent');
            var statusEl = el('pdf-progress-status'), titleEl = el('pdf-progress-title');
            var closeBtn = el('pdf-progress-close'), directLink = el('pdf-progress-direct');
            var running = null;

            // Building the PDF on the server fills the bar up to 90%; the file transfer fills the rest.
            var BUILD_SHARE = 90;

            function setProgress(percent, status) {
                percent = Math.max(0, Math.min(100, Math.round(percent)));
                bar.style.width = percent + '%';
                track.setAttribute('aria-valuenow', percent);
                percentEl.textContent = percent + '%';
                if (status) statusEl.textContent = status;
            }

            function token() {
                if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
                return 'pdf-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 12);
            }

            function formatBytes(bytes) {
                return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
            }

            function filenameFrom(response) {
                var header = response.headers.get('Content-Disposition') || '';
                var match = /filename\*?=(?:UTF-8'')?"?([^";]+)"?/i.exec(header);
                return match ? decodeURIComponent(match[1]) : 'med-bangladesh-products.pdf';
            }

            function close() {
                if (running) running.cancel();
                running = null;
                modal.hidden = true;
            }

            function fail(message) {
                modal.classList.add('is-error');
                titleEl.textContent = 'Could not prepare the PDF';
                statusEl.textContent = message;
                directLink.hidden = false;
                closeBtn.textContent = 'Close';
            }

            function start() {
                var id = token();
                var controller = window.AbortController ? new AbortController() : null;
                var state = { cancelled: false, buildPercent: 0, building: true, pollTimer: null };
                running = {
                    cancel: function () {
                        state.cancelled = true;
                        clearTimeout(state.pollTimer);
                        if (controller) controller.abort();
                    }
                };

                modal.classList.remove('is-error', 'is-done');
                modal.hidden = false;
                titleEl.textContent = 'Preparing product PDF';
                directLink.hidden = true;
                directLink.href = link.href;
                closeBtn.textContent = 'Cancel';
                setProgress(0, 'Starting…');

                function poll() {
                    if (state.cancelled || !state.building) return;
                    fetch(link.dataset.progressUrl + '/' + encodeURIComponent(id), {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    }).then(function (r) { return r.ok ? r.json() : null; }).then(function (p) {
                        if (!p || state.cancelled || !state.building) return;
                        if (p.stage === 'rendering') {
                            state.buildPercent = Math.max(state.buildPercent, p.percent);
                            setProgress(state.buildPercent * BUILD_SHARE / 100,
                                p.pages_total ? 'Building page ' + Math.max(1, p.pages_done) + ' of ' + p.pages_total + '…' : 'Building PDF…');
                        } else if (p.stage === 'merging') {
                            setProgress(BUILD_SHARE - 2, 'Finishing up…');
                        } else if (p.stage === 'queued') {
                            setProgress(2, 'Waiting for another download to finish…');
                        }
                    }).catch(function () {}).then(function () {
                        if (!state.cancelled && state.building) state.pollTimer = setTimeout(poll, 400);
                    });
                }
                state.pollTimer = setTimeout(poll, 250);

                var url = link.href + (link.href.indexOf('?') === -1 ? '?' : '&') + 'progress=' + encodeURIComponent(id);

                fetch(url, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/pdf' },
                    signal: controller ? controller.signal : undefined
                }).then(function (response) {
                    state.building = false;
                    clearTimeout(state.pollTimer);

                    var type = response.headers.get('Content-Type') || '';
                    if (!response.ok || type.indexOf('application/pdf') === -1) {
                        throw new Error(response.redirected || type.indexOf('text/html') !== -1
                            ? 'Your session may have expired. Please refresh the page and log in again.'
                            : 'The server returned an error (' + response.status + '). Please try again.');
                    }

                    var total = parseInt(response.headers.get('Content-Length') || '0', 10);
                    var reader = response.body.getReader();
                    var chunks = [], received = 0;
                    setProgress(BUILD_SHARE, 'Downloading…');

                    function read() {
                        return reader.read().then(function (result) {
                            if (result.done) return;
                            chunks.push(result.value);
                            received += result.value.length;
                            if (total > 0) {
                                setProgress(BUILD_SHARE + (100 - BUILD_SHARE) * received / total,
                                    'Downloading ' + formatBytes(received) + ' of ' + formatBytes(total) + '…');
                            }
                            return read();
                        });
                    }

                    return read().then(function () {
                        return { blob: new Blob(chunks, { type: 'application/pdf' }), name: filenameFrom(response) };
                    });
                }).then(function (file) {
                    if (state.cancelled) return;
                    var objectUrl = URL.createObjectURL(file.blob);
                    var a = document.createElement('a');
                    a.href = objectUrl;
                    a.download = file.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    setTimeout(function () { URL.revokeObjectURL(objectUrl); }, 60000);

                    modal.classList.add('is-done');
                    titleEl.textContent = 'PDF ready';
                    setProgress(100, 'Saved ' + file.name + ' (' + formatBytes(file.blob.size) + ').');
                    closeBtn.textContent = 'Close';
                    running = null;
                    setTimeout(function () { if (!running) modal.hidden = true; }, 1800);
                }).catch(function (error) {
                    state.building = false;
                    clearTimeout(state.pollTimer);
                    if (state.cancelled) return;
                    fail(error && error.message && error.name !== 'TypeError'
                        ? error.message
                        : 'Network problem while downloading. Please check your connection and try again.');
                    running = null;
                });
            }

            link.addEventListener('click', function (event) {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.button !== 0) return;
                event.preventDefault();
                if (!running) start();
            });
            closeBtn.addEventListener('click', close);
            directLink.addEventListener('click', function () { modal.hidden = true; });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.hidden) close();
            });
        })();
    </script>
@endpush
