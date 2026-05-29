<?php

namespace App\Http\Controllers;

use App\Services\ProductStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductStore $products)
    {
    }

    public function dashboard(): View
    {
        return view('dashboard', [
            'products' => $this->products->all(),
        ]);
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:120'],
            'strength' => ['required', 'string', 'max:50'],
            'form' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'image' => ['nullable', 'url', 'max:500'],
        ]);

        $this->products->create($validated);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Product created successfully.');
    }

    public function apiIndex(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->products->search(
                $request->query('name'),
                $request->query('company'),
            ),
        ]);
    }
}
