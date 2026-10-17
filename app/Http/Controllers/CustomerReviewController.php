<?php

namespace App\Http\Controllers;

use App\Enums\ProductPublicationStatus;
use App\Enums\ReviewableType;
use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function index(): View
    {
        return view('account.reviews.index', [
            'reviews' => Auth::user()->reviewsWritten()->with('order')->latest()->paginate(15),
            'eligibleReviewables' => $this->reviews->allEligibleReviewables(Auth::user()),
        ]);
    }

    public function storeForProduct(StoreProductReviewRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->publication_status === ProductPublicationStatus::Published, 404);

        $this->reviews->createForProduct(
            Auth::user(),
            $product,
            $request->integer('rating'),
            $request->string('title')->value() ?: null,
            $request->string('body')->value(),
        );

        return redirect()->to(route('products.show', $product).'#reviews')
            ->with('status', 'Thanks for your review — it will show once approved.');
    }

    public function store(StoreReviewRequest $request, ?Order $order = null): RedirectResponse
    {
        $targetOrder = $order ?? Order::where('customer_id', Auth::id())->findOrFail($request->integer('order_id'));

        $this->reviews->create(
            Auth::user(),
            $targetOrder,
            ReviewableType::from($request->string('reviewable_type')->value()),
            $request->integer('reviewable_id'),
            $request->integer('rating'),
            $request->string('title')->value() ?: null,
            $request->string('body')->value(),
        );

        return back()->with('status', 'Thanks for your review — it will show once approved.');
    }
}
