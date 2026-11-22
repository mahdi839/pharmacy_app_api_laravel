<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $orders = fn (): Builder => Order::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            });

        // One row per phone number, grouped and paginated in the database.
        $customers = $orders()
            ->selectRaw('customer_phone, COUNT(*) as orders_count, SUM(total) as total_spent, MAX(created_at) as last_order_at, MAX(id) as latest_order_id')
            ->groupBy('customer_phone')
            ->orderByDesc('last_order_at')
            ->orderByDesc('latest_order_id')
            ->paginate(20)
            ->withQueryString();

        // Name and address come from each customer's most recent order.
        $latestOrders = Order::query()
            ->whereIn('id', $customers->getCollection()->pluck('latest_order_id'))
            ->get(['id', 'customer_name', 'customer_address'])
            ->keyBy('id');

        $customers->setCollection($customers->getCollection()->map(function (Order $row) use ($latestOrders): array {
            $latest = $latestOrders->get($row->latest_order_id);

            return [
                'name' => $latest?->customer_name,
                'phone' => $row->customer_phone,
                'address' => $latest?->customer_address,
                'orders_count' => (int) $row->orders_count,
                'total_spent' => (float) $row->total_spent,
                'last_order_at' => Carbon::parse($row->last_order_at),
            ];
        }));

        $totalCustomers = $customers->total();
        $totalOrders = $orders()->count();
        $totalRevenue = (float) $orders()->sum('total');

        return view('customers.index', [
            'customers' => $customers,
            'filters' => ['search' => $search],
            'stats' => [
                'customers' => $totalCustomers,
                'repeat_customers' => DB::query()
                    ->fromSub($orders()->select('customer_phone')->groupBy('customer_phone')->havingRaw('COUNT(*) > 1'), 'repeaters')
                    ->count(),
                'new_this_month' => DB::query()
                    ->fromSub($orders()->select('customer_phone')->groupBy('customer_phone')->havingRaw('MIN(created_at) >= ?', [now()->startOfMonth()]), 'newcomers')
                    ->count(),
                'orders' => $totalOrders,
                'revenue' => $totalRevenue,
                'avg_spend' => $totalCustomers > 0 ? $totalRevenue / $totalCustomers : 0,
            ],
        ]);
    }
}
