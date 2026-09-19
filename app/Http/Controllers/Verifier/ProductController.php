<?php

namespace App\Http\Controllers\Verifier;

use App\Enums\ProductCondition;
use App\Enums\ProductGrade;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductVerificationService $verifications) {}

    public function index(Request $request): View
    {
        $verifier = Auth::user();
        $locationId = $verifier->verifierProfile?->assigned_location_id;

        // Scope query strictly to products with appointments at the verifier's location or assigned to the verifier
        $baseQuery = Product::query()
            ->whereHas('verifications', function ($q) use ($verifier, $locationId) {
                $q->where(function ($sub) use ($verifier, $locationId) {
                    $sub->where('verifier_id', $verifier->id);
                    if ($locationId) {
                        $sub->orWhere('location_id', $locationId);
                    }
                });
            });

        $query = (clone $baseQuery)
            ->with(['user', 'category', 'images', 'gradeAssignment', 'latestVerificationRequest'])
            ->latest();

        // Search by title, SKU, or seller
        if ($request->filled('search')) {
            $term = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', $term)->orWhere('email', 'like', $term));
            });
        }

        // Filter by Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        // Filter by Verification Status
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->query('verification_status'));
        }

        // Filter by Condition
        if ($request->filled('condition')) {
            $query->where('condition', $request->query('condition'));
        }

        $totalCount = (clone $baseQuery)->count();
        $verifiedCount = (clone $baseQuery)->where('verification_status', VerificationStatus::Verified)->count();
        $pendingCount = (clone $baseQuery)->whereIn('verification_status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending])->count();
        $unverifiedCount = (clone $baseQuery)->where('verification_status', VerificationStatus::NotRequested)->count();

        return view('verifier.products.index', [
            'products' => $query->paginate(15)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'totalCount' => $totalCount,
            'verifiedCount' => $verifiedCount,
            'pendingCount' => $pendingCount,
            'unverifiedCount' => $unverifiedCount,
            'search' => $request->query('search'),
            'selectedCategory' => $request->query('category_id'),
            'selectedStatus' => $request->query('verification_status'),
            'selectedCondition' => $request->query('condition'),
        ]);
    }

    public function show(Product $product): View
    {
        $verifier = Auth::user();
        $locationId = $verifier->verifierProfile?->assigned_location_id;

        $hasAppointment = $product->verifications()
            ->where(function ($sub) use ($verifier, $locationId) {
                $sub->where('verifier_id', $verifier->id);
                if ($locationId) {
                    $sub->orWhere('location_id', $locationId);
                }
            })
            ->exists();

        abort_unless($hasAppointment, 403, 'You are only authorized to view products scheduled for appointment at your assigned location.');

        $checklist = $this->verifications->checklistFor($product);

        return view('verifier.products.show', [
            'product' => $product->load(['user', 'category', 'images', 'attributeValues.attribute', 'verifications.gradeAssignment', 'gradeAssignment']),
            'checklist' => $checklist,
        ]);
    }

    public function verify(Request $request, Product $product): RedirectResponse
    {
        $verifier = Auth::user();
        $locationId = $verifier->verifierProfile?->assigned_location_id;

        $hasAppointment = $product->verifications()
            ->where(function ($sub) use ($verifier, $locationId) {
                $sub->where('verifier_id', $verifier->id);
                if ($locationId) {
                    $sub->orWhere('location_id', $locationId);
                }
            })
            ->exists();

        abort_unless($hasAppointment, 403, 'You are only authorized to inspect products scheduled for appointment at your assigned location.');

        $decision = $request->input('decision', 'pass');

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['pass', 'fail'])],
            'grade' => [Rule::requiredIf($decision === 'pass'), Rule::in([ProductGrade::A->value, ProductGrade::B->value, ProductGrade::C->value])],
            'battery_health' => ['nullable', 'integer', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'reason' => [Rule::requiredIf($decision === 'fail'), 'nullable', 'string', 'max:500'],
            'results' => ['nullable', 'array'],
        ]);

        $this->verifications->verifyDirectly(
            $product,
            $verifier,
            $validated['decision'],
            $validated['grade'] ?? null,
            isset($validated['battery_health']) ? (int) $validated['battery_health'] : null,
            $validated['notes'] ?? null,
            $validated['reason'] ?? null,
            $validated['results'] ?? []
        );

        $statusMsg = $decision === 'pass'
            ? "Product '{$product->title}' has been certified (Grade {$validated['grade']}) and locked from seller edits."
            : "Product '{$product->title}' has been marked as rejected.";

        return redirect()->route('verifier.products.index')->with('status', $statusMsg);
    }
}
