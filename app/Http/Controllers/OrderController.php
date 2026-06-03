<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('orders.index', [
            'orders' => Order::with('items.product.companyInfo')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('orders.create', [
            'order' => null,
            'products' => Product::orderBy('name')->get(),
            'statuses' => Order::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $order = $this->createOrder($this->validatedOrder($request), 'pending');

        return redirect()
            ->route('orders.index')
            ->with('status', "Order {$order->order_number} created successfully.");
    }

    public function edit(Order $order): View
    {
        $order->load('items.product');

        return view('orders.edit', [
            'order' => $order,
            'products' => Product::orderBy('name')->get(),
            'statuses' => Order::STATUSES,
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $this->validatedOrder($request, true);

        DB::transaction(function () use ($order, $validated): void {
            $order->update([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $order->items()->delete();
            $this->syncItems($order, collect($validated['items']));
        });

        return redirect()
            ->route('orders.index')
            ->with('status', "Order {$order->order_number} updated successfully.");
    }

    public function apiStore(Request $request): JsonResponse
    {
        $order = $this->createOrder($this->validatedOrder($request), 'pending');

        return response()->json([
            'message' => 'Order placed successfully.',
            'data' => $order->load('items.product')->apiPayload(),
        ], 201);
    }

    private function validatedOrder(Request $request, bool $includeStatus = false): array
    {
        $request->merge([
            'items' => collect($request->input('items', []))
                ->filter(fn (array $item): bool => filled($item['product_id'] ?? null) || filled($item['quantity'] ?? null))
                ->values()
                ->all(),
        ]);

        return $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['required', 'string', 'max:1000'],
            'status' => [$includeStatus ? 'required' : 'sometimes', Rule::in(Order::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);
    }

    private function createOrder(array $validated, string $status): Order
    {
        return DB::transaction(function () use ($validated, $status): Order {
            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'status' => $validated['status'] ?? $status,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncItems($order, collect($validated['items']));

            return $order->load('items.product');
        });
    }

    private function syncItems(Order $order, Collection $items): void
    {
        $subtotal = 0;
        $discountTotal = 0;
        $total = 0;

        $items
            ->groupBy('product_id')
            ->map(fn (Collection $rows): int => $rows->sum('quantity'))
            ->each(function (int $quantity, int|string $productId) use ($order, &$subtotal, &$discountTotal, &$total): void {
                $product = Product::with('companyInfo')->findOrFail($productId);
                $lineSubtotal = (float) $product->price * $quantity;
                $lineDiscount = $lineSubtotal * ($product->discount / 100);
                $lineTotal = $lineSubtotal - $lineDiscount;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'company' => $product->companyName(),
                    'strength' => $product->strength,
                    'form' => $product->form,
                    'unit_price' => $product->price,
                    'discount_percentage' => $product->discount,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $lineSubtotal;
                $discountTotal += $lineDiscount;
                $total += $lineTotal;
            });

        $order->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'total' => $total,
        ]);
    }

    private function nextOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }
}
