<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KycStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectKycRequest;
use App\Models\UserVerification;
use App\Models\VerificationDocument;
use App\Services\KycService;
use Illuminate\Http\JsonResponse;
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
            ->with(['user', 'documents'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByRaw('submitted_at IS NULL, submitted_at DESC, updated_at DESC')
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

    public function approve(Request $request, UserVerification $verification): RedirectResponse|JsonResponse
    {
        $this->kyc->approve($verification, $request->user());

        $message = "{$verification->user->name}'s KYC verification was approved successfully.";

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'status' => $verification->status->value,
                'status_label' => $verification->status->label(),
                'badge_color' => $verification->status->badgeColor(),
            ]);
        }

        return back()->with('status', $message);
    }

    public function reject(RejectKycRequest $request, UserVerification $verification): RedirectResponse
    {
        $this->kyc->reject($verification, $request->user(), $request->string('reason')->value());

        return back()->with('status', "{$verification->user->name}'s KYC verification was rejected.");
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
