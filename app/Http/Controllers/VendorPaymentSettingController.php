<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVendorPaymentSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VendorPaymentSettingController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();

        return view('seller.payment-settings.edit', [
            'settings' => $user->paymentSettings ?? $user->paymentSettings()->make(['cod_enabled' => true]),
            'accounts' => $user->paymentAccounts,
        ]);
    }

    public function update(UpdateVendorPaymentSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $action = $request->input('action');
        $setting = Auth::user()->paymentSettings()->firstOrCreate(['user_id' => Auth::id()]);

        if ($action === 'disconnect_stripe') {
            $setting->update([
                'stripe_enabled' => false,
                'stripe_publishable_key' => null,
                'stripe_secret_key' => null,
            ]);

            return back()->with('status', 'Stripe disconnected.');
        }

        if ($action === 'disconnect_paypal') {
            $setting->update([
                'paypal_enabled' => false,
                'paypal_client_id' => null,
                'paypal_client_secret' => null,
            ]);

            return back()->with('status', 'PayPal disconnected.');
        }

        if ($action === 'connect_stripe') {
            $setting->update([
                'stripe_enabled' => true,
                'stripe_publishable_key' => $validated['stripe_publishable_key'] ?? $setting->stripe_publishable_key,
                'stripe_secret_key' => $validated['stripe_secret_key'] ?? $setting->stripe_secret_key,
            ]);

            return back()->with('status', 'Stripe merchant credentials connected!');
        }

        if ($action === 'connect_paypal') {
            $setting->update([
                'paypal_enabled' => true,
                'paypal_client_id' => $validated['paypal_client_id'] ?? $setting->paypal_client_id,
                'paypal_client_secret' => $validated['paypal_client_secret'] ?? $setting->paypal_client_secret,
            ]);

            return back()->with('status', 'PayPal merchant credentials connected!');
        }

        $validated['stripe_enabled'] = $request->boolean('stripe_enabled');
        $validated['paypal_enabled'] = $request->boolean('paypal_enabled');
        $validated['cod_enabled'] = $request->boolean('cod_enabled');
        $validated['manual_bank_enabled'] = $request->boolean('manual_bank_enabled');

        $setting->update($validated);

        return back()->with('status', 'Payment settings updated.');
    }
}
