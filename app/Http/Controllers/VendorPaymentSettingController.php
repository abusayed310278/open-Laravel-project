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
        Auth::user()->paymentSettings()->updateOrCreate([], $request->validated());

        return back()->with('status', 'Payment settings updated.');
    }
}
