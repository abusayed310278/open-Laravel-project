<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KycDocumentType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVerificationRequirementRequest;
use App\Models\VerificationRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerificationRequirementController extends Controller
{
    public function index(): View
    {
        return view('admin.verification-requirements.index', [
            'requirementsByRole' => VerificationRequirement::query()
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn (VerificationRequirement $requirement) => $requirement->role->value),
            'roles' => [UserRole::Business, UserRole::Saler],
            'documentTypes' => KycDocumentType::cases(),
        ]);
    }

    public function store(StoreVerificationRequirementRequest $request): RedirectResponse
    {
        VerificationRequirement::query()->updateOrCreate(
            ['role' => $request->string('role')->value(), 'document_type' => $request->string('document_type')->value()],
            ['is_required' => $request->boolean('is_required'), 'is_active' => true],
        );

        return back()->with('status', 'Requirement saved.');
    }

    public function toggle(VerificationRequirement $verificationRequirement): RedirectResponse
    {
        $verificationRequirement->update(['is_active' => ! $verificationRequirement->is_active]);

        return back()->with('status', 'Requirement updated.');
    }

    public function destroy(VerificationRequirement $verificationRequirement): RedirectResponse
    {
        $verificationRequirement->delete();

        return back()->with('status', 'Requirement removed.');
    }
}
