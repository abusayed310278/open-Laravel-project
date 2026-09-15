<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductVerificationRequest;
use App\Models\VerificationLocation;
use App\Services\ProductVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductVerificationController extends Controller
{
    public function __construct(private readonly ProductVerificationService $verifications) {}

    public function index(): View
    {
        $user = Auth::user();

        return view('seller.appointments.index', [
            'products' => $user->products()
                ->with('latestVerificationRequest.location')
                ->latest()
                ->paginate(15),
            'eligibleProducts' => $user->products()
                ->where('verification_status', 'not_requested')
                ->orderByDesc('created_at')
                ->get(),
            'locations' => VerificationLocation::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductVerificationRequest $request): RedirectResponse
    {
        $product = Auth::user()->products()->findOrFail($request->integer('product_id'));

        $this->verifications->request(
            $product,
            Auth::user(),
            $request->integer('location_id'),
            $request->string('appointment_date')->value(),
            $request->string('appointment_time')->value(),
        );

        return back()->with('status', 'Verification appointment booked — check your email for details.');
    }
}
