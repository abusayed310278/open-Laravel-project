<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\KycStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVerification;
use App\Models\SubscriptionPlan;
use App\Models\SupportTicket;
use App\Models\UserVerification;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SellerOperationsController extends Controller
{
    /**
     * 1. Verification Status (KYC)
     */
    public function verification(Request $request): JsonResponse
    {
        $user = $request->user();
        $application = UserVerification::query()
            ->where('user_id', $user->id)
            ->with('documents')
            ->latest()
            ->first();

        $documents = [];
        if ($application && $application->documents) {
            foreach ($application->documents as $doc) {
                $documents[] = [
                    'id' => $doc->id,
                    'type' => $doc->document_type ?? 'Government ID',
                    'document_number' => $doc->document_number,
                    'status' => $doc->status ?? 'pending',
                    'created_at' => $doc->created_at?->format('Y-m-d H:i'),
                ];
            }
        }

        return response()->json([
            'is_kyc_approved' => (bool) $user->is_kyc_approved,
            'kyc_status' => $user->is_kyc_approved ? 'approved' : ($application?->status?->value ?? ($application?->status ?? 'pending')),
            'submitted_at' => $application?->submitted_at?->format('Y-m-d H:i') ?? $application?->created_at?->format('Y-m-d H:i'),
            'rejection_reason' => $application?->rejection_reason,
            'notes' => $application?->notes,
            'requirements' => [
                'Valid National Identification Card or Passport',
                'Official Commercial Registration or Trade License (if applicable)',
                'Proof of Operating Address or Utility Statement',
            ],
            'documents' => $documents,
        ]);
    }

    /**
     * Submit Verification Documents
     */
    public function submitVerification(Request $request): JsonResponse
    {
        $request->validate([
            'document_type' => ['nullable', 'string', 'max:100'],
            'document_number' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        $verification = UserVerification::firstOrCreate(
            ['user_id' => $user->id],
            [
                'status' => KycStatus::Pending ?? 'pending',
                'submitted_at' => now(),
            ]
        );

        $verification->update([
            'status' => KycStatus::Pending ?? 'pending',
            'submitted_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        $verification->documents()->create([
            'document_type' => $request->input('document_type', 'National ID'),
            'document_number' => $request->input('document_number'),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Verification details submitted successfully for review.',
        ]);
    }

    /**
     * 2. Subscription Plans
     */
    public function subscriptions(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription ?? $user->subscriptions()->latest()->first();
        $plan = $subscription?->plan;

        $currentPlan = [
            'name' => $plan?->name ?? 'Standard Seller',
            'status' => $subscription?->status?->value ?? ($subscription?->status ?? 'active'),
            'listing_credits' => (int) ($user->listing_credits ?? 0),
            'renews_at' => $subscription?->ends_at?->format('M d, Y') ?? 'Never',
            'price' => $plan ? (float) $plan->price : 0.0,
            'interval' => $plan?->interval ?? 'monthly',
        ];

        $availablePlans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('price')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'description' => $p->description,
                    'price' => (float) $p->price,
                    'currency' => 'QAR',
                    'interval' => $p->interval ?? 'monthly',
                    'listing_limit' => (int) ($p->listing_limit ?? 50),
                    'features' => is_array($p->features) ? $p->features : (json_decode($p->features ?? '[]', true) ?: [
                        'Priority marketplace listing',
                        'Standard seller commission rate',
                        'Verified store badge',
                        'Inventory analytics dashboard',
                    ]),
                ];
            });

        return response()->json([
            'current_plan' => $currentPlan,
            'plans' => $availablePlans,
        ]);
    }

    /**
     * 3. Payment Settings
     */
    public function paymentSettings(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = $user->paymentSettings()->firstOrCreate(['user_id' => $user->id]);

        return response()->json([
            'settings' => [
                'bank_name' => $settings->bank_name ?? '',
                'account_name' => $settings->account_name ?? ($user->name ?? ''),
                'account_number' => $settings->account_number ?? '',
                'iban' => $settings->iban ?? '',
                'swift_code' => $settings->swift_code ?? '',
                'bank_branch' => $settings->bank_branch ?? '',
                'manual_bank_enabled' => (bool) ($settings->manual_bank_enabled ?? true),
                'cod_enabled' => (bool) ($settings->cod_enabled ?? true),
                'stripe_enabled' => (bool) ($settings->stripe_enabled ?? false),
                'paypal_enabled' => (bool) ($settings->paypal_enabled ?? false),
            ],
        ]);
    }

    /**
     * Update Payment Settings
     */
    public function updatePaymentSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank_name' => ['nullable', 'string', 'max:150'],
            'account_name' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'iban' => ['nullable', 'string', 'max:100'],
            'swift_code' => ['nullable', 'string', 'max:50'],
            'bank_branch' => ['nullable', 'string', 'max:150'],
            'cod_enabled' => ['nullable', 'boolean'],
            'manual_bank_enabled' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $settings = $user->paymentSettings()->firstOrCreate(['user_id' => $user->id]);

        $settings->update([
            'bank_name' => $validated['bank_name'] ?? $settings->bank_name,
            'account_name' => $validated['account_name'] ?? $settings->account_name,
            'account_number' => $validated['account_number'] ?? $settings->account_number,
            'iban' => $validated['iban'] ?? $settings->iban,
            'swift_code' => $validated['swift_code'] ?? $settings->swift_code,
            'bank_branch' => $validated['bank_branch'] ?? $settings->bank_branch,
            'cod_enabled' => $request->has('cod_enabled') ? $request->boolean('cod_enabled') : $settings->cod_enabled,
            'manual_bank_enabled' => $request->has('manual_bank_enabled') ? $request->boolean('manual_bank_enabled') : $settings->manual_bank_enabled,
        ]);

        return response()->json([
            'message' => 'Payment and bank details updated successfully.',
            'settings' => $settings->fresh(),
        ]);
    }

    /**
     * 4. Support Tickets
     */
    public function supportTickets(Request $request): JsonResponse
    {
        $user = $request->user();
        $tickets = SupportTicket::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'ticket_number' => $t->ticket_number ?? ('#TKT-' . $t->id),
                    'subject' => $t->subject,
                    'category' => $t->category ?? 'General Inquiry',
                    'status' => $t->status?->value ?? ($t->status ?? 'open'),
                    'priority' => $t->priority ?? 'normal',
                    'created_at' => $t->created_at?->format('M d, Y H:i'),
                    'updated_at' => $t->updated_at?->format('M d, Y H:i'),
                ];
            });

        return response()->json([
            'tickets' => $tickets,
            'categories' => [
                'Order & Shipping Inquiry',
                'Payment & Payout Assistance',
                'Product Verification & Grading',
                'Account & KYC Verification',
                'Listing & Inventory Technical Issue',
                'Other Inquiries',
            ],
        ]);
    }

    /**
     * Create Support Ticket
     */
    public function storeSupportTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $user = $request->user();
        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'ticket_number' => 'TKT-' . strtoupper(bin2hex(random_bytes(3))),
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'status' => 'open',
            'priority' => 'normal',
        ]);

        if (method_exists($ticket, 'messages')) {
            $ticket->messages()->create([
                'user_id' => $user->id,
                'message' => $validated['body'],
            ]);
        }

        return response()->json([
            'message' => 'Support ticket created successfully. Our team will review and respond promptly.',
            'ticket' => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'status' => 'open',
            ],
        ], 201);
    }

    /**
     * 5. Inspection Queue
     */
    public function inspections(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = ProductVerification::query()
            ->where('seller_id', $user->id)
            ->with(['product', 'location', 'verifier'])
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_title' => $item->product?->title ?? 'Product #' . $item->product_id,
                    'product_sku' => $item->product?->sku ?? 'N/A',
                    'status' => $item->status?->value ?? ($item->status ?? 'pending'),
                    'location_name' => $item->location?->name ?? 'Main Hub Verification Station',
                    'verifier_name' => $item->verifier?->name ?? 'Assigned Verifier',
                    'scheduled_at' => $item->scheduled_at?->format('M d, Y h:i A'),
                    'inspected_at' => $item->inspected_at?->format('M d, Y h:i A'),
                    'notes' => $item->notes ?? $item->rejection_reason,
                    'created_at' => $item->created_at?->format('M d, Y'),
                ];
            });

        return response()->json([
            'inspections' => $items,
            'summary' => [
                'total' => $items->count(),
                'pending' => $items->where('status', 'pending')->count(),
                'scheduled' => $items->where('status', 'scheduled')->count(),
                'verified' => $items->where('status', 'verified')->count(),
            ],
        ]);
    }

    /**
     * 6. Warehouse Deposits
     */
    public function warehouseDeposits(Request $request): JsonResponse
    {
        $user = $request->user();

        $deposits = $user->warehouseDeposits()
            ->with(['product', 'warehouse'])
            ->latest()
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'product_id' => $d->product_id,
                    'product_title' => $d->product?->title ?? 'Product #' . $d->product_id,
                    'product_sku' => $d->product?->sku ?? 'N/A',
                    'warehouse_name' => $d->warehouse?->name ?? 'Central Fulfillment Hub',
                    'quantity' => (int) ($d->quantity ?? 1),
                    'status' => $d->status ?? 'pending',
                    'tracking_number' => $d->tracking_number ?? ('#WHD-' . $d->id),
                    'created_at' => $d->created_at?->format('M d, Y H:i'),
                ];
            });

        $eligibleProducts = $user->products()
            ->select('id', 'title', 'sku', 'quantity')
            ->orderBy('title')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'sku' => $p->sku,
                'available_quantity' => $p->quantity,
            ]);

        $warehouses = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($w) => [
                'id' => $w->id,
                'name' => $w->name,
                'address' => $w->address ?? 'Central Hub',
            ]);

        return response()->json([
            'deposits' => $deposits,
            'eligible_products' => $eligibleProducts,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Store Warehouse Deposit Request
     */
    public function storeWarehouseDeposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'warehouse_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $product = $user->products()->findOrFail($validated['product_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        $deposit = WarehouseProduct::create([
            'seller_id' => $user->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $validated['quantity'],
            'status' => 'pending',
            'tracking_number' => 'WHD-' . strtoupper(bin2hex(random_bytes(3))),
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Warehouse deposit request submitted successfully.',
            'deposit' => [
                'id' => $deposit->id,
                'tracking_number' => $deposit->tracking_number,
                'status' => 'pending',
            ],
        ], 201);
    }

    /**
     * 7. Manage Inventory
     */
    public function inventory(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $user->products();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $allProducts = $user->products()->get();
        $totalItems = $allProducts->count();
        $inStock = $allProducts->where('quantity', '>', 5)->count();
        $lowStock = $allProducts->where('quantity', '>', 0)->where('quantity', '<=', 5)->count();
        $outOfStock = $allProducts->where('quantity', '<=', 0)->count();

        $items = $query->latest()->get()->map(function ($p) {
            $qty = (int) $p->quantity;
            $stockStatus = $qty <= 0 ? 'out_of_stock' : ($qty <= 5 ? 'low_stock' : 'in_stock');

            return [
                'id' => $p->id,
                'title' => $p->title,
                'sku' => $p->sku ?? 'N/A',
                'price' => (float) $p->price,
                'quantity' => $qty,
                'stock_status' => $stockStatus,
                'status' => $p->status?->value ?? ($p->status ?? 'published'),
                'thumbnail' => $p->thumbnail ? url(Storage::url($p->thumbnail)) : null,
                'updated_at' => $p->updated_at?->format('M d, Y'),
            ];
        });

        return response()->json([
            'inventory' => $items,
            'summary' => [
                'total_products' => $totalItems,
                'in_stock' => $inStock,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
            ],
        ]);
    }

    /**
     * Quick Update Inventory (Quantity & Price)
     */
    public function updateInventory(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();
        if ($product->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized action on product.'], 403);
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $product->update([
            'quantity' => $validated['quantity'],
            'price' => $validated['price'] ?? $product->price,
        ]);

        return response()->json([
            'message' => 'Inventory updated successfully.',
            'product' => [
                'id' => $product->id,
                'quantity' => (int) $product->quantity,
                'price' => (float) $product->price,
            ],
        ]);
    }
}
