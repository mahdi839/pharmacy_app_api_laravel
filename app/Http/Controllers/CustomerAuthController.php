<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'gmail' => ['nullable', 'email', 'max:120', 'unique:customers,gmail'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
        ]);

        $customer = Customer::create($validated);
        $token = $customer->refreshApiToken();

        return response()->json([
            'message' => 'Registration successful.',
            'token' => $token,
            'customer' => $customer->apiPayload(),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
        ]);

        $login = $validated['login'];
        $customer = Customer::query()
            ->where('gmail', $login)
            ->orWhere('phone', $login)
            ->first();

        if (! $customer || ! Hash::check($validated['password'], $customer->password)) {
            return response()->json([
                'message' => 'The login details are not correct.',
            ], 422);
        }

        return response()->json([
            'message' => 'Login successful.',
            'token' => $customer->refreshApiToken(),
            'customer' => $customer->apiPayload(),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'customer' => $this->customerFromRequest($request)?->apiPayload(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $customer = $this->customerFromRequest($request);
        $customer?->forceFill(['api_token' => null])->save();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function orders(Request $request): JsonResponse
    {
        $customer = $this->customerFromRequest($request);

        if (! $customer) {
            return response()->json(['message' => 'Please login first.'], 401);
        }

        return response()->json([
            'data' => $customer->orders()
                ->with('items.product')
                ->latest()
                ->get()
                ->map->apiPayload()
                ->values(),
        ]);
    }

    public static function customerFromRequest(Request $request): ?Customer
    {
        $token = $request->bearerToken();

        if (! $token) {
            return null;
        }

        return Customer::where('api_token', $token)->first();
    }
}
