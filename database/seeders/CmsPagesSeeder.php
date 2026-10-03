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
<div class="space-y-8 text-sm text-gray-700 leading-relaxed">
    <div class="p-6 rounded-2xl bg-gradient-to-r from-brand-50 to-amber-50/50 border border-brand-100 text-gray-900">
        <h2 class="text-xl font-extrabold text-gray-950 mb-2">Welcome to Openbox Support & Knowledge Base</h2>
        <p class="text-gray-600">Everything you need to know about buying certified electronics, selling securely with escrow protection, and understanding our 40-point verification standards in Bangladesh.</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="p-5 border border-gray-200/80 rounded-xl bg-white shadow-xs space-y-2">
            <div class="w-10 h-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-lg mb-2">📦</div>
            <h3 class="text-base font-bold text-gray-900">Buyer Guide & Doorstep Inspection</h3>
            <p class="text-xs text-gray-600 leading-normal">
                Every smartphone, laptop, and smartwatch listed on Openbox is certified with an official condition grade (Like New, Excellent, Good, or Fair). When your package arrives, you receive full rights to perform a doorstep inspection before approving payment release from our Escrow Vault.
            </p>
        </div>

        <div class="p-5 border border-gray-200/80 rounded-xl bg-white shadow-xs space-y-2">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg mb-2">🏷️</div>
            <h3 class="text-base font-bold text-gray-900">Seller Portal & Warehousing</h3>
            <p class="text-xs text-gray-600 leading-normal">
                Individual sellers and verified store owners can list devices, schedule drop-offs at our certified inspection locations, and store verified inventory in secure Openbox custody warehouses. Payouts are transferred automatically to your bKash, Nagad, or Bank account upon buyer acceptance.
            </p>
        </div>

        <div class="p-5 border border-gray-200/80 rounded-xl bg-white shadow-xs space-y-2">
            <div class="w-10 h-10 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-lg mb-2">🔍</div>
            <h3 class="text-base font-bold text-gray-900">40-Point Diagnostic Checklist</h3>
            <p class="text-xs text-gray-600 leading-normal">
                Our certified hardware technicians inspect display touch response, camera sensors, biometric security (Face ID/Fingerprint), speaker acoustics, microphone input, charging port integrity, battery health cycles, and anti-theft IMEI cross-verification.
            </p>
        </div>

        <div class="p-5 border border-gray-200/80 rounded-xl bg-white shadow-xs space-y-2">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg mb-2">🛡️</div>
            <h3 class="text-base font-bold text-gray-900">Escrow Vault & 7-Day Guarantee</h3>
            <p class="text-xs text-gray-600 leading-normal">
                Your payment never goes directly to an unverified stranger. It is deposited into our protected Escrow Vault and held securely. If the device does not match the certified checklist report, our 7-Day Money-Back Guarantee ensures a swift refund.
            </p>
        </div>
    </div>

    <section class="space-y-4">
        <h3 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">Frequently Asked Questions (FAQ)</h3>
        
        <div class="space-y-3">
            <details class="group p-4 border border-gray-200 rounded-xl bg-gray-50/50 open:bg-white open:shadow-xs transition-colors">
                <summary class="font-semibold text-gray-900 cursor-pointer list-none flex justify-between items-center">
                    <span>How does Openbox verify device condition and authenticity?</span>
                    <span class="text-gray-400 group-open:rotate-180 transition-transform">&darr;</span>
                </summary>
                <div class="mt-3 text-xs text-gray-600 space-y-1.5 leading-relaxed">
                    <p>When a seller submits a device, it is physically delivered to one of our certified inspection locations. Verifier engineers run comprehensive hardware tests and cross-examine the serial number and IMEI against official manufacturer warranty logs and anti-theft databases.</p>
                </div>
            </details>

            <details class="group p-4 border border-gray-200 rounded-xl bg-gray-50/50 open:bg-white open:shadow-xs transition-colors">
                <summary class="font-semibold text-gray-900 cursor-pointer list-none flex justify-between items-center">
                    <span>What is the Doorstep Inspection procedure?</span>
                    <span class="text-gray-400 group-open:rotate-180 transition-transform">&darr;</span>
                </summary>
                <div class="mt-3 text-xs text-gray-600 space-y-1.5 leading-relaxed">
                    <p>When the delivery rider arrives with your order, you are given up to 10 minutes to unbox and power on the device, test basic touchscreen response, and verify the physical condition against the inspection checklist printed on the packaging before confirming acceptance.</p>
                </div>
            </details>

            <details class="group p-4 border border-gray-200 rounded-xl bg-gray-50/50 open:bg-white open:shadow-xs transition-colors">
                <summary class="font-semibold text-gray-900 cursor-pointer list-none flex justify-between items-center">
                    <span>How does the Escrow Payment Vault work?</span>
                    <span class="text-gray-400 group-open:rotate-180 transition-transform">&darr;</span>
                </summary>
                <div class="mt-3 text-xs text-gray-600 space-y-1.5 leading-relaxed">
                    <p>Buyers pay using bKash, Nagad, debit/credit cards, or bank transfer. The funds are held in our designated escrow account. Only after the buyer accepts delivery and the 7-day initial inspection window concludes does Openbox disburse payment to the seller\'s balance.</p>
                </div>
            </details>

            <details class="group p-4 border border-gray-200 rounded-xl bg-gray-50/50 open:bg-white open:shadow-xs transition-colors">
                <summary class="font-semibold text-gray-900 cursor-pointer list-none flex justify-between items-center">
                    <span>How do sellers withdraw their earnings?</span>
                    <span class="text-gray-400 group-open:rotate-180 transition-transform">&darr;</span>
                </summary>
                <div class="mt-3 text-xs text-gray-600 space-y-1.5 leading-relaxed">
                    <p>Sellers can submit a payout request anytime through their Seller Dashboard under <em>Commerce &rarr; Payout Requests</em>. Payouts to bKash and Nagad are processed within 12–24 business hours, and electronic bank transfers (BEFTN/NPSB) settle within 1–2 banking days.</p>
                </div>
            </details>
        </div>
    </section>

    <div class="p-5 border border-brand-200 rounded-xl bg-brand-50/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h4 class="font-bold text-gray-900 text-sm">Still have questions or need assistance?</h4>
            <p class="text-xs text-gray-600">Our customer care team is available daily from 9:00 AM to 9:00 PM.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="mailto:support@openbox.com.bd" class="px-4 py-2 text-xs font-semibold text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors shadow-xs">Email Support</a>
            <a href="tel:+8809610000111" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors shadow-xs">Call Helpline</a>
        </div>
    </div>
</div>
                ',
                'meta_title' => 'Help Center & Knowledge Base — Openbox Bangladesh',
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
<div class="space-y-8 text-sm text-gray-700 leading-relaxed">
    <div class="p-5 rounded-xl bg-gray-50 border border-gray-200 text-xs text-gray-800">
        <strong>Last Updated: October 2026</strong>. Please read these Terms of Service ("Terms") carefully. By creating an account, browsing, buying, or listing items on Openbox ("Platform", "we", "us"), you agree to be bound by these Terms and our related operating policies.
    </div>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">1. The Openbox Marketplace & Role Architecture</h2>
        <p>Openbox operates an authenticated multi-role recommerce ecosystem connecting Buyers, Individual Sellers ("Salers"), Commercial Store Owners ("Business Sellers"), and certified Verifier Staff.</p>
        <ul class="list-disc list-inside space-y-1.5 pl-2 text-xs">
            <li><strong>Buyers:</strong> Individuals purchasing pre-owned, open-box, or refurbished electronics protected by our Escrow Vault and Doorstep Inspection guarantee.</li>
            <li><strong>Sellers:</strong> Must undergo Identity (NID) and KYC verification before publishing product listings or depositing hardware.</li>
            <li><strong>Verification Staff:</strong> Certified technical engineers who physically inspect, test, and assign condition grades to devices at designated hubs.</li>
        </ul>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">2. Hardware Inspection & Official Grading Standards</h2>
        <p>All items sold through Openbox undergo an obligatory 40-point technical checklist. Openbox assigns one of the following official condition tiers:</p>
        <div class="grid sm:grid-cols-2 gap-3 text-xs my-2">
            <div class="p-3 border border-emerald-200 rounded-lg bg-emerald-50/50">
                <span class="font-bold text-emerald-950 block">Grade A+ (Like New)</span>
                Flawless cosmetic condition, zero visible scratches, 90%+ battery health, all original hardware components functioning as new.
            </div>
            <div class="p-3 border border-sky-200 rounded-lg bg-sky-50/50">
                <span class="font-bold text-sky-950 block">Grade A (Excellent)</span>
                Minimal micro-scratches invisible from arm\'s length, 85%+ battery health, 100% verified functional components.
            </div>
            <div class="p-3 border border-amber-200 rounded-lg bg-amber-50/50">
                <span class="font-bold text-amber-950 block">Grade B (Good)</span>
                Normal signs of light wear or subtle scuffs, 80%+ battery health, fully tested hardware with zero functional defects.
            </div>
            <div class="p-3 border border-gray-300 rounded-lg bg-gray-100/70">
                <span class="font-bold text-gray-900 block">Grade C (Fair)</span>
                Noticeable cosmetic wear, denting, or replaced screens/batteries fully disclosed on the inspection certificate.
            </div>
        </div>
        <p class="text-xs text-gray-600">Openbox reserves full authority to downgrade or reject any submitted device that fails battery cycle testing, exhibits motherboard tampering, or fails anti-theft IMEI verification.</p>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">3. Escrow Vault Payment & Settlement Terms</h2>
        <p>To eliminate scams and non-delivery, all buyer payments are deposited directly into the Openbox Escrow Vault via SSLCommerz, bKash, Nagad, or approved banking channels.</p>
        <ul class="list-disc list-inside space-y-1.5 pl-2 text-xs">
            <li><strong>Release Trigger:</strong> Escrow funds are only released to the seller after the buyer accepts doorstep delivery and the 7-day return guarantee expires.</li>
            <li><strong>Platform Commission:</strong> Openbox deducts an agreed platform commission per transaction according to the seller\'s subscription tier or commission rule table.</li>
            <li><strong>Payouts:</strong> Sellers can request wallet disbursements to verified bKash, Nagad, or bank accounts once funds clear the escrow hold period.</li>
        </ul>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">4. Doorstep Inspection & 7-Day Money-Back Guarantee</h2>
        <p>Buyers are entitled to 10 minutes of physical inspection upon delivery by our authorized courier partner. If the device differs from the certified condition report, the buyer may reject the parcel on the spot at zero penalty.</p>
        <p>Following delivery, buyers retain a <strong>7-Day Warranty Window</strong> against internal hardware defects (motherboard failure, unexpected shutdowns, speaker failure) not documented in the original inspection report.</p>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">5. Anti-Theft, Stolen Property & Law Enforcement Cooperation</h2>
        <p>Openbox maintains a zero-tolerance policy against stolen electronics, counterfeit items, and blacklisted IMEIs. All serial numbers are recorded in our audit database. Any user attempting to list stolen hardware will face immediate account termination and reporting to the Bangladesh Police Criminal Investigation Department (CID).</p>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">6. Governing Law & Dispute Resolution</h2>
        <p>These Terms are governed by and construed in accordance with the laws of the People\'s Republic of Bangladesh. Any dispute arising out of or in connection with this platform shall be subject to the exclusive jurisdiction of the competent courts of Dhaka, Bangladesh.</p>
    </section>
</div>
                ',
                'meta_title' => 'Terms of Service — Openbox Bangladesh',
                'meta_description' => 'Review the official Terms of Service governing Openbox platform usage, device grading, and escrow protection.',
                'status' => ContentStatus::Published,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '
<div class="space-y-8 text-sm text-gray-700 leading-relaxed">
    <div class="p-5 rounded-xl bg-gray-50 border border-gray-200 text-xs text-gray-800">
        <strong>Privacy Commitment:</strong> Openbox ("we", "us", "our") is dedicated to maintaining the confidentiality, integrity, and security of your personal data. This Privacy Policy details the information we collect, how it is used to safeguard transactions, and your rights under the Information and Communication Technology (ICT) Act of Bangladesh.
    </div>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">1. Information We Collect</h2>
        <div class="grid sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 border border-gray-200 rounded-lg bg-white space-y-1">
                <span class="font-bold text-gray-900 block">Personal Profile Data</span>
                <p class="text-gray-600">Full name, verified mobile number, email address, physical delivery addresses, and login credentials.</p>
            </div>
            <div class="p-4 border border-gray-200 rounded-lg bg-white space-y-1">
                <span class="font-bold text-gray-900 block">Seller KYC & Identity Proof</span>
                <p class="text-gray-600">National ID (NID) photos, Trade Licenses (for store owners), utility bills for location verification, and store photos.</p>
            </div>
            <div class="p-4 border border-gray-200 rounded-lg bg-white space-y-1">
                <span class="font-bold text-gray-900 block">Device Hardware Telemetry</span>
                <p class="text-gray-600">IMEI numbers, device serial numbers, battery health percentages, component diagnostics, and model identifiers recorded during hub inspection.</p>
            </div>
            <div class="p-4 border border-gray-200 rounded-lg bg-white space-y-1">
                <span class="font-bold text-gray-900 block">Financial & Escrow Records</span>
                <p class="text-gray-600">bKash/Nagad wallet numbers, bank account numbers for payouts, transaction IDs, invoice logs, and escrow authorization timestamps.</p>
            </div>
        </div>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">2. How We Use Your Information</h2>
        <ul class="list-disc list-inside space-y-1.5 pl-2 text-xs">
            <li><strong>Order Fulfillment & Delivery:</strong> Dispatching verified devices with authorized couriers for doorstep inspection.</li>
            <li><strong>Anti-Theft Protection:</strong> Cross-referencing IMEI numbers against lost/stolen device registries to safeguard buyers.</li>
            <li><strong>Escrow Vault Management:</strong> Securing payments until physical item verification and buyer sign-off.</li>
            <li><strong>Seller KYC Auditing:</strong> Preventing fictitious commercial listings and establishing legitimate recommerce channels.</li>
            <li><strong>Security Alerts:</strong> Sending SMS OTP verification codes, order status updates, and login notices.</li>
        </ul>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">3. Third-Party Data Sharing & Protection</h2>
        <p>We do not sell, rent, or trade your personal data to marketing third parties. Disclosures occur strictly on a need-to-know operational basis:</p>
        <ul class="list-disc list-inside space-y-1.5 pl-2 text-xs text-gray-600">
            <li><strong>Logistics Partners:</strong> Sharing recipient name, contact number, and address strictly for doorstep delivery and inspection.</li>
            <li><strong>Payment Gateways (bKash, Nagad, SSLCommerz):</strong> Processing encrypted checkout transactions and payout disbursements.</li>
            <li><strong>Law Enforcement & Regulators:</strong> Complying with court orders, Bangladesh BTRC directives, or CID anti-fraud investigations.</li>
        </ul>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">4. Data Encryption & Security Measures</h2>
        <p>All user communication is encrypted via 256-bit SSL/TLS protocol in transit. Sensitive credentials and passwords are encrypted using one-way cryptographic hashing algorithms. Our database infrastructure is maintained behind secured firewalls with role-based internal access controls.</p>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">5. Your Rights & Account Deletion</h2>
        <p>You possess full authority over your personal information, including the right to access your stored records, rectify outdated details, and request complete account termination under our <a href="/pages/delete-policy" class="text-brand-600 underline font-medium hover:text-brand-700">Account & Data Deletion Policy</a>.</p>
    </section>

    <section class="space-y-2 pt-2 border-t border-gray-100 text-xs">
        <h3 class="font-bold text-gray-900">Privacy Inquiries</h3>
        <p class="text-gray-600">If you have inquiries regarding our data processing policies, please email our Data Protection Officer at <strong>privacy@openbox.com.bd</strong>.</p>
    </section>
</div>
                ',
                'meta_title' => 'Privacy Policy — Openbox Bangladesh',
                'meta_description' => 'Understand how Openbox collects, uses, and safeguards your personal data under Bangladesh regulations.',
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
            [
                'title' => 'Account & Data Deletion Policy',
                'slug' => 'delete-policy',
                'content' => '
<div class="space-y-8 text-sm text-gray-700 leading-relaxed">
    <div class="p-5 rounded-xl bg-rose-50/70 border border-rose-100 text-xs text-rose-950">
        <strong>Right to Erasure:</strong> Openbox respects your right to control your digital footprint. This Account & Data Deletion Policy explains our transparent process for requesting account closure, the categories of data permanently purged, and the specific statutory records we are legally required to retain.
    </div>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">1. What Happens When You Request Deletion?</h2>
        <p>When an account deletion request is approved, Openbox permanently erases your personal profile and anonymizes associated records:</p>
        
        <div class="grid sm:grid-cols-2 gap-4 my-3 text-xs">
            <div class="p-4 bg-emerald-50/60 border border-emerald-200 rounded-xl">
                <span class="font-bold text-emerald-950 block mb-1 text-sm">✅ Permanently Purged & Erased</span>
                <ul class="space-y-1 text-emerald-900">
                    <li>• Login credentials, email address & phone number</li>
                    <li>• Account passwords, auth tokens & active sessions</li>
                    <li>• Saved delivery addresses and recipient names</li>
                    <li>• Cart items, wishlist records & search history</li>
                    <li>• Marketing subscriptions & notification preferences</li>
                    <li>• Customer support chat logs (after case closure)</li>
                </ul>
            </div>

            <div class="p-4 bg-amber-50/60 border border-amber-200 rounded-xl">
                <span class="font-bold text-amber-950 block mb-1 text-sm">⚖️ Retained for Statutory & Legal Obligations</span>
                <ul class="space-y-1 text-amber-900">
                    <li>• <strong>Tax & Invoices:</strong> Completed financial orders, VAT/tax receipts (retained for statutory periods as required by Bangladesh National Board of Revenue).</li>
                    <li>• <strong>Device Anti-Theft Logs:</strong> IMEI & serial numbers of inspected hardware (anonymized and decoupled from your personal profile to prevent stolen device circulation).</li>
                    <li>• <strong>Fraud Audit Trails:</strong> Historical escrow records involved in resolved fraud investigations.</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">2. Pre-requisites Prior to Deletion Request</h2>
        <p>To protect buyers, sellers, and open escrow funds, your account must meet the following criteria before deletion can proceed:</p>
        <ul class="list-disc list-inside space-y-1.5 pl-2 text-xs">
            <li><strong>No Active Orders:</strong> All orders must be in completed, cancelled, or refunded status. No packages may be in-transit.</li>
            <li><strong>No Open Escrow or Dispute Claims:</strong> The 7-day return guarantee period on your recent purchases must be concluded.</li>
            <li><strong>Zero Wallet Balance:</strong> Sellers and buyers must withdraw all remaining balance from their Openbox Wallet to their bank or mobile financial service (bKash/Nagad).</li>
            <li><strong>No Active Warehouse Inventory:</strong> Sellers must retrieve or liquidate any physical devices stored in Openbox custody hubs.</li>
        </ul>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">3. How to Submit an Account Deletion Request</h2>
        <p>You can initiate an account deletion through either of the following verified channels:</p>

        <div class="space-y-3">
            <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-xs">
                <h4 class="font-bold text-gray-900 text-sm mb-1">Option A: In-App Self-Service Request</h4>
                <p class="text-xs text-gray-600 mb-2">
                    Log in to your Openbox account, go to <strong>Account &rarr; Security Settings</strong>, scroll to the bottom, and select <strong>Request Account Deletion</strong>. You will receive an SMS/Email OTP verification code to confirm your request.
                </p>
            </div>

            <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-xs">
                <h4 class="font-bold text-gray-900 text-sm mb-1">Option B: Direct Email to Privacy Office</h4>
                <p class="text-xs text-gray-600 mb-2">
                    Send an email from your registered Openbox email address to <strong>privacy@openbox.com.bd</strong> with the subject line <em>"Account Deletion Request - [Your Registered Phone Number]"</em>. Our team will verify ownership and process your request.
                </p>
            </div>
        </div>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-gray-950 border-b border-gray-100 pb-2">4. Turnaround Timeline &amp; 14-Day Cooling Period</h2>
        <p>
            To safeguard users against accidental deletion or unauthorized account hijacking:
        </p>
        <ul class="list-disc list-inside space-y-1 pl-2 text-xs">
            <li>Upon receiving your verified request, your account is immediately disabled and deactivated from public view.</li>
            <li>A <strong>14-day cooling-off period</strong> begins. During this window, you may contact support to cancel the deletion if requested by error.</li>
            <li>After 14 days, our automated data shredding system permanently purges all eligible records. The entire process concludes within <strong>30 calendar days</strong>.</li>
        </ul>
    </section>

    <section class="space-y-2 pt-2 border-t border-gray-100">
        <h3 class="text-base font-bold text-gray-900">Questions or Assistance?</h3>
        <p class="text-xs text-gray-600">
            If you need assistance backing up past order receipts or have questions about data retention, please contact our Support Team at <strong>support@openbox.com.bd</strong> or call our customer hotline.
        </p>
    </section>
</div>
                ',
                'meta_title' => 'Account & Data Deletion Policy — Openbox Bangladesh',
                'meta_description' => 'Understand how to request account deletion on Openbox, what data is permanently wiped, what records are retained for legal/tax reasons, and timelines.',
                'status' => ContentStatus::Published,
            ],
            [
                'title' => 'Blog',
                'slug' => 'blog',
                'content' => '<p>Openbox Blog & Articles — Guides, Reviews, and Gadget Insights.</p>',
                'meta_title' => 'Blog — Openbox',
                'meta_description' => 'Read our latest tech articles, gadget comparisons, and certified device buying guides.',
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
