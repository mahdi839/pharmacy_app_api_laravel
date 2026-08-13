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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $filters = request()->only(['search', 'from', 'to', 'status']);

        return view('orders.index', [
            'orders' => Order::with('items.product.companyInfo')
                ->when($filters['search'] ?? null, function ($query, string $search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('order_number', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%")
                            ->orWhere('customer_phone', 'like', "%{$search}%");
                    });
                })
                ->when($filters['from'] ?? null, fn ($query, string $from) => $query->whereDate('created_at', '>=', $from))
                ->when($filters['to'] ?? null, fn ($query, string $to) => $query->whereDate('created_at', '<=', $to))
                ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
                ->latest()
                ->get(),
            'statuses' => Order::STATUSES,
            'filters' => $filters,
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
        [$order, $wasCombined] = $this->createOrCombineOrder($this->validatedOrder($request), 'pending');

        $message = $wasCombined
            ? "Items added to existing order {$order->order_number}."
            : "Order {$order->order_number} created successfully.";

        return redirect()
            ->route('orders.index')
            ->with('status', $message);
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
            $this->restoreItemStock($order);

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

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);

        $order->update($validated);

        return back()->with('status', "Order {$order->order_number} status updated.");
    }

    public function invoice(Order $order): View
    {
        return view('orders.invoice', [
            'order' => $order->load('items.product.companyInfo'),
        ]);
    }

    public function apiStore(Request $request): JsonResponse
    {
        $customer = CustomerAuthController::customerFromRequest($request);

        if (! $customer) {
            return response()->json(['message' => 'Please login before placing an order.'], 401);
        }

        $validated = $this->validatedOrder($request);
        $customer->update(['address' => $validated['customer_address']]);
        [$order, $wasCombined] = $this->createOrCombineOrder($validated, 'pending', $customer->id);

        return response()->json([
            'message' => $wasCombined
                ? "Your items were added to order {$order->order_number}."
                : 'Order placed successfully.',
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

    /**
     * Add repeat purchases to a customer's pending order for 12 hours from its creation.
     *
     * @return array{0: Order, 1: bool}
     */
    private function createOrCombineOrder(array $validated, string $status, ?int $customerId = null): array
    {
        return DB::transaction(function () use ($validated, $status, $customerId): array {
            if ($customerId) {
                DB::table('customers')->where('id', $customerId)->lockForUpdate()->first();
            }

            $order = Order::query()
                ->where('status', 'pending')
                ->where('created_at', '>=', now()->subHours(12))
                ->where(function ($query) use ($customerId, $validated): void {
                    if ($customerId) {
                        $query
                            ->where('customer_id', $customerId)
                            ->orWhere(function ($query) use ($validated): void {
                                $query
                                    ->whereNull('customer_id')
                                    ->where('customer_phone', $validated['customer_phone']);
                            });

                        return;
                    }

                    $query->where('customer_phone', $validated['customer_phone']);
                })
                ->latest('created_at')
                ->lockForUpdate()
                ->first();

            if ($order) {
                $order->update([
                    'customer_id' => $customerId ?? $order->customer_id,
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_address' => $validated['customer_address'],
                    'notes' => $this->combineNotes($order->notes, $validated['notes'] ?? null),
                    'combined_order_count' => $order->combined_order_count + 1,
                ]);

                $this->appendItems($order, collect($validated['items']));

                return [$order->load('items.product'), true];
            }

            $order = Order::create([
                'customer_id' => $customerId,
                'order_number' => $this->nextOrderNumber(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'status' => $validated['status'] ?? $status,
                'combined_order_count' => 1,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncItems($order, collect($validated['items']));

            return [$order->load('items.product'), false];
        });
    }

    private function appendItems(Order $order, Collection $items): void
    {
        $items
            ->groupBy('product_id')
            ->map(fn (Collection $rows): int => $rows->sum('quantity'))
            ->sortKeys()
            ->each(function (int $quantity, int|string $productId) use ($order): void {
                $product = Product::query()->lockForUpdate()->findOrFail($productId);
                $product->load('companyInfo');

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Only {$product->stock} unit(s) of {$product->name} are currently available.",
                    ]);
                }

                $product->decrement('stock', $quantity);
                $unitPrice = $product->effectiveMrpRate();
                $lineTotal = $product->discountedPrice() * $quantity;
                $company = $product->companyName();

                $existingItem = $order->items()
                    ->where('product_id', $product->id)
                    ->where('product_name', $product->name)
                    ->where('company', $company)
                    ->where('strength', $product->strength)
                    ->where('form', $product->form)
                    ->where('unit_price', $unitPrice)
                    ->where('discount_percentage', $product->discount)
                    ->lockForUpdate()
                    ->first();

                if ($existingItem) {
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + $quantity,
                        'line_total' => (float) $existingItem->line_total + $lineTotal,
                    ]);

                    return;
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'company' => $company,
                    'strength' => $product->strength,
                    'form' => $product->form,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => $product->discount,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ]);
            });

        $this->recalculateTotals($order);
    }

    private function recalculateTotals(Order $order): void
    {
        $items = $order->items()->get();
        $subtotal = $items->sum(
            fn ($item): float => (float) $item->unit_price * $item->quantity,
        );
        $total = $items->sum(fn ($item): float => (float) $item->line_total);

        $order->update([
            'subtotal' => $subtotal,
            'discount_total' => $subtotal - $total,
            'total' => $total,
        ]);
    }

    private function combineNotes(?string $existingNotes, ?string $newNotes): ?string
    {
        $existingNotes = filled($existingNotes) ? trim($existingNotes) : null;
        $newNotes = filled($newNotes) ? trim($newNotes) : null;

        if (! $newNotes || $newNotes === $existingNotes) {
            return $existingNotes;
        }

        return $existingNotes ? "{$existingNotes}\n{$newNotes}" : $newNotes;
    }

    private function syncItems(Order $order, Collection $items): void
    {
        $subtotal = 0;
        $discountTotal = 0;
        $total = 0;

        $items
            ->groupBy('product_id')
            ->map(fn (Collection $rows): int => $rows->sum('quantity'))
            ->sortKeys()
            ->each(function (int $quantity, int|string $productId) use ($order, &$subtotal, &$discountTotal, &$total): void {
                $product = Product::query()->lockForUpdate()->findOrFail($productId);
                $product->load('companyInfo');

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Only {$product->stock} unit(s) of {$product->name} are currently available.",
                    ]);
                }

                $product->decrement('stock', $quantity);
                $unitPrice = $product->effectiveMrpRate();
                $lineSubtotal = $unitPrice * $quantity;
                $lineTotal = $product->discountedPrice() * $quantity;
                $lineDiscount = $lineSubtotal - $lineTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'company' => $product->companyName(),
                    'strength' => $product->strength,
                    'form' => $product->form,
                    'unit_price' => $unitPrice,
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

    private function restoreItemStock(Order $order): void
    {
        $order->items()
            ->selectRaw('product_id, SUM(quantity) as quantity')
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderBy('product_id')
            ->get()
            ->each(function ($item): void {
                Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first()
                    ?->increment('stock', (int) $item->quantity);
            });
    }

    private function nextOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }
}
