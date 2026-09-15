<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerAddressController extends Controller
{
    public function index(): View
    {
        return view('account.addresses.index', [
            'addresses' => Auth::user()->addresses()->orderByDesc('is_default')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $address = Auth::user()->addresses()->create($this->validated($request));

        if ($request->boolean('is_default')) {
            $this->makeDefault($address);
        }

        return back()->with('status', 'Address added.');
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $address->update($this->validated($request));

        if ($request->boolean('is_default')) {
            $this->makeDefault($address);
        }

        return back()->with('status', 'Address updated.');
    }

    public function destroy(Address $address): RedirectResponse
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $address->delete();

        return back()->with('status', 'Address removed.');
    }

    public function setDefault(Address $address): RedirectResponse
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $this->makeDefault($address);

        return back()->with('status', 'Default address updated.');
    }

    private function makeDefault(Address $address): void
    {
        Address::where('user_id', $address->user_id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);
    }
}
