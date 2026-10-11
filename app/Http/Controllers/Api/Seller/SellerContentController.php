<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerContentController extends Controller
{
    /**
     * Get Seller Help, Policies, and Guidelines.
     */
    public function helpGuidelines(Request $request): JsonResponse
    {
        return response()->json([
            'title' => 'Seller Help & Guidelines',
            'last_updated' => 'October 2026',
            'support_email' => 'support@openboxo.com',
            'support_phone' => '+974 4400 0000',
            'sections' => [
                [
                    'id' => 'onboarding',
                    'title' => '1. Store Onboarding & KYC Verification',
                    'icon' => 'verified_user',
                    'content' => 'All sellers must complete KYC identity verification with a valid National ID or Commercial Registration before listings are published. Store profile information, business logo, and contact details must remain authentic and up to date.',
                ],
                [
                    'id' => 'catalog_rules',
                    'title' => '2. Product Listing & Grading Standards',
                    'icon' => 'inventory_2',
                    'content' => 'Upload clear, high-resolution photos taken from multiple angles on a clean background. Accurately assign product conditions: Brand New, Like New, Good, or Fair. Counterfeit, replica, or illegally obtained items result in immediate permanent store ban.',
                ],
                [
                    'id' => 'fulfillment',
                    'title' => '3. Order Fulfillment & Shipping SLAs',
                    'icon' => 'local_shipping',
                    'content' => 'Confirm and process incoming customer orders within 24 hours. When marking an order as Shipped, you must provide a verifiable courier tracking number. Delayed shipments negatively impact store ratings and listing visibility.',
                ],
                [
                    'id' => 'payouts',
                    'title' => '4. Wallet, Commissions & Payouts',
                    'icon' => 'account_balance_wallet',
                    'content' => 'Completed orders credit your Available Wallet after the customer inspection window expires. Payout requests can be submitted anytime once the minimum threshold (QAR 50.00) is reached, and are processed within 2-3 business days.',
                ],
                [
                    'id' => 'returns',
                    'title' => '5. Customer Returns & Dispute Resolution',
                    'icon' => 'assignment_return',
                    'content' => 'Sellers must honor buyer protection for items that arrive defective or misdescribed. Openbox customer dispute mediation is binding on all platform merchants.',
                ],
            ],
            'faqs' => [
                [
                    'question' => 'How long does KYC verification take?',
                    'answer' => 'KYC submissions are typically reviewed by our compliance team within 24-48 business hours.',
                ],
                [
                    'question' => 'What happens if a customer requests a return?',
                    'answer' => 'You will be notified immediately. If the returned item matches the claim (damaged or incorrect), the refund is processed and the seller balance is adjusted.',
                ],
                [
                    'question' => 'How can I increase my product sales?',
                    'answer' => 'Ensure competitive pricing, offer detailed specs, maintain prompt dispatch times, and maintain a 4.5+ star seller rating.',
                ],
            ],
        ]);
    }

    /**
     * Get Vendor Terms of Service agreement.
     */
    public function termsOfService(Request $request): JsonResponse
    {
        $cmsPage = Page::published()->whereIn('slug', ['vendor-terms', 'terms-of-service'])->first();
        $rawHtml = $cmsPage?->content;
        $plainText = $rawHtml ? trim(html_entity_decode(strip_tags(str_replace(['<p>', '<br>', '</li>', '</div>'], "\n", $rawHtml)))) : null;

        return response()->json([
            'title' => $cmsPage?->title ?? 'Vendor Terms of Service',
            'last_updated' => $cmsPage?->updated_at?->format('F d, Y') ?? 'October 2026',
            'summary' => 'These Vendor Terms govern your participation as a merchant on the Openbox Marketplace platform.',
            'content' => $plainText,
            'sections' => [
                [
                    'title' => '1. Acceptance of Terms',
                    'body' => 'By registering as a seller or offering products on Openbox, you agree to comply with and be bound by these Vendor Terms of Service and all related platform policies.',
                ],
                [
                    'title' => '2. Seller Representations & Warranties',
                    'body' => 'You represent that you have legal authority to sell all listed items, that products are genuine, free from liens, and comply with all applicable import and consumer protection regulations.',
                ],
                [
                    'title' => '3. Marketplace Fees & Payments',
                    'body' => 'Openbox charges a transparent commission fee per completed transaction. Payouts are remitted to your verified bank account or digital payment gateway per the published schedule.',
                ],
                [
                    'title' => '4. Fulfillment & Customer Service',
                    'body' => 'You are responsible for safely packaging, handling, and shipping orders within platform timeframes, and providing accurate tracking information.',
                ],
                [
                    'title' => '5. Suspension & Account Termination',
                    'body' => 'Openbox reserves the right to suspend or terminate accounts that breach quality standards, list restricted items, engage in fraudulent transactions, or accumulate excessive customer complaints.',
                ],
                [
                    'title' => '6. Governing Law & Dispute Resolution',
                    'body' => 'These terms are governed by the laws of Qatar. Any disputes arising in connection with these terms shall be resolved under the competent courts of Qatar.',
                ],
            ],
        ]);
    }
}
