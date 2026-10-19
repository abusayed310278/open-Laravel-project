<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KycDocumentType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVerificationRequirementRequest;
use App\Models\VerificationRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationRequirementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search'));

        $requirementsQuery = VerificationRequirement::query()
            ->when($request->filled('search'), function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('document_type', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order');

        return view('admin.verification-requirements.index', [
            'requirementsByRole' => $requirementsQuery
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
