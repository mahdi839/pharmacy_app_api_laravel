<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Save (or refresh) the FCM push token of the logged-in customer's phone.
     */
    public function store(Request $request): JsonResponse
    {
        $customer = CustomerAuthController::customerFromRequest($request);

        if (! $customer) {
            return response()->json(['message' => 'Please login first.'], 401);
        }

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'platform' => ['nullable', 'string', 'in:android,ios'],
        ]);

        // A phone keeps the same token when a different customer logs in on it,
        // so the token simply moves to whoever is logged in now.
        DeviceToken::updateOrCreate(
            ['token_hash' => DeviceToken::hashToken($validated['token'])],
            [
                'customer_id' => $customer->id,
                'token' => $validated['token'],
                'platform' => $validated['platform'] ?? 'android',
                'last_seen_at' => now(),
            ],
        );

        return response()->json(['message' => 'Device registered for notifications.']);
    }

    /**
     * Remove a phone's token (called on logout so that phone stops getting this customer's notifications).
     */
    public function destroy(Request $request): JsonResponse
    {
        $customer = CustomerAuthController::customerFromRequest($request);

        if (! $customer) {
            return response()->json(['message' => 'Please login first.'], 401);
        }

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
        ]);

        $customer->deviceTokens()
            ->where('token_hash', DeviceToken::hashToken($validated['token']))
            ->delete();

        return response()->json(['message' => 'Device removed from notifications.']);
    }
}
