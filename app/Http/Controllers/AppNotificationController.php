<?php

namespace App\Http\Controllers;

use App\Jobs\SendAppNotification;
use App\Models\AppNotification;
use App\Models\DeviceToken;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AppNotificationController extends Controller
{
    public function index(): View
    {
        return view('notifications.index', [
            'notifications' => AppNotification::with('product')->latest()->paginate(20),
            'deviceCount' => DeviceToken::whereNotNull('customer_id')->count(),
        ]);
    }

    public function create(): View
    {
        return view('notifications.create', [
            'products' => Product::orderBy('name')->get(['id', 'name', 'strength', 'form']),
            'deviceCount' => DeviceToken::whereNotNull('customer_id')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $notification = AppNotification::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'product_id' => $validated['product_id'] ?? null,
            'image' => $request->hasFile('image') ? $request->file('image')->store('notifications', 'public') : null,
            'status' => AppNotification::STATUS_PENDING,
        ]);

        // Sends right after the admin's page has loaded, so the form submit stays fast.
        SendAppNotification::dispatchAfterResponse($notification);

        return redirect()
            ->route('notifications.index')
            ->with('status', 'Notification saved and is being sent to customers. Refresh in a moment to see the delivery count.');
    }

    public function destroy(AppNotification $notification): RedirectResponse
    {
        if ($notification->image) {
            Storage::disk('public')->delete($notification->image);
        }

        $notification->delete();

        return redirect()
            ->route('notifications.index')
            ->with('status', 'Notification deleted. It no longer appears in the mobile app.');
    }
}
