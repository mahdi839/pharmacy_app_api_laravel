<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Med Bangladesh - Product List</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #173d31; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #14543e; }
        .meta { color: #657870; font-size: 10px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th { background: #1a7f5a; color: #ffffff; text-align: left; padding: 7px 8px; font-size: 11px; }
        td { padding: 6px 8px; border-bottom: 1px solid #dce7e2; }
        tbody tr:nth-child(even) td { background: #f4f7f6; }
        .num { text-align: right; white-space: nowrap; }
        .empty { text-align: center; color: #657870; padding: 20px; }
    </style>
</head>
<body>
    <h1>Med Bangladesh - Product List</h1>
    <div class="meta">
        {{ number_format($products->count()) }} products &middot; Generated {{ $generatedAt->format('d M Y, h:i A') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th class="num">MRP Rate</th>
                <th class="num">Sell Price</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td class="num">{{ $product->mrp_rate === null ? '-' : 'BDT '.number_format((float) $product->mrp_rate, 2) }}</td>
                    <td class="num">BDT {{ number_format($product->effectiveSellPrice(), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="3">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
