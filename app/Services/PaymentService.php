<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Enums\PaymentSubmissionStatus;
use App\Enums\RefundStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\ActivityLog;
use App\Models\ManualPaymentSubmission;
use App\Models\Refund;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VendorOrder;
use App\Notifications\ManualPaymentVerified;
use App\Notifications\RefundStatusChanged;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly CommissionService $commissionService,
        private readonly InventoryService $inventoryService,
    ) {}

    public function recordPendingTransaction(VendorOrder $vendorOrder, string $provider): Transaction
    {
        return Transaction::create([
            'transaction_number' => 'TXN-'.now()->format('Y').'-'.Str::upper(Str::random(8)),
            'order_id' => $vendorOrder->order_id,
            'vendor_order_id' => $vendorOrder->id,
            'user_id' => $vendorOrder->vendor_id,
            'type' => TransactionType::Payment,
            'payment_route' => $vendorOrder->payment_route,
            'provider' => $provider,
            'amount' => $vendorOrder->total,
            'status' => TransactionStatus::Pending,
        ]);
    }

    public function submitManualPayment(VendorOrder $vendorOrder, UploadedFile $proof, ?string $reference, ?string $bankName): ManualPaymentSubmission
    {
        $submission = ManualPaymentSubmission::create([
            'order_id' => $vendorOrder->order_id,
            'vendor_order_id' => $vendorOrder->id,
            'amount' => $vendorOrder->total,
            'reference' => $reference,
            'bank_name' => $bankName,
            'proof_file_path' => $proof->store('manual-payments', 'local'),
            'status' => PaymentSubmissionStatus::Pending,
        ]);

        ActivityLog::record('payment.manual_submitted', $submission);

        return $submission;
    }

    public function verifyManualPayment(ManualPaymentSubmission $submission, User $verifier): ManualPaymentSubmission
    {
        $submission->update([
            'status' => PaymentSubmissionStatus::Verified,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);

        Transaction::query()
            ->where('vendor_order_id', $submission->vendor_order_id)
            ->where('status', TransactionStatus::Pending)
            ->update(['status' => TransactionStatus::Completed]);

        $submission->vendorOrder->order->customer->notify(new ManualPaymentVerified($submission));

        $this->invoiceService->markPaid($submission->vendorOrder);
        $this->commissionService->recordSaleForVendorOrder($submission->vendorOrder);

        ActivityLog::record('payment.manual_verified', $submission);

        return $submission;
    }

    public function rejectManualPayment(ManualPaymentSubmission $submission, User $verifier, string $notes): ManualPaymentSubmission
    {
        $submission->update([
            'status' => PaymentSubmissionStatus::Rejected,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'notes' => $notes,
        ]);

        ActivityLog::record('payment.manual_rejected', $submission, ['notes' => $notes]);

        return $submission;
    }

    public function confirmCodCollected(VendorOrder $vendorOrder): void
    {
        Transaction::query()
            ->where('vendor_order_id', $vendorOrder->id)
            ->where('status', TransactionStatus::Pending)
            ->update(['status' => TransactionStatus::Completed]);

        $this->invoiceService->markPaid($vendorOrder);
        $this->commissionService->recordSaleForVendorOrder($vendorOrder);

        ActivityLog::record('payment.cod_collected', $vendorOrder);
    }

    public function requestRefund(VendorOrder $vendorOrder, User $requester, string $reason, float $amount): Refund
    {
        $transaction = Transaction::query()->where('vendor_order_id', $vendorOrder->id)->latest()->first();

        $refund = Refund::create([
            'order_id' => $vendorOrder->order_id,
            'vendor_order_id' => $vendorOrder->id,
            'transaction_id' => $transaction?->id,
            'requested_by' => $requester->id,
            'reason' => $reason,
            'amount' => $amount,
            'status' => RefundStatus::Pending,
        ]);

        ActivityLog::record('refund.requested', $refund);

        return $refund;
    }

    public function approveRefund(Refund $refund, User $approver): Refund
    {
        $refund->update([
            'status' => RefundStatus::Completed,
            'approved_by' => $approver->id,
            'processed_at' => now(),
        ]);

        if ($refund->transaction) {
            $refund->transaction->update(['status' => TransactionStatus::Refunded]);
        }

        $this->invoiceService->markRefunded($refund->vendorOrder);
        $this->commissionService->reverseForVendorOrder($refund->vendorOrder);

        $refund->vendorOrder->loadMissing('items.product');

        foreach ($refund->vendorOrder->items as $item) {
            if ($item->product) {
                $this->inventoryService->restore($item->product, $item->quantity, InventoryMovementType::Return, $item);
            }
        }

        $refund->vendorOrder->order->customer->notify(new RefundStatusChanged($refund));

        ActivityLog::record('refund.approved', $refund);

        return $refund;
    }

    public function rejectRefund(Refund $refund, User $approver): Refund
    {
        $refund->update([
            'status' => RefundStatus::Rejected,
            'approved_by' => $approver->id,
            'processed_at' => now(),
        ]);

        $refund->vendorOrder->order->customer->notify(new RefundStatusChanged($refund));

        ActivityLog::record('refund.rejected', $refund);

        return $refund;
    }
}
