<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KycStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectKycRequest;
use App\Models\UserVerification;
use App\Models\VerificationDocument;
use App\Services\KycService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function __construct(private readonly KycService $kyc) {}

    public function index(Request $request): View
    {
        $applications = UserVerification::query()
            ->with('user')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->whereNot('status', KycStatus::Draft)
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.verifications.index', [
            'applications' => $applications,
            'statuses' => KycStatus::cases(),
        ]);
    }

    public function show(UserVerification $verification): View
    {
        return view('admin.verifications.show', [
            'application' => $verification->load(['user', 'documents', 'verifiedBy']),
        ]);
    }

    public function approve(Request $request, UserVerification $verification): RedirectResponse
    {
        $this->kyc->approve($verification, $request->user());

        return redirect()->route('admin.verifications.index')->with('status', "{$verification->user->name}'s verification was approved.");
    }

    public function reject(RejectKycRequest $request, UserVerification $verification): RedirectResponse
    {
        $this->kyc->reject($verification, $request->user(), $request->string('reason')->value());

        return redirect()->route('admin.verifications.index')->with('status', "{$verification->user->name}'s verification was rejected.");
    }

    /**
     * Stream a private KYC document via a short-lived signed URL rather than
     * exposing storage paths directly.
     */
    public function viewDocument(VerificationDocument $document): RedirectResponse
    {
        $url = Storage::disk('local')->temporaryUrl($document->file_path, now()->addMinutes(5));

        return redirect()->away($url);
    }
}
