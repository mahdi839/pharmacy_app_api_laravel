<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Services\ProductPdfBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['name', 'company', 'from', 'to']);
        $filtered = fn () => Product::query()
            ->search($filters['name'] ?? null, $filters['company'] ?? null, $filters['from'] ?? null, $filters['to'] ?? null);

        $products = $filtered()
            ->with('companyInfo')
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'filters' => $filters,
            'totals' => [
                'products' => $products->total(),
                'companies' => $filtered()->distinct()->count('company_id'),
                'stock' => (int) $filtered()->sum('stock'),
                'stock_value' => (float) $filtered()->sum(DB::raw('purchase_price * stock')),
            ],
        ]);
    }

    public function pdf(Request $request, ProductPdfBuilder $builder): BinaryFileResponse
    {
        // Fallback rendering in this process (when worker processes are unavailable)
        // keeps the whole document in memory and can take a while.
        ini_set('memory_limit', '1024M');
        set_time_limit(180);

        $token = $request->query('progress');
        $token = is_string($token) && preg_match('/^[A-Za-z0-9-]{16,64}$/', $token) ? $token : null;

        $path = $builder->pdfPath($request->only(['name', 'company', 'from', 'to']), $token);

        return response()->download($path, 'med-bangladesh-products-'.now()->format('Y-m-d').'.pdf', [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function pdfProgress(string $token, ProductPdfBuilder $builder): JsonResponse
    {
        return response()->json($builder->progress($token));
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

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            return back()->with('error', 'This product is already used in orders, so it cannot be deleted.');
        }

        if ($product->image && ! str_starts_with($product->image, 'http')) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Product deleted successfully.');
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

    public function apiShow(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product->load('companyInfo')->apiPayload(),
        ]);
    }

    private function validatedProduct(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'strength' => ['required', 'string', 'max:50'],
            'form' => ['required', 'string', 'max:50'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'mrp_rate' => ['nullable', 'numeric', 'min:0'],
            'sell_price' => ['nullable', 'required_without:mrp_rate', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function prepareProductData(Request $request, array $validated, ?Product $product = null): array
    {
        $company = Company::findOrFail($validated['company_id']);
        $mrpRate = filled($validated['mrp_rate'] ?? null) ? (float) $validated['mrp_rate'] : null;
        $discount = (int) ($validated['discount'] ?? 0);

        $validated['company'] = $company->name;
        $validated['discount'] = $discount;

        if ($mrpRate !== null) {
            $validated['sell_price'] = round($mrpRate - ($mrpRate * ($discount / 100)), 2);
            $validated['price'] = $mrpRate;
        } else {
            $validated['sell_price'] = (float) $validated['sell_price'];
            $validated['price'] = $validated['sell_price'];
        }

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
