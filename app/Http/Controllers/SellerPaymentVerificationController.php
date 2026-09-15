<?php

namespace App\Http\Controllers;

use App\Enums\PaymentSubmissionStatus;
use App\Http\Requests\Admin\RejectManualPaymentRequest;
use App\Models\ManualPaymentSubmission;
use App\Models\VendorOrder;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SellerPaymentVerificationController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request): View
    {
        $submissions = ManualPaymentSubmission::query()
            ->with(['vendorOrder', 'order'])
            ->whereHas('vendorOrder', fn ($query) => $query->where('vendor_id', Auth::id()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('seller.payment-verifications.index', [
            'submissions' => $submissions,
            'statuses' => PaymentSubmissionStatus::cases(),
            'routePrefix' => Auth::user()->isBusiness() ? 'business.' : 'saler.',
        ]);
    }

    public function verify(Request $request, ManualPaymentSubmission $submission): RedirectResponse
    {
        abort_unless($submission->vendorOrder->vendor_id === Auth::id(), 403);

        $this->payments->verifyManualPayment($submission, $request->user());

        return back()->with('status', 'Payment verified.');
    }

    public function reject(RejectManualPaymentRequest $request, ManualPaymentSubmission $submission): RedirectResponse
    {
        abort_unless($submission->vendorOrder->vendor_id === Auth::id(), 403);

        $this->payments->rejectManualPayment($submission, $request->user(), $request->string('notes')->value());

        return back()->with('status', 'Payment submission rejected.');
    }

    public function proof(ManualPaymentSubmission $submission): RedirectResponse
    {
        abort_unless($submission->vendorOrder->vendor_id === Auth::id(), 403);

        return redirect()->away(Storage::disk('local')->temporaryUrl($submission->proof_file_path, now()->addMinutes(5)));
    }

    public function collectCod(VendorOrder $vendorOrder): RedirectResponse
    {
        abort_unless($vendorOrder->vendor_id === Auth::id(), 403);

        $this->payments->confirmCodCollected($vendorOrder);

        return back()->with('status', 'Cash on delivery marked as collected.');
    }
}
