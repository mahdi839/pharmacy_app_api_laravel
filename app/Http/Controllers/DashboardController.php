<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stockValue = Product::query()->sum(DB::raw('purchase_price * stock'));

        return view('dashboard.index', [
            'totals' => [
                'companies' => Company::count(),
                'products' => Product::count(),
                'stocks' => Product::sum('stock'),
                'stock_value' => $stockValue,
                'customers' => Customer::count(),
                'orders' => Order::count(),
                'sell' => Order::sum('total'),
            ],
            'salesStats' => [
                'today' => Order::whereDate('created_at', today())->sum('total'),
                'this_month' => Order::whereBetween('created_at', [now()->startOfMonth(), now()->endOfDay()])->sum('total'),
                'completed' => Order::whereIn('status', ['delivered', 'completed'])->sum('total'),
                'pending' => Order::whereIn('status', ['pending', 'processing', 'on delivery'])->sum('total'),
            ],
            'dailySales' => Order::query()
                ->selectRaw('DATE(created_at) as sale_date, SUM(total) as total_sell, COUNT(*) as orders_count')
                ->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
                ->groupBy('sale_date')
                ->orderBy('sale_date')
                ->get(),
            'topCustomers' => Order::query()
                ->selectRaw('COALESCE(customers.name, orders.customer_name) as name, orders.customer_phone as phone, COUNT(orders.id) as orders_count, SUM(orders.total) as total_sell')
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->groupBy(DB::raw('COALESCE(customers.name, orders.customer_name)'), 'orders.customer_phone')
                ->orderByDesc('total_sell')
                ->limit(5)
                ->get(),
            'topProducts' => OrderItem::query()
                ->selectRaw('product_name, SUM(quantity) as quantity_sold, SUM(line_total) as total_sell')
                ->groupBy('product_name')
                ->orderByDesc('total_sell')
                ->limit(5)
                ->get(),
            'topCompanies' => OrderItem::query()
                ->selectRaw('company, SUM(quantity) as quantity_sold, SUM(line_total) as total_sell')
                ->groupBy('company')
                ->orderByDesc('total_sell')
                ->limit(5)
                ->get(),
        ]);
    }
}
