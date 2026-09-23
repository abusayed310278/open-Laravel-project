<?php

namespace App\Http\Controllers\Verifier;

use App\Enums\ProductApprovalStatus;
use App\Enums\ProductCondition;
use App\Enums\ProductGrade;
use App\Enums\ProductStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\ProductVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductVerificationService $verifications,
        private readonly ProductService $products
    ) {}

    public function index(Request $request): View
    {
        $verifier = Auth::user();
        $locationId = $verifier->verifierProfile?->assigned_location_id;

        $baseQuery = Product::query();

        if ($locationId) {
            $baseQuery->whereHas('verifications', function ($q) use ($verifier, $locationId) {
                $q->where('location_id', $locationId)
                  ->orWhere('verifier_id', $verifier->id);
            });
        } else {
            $baseQuery->where(function ($q) use ($verifier) {
                $q->whereHas('verifications', fn ($vq) => $vq->whereNull('location_id')->orWhere('verifier_id', $verifier->id))
                  ->orDoesntHave('verifications');
            });
        }

        $query = (clone $baseQuery)
            ->with(['user', 'category', 'brand', 'images', 'gradeAssignment', 'latestVerificationRequest'])
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

        // Filter by Product Status / Active State (Like Admin)
        if ($request->filled('status')) {
            $statusVal = $request->query('status');
            if ($statusVal === 'active') {
                $query->where(function ($q) {
                    $q->where('status', ProductStatus::Published)
                        ->orWhere('approval_status', ProductApprovalStatus::Approved);
                });
            } elseif ($statusVal === 'inactive') {
                $query->where(function ($q) {
                    $q->where('status', ProductStatus::Draft)
                        ->orWhere('status', ProductStatus::Suspended)
                        ->orWhere('status', ProductStatus::Archived);
                });
            } else {
                $query->where('status', $statusVal);
            }
        }

        // Filter by Verification Status
        if ($request->filled('verification_status')) {
            $vStatusVal = $request->query('verification_status');
            if (in_array($vStatusVal, ['pending', 'pending_queue', 'queue', 'scheduled', 'inspecting'], true)) {
                $query->whereIn('verification_status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending]);
            } else {
                $query->where('verification_status', $vStatusVal);
            }
        }

        // Filter by Condition
        if ($request->filled('condition')) {
            $query->where('condition', $request->query('condition'));
        }

        $totalCount = (clone $baseQuery)->count();
        $activeCount = (clone $baseQuery)->where(function ($q) {
            $q->where('status', ProductStatus::Published)
                ->orWhere('approval_status', ProductApprovalStatus::Approved);
        })->count();
        $inactiveCount = (clone $baseQuery)->where(function ($q) {
            $q->where('status', ProductStatus::Draft)
                ->orWhere('status', ProductStatus::Suspended)
                ->orWhere('status', ProductStatus::Archived);
        })->count();
        $pendingCount = (clone $baseQuery)->whereIn('verification_status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending])->count();
        $verifiedCount = (clone $baseQuery)->where('verification_status', VerificationStatus::Verified)->count();

        return view('verifier.products.index', [
            'products' => $query->paginate(15)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
            'pendingCount' => $pendingCount,
            'verifiedCount' => $verifiedCount,
            'search' => $request->query('search'),
            'selectedCategory' => $request->query('category_id'),
            'selectedProductStatus' => $request->query('status'),
            'selectedStatus' => $request->query('verification_status'),
            'selectedCondition' => $request->query('condition'),
        ]);
    }

    public function show(Product $product): View
    {
        $checklist = $this->verifications->checklistFor($product);

        return view('verifier.products.show', [
            'product' => $product->load(['user', 'category', 'brand', 'images', 'attributeValues.attribute', 'verifications.gradeAssignment', 'gradeAssignment']),
            'checklist' => $checklist,
        ]);
    }

    public function verify(Request $request, Product $product): RedirectResponse
    {
        $verifier = Auth::user();
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

        $msg = $decision === 'pass'
            ? "\"{$product->title}\" was successfully verified and graded Grade {$validated['grade']}."
            : "\"{$product->title}\" verification failed.";

        return back()->with('status', $msg);
    }

    public function edit(Product $product): View
    {
        return view('verifier.products.edit', [
            'product' => $product->load(['images', 'attributeValues']),
            'categories' => Category::query()->active()->with(['attributes.values', 'attributes.group'])->orderBy('name')->get(),
            'brands' => Brand::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update(
            $product,
            $request->safe()->except(['images', 'attributes', 'primary_image_id', 'primary_image_index', 'delete_images']),
            $request->file('images', []),
            $request->input('attributes', []),
            $request->user(),
            $request->input('primary_image_id') ? (int) $request->input('primary_image_id') : null,
            $request->input('primary_image_index') !== null ? (int) $request->input('primary_image_index') : null,
            array_map('intval', $request->input('delete_images', []))
        );

        return redirect()->route('verifier.products.index')->with('status', "\"{$product->title}\" was updated successfully.");
    }

    public function approve(Product $product): RedirectResponse
    {
        $this->products->approve($product);

        return back()->with('status', "\"{$product->title}\" was approved.");
    }

    public function publish(Product $product): RedirectResponse
    {
        $this->products->publish($product);

        return back()->with('status', "\"{$product->title}\" is now published.");
    }

    public function unpublish(Product $product): RedirectResponse
    {
        $this->products->unpublish($product);

        return back()->with('status', "\"{$product->title}\" was unpublished.");
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        if ($product->publication_status?->value === 'published' || $product->status?->value === 'published') {
            $this->products->unpublish($product);
            $message = "\"{$product->title}\" was set to inactive (unpublished).";
        } else {
            $this->products->publish($product);
            $message = "\"{$product->title}\" is now active (published).";
        }

        return back()->with('status', $message);
    }

    public function reject(RejectProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->reject($product, $request->string('reason')->value());

        return back()->with('status', "\"{$product->title}\" was rejected.");
    }
}
