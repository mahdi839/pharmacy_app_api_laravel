<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1f2933; margin: 0; padding: 32px; }
        .top { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 28px; }
        h1 { margin: 0; color: #12372f; }
        .muted { color: #667085; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border-bottom: 1px solid #e3e8ef; padding: 12px; text-align: left; }
        th { background: #f8fafc; font-size: 12px; text-transform: uppercase; }
        .totals { margin-left: auto; margin-top: 20px; width: 320px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #edf1f5; }
        .grand { font-size: 18px; font-weight: 800; color: #12372f; }
        .print { background: #1a7f5a; color: #fff; border: 0; border-radius: 6px; padding: 10px 14px; font-weight: 800; cursor: pointer; }
        @media print { .print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="top">
        <div>
            <h1>Med Bangladesh</h1>
            <div class="muted">Pharmacy order invoice</div>
        </div>
        <div>
            <button class="print" onclick="window.print()">Print Invoice</button>
        </div>
    </div>

    <div class="top">
        <div>
            <strong>Bill To</strong>
            <div>{{ $order->customer_name }}</div>
            <div>{{ $order->customer_phone }}</div>
            <div>{{ $order->customer_address }}</div>
        </div>
        <div>
            <strong>{{ $order->order_number }}</strong>
            <div class="muted">{{ $order->created_at->format('M d, Y h:i A') }}</div>
            <div class="muted">Status: {{ ucfirst($order->status) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Medicine</th>
                <th>Company</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }} <div class="muted">{{ $item->strength }} | {{ $item->form }}</div></td>
                    <td>{{ $item->company }}</td>
                    <td>BDT {{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ $item->discount_percentage }}%</td>
                    <td>{{ $item->quantity }}</td>
                    <td>BDT {{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="row"><span>Subtotal</span><span>BDT {{ number_format((float) $order->subtotal, 2) }}</span></div>
        <div class="row"><span>Discount</span><span>BDT {{ number_format((float) $order->discount_total, 2) }}</span></div>
        <div class="row grand"><span>Grand Total</span><span>BDT {{ number_format((float) $order->total, 2) }}</span></div>
    </div>
</body>
</html>
