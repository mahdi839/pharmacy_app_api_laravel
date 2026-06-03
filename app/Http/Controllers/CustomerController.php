<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Order::latest()
            ->get()
            ->groupBy('customer_phone')
            ->map(function ($orders, string $phone): array {
                $latestOrder = $orders->first();

                return [
                    'name' => $latestOrder->customer_name,
                    'phone' => $phone,
                    'address' => $latestOrder->customer_address,
                    'orders_count' => $orders->count(),
                    'total_spent' => $orders->sum(fn (Order $order): float => (float) $order->total),
                    'last_order_at' => $latestOrder->created_at,
                ];
            })
            ->sortByDesc('last_order_at')
            ->values();

        return view('customers.index', [
            'customers' => $customers,
        ]);
    }
}
