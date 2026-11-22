<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Med Bangladesh - Product Price List</title>
    <style>
        @page { margin: 30px 36px 58px; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5px;
            color: #1f2d28;
        }

        /* ---------- Letterhead ---------- */
        .accent-bar { height: 5px; background: #0f5a40; border-radius: 3px 3px 0 0; }
        .accent-bar-thin { height: 2px; background: #c9a227; margin-bottom: 20px; }

        .letterhead { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .letterhead td { vertical-align: middle; padding: 0; }
        .logo-mark {
            width: 42px; height: 33px;
            padding-top: 9px;
            background: #0f5a40;
            border-radius: 8px;
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            line-height: 1;
        }
        .brand-name {
            font-size: 22px;
            font-weight: bold;
            color: #0f5a40;
            letter-spacing: 0.5px;
            margin: 0;
            line-height: 1.1;
        }
        .brand-tagline {
            font-size: 8.5px;
            color: #8a7a3c;
            letter-spacing: 2.2px;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            color: #1f2d28;
            text-align: right;
            letter-spacing: 0.4px;
        }
        .doc-sub {
            font-size: 8.5px;
            color: #6b7a74;
            text-align: right;
            margin-top: 3px;
        }

        /* ---------- Summary strip ---------- */
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .summary td.gap { width: 2%; padding: 0; background: none; border: none; }
        .summary td.card {
            background: #f3f8f5;
            border: 1px solid #dbe8e1;
            border-left: 3px solid #0f5a40;
            border-radius: 4px;
            padding: 8px 10px;
            width: 32%;
        }
        .summary .label {
            font-size: 7.5px;
            color: #6b7a74;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }
        .summary .value { font-size: 13px; font-weight: bold; color: #0f5a40; margin-top: 3px; }
        .summary .value.small { font-size: 9.5px; margin-top: 5px; color: #1f2d28; }

        .filters { font-size: 8.5px; color: #6b7a74; margin: -6px 0 12px; }
        .filters strong { color: #1f2d28; }

        /* ---------- Product table ---------- */
        table.products { width: 100%; border-collapse: collapse; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        table.products th {
            background: #0f5a40;
            color: #ffffff;
            text-align: left;
            padding: 8px 8px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            border-bottom: 2px solid #c9a227;
        }
        table.products td {
            padding: 7px 8px;
            border-bottom: 1px solid #e6ece9;
            vertical-align: middle;
        }
        table.products tbody tr:nth-child(even) td { background: #f8fbf9; }
        .col-sl { width: 26px; color: #9aa8a2; text-align: center; }
        .product-name { font-weight: bold; color: #1f2d28; }
        .company { color: #4d5e57; }
        .num { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        .mrp { color: #6b7a74; }
        .sell { font-weight: bold; color: #0f5a40; }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 9px;
            font-size: 8px;
            font-weight: bold;
            background: #fbf3d9;
            color: #8a6a00;
            border: 1px solid #ecd98f;
        }
        .muted { color: #b3bfba; }
        .empty { text-align: center; color: #6b7a74; padding: 28px; font-style: italic; }

        /* ---------- Footer (repeats on every page) ---------- */
        .footer {
            position: fixed;
            left: 0; right: 0; bottom: -34px;
            border-top: 1px solid #dbe8e1;
            padding-top: 7px;
            font-size: 7.5px;
            color: #8a9893;
        }
        .footer table { width: 100%; border-collapse: collapse; }
        .footer td { padding: 0; }
        .footer .brand { color: #0f5a40; font-weight: bold; letter-spacing: 0.4px; }
        .page-number:after { content: "Page " counter(page); }
    </style>
</head>
<body>
    <div class="footer">
        <table>
            <tr>
                <td><span class="brand">Med Bangladesh</span> &nbsp;&middot;&nbsp; Prices are in Bangladeshi Taka (BDT) and subject to change.</td>
                <td style="text-align: right;" class="page-number"></td>
            </tr>
        </table>
    </div>

    <div class="accent-bar"></div>
    <div class="accent-bar-thin"></div>

    <table class="letterhead">
        <tr>
            <td style="width: 52px;"><div class="logo-mark">M</div></td>
            <td>
                <div class="brand-name">Med Bangladesh</div>
                <div class="brand-tagline">Trusted Medicine Supply</div>
            </td>
            <td>
                <div class="doc-title">Product Price List</div>
                <div class="doc-sub">Generated {{ $generatedAt->format('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    @php
        $companyCount = $products->map(fn ($p) => $p->companyName())->filter()->unique()->count();
        $filters = $filters ?? [];
    @endphp

    <table class="summary">
        <tr>
            <td class="card">
                <div class="label">Total Products</div>
                <div class="value">{{ number_format($products->count()) }}</div>
            </td>
            <td class="gap"></td>
            <td class="card">
                <div class="label">Companies</div>
                <div class="value">{{ number_format($companyCount) }}</div>
            </td>
            <td class="gap"></td>
            <td class="card">
                <div class="label">Price List Date</div>
                <div class="value small">{{ $generatedAt->format('d F Y') }}</div>
            </td>
        </tr>
    </table>

    @if (! empty($filters))
        <div class="filters">
            Filtered by:
            @foreach ($filters as $key => $value)
                <strong>{{ ucfirst($key) }}</strong>: {{ $value }}@if (! $loop->last) &nbsp;&middot;&nbsp; @endif
            @endforeach
        </div>
    @endif

    <table class="products">
        <thead>
            <tr>
                <th class="col-sl">#</th>
                <th>Product Name</th>
                <th>Company</th>
                <th class="num">MRP Rate</th>
                <th class="center">Discount</th>
                <th class="num">Sell Price</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td class="col-sl">{{ $loop->iteration }}</td>
                    <td class="product-name">{{ $product->name }}</td>
                    <td class="company">{{ $product->companyName() ?: '-' }}</td>
                    <td class="num mrp">{{ $product->mrp_rate === null ? '-' : 'BDT '.number_format((float) $product->mrp_rate, 2) }}</td>
                    <td class="center">
                        @if ((int) $product->discount > 0)
                            <span class="badge">{{ (int) $product->discount }}% OFF</span>
                        @else
                            <span class="muted">&mdash;</span>
                        @endif
                    </td>
                    <td class="num sell">BDT {{ number_format($product->effectiveSellPrice(), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="6">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
