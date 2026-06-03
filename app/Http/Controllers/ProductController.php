<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.index', [
            'products' => Product::with('companyInfo')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'product' => null,
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedProduct($request);
        $validated = $this->prepareProductData($request, $validated);

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validatedProduct($request);
        $validated = $this->prepareProductData($request, $validated, $product);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('status', 'Product updated successfully.');
    }

    public function apiIndex(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Product::with('companyInfo')
                ->search($request->query('name'), $request->query('company'))
                ->latest()
                ->get()
                ->map->apiPayload(),
        ]);
    }

    private function validatedProduct(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'strength' => ['required', 'string', 'max:50'],
            'form' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function prepareProductData(Request $request, array $validated, ?Product $product = null): array
    {
        $company = Company::findOrFail($validated['company_id']);

        $validated['company'] = $company->name;
        $validated['discount'] = (int) ($validated['discount'] ?? 0);

        if ($request->hasFile('image')) {
            if ($product?->image && ! str_starts_with($product->image, 'http')) {
                Storage::disk('public')->delete($product->image);
            }

            $validated['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($validated['image']);
        }

        return $validated;
    }
}
