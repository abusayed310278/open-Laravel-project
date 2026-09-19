<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

class CmsPagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Help Center',
                'slug' => 'help-center',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-lg font-medium text-gray-800">Welcome to the Openbox Help Center. Find answers to common questions about buying, selling, and account verification.</p>
                        
                        <div class="grid md:grid-cols-2 gap-6 mt-6">
                            <div class="p-5 border border-gray-200 rounded-lg bg-white shadow-xs">
                                <h3 class="text-base font-bold text-gray-900 mb-2">📦 Buying on Openbox</h3>
                                <p class="text-sm text-gray-600">Learn about our device grading standards, doorstep inspection process, and escrow protection before completing your purchase.</p>
                            </div>
                            <div class="p-5 border border-gray-200 rounded-lg bg-white shadow-xs">
                                <h3 class="text-base font-bold text-gray-900 mb-2">🏷️ Selling & Warehousing</h3>
                                <p class="text-sm text-gray-600">Discover how to register as an individual seller or store owner, deposit devices into our certified warehouses, and get paid fast.</p>
                            </div>
                        </div>

                        <div class="mt-8 space-y-4">
                            <h3 class="text-lg font-bold text-gray-900">Frequently Asked Questions</h3>
                            <details class="p-4 border border-gray-200 rounded-lg bg-gray-50/50">
                                <summary class="font-semibold text-gray-800 cursor-pointer">How does Openbox verify electronic devices?</summary>
                                <p class="mt-2 text-sm text-gray-600">Every device passes a 40-point technical checklist conducted by certified verifiers at our designated inspection hubs.</p>
                            </details>
                            <details class="p-4 border border-gray-200 rounded-lg bg-gray-50/50">
                                <summary class="font-semibold text-gray-800 cursor-pointer">What is the payment protection policy?</summary>
                                <p class="mt-2 text-sm text-gray-600">Your payments are held securely in escrow until you inspect and approve your received item.</p>
                            </details>
                        </div>
                    </div>
                ',
                'meta_title' => 'Help Center & Knowledge Base — Openbox',
                'meta_description' => 'Find guides, FAQs, and support articles on buying, selling, and verifying electronics on Openbox.',
                'status' => ContentStatus::Published,
            ],
            [
                'title' => 'Track Order',
                'slug' => 'track-order',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-lg font-medium text-gray-800">Track your electronics orders in real-time from verification to doorstep delivery.</p>
                        
                        <div class="p-6 border border-brand-200 rounded-xl bg-brand-50/20">
                            <h3 class="text-base font-bold text-gray-900 mb-2">How to Track Your Order</h3>
                            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                                <li>Log in to your Openbox account and visit your <strong>My Orders</strong> page.</li>
                                <li>Select the specific order number to view detailed status updates.</li>
                                <li>Follow status progression: <em>Order Confirmed &rarr; Inspection &amp; Grading &rarr; Warehoused &rarr; Out for Delivery</em>.</li>
                            </ol>
                        </div>

                        <div class="p-5 border border-gray-200 rounded-lg bg-white">
                            <h4 class="font-bold text-gray-800 mb-1">Need live updates?</h4>
                            <p class="text-sm text-gray-600">You will also receive SMS and email notifications whenever your package passes verification checkpoints.</p>
                        </div>
                    </div>
                ',
                'meta_title' => 'Track Order — Openbox',
                'meta_description' => 'Check the real-time status of your Openbox order and delivery progress.',
                'status' => ContentStatus::Published,
            ],
            [
                'title' => 'Returns & Refund Policy',
                'slug' => 'returns-refund-policy',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-lg font-medium text-gray-800">We offer a transparent 7-Day Money-Back Guarantee and doorstep inspection assurance.</p>

                        <div class="space-y-4 text-sm text-gray-700">
                            <h3 class="text-base font-bold text-gray-900">1. Eligibility for Returns</h3>
                            <p>Items are eligible for return if the delivered device differs from its listed grade, suffers hardware defects unmentioned in the inspection checklist, or fails during the initial 7-day window.</p>

                            <h3 class="text-base font-bold text-gray-900">2. Doorstep Inspection Guarantee</h3>
                            <p>Upon delivery, you have the right to unpack and inspect the device in front of the delivery agent before finalizing acceptance.</p>

                            <h3 class="text-base font-bold text-gray-900">3. Refund Timeline</h3>
                            <p>Approved refunds are processed within 24–48 hours back to your original payment method (Bank Transfer, Mobile Wallet, or Card).</p>
                        </div>
                    </div>
                ',
                'meta_title' => 'Returns & Refund Policy — Openbox',
                'meta_description' => 'Read Openbox’s 7-day return policy, doorstep inspection guarantee, and refund procedures.',
                'status' => ContentStatus::Published,
            ],
            [
                'title' => 'Escrow Protection',
                'slug' => 'escrow-protection',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-lg font-medium text-gray-800">Your funds stay 100% safe in Openbox Escrow Vault until you verify and receive your item.</p>

                        <div class="grid md:grid-cols-3 gap-5">
                            <div class="p-4 border border-gray-200 rounded-lg bg-white text-center">
                                <div class="text-2xl font-bold text-brand-600 mb-1">1. Deposit</div>
                                <p class="text-xs text-gray-600">Buyer pays into Openbox secure escrow account.</p>
                            </div>
                            <div class="p-4 border border-gray-200 rounded-lg bg-white text-center">
                                <div class="text-2xl font-bold text-brand-600 mb-1">2. Verify</div>
                                <p class="text-xs text-gray-600">Certified verifier checks physical hardware &amp; specs.</p>
                            </div>
                            <div class="p-4 border border-gray-200 rounded-lg bg-white text-center">
                                <div class="text-2xl font-bold text-brand-600 mb-1">3. Release</div>
                                <p class="text-xs text-gray-600">Funds are released to seller only after buyer acceptance.</p>
                            </div>
                        </div>
                    </div>
                ',
                'meta_title' => 'Escrow Protection & Buyer Guarantee — Openbox',
                'meta_description' => 'Learn how Openbox Escrow Vault protects buyer payments until delivery and verification.',
                'status' => ContentStatus::Published,
            ],
            [
                'title' => 'Contact Support',
                'slug' => 'contact-support',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-lg font-medium text-gray-800">Our customer support team is available 7 days a week to assist you.</p>

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div class="p-5 border border-gray-200 rounded-lg bg-white">
                                <h4 class="font-bold text-gray-900 mb-2">📞 Phone Support</h4>
                                <p class="text-sm text-gray-600">+880 9610-000111</p>
                                <p class="text-xs text-gray-400 mt-1">Everyday: 9:00 AM – 9:00 PM</p>
                            </div>
                            <div class="p-5 border border-gray-200 rounded-lg bg-white">
                                <h4 class="font-bold text-gray-900 mb-2">✉️ Email Support</h4>
                                <p class="text-sm text-gray-600">support@openbox.com.bd</p>
                                <p class="text-xs text-gray-400 mt-1">Average response time: &lt; 2 hours</p>
                            </div>
                        </div>

                        <div class="p-5 border border-brand-200 rounded-lg bg-brand-50/20">
                            <h4 class="font-bold text-gray-900 mb-1">💬 In-App Support Tickets</h4>
                            <p class="text-sm text-gray-600">Log in to your account to open a support ticket or chat directly with our support team in real-time.</p>
                        </div>
                    </div>
                ',
                'meta_title' => 'Contact Support — Openbox',
                'meta_description' => 'Get in touch with Openbox customer service via phone, email, or live chat support.',
                'status' => ContentStatus::Published,
            ],
            [
                'title' => 'About Openbox',
                'slug' => 'about-openbox',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-lg font-medium text-gray-800">Openbox is Bangladesh’s first verified electronics marketplace connecting buyers, individual sellers, and retail stores with certified quality assurance.</p>

                        <div class="prose max-w-none text-gray-700 space-y-4">
                            <h3 class="text-xl font-bold text-gray-900">Our Mission</h3>
                            <p>To eliminate uncertainty in the secondhand and refurbished electronics market through transparent grading, certified physical inspections, and escrow-backed transactions.</p>

                            <h3 class="text-xl font-bold text-gray-900">Why Openbox?</h3>
                            <ul class="list-disc list-inside space-y-2">
                                <li><strong>100% Inspected Devices:</strong> No ghost listings or inaccurate descriptions.</li>
                                <li><strong>Central Warehousing:</strong> Products stored safely in climate-controlled hubs.</li>
                                <li><strong>Buyer Escrow Protection:</strong> Full payment security for every transaction.</li>
                            </ul>
                        </div>
                    </div>
                ',
                'meta_title' => 'About Openbox — Bangladesh Verified Electronics Marketplace',
                'meta_description' => 'Learn about Openbox mission, certified grading system, and verified electronics marketplace.',
                'status' => ContentStatus::Published,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-lg font-medium text-gray-800">Please read these Terms of Service carefully before using the Openbox platform.</p>

                        <div class="space-y-4 text-sm text-gray-700">
                            <h3 class="text-base font-bold text-gray-900">1. Account Terms</h3>
                            <p>You must provide accurate account information and maintain the security of your credentials. Users under 18 must have parental or legal guardian consent.</p>

                            <h3 class="text-base font-bold text-gray-900">2. Seller Obligations</h3>
                            <p>All devices listed for sale must belong to the seller lawfully and accurately match the condition disclosed during verification submission.</p>

                            <h3 class="text-base font-bold text-gray-900">3. Verification &amp; Grading</h3>
                            <p>Openbox reserves the right to assign or adjust device grades based on physical and technical inspection findings.</p>
                        </div>
                    </div>
                ',
                'meta_title' => 'Terms of Service — Openbox',
                'meta_description' => 'Review the official Terms of Service governing Openbox platform usage and sales.',
                'status' => ContentStatus::Published,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-lg font-medium text-gray-800">Openbox is committed to protecting your personal data and privacy.</p>

                        <div class="space-y-4 text-sm text-gray-700">
                            <h3 class="text-base font-bold text-gray-900">Information We Collect</h3>
                            <p>We collect essential account information (name, phone, email, address), verification details for sellers, and transaction history required to process orders securely.</p>

                            <h3 class="text-base font-bold text-gray-900">Data Security</h3>
                            <p>All sensitive credentials and personal data are encrypted at rest and transmitted over secure SSL/TLS channels.</p>
                        </div>
                    </div>
                ',
                'meta_title' => 'Privacy Policy — Openbox',
                'meta_description' => 'Understand how Openbox collects, uses, and safeguards your personal data.',
                'status' => ContentStatus::Published,
            ],
            [
                'title' => 'Trust & Safety',
                'slug' => 'trust-safety',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-lg font-medium text-gray-800">Safety and authenticity are at the core of everything we build at Openbox.</p>

                        <div class="grid md:grid-cols-2 gap-5">
                            <div class="p-5 border border-gray-200 rounded-lg bg-white">
                                <h4 class="font-bold text-gray-900 mb-1">🛡️ Anti-Fraud Guarantee</h4>
                                <p class="text-sm text-gray-600">Serial numbers and IMEIs are cross-referenced before approval to ensure legitimate ownership.</p>
                            </div>
                            <div class="p-5 border border-gray-200 rounded-lg bg-white">
                                <h4 class="font-bold text-gray-900 mb-1">🔍 Certified Inspectors</h4>
                                <p class="text-sm text-gray-600">Verifiers undergo background checks and rigorous technical training.</p>
                            </div>
                        </div>
                    </div>
                ',
                'meta_title' => 'Trust & Safety — Openbox',
                'meta_description' => 'Discover our security standards, IMEI verification, and anti-fraud guarantees.',
                'status' => ContentStatus::Published,
            ],
            [
                'title' => 'Careers',
                'slug' => 'careers',
                'content' => '
                    <div class="space-y-6">
                        <p class="text-lg font-medium text-gray-800">Join Openbox and help us reshape the future of verified recommerce in South Asia.</p>

                        <div class="p-6 border border-gray-200 rounded-xl bg-white space-y-4">
                            <h3 class="text-lg font-bold text-gray-900">Open Positions</h3>
                            <div class="p-4 border border-gray-100 rounded-lg flex justify-between items-center bg-gray-50/50">
                                <div>
                                    <h4 class="font-bold text-gray-800">Electronics Verification Engineer</h4>
                                    <p class="text-xs text-gray-500">Dhaka Hub &bull; Full-time</p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold bg-brand-100 text-brand-700 rounded-full">Apply Now</span>
                            </div>
                            <div class="p-4 border border-gray-100 rounded-lg flex justify-between items-center bg-gray-50/50">
                                <div>
                                    <h4 class="font-bold text-gray-800">Full-Stack Laravel Developer</h4>
                                    <p class="text-xs text-gray-500">Remote / Dhaka &bull; Full-time</p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold bg-brand-100 text-brand-700 rounded-full">Apply Now</span>
                            </div>
                        </div>
                    </div>
                ',
                'meta_title' => 'Careers at Openbox',
                'meta_description' => 'Explore career opportunities and open roles at Openbox.',
                'status' => ContentStatus::Published,
            ],
        ];

        foreach ($pages as $data) {
            Page::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
