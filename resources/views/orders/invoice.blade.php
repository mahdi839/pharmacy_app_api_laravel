<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        :root { --paper-width: 80mm; }
        @page { size: 80mm 200mm; margin: 0; }
        body {
            background: #f3f4f6;
            color: #000;
            font-family: "Courier New", monospace;
            font-size: 11.5px;
            font-weight: 500;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            -webkit-font-smoothing: none;
            text-rendering: geometricPrecision;
        }
        .receipt {
            background: #fff;
            margin: 0 auto;
            padding: 4mm;
            width: var(--paper-width);
        }
        .center { text-align: center; }
        .store-name { font-family: Arial, sans-serif; font-size: 18px; font-weight: 900; }
        .muted { font-size: 10px; font-weight: 700; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .info-row, .total-row, .item-summary {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }
        .info-row, .total-row { font-weight: 700; }
        .info-row span:last-child, .total-row span:last-child, .item-summary span:last-child {
            text-align: right;
        }
        .customer {
            font-weight: 700;
            overflow-wrap: anywhere;
        }
        .item { margin: 8px 0; }
        .item-name {
            font-size: 12.5px;
            font-weight: 900;
            letter-spacing: 0.1px;
            text-transform: uppercase;
        }
        .item-meta {
            font-size: 10px;
            font-weight: 700;
            line-height: 1.35;
            margin-top: 2px;
        }
        .item-summary {
            font-size: 11px;
            font-weight: 800;
            margin-top: 4px;
        }
        .grand { font-size: 13px; font-weight: 900; padding-top: 3px; }
        .print-actions { margin: 0 auto 12px; text-align: center; width: 80mm; }
        .print {
            background: #1a7f5a;
            border: 0;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-weight: 800;
            padding: 10px 14px;
        }
        @media print {
            body {
                background: #fff;
                color: #000;
                font-size: 12px;
                font-weight: 600;
                line-height: 1.45;
                padding: 0;
                width: var(--paper-width);
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .receipt {
                margin: 0;
                padding: 3mm;
                width: var(--paper-width);
            }
            .print-actions { display: none; }
            .store-name,
            .item-name,
            .item-summary strong,
            .grand,
            strong {
                font-weight: 900;
            }
            .item-meta,
            .muted,
            .info-row,
            .total-row {
                color: #000;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button class="print" onclick="printReceipt(58)">Print 58mm Receipt</button>
        <button class="print" onclick="printReceipt(80)">Print 80mm Receipt</button>
    </div>

    <main class="receipt" id="receipt">
        <header class="center">
            <div class="store-name">MED BANGLADESH</div>
            <div>Medicine Order Receipt</div>
        </header>

        <div class="divider"></div>

        <div class="info-row"><span>Invoice</span><strong>{{ $order->order_number }}</strong></div>
        <div class="info-row"><span>Date</span><span>{{ $order->created_at->format('d-m-Y h:i A') }}</span></div>
        <div class="info-row"><span>Status</span><span>{{ ucfirst($order->status) }}</span></div>
        @if ($order->combined_order_count > 1)
            <div class="info-row"><span>Orders combined</span><span>{{ $order->combined_order_count }}</span></div>
            <div class="info-row"><span>Last updated</span><span>{{ $order->updated_at->format('d-m-Y h:i A') }}</span></div>
        @endif

        <div class="divider"></div>

        <div class="customer">
            <strong>Customer</strong><br>
            {{ $order->customer_name }}<br>
            {{ $order->customer_phone }}<br>
            {{ $order->customer_address }}
        </div>

        <div class="divider"></div>

        @foreach ($order->items as $item)
            <div class="item">
                <div class="item-name">{{ $item->product_name }}</div>
                <div class="item-meta">{{ $item->company }} | {{ $item->strength }} {{ $item->form }} | {{ $item->discount_percentage }}% OFF</div>
                <div class="item-summary">
                    <span>{{ $item->quantity }} x BDT {{ number_format((float) $item->unit_price, 2) }}</span>
                    <strong>BDT {{ number_format((float) $item->line_total, 2) }}</strong>
                </div>
            </div>
        @endforeach

        <div class="divider"></div>

        <div class="total-row"><span>Subtotal</span><span>BDT {{ number_format((float) $order->subtotal, 2) }}</span></div>
        <div class="total-row"><span>Discount</span><span>- BDT {{ number_format((float) $order->discount_total, 2) }}</span></div>
        <div class="total-row grand"><span>GRAND TOTAL</span><span>BDT {{ number_format((float) $order->total, 2) }}</span></div>

        @if ($order->notes)
            <div class="divider"></div>
            <div><strong>Note:</strong> {{ $order->notes }}</div>
        @endif

        <div class="divider"></div>
        <footer class="center muted">
            Thank you for your order.
        </footer>
    </main>
    <script>
        let selectedPaperWidth = 80;

        const configureThermalPage = (paperWidth = selectedPaperWidth) => {
            selectedPaperWidth = paperWidth;
            document.documentElement.style.setProperty('--paper-width', `${paperWidth}mm`);
            const receipt = document.getElementById('receipt');
            const heightInMillimeters = Math.ceil(receipt.scrollHeight * 25.4 / 96);
            let pageStyle = document.getElementById('thermal-page-size');

            if (!pageStyle) {
                pageStyle = document.createElement('style');
                pageStyle.id = 'thermal-page-size';
                document.head.appendChild(pageStyle);
            }

            pageStyle.textContent = `@page { size: ${paperWidth}mm ${heightInMillimeters}mm; margin: 0; }`;
        };

        const printReceipt = (paperWidth) => {
            configureThermalPage(paperWidth);
            window.print();
        };

        window.addEventListener('beforeprint', () => configureThermalPage());
    </script>
</body>
</html>
