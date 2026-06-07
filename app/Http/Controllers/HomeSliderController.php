<?php

namespace App\Http\Controllers;

use App\Models\HomeSlider;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeSliderController extends Controller
{
    public function index(): View
    {
        return view('home-sliders.index', [
            'sliders' => HomeSlider::withCount('products')->orderBy('sort_order')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('home-sliders.create', [
            'slider' => null,
            'products' => Product::orderBy('name')->get(),
            'selectedProducts' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $slider = HomeSlider::create($this->validatedSlider($request));
        $this->syncProducts($slider, $request->input('product_ids', []));

        return redirect()->route('home-sliders.index')->with('status', 'Home slider created successfully.');
    }

    public function edit(HomeSlider $homeSlider): View
    {
        return view('home-sliders.edit', [
            'slider' => $homeSlider->load('products'),
            'products' => Product::orderBy('name')->get(),
            'selectedProducts' => $homeSlider->products->pluck('id')->map(fn ($id): string => (string) $id)->all(),
        ]);
    }

    public function update(Request $request, HomeSlider $homeSlider): RedirectResponse
    {
        $homeSlider->update($this->validatedSlider($request));
        $this->syncProducts($homeSlider, $request->input('product_ids', []));

        return redirect()->route('home-sliders.index')->with('status', 'Home slider updated successfully.');
    }

    public function destroy(HomeSlider $homeSlider): RedirectResponse
    {
        $homeSlider->delete();

        return redirect()->route('home-sliders.index')->with('status', 'Home slider deleted successfully.');
    }

    public function apiIndex(): JsonResponse
    {
        return response()->json([
            'data' => HomeSlider::with('products.companyInfo')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->latest()
                ->get()
                ->map->apiPayload()
                ->values(),
        ]);
    }

    private function validatedSlider(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        return [
            'name' => $validated['name'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }

    private function syncProducts(HomeSlider $slider, array $productIds): void
    {
        $sync = collect($productIds)
            ->filter()
            ->unique()
            ->values()
            ->mapWithKeys(fn ($productId, int $index): array => [
                $productId => ['sort_order' => $index],
            ])
            ->all();

        $slider->products()->sync($sync);
    }
}
