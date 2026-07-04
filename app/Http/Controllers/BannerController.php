<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        return view('banners.index', [
            'banners' => Banner::latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('banners.create', [
            'banner' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120', 'dimensions:width=1080,height=360'],
        ]);

        Banner::create([
            'image' => $validated['image']->store('banners', 'public'),
        ]);

        return redirect()->route('banners.index')->with('status', 'Banner created successfully.');
    }

    public function edit(Banner $banner): View
    {
        return view('banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['nullable', 'image', 'max:5120', 'dimensions:width=1080,height=360'],
        ]);

        if ($request->hasFile('image')) {
            $newImage = $validated['image']->store('banners', 'public');
            Storage::disk('public')->delete($banner->image);
            $banner->update([
                'image' => $newImage,
            ]);
        }

        return redirect()->route('banners.index')->with('status', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        Storage::disk('public')->delete($banner->image);
        $banner->delete();

        return redirect()->route('banners.index')->with('status', 'Banner deleted successfully.');
    }

    public function apiIndex(): JsonResponse
    {
        return response()->json([
            'data' => Banner::latest()->get()->map->apiPayload()->values(),
        ]);
    }
}
