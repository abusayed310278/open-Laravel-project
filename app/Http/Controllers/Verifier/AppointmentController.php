<?php

namespace App\Http\Controllers\Verifier;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Verifier\SubmitInspectionRequest;
use App\Models\ProductVerification;
use App\Services\ProductVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly ProductVerificationService $verifications) {}

    public function index(): View
    {
        $verifier = Auth::user();
        $locationId = $verifier->verifierProfile?->assigned_location_id;

        $query = ProductVerification::query()
            ->with(['product', 'seller', 'location'])
            ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting])
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->orderBy('scheduled_at');

        return view('verifier.appointments.index', [
            'appointments' => $query->paginate(15),
        ]);
    }

    public function inspect(ProductVerification $verification): View
    {
        return view('verifier.appointments.inspect', [
            'verification' => $verification->load(['product.images', 'product.attributeValues.attribute', 'seller']),
            'checklist' => $this->verifications->checklistFor($verification->product),
        ]);
    }

    public function submit(SubmitInspectionRequest $request, ProductVerification $verification): RedirectResponse
    {
        $verifier = $request->user();

        if ($verification->status !== VerificationStatus::Inspecting) {
            $this->verifications->startInspection($verification, $verifier);
        }

        if ($request->string('decision')->value() === 'pass') {
            $this->verifications->pass(
                $verification,
                $verifier,
                $request->input('results'),
                $request->string('grade')->value(),
                $request->integer('battery_health') ?: null,
                $request->string('notes')->value() ?: null,
            );
        } else {
            $this->verifications->fail(
                $verification,
                $verifier,
                $request->input('results'),
                $request->string('reason')->value(),
            );
        }

        return redirect()->route('verifier.appointments.index')->with('status', 'Inspection submitted.');
    }
}
