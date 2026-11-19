<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notification inbox for the mobile app.
 */
class CustomerNotificationController extends Controller
{
    private const LIMIT = 50;

    public function index(Request $request): JsonResponse
    {
        $customer = CustomerAuthController::customerFromRequest($request);

        if (! $customer) {
            return response()->json(['message' => 'Please login first.'], 401);
        }

        $unreadAfter = $customer->notificationsUnreadAfter();

        return response()->json([
            'data' => AppNotification::with('product')
                ->latest()
                ->latest('id')
                ->limit(self::LIMIT)
                ->get()
                ->map(fn (AppNotification $notification): array => $notification->apiPayload($unreadAfter))
                ->values(),
            'unread_count' => AppNotification::where('created_at', '>', $unreadAfter)->count(),
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $customer = CustomerAuthController::customerFromRequest($request);

        if (! $customer) {
            return response()->json(['message' => 'Please login first.'], 401);
        }

        $customer->forceFill(['notifications_seen_at' => now()])->save();

        return response()->json(['message' => 'Notifications marked as read.', 'unread_count' => 0]);
    }
}
