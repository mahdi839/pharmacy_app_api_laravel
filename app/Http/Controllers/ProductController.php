<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.index', [
            'products' => Product::latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'product' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedProduct($request);
        $validated['discount'] = (int) ($validated['discount'] ?? 0);

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validatedProduct($request);
        $validated['discount'] = (int) ($validated['discount'] ?? 0);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('status', 'Product updated successfully.');
    }

    public function apiIndex(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Product::search($request->query('name'), $request->query('company'))
                ->latest()
                ->get()
                ->map->apiPayload(),
        ]);
    }

    private function validatedProduct(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:120'],
            'strength' => ['required', 'string', 'max:50'],
            'form' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'image' => ['nullable', 'url', 'max:500'],
        ]);
    }
}
