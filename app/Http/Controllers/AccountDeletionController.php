<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountDeletionController extends Controller
{
    public function show(): View
    {
        return view('account-deletion');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'max:100'],
            'confirm_deletion' => ['accepted'],
        ]);

        $customer = Customer::query()
            ->where('gmail', $validated['login'])
            ->orWhere('phone', $validated['login'])
            ->first();

        if (! $customer || ! Hash::check($validated['password'], $customer->password)) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' => 'The account details could not be verified.',
                ]);
        }

        DB::transaction(function () use ($customer): void {
            $customer->orders()->update([
                'customer_name' => 'Deleted customer',
                'customer_phone' => 'Removed',
                'customer_address' => 'Removed following account deletion',
                'notes' => null,
            ]);

            $customer->delete();
        });

        return redirect()
            ->route('account-deletion')
            ->with('deletion_status', 'Your Med Bangladesh account and associated personal data have been deleted.');
    }
}
