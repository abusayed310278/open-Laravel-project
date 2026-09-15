<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitKycRequest;
use App\Services\KycService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function __construct(private readonly KycService $kyc) {}

    public function index(): View
    {
        $user = Auth::user();

        return view('verification.index', [
            'requirements' => $this->kyc->requirementsFor($user),
            'application' => $this->kyc->currentApplication($user)->load('documents'),
        ]);
    }

    public function store(SubmitKycRequest $request): RedirectResponse
    {
        $this->kyc->submit(
            $request->user(),
            $request->file('documents', []),
            $request->input('document_numbers', []),
        );

        return back()->with('status', 'Your documents were submitted for review.');
    }
}
