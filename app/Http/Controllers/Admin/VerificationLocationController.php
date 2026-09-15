<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVerificationLocationRequest;
use App\Models\VerificationLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerificationLocationController extends Controller
{
    public function index(): View
    {
        return view('admin.verification-locations.index', [
            'locations' => VerificationLocation::query()->withCount('verifiers')->orderBy('name')->get(),
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

    public function destroy(VerificationLocation $verificationLocation): RedirectResponse
    {
        $verificationLocation->delete();

        return back()->with('status', 'Location removed.');
    }
}
