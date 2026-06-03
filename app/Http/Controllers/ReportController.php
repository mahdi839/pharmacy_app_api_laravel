<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();

        $orders = Order::with('items')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->get();

        $completedOrders = $orders->whereIn('status', ['delivered', 'completed']);

        return view('reports.sales', [
            'from' => $from,
            'to' => $to,
            'orders' => $orders,
            'stats' => [
                'orders_count' => $orders->count(),
                'completed_count' => $completedOrders->count(),
                'gross_sales' => $orders->sum(fn (Order $order): float => (float) $order->subtotal),
                'discount_total' => $orders->sum(fn (Order $order): float => (float) $order->discount_total),
                'net_sales' => $orders->sum(fn (Order $order): float => (float) $order->total),
                'items_sold' => $orders->sum(fn (Order $order): int => $order->items->sum('quantity')),
            ],
            'statusCounts' => $orders->groupBy('status')->map->count(),
        ]);
    }
}
