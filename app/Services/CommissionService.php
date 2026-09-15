<?php

namespace App\Services;

use App\Enums\CommissionRecordStatus;
use App\Enums\CommissionRuleType;
use App\Enums\CommissionType;
use App\Enums\SellerTransactionSource;
use App\Models\CommissionRecord;
use App\Models\CommissionRule;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorOrder;

class CommissionService
{
    public function __construct(private readonly WalletService $wallets) {}

    /**
     * Resolve the commission rule that applies to a sale, most specific
     * first: product → seller → category → seller-type (business/saler) →
     * platform-wide global default. Returns null if nothing matches (no
     * commission taken).
     */
    public function resolveRule(Product $product, User $seller): ?CommissionRule
    {
        foreach (CommissionRuleType::specificityOrder() as $type) {
            $referenceId = match ($type) {
                CommissionRuleType::Product => $product->id,
                CommissionRuleType::Seller => $seller->id,
                CommissionRuleType::Category => $product->category_id,
                CommissionRuleType::Business, CommissionRuleType::Global => null,
            };

            $rule = CommissionRule::query()
                ->active()
                ->where('type', $type)
                ->where('reference_id', $referenceId)
                ->orderByDesc('priority')
                ->first();

            if ($rule) {
                return $rule;
            }
        }

        return null;
    }

    public function commissionAmountFor(CommissionRule $rule, float $saleAmount): float
    {
        $amount = $rule->commission_type === CommissionType::Percentage
            ? $saleAmount * ((float) $rule->value / 100)
            : (float) $rule->value;

        return round(min($amount, $saleAmount), 2);
    }

    /**
     * Credit the seller's wallet (pending) for every item on a vendor
     * order once its payment is confirmed. Idempotent — skips items that
     * already have a commission record so this can be called safely
     * without tracking whether it already ran.
     */
    public function recordSaleForVendorOrder(VendorOrder $vendorOrder): void
    {
        $vendorOrder->loadMissing('items.product', 'vendor');

        foreach ($vendorOrder->items as $item) {
            if ($item->commissionRecord || ! $item->product) {
                continue;
            }

            $this->recordForItem($item, $vendorOrder->vendor);
        }
    }

    private function recordForItem(OrderItem $item, User $seller): CommissionRecord
    {
        $rule = $this->resolveRule($item->product, $seller);
        $saleAmount = (float) $item->total_price;
        $commissionAmount = $rule ? $this->commissionAmountFor($rule, $saleAmount) : 0.0;
        $sellerAmount = round($saleAmount - $commissionAmount, 2);

        $record = CommissionRecord::create([
            'order_item_id' => $item->id,
            'seller_id' => $seller->id,
            'sale_amount' => $saleAmount,
            'commission_rate' => $rule?->value ?? 0,
            'commission_amount' => $commissionAmount,
            'seller_amount' => $sellerAmount,
            'status' => CommissionRecordStatus::Pending,
        ]);

        $this->wallets->creditPending($seller, $sellerAmount, SellerTransactionSource::Sale, $record);

        return $record;
    }

    /**
     * Move every pending commission record for a vendor order to
     * available — called once the order is delivered.
     */
    public function releaseForVendorOrder(VendorOrder $vendorOrder): void
    {
        $records = CommissionRecord::query()
            ->whereIn('order_item_id', $vendorOrder->items()->pluck('id'))
            ->where('status', CommissionRecordStatus::Pending)
            ->get();

        foreach ($records as $record) {
            $record->update(['status' => CommissionRecordStatus::Available]);
            $this->wallets->releasePendingToAvailable($record->seller, (float) $record->seller_amount);
        }
    }

    /**
     * Reverse every commission record tied to a refunded vendor order.
     */
    public function reverseForVendorOrder(VendorOrder $vendorOrder): void
    {
        $records = CommissionRecord::query()
            ->whereIn('order_item_id', $vendorOrder->items()->pluck('id'))
            ->whereIn('status', [CommissionRecordStatus::Pending, CommissionRecordStatus::Available])
            ->get();

        foreach ($records as $record) {
            $wasAvailable = $record->status === CommissionRecordStatus::Available;
            $record->update(['status' => CommissionRecordStatus::Refunded]);
            $this->wallets->reverseForRefund($record->seller, (float) $record->seller_amount, $wasAvailable, $record);
        }
    }
}
