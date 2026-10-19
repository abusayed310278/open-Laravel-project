<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVerificationLocationRequest;
use App\Models\VerificationLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationLocationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search'));

        $locations = VerificationLocation::query()
            ->withCount('verifiers')
            ->when($request->filled('search'), function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.verification-locations.index', [
            'locations' => $locations,
        ]);
    }

    public function store(StoreVerificationLocationRequest $request): RedirectResponse
    {
        VerificationLocation::query()->create($request->validated());

        return back()->with('status', 'Location added.');
    }

    public function update(StoreVerificationLocationRequest $request, VerificationLocation $verificationLocation): RedirectResponse
    {
        $verificationLocation->update($request->validated());

        return back()->with('status', 'Location updated.');
    }

    public function toggleActive(VerificationLocation $verificationLocation): RedirectResponse
    {
        $verificationLocation->update(['is_active' => ! $verificationLocation->is_active]);

        $status = $verificationLocation->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Location {$status}.");
    }

    public function destroy(VerificationLocation $verificationLocation): RedirectResponse
    {
        $verificationLocation->delete();

        return back()->with('status', 'Location removed.');
    }
}
