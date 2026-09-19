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

        $products = $user->products()
            ->with(['latestVerificationRequest.location', 'latestVerificationRequest.verifier', 'category'])
            ->latest()
            ->paginate(15);

        $eligibleProducts = $user->products()
            ->where('verification_status', 'not_requested')
            ->orderByDesc('created_at')
            ->get();

        $locations = VerificationLocation::query()->where('is_active', true)->orderBy('name')->get();
        $timeSlots = ProductVerificationService::get30MinTimeSlots();

        $verifications = \App\Models\ProductVerification::query()
            ->where('seller_id', $user->id)
            ->whereNotNull('scheduled_at')
            ->with(['product.category', 'location', 'verifier'])
            ->get();

        $calendarAppointments = $verifications->map(function ($v) {
            return [
                'id' => $v->id,
                'product_id' => $v->product_id,
                'title' => $v->product->title ?? 'Product',
                'image' => $v->product->primaryImageUrl(),
                'sku' => $v->product->sku ?? 'N/A',
                'price' => '$' . number_format($v->product->price ?? 0, 2),
                'category' => $v->product->category?->name ?? 'Electronics',
                'date' => $v->scheduled_at ? $v->scheduled_at->format('Y-m-d') : '',
                'time' => $v->scheduled_at ? $v->scheduled_at->format('g:i A') : '',
                'formatted_schedule' => $v->scheduled_at ? $v->scheduled_at->format('M j, Y \a\t g:i A') . ' (30 min slot)' : '—',
                'status' => $v->status->value,
                'status_label' => $v->status->label(),
                'status_color' => $v->status->badgeColor(),
                'location_name' => $v->location?->name ?? 'Main Hub',
                'location_address' => implode(', ', array_filter([$v->location?->address, $v->location?->city, $v->location?->country])),
                'verifier_name' => $v->verifier?->name ?? 'Assigned Hub Inspector',
                'verifier_email' => $v->verifier?->email ?? 'verifier@openbox.com',
                'notes' => $v->notes ?? 'Standard 30-min physical device scrutiny appointment.',
            ];
        });

        return view('seller.appointments.index', [
            'products' => $products,
            'eligibleProducts' => $eligibleProducts,
            'locations' => $locations,
            'timeSlots' => $timeSlots,
            'calendarAppointments' => $calendarAppointments,
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
