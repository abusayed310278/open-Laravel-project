<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;

class UserManagementService
{
    public function updateStatus(User $user, UserStatus $status): User
    {
        $user->update(['status' => $status]);

        return $user;
    }

    public function updateRole(User $user, UserRole $role): User
    {
        $user->update(['role' => $role]);

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        return $this->bulkDeleteUsers([$user->id]) > 0;
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkDeleteUsers(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($ids) {
            $this->cleanUpUserDependencies($ids);

            return User::whereIn('id', $ids)->delete();
        });
    }

    /**
     * Clean up records referencing users that do not cascade on delete.
     *
     * @param  array<int>  $ids
     */
    private function cleanUpUserDependencies(array $ids): void
    {
        // 1. Invoices where users are seller or buyer (invoice_items cascade on invoice delete)
        \Illuminate\Support\Facades\DB::table('invoices')
            ->whereIn('seller_id', $ids)
            ->orWhereIn('buyer_id', $ids)
            ->delete();

        // 2. Commission records for sellers
        \Illuminate\Support\Facades\DB::table('commission_records')
            ->whereIn('seller_id', $ids)
            ->delete();

        // 3. Seller transactions and payouts
        \Illuminate\Support\Facades\DB::table('seller_transactions')
            ->whereIn('user_id', $ids)
            ->delete();
        \Illuminate\Support\Facades\DB::table('seller_payouts')
            ->whereIn('user_id', $ids)
            ->delete();

        // 4. Vendor orders where user is vendor
        $vendorOrderIds = \Illuminate\Support\Facades\DB::table('vendor_orders')
            ->whereIn('vendor_id', $ids)
            ->pluck('id');
        if ($vendorOrderIds->isNotEmpty()) {
            \Illuminate\Support\Facades\DB::table('invoices')->whereIn('vendor_order_id', $vendorOrderIds)->delete();
            \Illuminate\Support\Facades\DB::table('order_items')->whereIn('vendor_order_id', $vendorOrderIds)->delete();
            \Illuminate\Support\Facades\DB::table('refunds')->whereIn('vendor_order_id', $vendorOrderIds)->delete();
            \Illuminate\Support\Facades\DB::table('transactions')->whereIn('vendor_order_id', $vendorOrderIds)->delete();
            \Illuminate\Support\Facades\DB::table('vendor_orders')->whereIn('id', $vendorOrderIds)->delete();
        }

        // 5. Orders where user is customer
        $orderIds = \Illuminate\Support\Facades\DB::table('orders')
            ->whereIn('customer_id', $ids)
            ->pluck('id');
        if ($orderIds->isNotEmpty()) {
            \Illuminate\Support\Facades\DB::table('invoices')->whereIn('order_id', $orderIds)->delete();
            \Illuminate\Support\Facades\DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
            \Illuminate\Support\Facades\DB::table('vendor_orders')->whereIn('order_id', $orderIds)->delete();
            \Illuminate\Support\Facades\DB::table('orders')->whereIn('id', $orderIds)->delete();
        }

        // 6. Transactions
        \Illuminate\Support\Facades\DB::table('transactions')->whereIn('user_id', $ids)->delete();

        // 7. Warehouse products and receipts
        \Illuminate\Support\Facades\DB::table('warehouse_products')->whereIn('seller_id', $ids)->delete();
        \Illuminate\Support\Facades\DB::table('warehouse_receipts')->whereIn('received_by', $ids)->update(['received_by' => null]);

        // 8. Chat & Support
        \Illuminate\Support\Facades\DB::table('chat_messages')->whereIn('sender_id', $ids)->delete();
        \Illuminate\Support\Facades\DB::table('chat_conversations')->whereIn('buyer_id', $ids)->orWhereIn('seller_id', $ids)->delete();
        \Illuminate\Support\Facades\DB::table('support_ticket_messages')->whereIn('sender_id', $ids)->delete();
        \Illuminate\Support\Facades\DB::table('support_tickets')->whereIn('user_id', $ids)->delete();

        // 9. Review replies and reports
        \Illuminate\Support\Facades\DB::table('review_replies')->whereIn('replier_id', $ids)->delete();
        \Illuminate\Support\Facades\DB::table('review_reports')->whereIn('reporter_id', $ids)->delete();

        // 10. Refunds requested by user
        \Illuminate\Support\Facades\DB::table('refunds')->whereIn('requested_by', $ids)->delete();

        // 11. Posts authored by user
        \Illuminate\Support\Facades\DB::table('posts')->whereIn('author_id', $ids)->delete();

        // 12. Product grade assignments verified by user
        \Illuminate\Support\Facades\DB::table('product_grade_assignments')->whereIn('verifier_id', $ids)->update(['verifier_id' => null]);
    }
}
