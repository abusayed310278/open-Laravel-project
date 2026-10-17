<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReviewableType;
use App\Enums\ReviewReportStatus;
use App\Enums\ReviewStatus;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\ReviewReport;
use App\Models\User;

class ReviewService
{
    /**
     * Reviewable products/sellers from a delivered order the customer
     * hasn't already reviewed — one entry per vendor order's product and
     * per vendor order's seller.
     *
     * @return array<int, array{type: ReviewableType, id: int, label: string}>
     */
    public function eligibleReviewables(User $customer, Order $order): array
    {
        abort_unless($order->customer_id === $customer->id, 403);

        $order->loadMissing('vendorOrders.items', 'vendorOrders.vendor');

        $alreadyReviewed = Review::query()
            ->where('reviewer_id', $customer->id)
            ->where('order_id', $order->id)
            ->get(['reviewable_type', 'reviewable_id'])
            ->map(fn (Review $r) => $r->reviewable_type->value.':'.$r->reviewable_id)
            ->all();

        $eligible = [];

        foreach ($order->vendorOrders as $vendorOrder) {
            if (in_array($vendorOrder->status, [OrderStatus::Cancelled, OrderStatus::Failed, OrderStatus::Refunded], true)) {
                continue;
            }

            foreach ($vendorOrder->items as $item) {
                $key = ReviewableType::Product->value.':'.$item->product_id;

                if (! in_array($key, $alreadyReviewed, true)) {
                    $eligible[$key] = ['type' => ReviewableType::Product, 'id' => $item->product_id, 'label' => $item->product_title];
                }
            }

            $key = ReviewableType::Seller->value.':'.$vendorOrder->vendor_id;

            if (! in_array($key, $alreadyReviewed, true)) {
                $eligible[$key] = ['type' => ReviewableType::Seller, 'id' => $vendorOrder->vendor_id, 'label' => $vendorOrder->vendor->name];
            }
        }

        return array_values($eligible);
    }

    /**
     * Get all eligible unreviewed products & sellers across all orders for a customer.
     *
     * @return array<int, array{order_id: int, order_number: string, type: ReviewableType, id: int, label: string, subtext: string, image: ?string}>
     */
    public function allEligibleReviewables(User $customer): array
    {
        $orders = $customer->orders()
            ->with(['vendorOrders.items.product', 'vendorOrders.vendor'])
            ->whereNotIn('status', [OrderStatus::Cancelled, OrderStatus::Failed, OrderStatus::Refunded])
            ->latest()
            ->get();

        $alreadyReviewed = Review::query()
            ->where('reviewer_id', $customer->id)
            ->get(['order_id', 'reviewable_type', 'reviewable_id'])
            ->map(fn (Review $r) => $r->order_id.':'.$r->reviewable_type->value.':'.$r->reviewable_id)
            ->all();

        $eligible = [];

        foreach ($orders as $order) {
            foreach ($order->vendorOrders as $vendorOrder) {
                if (in_array($vendorOrder->status, [OrderStatus::Cancelled, OrderStatus::Failed, OrderStatus::Refunded], true)) {
                    continue;
                }

                foreach ($vendorOrder->items as $item) {
                    $compositeKey = $order->id.':'.ReviewableType::Product->value.':'.$item->product_id;

                    if (! in_array($compositeKey, $alreadyReviewed, true) && ! isset($eligible[$compositeKey])) {
                        $eligible[$compositeKey] = [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'type' => ReviewableType::Product,
                            'id' => $item->product_id,
                            'label' => $item->product_title,
                            'subtext' => 'Order #' . $order->order_number . ' · ' . $order->created_at->format('M j, Y'),
                            'image' => $item->product?->primaryImageUrl(),
                        ];
                    }
                }

                $sellerKey = $order->id.':'.ReviewableType::Seller->value.':'.$vendorOrder->vendor_id;
                if (! in_array($sellerKey, $alreadyReviewed, true) && ! isset($eligible[$sellerKey])) {
                    $eligible[$sellerKey] = [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'type' => ReviewableType::Seller,
                        'id' => $vendorOrder->vendor_id,
                        'label' => 'Seller: ' . $vendorOrder->vendor->name,
                        'subtext' => 'Order #' . $order->order_number . ' · ' . $order->created_at->format('M j, Y'),
                        'image' => null,
                    ];
                }
            }
        }

        return array_values($eligible);
    }

    public function create(User $reviewer, Order $order, ReviewableType $type, int $reviewableId, int $rating, ?string $title, string $body): Review
    {
        $eligible = collect($this->eligibleReviewables($reviewer, $order));

        abort_unless(
            $eligible->contains(fn (array $r) => $r['type'] === $type && $r['id'] === $reviewableId),
            422,
            'You can only review items from your orders that you haven\'t already reviewed.'
        );

        $review = Review::create([
            'reviewer_id' => $reviewer->id,
            'reviewable_type' => $type,
            'reviewable_id' => $reviewableId,
            'order_id' => $order->id,
            'rating' => max(1, min(5, $rating)),
            'title' => $title,
            'body' => $body,
            'status' => ReviewStatus::Pending,
        ]);

        ActivityLog::record('review.submitted', $review);

        return $review;
    }

    /**
     * Any logged-in user may review a live product directly from its page, once,
     * and never their own listing. Goes through the same moderation queue.
     */
    public function createForProduct(User $reviewer, Product $product, int $rating, ?string $title, string $body): Review
    {
        abort_if($product->user_id === $reviewer->id, 403, 'You cannot review your own product.');

        abort_if(
            Review::query()
                ->where('reviewer_id', $reviewer->id)
                ->where('reviewable_type', ReviewableType::Product)
                ->where('reviewable_id', $product->id)
                ->exists(),
            422,
            'You have already reviewed this product.'
        );

        $review = Review::create([
            'reviewer_id' => $reviewer->id,
            'reviewable_type' => ReviewableType::Product,
            'reviewable_id' => $product->id,
            'order_id' => null,
            'rating' => max(1, min(5, $rating)),
            'title' => $title,
            'body' => $body,
            'status' => ReviewStatus::Pending,
        ]);

        ActivityLog::record('review.submitted', $review);

        return $review;
    }

    public function approve(Review $review, User $admin): Review
    {
        $review->update(['status' => ReviewStatus::Approved]);

        ActivityLog::record('review.approved', $review, ['by' => $admin->id]);

        return $review;
    }

    public function reject(Review $review, User $admin): Review
    {
        $review->update(['status' => ReviewStatus::Rejected]);

        ActivityLog::record('review.rejected', $review, ['by' => $admin->id]);

        return $review;
    }

    public function reply(Review $review, User $replier, string $body): ReviewReply
    {
        $reply = $review->replies()->create([
            'replier_id' => $replier->id,
            'body' => $body,
        ]);

        ActivityLog::record('review.replied', $review);

        return $reply;
    }

    public function report(Review $review, User $reporter, string $reason): ReviewReport
    {
        $report = $review->reports()->create([
            'reporter_id' => $reporter->id,
            'reason' => $reason,
            'status' => ReviewReportStatus::Pending,
        ]);

        ActivityLog::record('review.reported', $review);

        return $report;
    }

    public function resolveReport(ReviewReport $report): ReviewReport
    {
        $report->update(['status' => ReviewReportStatus::Resolved]);

        ActivityLog::record('review.report_resolved', $report);

        return $report;
    }

    public function dismissReport(ReviewReport $report): ReviewReport
    {
        $report->update(['status' => ReviewReportStatus::Dismissed]);

        ActivityLog::record('review.report_dismissed', $report);

        return $report;
    }
}
