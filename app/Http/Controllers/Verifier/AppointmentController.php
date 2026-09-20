<?php

namespace App\Http\Controllers\Verifier;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Verifier\SubmitInspectionRequest;
use App\Models\ProductVerification;
use App\Services\ProductVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly ProductVerificationService $verifications) {}

    public function dashboard(): View
    {
        $verifier = Auth::user();
        $locationId = $verifier->verifierProfile?->assigned_location_id;
        $location = $verifier->verifierProfile?->location;

        $baseQuery = ProductVerification::query();
        if ($locationId) {
            $baseQuery->where(function ($q) use ($verifier, $locationId) {
                $q->where('location_id', $locationId)
                  ->orWhere('verifier_id', $verifier->id);
            });
        }

        $todayAppointmentsCount = (clone $baseQuery)
            ->whereDate('scheduled_at', '<=', today())
            ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending])
            ->count();

        $pendingInspectionsCount = (clone $baseQuery)
            ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending])
            ->count();

        $completedThisMonthCount = (clone $baseQuery)
            ->whereIn('status', [VerificationStatus::Verified, VerificationStatus::Rejected])
            ->whereMonth('inspected_at', now()->month)
            ->whereYear('inspected_at', now()->year)
            ->count();

        $totalCompleted = (clone $baseQuery)
            ->whereIn('status', [VerificationStatus::Verified, VerificationStatus::Rejected])
            ->count();

        $passedCount = (clone $baseQuery)
            ->where('status', VerificationStatus::Verified)
            ->count();

        $passRate = $totalCompleted > 0 ? round(($passedCount / $totalCompleted) * 100) : 100;

        $todayAppointments = (clone $baseQuery)
            ->with(['product.images', 'product.category', 'seller', 'location'])
            ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending])
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get();

        $recentInspections = (clone $baseQuery)
            ->with(['product.images', 'seller', 'gradeAssignment'])
            ->whereIn('status', [VerificationStatus::Verified, VerificationStatus::Rejected])
            ->latest('inspected_at')
            ->limit(6)
            ->get();

        return view('verifier.dashboard', [
            'verifier' => $verifier,
            'location' => $location,
            'todayAppointmentsCount' => $todayAppointmentsCount,
            'pendingInspectionsCount' => $pendingInspectionsCount,
            'completedThisMonthCount' => $completedThisMonthCount,
            'passRate' => $passRate,
            'pendingAppointments' => $todayAppointments,
            'todayAppointments' => $todayAppointments,
            'recentInspections' => $recentInspections,
        ]);
    }

    public function index(Request $request): View
    {
        $verifier = Auth::user();
        $locationId = $verifier->verifierProfile?->assigned_location_id;

        $query = ProductVerification::query()
            ->with(['product.images', 'product.category', 'seller', 'location'])
            ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending]);

        if ($locationId) {
            $query->where(function ($q) use ($verifier, $locationId) {
                $q->where('location_id', $locationId)
                  ->orWhere('verifier_id', $verifier->id);
            });
        }

        if ($request->filled('status')) {
            $statusVal = $request->query('status');
            if (in_array($statusVal, ['pending', 'pending_queue', 'queue'], true)) {
                $query->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending]);
            } else {
                $query->where('status', $statusVal);
            }
        }

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.$request->query('search').'%';
            $q->where(function ($sub) use ($term) {
                $sub->whereHas('product', fn ($pq) => $pq->where('title', 'like', $term)->orWhere('sku', 'like', $term))
                    ->orWhereHas('seller', fn ($sq) => $sq->where('name', 'like', $term)->orWhere('email', 'like', $term));
            });
        })->orderBy('scheduled_at');

        $calendarQuery = ProductVerification::query()
            ->with(['product.images', 'product.category', 'seller', 'location'])
            ->whereNotNull('scheduled_at');

        if ($locationId) {
            $calendarQuery->where(function ($q) use ($verifier, $locationId) {
                $q->where('location_id', $locationId)
                  ->orWhere('verifier_id', $verifier->id);
            });
        }

        $calendarAppointments = $calendarQuery->get()
            ->map(function ($v) {
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
                    'seller_name' => $v->seller?->name ?? 'Seller',
                    'seller_email' => $v->seller?->email ?? '',
                    'inspect_url' => route('verifier.appointments.inspect', $v),
                    'notes' => $v->notes ?? 'Scheduled physical device scrutiny.',
                ];
            });

        return view('verifier.appointments.index', [
            'appointments' => $query->paginate(15)->withQueryString(),
            'calendarAppointments' => $calendarAppointments,
            'currentStatus' => $request->query('status'),
            'search' => $request->query('search'),
        ]);
    }

    public function history(Request $request): View
    {
        $verifier = Auth::user();
        $locationId = $verifier->verifierProfile?->assigned_location_id;

        $query = ProductVerification::query()
            ->with(['product.images', 'seller', 'location', 'gradeAssignment', 'verifier'])
            ->whereIn('status', [VerificationStatus::Verified, VerificationStatus::Rejected])
            ->where('location_id', $locationId)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('grade'), function ($q) use ($request) {
                $q->whereHas('gradeAssignment', fn ($gq) => $gq->where('grade', $request->query('grade')));
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->query('search').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereHas('product', fn ($pq) => $pq->where('title', 'like', $term)->orWhere('sku', 'like', $term))
                        ->orWhereHas('seller', fn ($sq) => $sq->where('name', 'like', $term)->orWhere('email', 'like', $term));
                });
            })
            ->latest('inspected_at');

        return view('verifier.history.index', [
            'history' => $query->paginate(15)->withQueryString(),
            'currentStatus' => $request->query('status'),
            'currentGrade' => $request->query('grade'),
            'search' => $request->query('search'),
        ]);
    }

    public function inspect(ProductVerification $verification): View
    {
        return view('verifier.appointments.inspect', [
            'verification' => $verification->load(['product.images', 'product.attributeValues.attribute', 'product.category', 'seller', 'location']),
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
                $request->input('results', []),
                $request->string('grade')->value(),
                $request->integer('battery_health') ?: null,
                $request->string('notes')->value() ?: null,
            );
        } else {
            $this->verifications->fail(
                $verification,
                $verifier,
                $request->input('results', []),
                $request->string('reason')->value(),
            );
        }

        return redirect()->route('verifier.appointments.index')->with('status', 'Inspection report submitted successfully.');
    }
}
