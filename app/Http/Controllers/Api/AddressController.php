<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->get();

        return response()->json([
            'addresses' => $addresses->map(fn (Address $a) => [
                'id' => (string) $a->id,
                'label' => $a->label ?? 'Home',
                'recipient' => $a->name,
                'phone' => $a->phone,
                'line1' => $a->line1,
                'city' => $a->city,
                'state' => $a->state ?? '',
                'country' => $a->country ?? '',
                'postal_code' => $a->postal_code ?? '',
                'is_default' => (bool) $a->is_default,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'recipient' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $isDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'label' => $validated['label'] ?? 'Home',
            'name' => $validated['recipient'],
            'phone' => $validated['phone'],
            'line1' => $validated['line1'],
            'city' => $validated['city'],
            'state' => $validated['state'] ?? 'Doha',
            'country' => $validated['country'] ?? 'Qatar',
            'postal_code' => $validated['postal_code'] ?? '00000',
            'is_default' => $isDefault,
        ]);

        return response()->json([
            'message' => 'Address added successfully',
            'address' => [
                'id' => (string) $address->id,
                'label' => $address->label,
                'recipient' => $address->name,
                'phone' => $address->phone,
                'line1' => $address->line1,
                'city' => $address->city,
                'state' => $address->state,
                'country' => $address->country,
                'postal_code' => $address->postal_code,
                'is_default' => (bool) $address->is_default,
            ],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'recipient' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            $user->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update([
            'label' => $validated['label'] ?? $address->label ?? 'Home',
            'name' => $validated['recipient'],
            'phone' => $validated['phone'],
            'line1' => $validated['line1'],
            'city' => $validated['city'],
            'state' => $validated['state'] ?? $address->state ?? 'Doha',
            'country' => $validated['country'] ?? $address->country ?? 'Qatar',
            'postal_code' => $validated['postal_code'] ?? $address->postal_code ?? '00000',
            'is_default' => $isDefault ? true : $address->is_default,
        ]);

        return response()->json([
            'message' => 'Address updated successfully',
            'address' => [
                'id' => (string) $address->id,
                'label' => $address->label,
                'recipient' => $address->name,
                'phone' => $address->phone,
                'line1' => $address->line1,
                'city' => $address->city,
                'state' => $address->state,
                'country' => $address->country,
                'postal_code' => $address->postal_code,
                'is_default' => (bool) $address->is_default,
            ],
        ]);
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $user->addresses()->update(['is_default' => false]);
        $user->addresses()->where('id', $id)->update(['is_default' => true]);

        return response()->json(['message' => 'Default address updated']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $address = $user->addresses()->where('id', $id)->first();
        if ($address) {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault && $user->addresses()->exists()) {
                $user->addresses()->first()->update(['is_default' => true]);
            }
        }

        return response()->json(['message' => 'Address deleted successfully']);
    }
}