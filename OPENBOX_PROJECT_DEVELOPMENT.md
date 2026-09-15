# OPENBOX — Complete Project Development Roadmap
> AI Agent Build Guide | Laravel + Blade + Tailwind CSS | Multi-Vendor Marketplace

---

## Project Overview

Openbox is a **multi-vendor marketplace + classified marketplace + verified/refurbished product marketplace** with physical product verification, warehouse management, and a grading system. It is NOT a simple e-commerce site.

**Key differentiators:**
- Physical product inspection and verification by Openbox verifiers
- Transparent condition grading system (Grade A / B / C)
- Warehouse custody with Openbox badge
- Dual payment architecture: Openbox gateway for Openbox/warehouse products; seller's own gateway for business/saler products
- Subscription-based listing for salers; monthly/yearly subscription for businesses
- Real-time chat and support system

---

## Build Status (as of 2026-09-10)

All phases below (both the DB-schema Phase 1–16 list and the granular PHASE 0–22 step-by-step list further down this doc) are **built and live-verified end-to-end**. Remaining gaps are infra/product decisions awaiting user input, not missing code — see "Deliberately not built" at the bottom of this section.

| Area | Status | Notes |
|---|---|---|
| Foundation & layouts | ✅ | 6 layouts (app/admin/business/saler/verifier/customer), Blade component library, homepage, portal shells |
| Auth & roles | ✅ | Role-based registration (customer/business/saler), phone OTP (log-stub), profile-completion gate, branded mail theme |
| Admin settings | ✅ | Branding/mail/storage/system tabs, runtime color/font theming, encrypted secrets |
| Error pages | ✅ | 403/404/419/429/500/503 |
| KYC & verification | ✅ | Requirements, submissions, admin approve/reject queue, signed-URL private docs |
| Catalog | ✅ | Categories (tree), brands, attributes, category↔attribute pivot |
| Products | ✅ | Full lifecycle draft→published, dynamic attributes, multi-image, seller + admin flows |
| Stores | ✅ | Public directory, business `/store/{slug}`, saler `/seller/{slug}`, opt-in activation |
| Grading system | ✅ | Grade A/B/C, public explainer page, verification-gated display |
| Product verification | ✅ | Locations, verifier accounts, checklists, appointment booking, inspection → grade + lock |
| Warehouse | ✅ | Slots, deposits, receive/release, movement log, warehouse badge |
| Subscriptions | ✅ | Saler credit-packs, business plans, publish gating, expiry job |
| Cart, checkout & orders | ✅ | Guest/user cart, wishlist, COD checkout, per-seller order/payment-route grouping |
| Payments | ✅ | Manual bank + COD live; transactions/refunds; Stripe/PayPal UI scaffolded, **not wired to a real gateway** |
| Invoices | ✅ | Auto-generated per vendor order, PDF via DomPDF, 3 portal views |
| Order fulfillment | ✅ | Status pipeline, tracking, history log, customer notifications |
| Reviews | ✅ | Product + seller reviews, moderation queue, replies, reports |
| Chat & support | ✅ | Polling-based chat (no websockets), support ticket queue |
| Wallets, commission, payout | ✅ | Pending→available ledger, commission rule resolution, payout queue |
| Notification center | ✅ | Bell dropdown, `/notifications`, per-category email opt-out |
| Inventory | ✅ | Stock deduction/restore with locking, movement ledger |
| Blog & CMS | ✅ | Posts, tags, pages, banners, homepage banner strip |
| Homepage real-data wiring | ✅ | Featured/refurbished/vendors/reviews pulled from real DB, no mock arrays |
| Reports & analytics | ✅ | Admin 7-tab reports, business/saler seller-facing reports |
| Security & performance | ✅ | Security headers, password policy, endpoint rate limits, FK-adjacent indexes |
| Scheduler jobs | ✅ | `ExpireListings`, `SendSubscriptionExpiryReminders`, `ProcessPendingPayouts`, `CleanupExpiredCarts` |
| Caching | ✅ | Cached category tree with array-rehydration workaround for the `database` cache driver |
| Automated tests | ✅ | 30/30 passing — checkout/inventory, commission/wallet, review-eligibility integration tests |
| Dummy/demo data | ✅ | Seeder-driven (`database/seeders/DummyDataSeeder.php`) — no manually-created DB rows |

**Deliberately not built** (infra/product decisions, not code gaps): Horizon, Telescope, Meilisearch/Typesense, Sentry/Bugsnag, CDN, formal `Policy` classes (covered via `abort_unless`/scoped-query ownership checks instead), login activity logging, "logout other devices," real Stripe/PayPal gateway integration, payment idempotency keys, webhook signature validation, CSV/Excel/PDF report export.

### UI polish pass (2026-09-10) — reference: openbox.base44.app design

Redesigned to match a reference marketplace screenshot the user supplied:
- **Homepage banners** (`resources/views/pages/home.blade.php`): were rendering as bare, un-styled `<img>` tags that showed a broken-image icon with no layout whenever the external `picsum.photos` placeholder failed to load. Rewrote as fixed-aspect-ratio gradient cards with a dark overlay + title/CTA text baked into the markup, so the section always looks intentional even if the background image never loads.
- **Product detail page** (`resources/views/pages/product.blade.php`, `ProductPageController::show()`): rebuilt to match the reference layout — square gallery with thumbnail strip, badge row, title/rating, price with computed discount %, a seller card (avatar initial, verified check, aggregate seller rating pulled from `approvedReviewsAsSeller()`, city, "Contact" button linking to chat), quantity stepper, **Add to Cart** + **Buy Now** (Buy Now reuses the same `cart.add` route with a `checkout=1` field — `CartController::store()` now redirects straight to `/checkout` when it's set), a trust-badge row, a Specifications-first tab order, a "Similar Products" section (existing, same category) and a new "More from {seller}" section (other live products by the same vendor).
- **Recent Fixes**:
  - Wired up product cards on the homepage to be clickable and link to the detailed product page by properly passing `href` and `image` props to the `x-product-card` component.
  - Added Javascript to `product.blade.php` to handle interactive thumbnail clicking for the gallery and switching between Specifications, Description, and Reviews tabs without page reloads.
  - Fixed an issue where the `ProductImage::url()` model helper wrapped external `picsum.photos` dummy image URLs in Laravel's local `/storage/` path. Added a native `http` check to return external URLs directly.
  - Fixed asymmetric banner grid layout on the homepage: if there is an odd number of banners (e.g., 3), the very first banner is styled as a large hero spanning `sm:col-span-2` with a tailored `aspect-[24/5]` ratio.
- **Deliberately NOT built**: the reference page's "Trade-In Value Estimator" widget (device/brand pickers, condition-based payout percentages, age slider). It has no backing data model or valuation logic anywhere in this app's schema — building it would mean either fabricating fake payout percentages or inventing a whole new trade-in subsystem (rules, valuation service, admin config) that was never scoped in this roadmap. Flagged for the user to decide as a real feature/phase rather than faked as static UI.
- Trust-badge copy ("Buyer Protection", "Secure Payment", shipping line) intentionally uses generic, honest wording instead of the reference's specific numeric claims ("14-day return policy", "1 Year Apple Warranty") — this app has no warranty/return-window field on `Product`, so inventing specific day-counts would display false policy info to real buyers.
- **Homepage category strip** (`resources/views/pages/home.blade.php`): dropped the photo/letter-avatar treatment for category tiles. Added `<x-category-icon>` (`resources/views/components/category-icon.blade.php`) — a small per-category outline SVG (phone/laptop/tablet/watch/controller/headphones/camera, generic grid fallback for anything else) keyed by category slug, matching the reference site's icon-based category grid instead of raster images.

### Typography — matches reference site (2026-09-11)

Inspected `openbox.base44.app`'s raw HTML `<head>` (curl, not the JS-rendered shell) and found it loads **Montserrat** (weights 300/400/500/600/700/800) from Google Fonts:
```html
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
```
Applied as this project's default typeface, keeping the existing self-hosting approach (Bunny Fonts mirror via `laravel-vite-plugin/fonts`, not a live Google Fonts request) rather than switching mechanisms:
- `vite.config.js` — `bunny('Inter', ...)` → `bunny('Montserrat', { weights: [300, 400, 500, 600, 700, 800] })`.
- `resources/css/app.css` — `--font-sans` now leads with `'Montserrat'`.
- Admin Settings → Branding font picker (`UpdateBrandingRequest::FONTS`, `SettingsController::branding()`) — added `'Montserrat'` as an explicit option and made it the default instead of `'Inter'`; `branding-style.blade.php`'s "skip loading a Google Fonts link for the already-self-hosted default" guard now checks against `'Montserrat'` instead of `'Inter'` so picking Inter (or any other listed font) at runtime still correctly loads it.
- The runtime font-override mechanism itself (admin can still pick any of the 8 listed fonts and it takes effect without a rebuild) is unchanged — only the shipped default changed.

### Customer "My Account" section — built out to match reference (2026-09-11)

The user supplied 7 reference screenshots of a customer account area (Overview, My Orders, Addresses, Messages, Notifications, Returns, Profile Settings, Security). Cross-checking against this app found `resources/views/account/dashboard.blade.php` was a literal placeholder stub ("this is the foundation shell... will populate as their respective build phases land") and `layouts/customer.blade.php`'s sidebar linked to route names (`account.addresses.index`, `account.settings.index`) that were never actually registered — the nav rendered but half the links went nowhere. Rather than a cosmetic pass, this closed the real functional gap:

- **Overview** (`CustomerAccountController@index`, replacing the direct `Route::view`): real stat tiles — order count, wishlist item count, unread-message count (`ChatMessage` rows with `read_at IS NULL` where the sender isn't the current user), and written-review count — plus a "Recent Orders" list, all from real queries, not mock data.
- **Addresses** (new — `CustomerAddressController`, `app/Models/Address` already existed with no UI anywhere): full index/store/update/destroy/set-default, ownership-checked via `abort_unless($address->user_id === Auth::id())`, one address enforced as default at a time.
- **Returns** (new — `CustomerReturnController`): lists the signed-in customer's own `Refund` rows (`requested_by = Auth::id()`) with reason/status/date — the refund-*request* endpoint already existed (Phase 14 Payments) but customers had no page to see their own request history.
- **Profile Settings** (new — `CustomerProfileController`): name/email/phone edit. Changing the email resets `email_verified_at` to `null` via a separate `forceFill()->save()` — `email_verified_at` isn't in `User`'s fillable list (see `.ai/rules/http-controllers.md`), so a plain mass-assigned update silently drops it.
- **Security** (new — `CustomerSecurityController`): password change using Laravel's built-in `current_password` validation rule against the authenticated user, `Password::defaults()` policy, and `User::$password`'s `hashed` cast (pass the plain new value straight through — pre-hashing it would double-hash).
- **Sidebar nav** (`layouts/customer.blade.php`) rebuilt to match the reference's order and item set (Overview, My Orders, Wishlist, Addresses, Messages, Notifications, Returns, Profile Settings, Security), keeping the two extra tabs (Invoices, Reviews) that were already real, working features not present in the reference. Wishlist and Notifications intentionally link to their existing standalone top-level pages (`wishlist`, `notifications.index`) rather than being re-implemented inside the account shell — both already work fully; duplicating them into the sidebar chrome would be pure rework for no functional gain.
- Added `heart`, `bell`, `shield-check`, `undo` to the shared `App\Support\Icons` path set used by every dashboard sidebar (Admin/Business/Saler/Verifier/Customer), rather than inlining one-off SVGs in this layout only.
- Verified fully end-to-end live: logged in as a real seeded customer (`sarah.ahmed@example.com`) via curl, hit all 6 new/changed pages (200s, no Blade errors), confirmed the profile form pre-fills real name/email and the addresses page renders the seeded "Home" address in Doha.

### Topbar, Universal Profile & Auth Refinement Pass (2026-09-14)

- **Announcement Bar** (`resources/views/layouts/partials/announcement-bar.blade.php`): Updated top bar to white background (`bg-white`) with pure black typography (`text-black`) and subtle brand hover transitions (`hover:text-brand-600`).
- **Authentication Eye Toggles**: Added interactive show/hide password visibility toggles (`eye-open` and `eye-closed`) to **Register**, **Login**, and **Reset Password** forms.
- **Reset Password Streamlining**: Hidden email field in `reset-password.blade.php` so users only interact with New Password (with autofocus) and Confirm Password.
- **Universal Dashboard Topbar** (`resources/views/components/dashboard-topbar.blade.php`): Integrated `[ ↗ View Site ]` button, vertical divider, user role label, amber avatar badge (`bg-brand-400 text-gray-950 font-bold`), and dropdown menu with profile edit, dynamic settings link, and sign out across all portals (Admin, Business, Saler, Verifier, Customer).
- **Universal Profile Management** (`/account/profile`): Unlocked access for all authenticated roles (Admin, Business, Seller, Verifier), supported avatar photo uploads, and synchronized company name and location to `business_profiles` and `saler_profiles`. Tabs strictly matched with `admin/settings/_tabs.blade.php` brand styling.
- **Multi-Role Verification**: Verified registration, onboarding, and dashboard access end-to-end for Buyer, Business, and Individual Seller.

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | Laravel (latest), PHP 8.3+, MySQL 8+ |
| Frontend | Blade, Tailwind CSS, Vanilla JS, Fetch/AJAX |
| Auth | Laravel Auth + Email/Phone verification |
| Queue | Laravel Queues + Scheduler |
| Storage | Local/public (dev) → Cloudflare R2 / S3 (prod) |
| Payment | Stripe, PayPal, COD, Manual Bank |
| Real-time | Laravel Echo + Pusher (or Reverb) for chat/notifications |
| Search | MySQL full-text (dev) → Meilisearch/Typesense (prod) |
| PDF | Laravel DomPDF or Snappy |
| UI extras | SweetAlert2, DataTables |

---

## Roles

```
ADMIN       — Platform owner, full control
VERIFIER    — Openbox inspection staff
BUSINESS    — Verified companies/stores (subscription-based)
SALER       — Individual sellers (credit-based subscription)
CUSTOMER    — Buyers (registered or guest)
```

Single `users` table with role-specific profile tables.

---

## Product Grading System

Every product in Openbox has a **condition** field AND a **grade** field.

### Grades

| Grade | Label | Description | Battery Health |
|---|---|---|---|
| A | Like New | No visible scratches or defects. Fully tested and certified. | Above 90% |
| B | Good | Minor cosmetic marks, no functionality impact. Fully functional. | Above 80% |
| C | Fair | Visible wear and scratches. Fully functional with cosmetic imperfections. | Above 70% |

### Condition vs Grade

- **Condition** = `new`, `used`, `refurbished` (seller-declared)
- **Grade** = `A`, `B`, `C`, `ungraded` (assigned by Openbox verifier during inspection)

Grades only apply to `used` and `refurbished` products. New products are always Grade A by default. Grade is locked after verification — seller cannot change it.

### Grade display on product card

```
┌─────────────────────────┐
│      Product Image      │
├─────────────────────────┤
│ ✓ Verified  [Grade B]   │
│ iPhone 14 Pro           │
│ ★ 4.8 · $499            │
│ Good condition           │
│ Seller: ABC Store        │
└─────────────────────────┘
```

### Grade Info Page (public)

Route: `/grading-system`

Display the grading table transparently so buyers can shop with confidence.

---

## Payment Architecture Decision

### Rule

| Product Owner | Payment Flow |
|---|---|
| Openbox Admin products | Customer → Openbox payment gateway |
| Warehouse-deposited products | Customer → Openbox payment gateway |
| Business seller products | Customer → Business's own Stripe/PayPal |
| Saler seller products | Customer → Saler's own Stripe/PayPal |

### Implementation

- At checkout, cart is split by `payment_route`:
  - `openbox` — items owned/warehoused by Openbox
  - `seller` — items owned by business/saler
- Each split group processes through the appropriate gateway
- One customer order can have both routes; they generate separate payment sessions
- No platform-mediated escrow for seller products; seller receives payment directly

### Database field

```sql
products.payment_route ENUM('openbox', 'seller')
```

Auto-set based on:
- `owner_type = admin` OR `warehouse_status = stored` → `openbox`
- `owner_type = business` OR `owner_type = saler` → `seller`

---

## Subscription System

### Saler Subscriptions (Credit-based)

| Plan | Credits | Duration | Price |
|---|---|---|---|
| Starter | 5 listings | 45 days | $30 |
| Growth | 10 listings | 60 days | $50 |
| Pro | 25 listings | 90 days | $100 |

- Each published product consumes 1 credit
- Credits expire with the subscription period
- Admin can create/edit plans from dashboard

### Business Subscriptions (Recurring)

| Plan | Monthly | Yearly | Products | Features |
|---|---|---|---|---|
| Basic | $49/mo | $470/yr | Up to 50 | Store page, basic analytics |
| Standard | $99/mo | $950/yr | Up to 200 | Priority support, advanced analytics |
| Enterprise | $199/mo | $1,900/yr | Unlimited | Dedicated manager, API access |

- Monthly = charged every 30 days via Stripe/PayPal recurring
- Yearly = charged once annually (discount applied)
- Business products do not consume listing credits
- Subscription status gates product publishing
- Admin can create/edit business plans from dashboard

---

## Chat, Notification & Support System

### Chat

**Buyer ↔ Seller chat** (pre-purchase inquiry)

```
chat_conversations
chat_messages
chat_participants
```

- Buyer clicks "Ask Seller" on product page
- Conversation linked to product + seller
- Real-time via Laravel Echo + Pusher/Reverb
- Seller receives in-dashboard chat inbox
- Admin can view/moderate all conversations

**Support chat** (customer ↔ Openbox support)

```
support_tickets
support_ticket_messages
support_ticket_attachments
```

- Customer opens support ticket from account
- Admin/support staff reply from admin panel
- Status: `open`, `in_progress`, `waiting_customer`, `resolved`, `closed`

### Notifications

```
notifications (Laravel default)
notification_preferences
```

Channels: Database, Email → later SMS, Push

| Event | Recipient | Channel |
|---|---|---|
| New message received | Seller/Buyer | DB + Email |
| Product approved | Seller/Business | DB + Email |
| Product rejected | Seller/Business | DB + Email |
| Product verified | Seller | DB + Email |
| Subscription expiring (7 days) | Saler/Business | DB + Email |
| Subscription expired | Saler/Business | DB + Email |
| Order placed | Seller + Customer | DB + Email |
| Order shipped | Customer | DB + Email |
| Order delivered | Customer + Seller | DB + Email |
| Payment received | Seller | DB + Email |
| Verification appointment | Saler | DB + Email |
| KYC approved/rejected | All sellers | DB + Email |
| Support ticket reply | Customer | DB + Email |
| New support ticket | Admin/Staff | DB + Email |
| Payout processed | Seller | DB + Email |

---

## Database Schema

### Phase 1 — Users & Auth

```sql
users
  id, name, email, phone, password, role ENUM(admin,verifier,business,saler,customer),
  status ENUM(pending,active,suspended,blocked), email_verified_at, phone_verified_at,
  last_login_at, created_at, updated_at

user_profiles
  id, user_id, avatar, bio, date_of_birth, gender, created_at, updated_at

business_profiles
  id, user_id, business_name, slug, logo, cover_image, description,
  trade_license_number, vat_number, tax_number, address, city, country,
  phone, website, social_links JSON, is_store_active, created_at, updated_at

saler_profiles
  id, user_id, display_name, slug, profile_photo, cover_image, bio,
  location, city, country, is_store_active, created_at, updated_at

verifier_profiles
  id, user_id, employee_id, assigned_location_id, specializations JSON,
  created_at, updated_at
```

### Phase 2 — KYC & Verification

```sql
verification_requirements
  id, role ENUM(saler,business), document_type, is_required, is_active,
  sort_order, created_at, updated_at

user_verifications
  id, user_id, verification_type, status ENUM(draft,submitted,under_review,approved,rejected,expired),
  submitted_at, verified_at, verified_by, rejection_reason, notes, created_at, updated_at

verification_documents
  id, verification_id, document_type ENUM(NID,PASSPORT,DRIVING_LICENSE,TRADE_LICENSE,VAT,TAX,BUSINESS_REGISTRATION,ADDRESS_PROOF,OTHER),
  document_number, file_path, expiry_date,
  status ENUM(pending,approved,rejected), verified_by, verified_at, remarks,
  created_at, updated_at
```

### Phase 3 — Categories, Brands, Attributes

```sql
categories
  id, parent_id, name, slug, description, image, status, sort_order,
  meta_title, meta_description, created_at, updated_at

brands
  id, name, slug, logo, description, status, created_at, updated_at

attributes
  id, name, slug, type ENUM(text,select,multi_select,number,boolean),
  unit, is_filterable, created_at, updated_at

attribute_values
  id, attribute_id, value, slug, sort_order, created_at, updated_at

category_attributes
  id, category_id, attribute_id, is_required, sort_order, created_at, updated_at
```

### Phase 4 — Products

```sql
products
  id, owner_type ENUM(admin,business,saler), owner_id,
  category_id, brand_id,
  title, slug, description, short_description,
  condition ENUM(new,used,refurbished),
  grade ENUM(A,B,C,ungraded),
  grade_notes TEXT,
  status ENUM(draft,pending_approval,approved,rejected,published,expired,suspended,sold,archived),
  approval_status ENUM(pending,approved,rejected),
  publication_status ENUM(unpublished,published,expired),
  verification_status ENUM(not_requested,pending,scheduled,inspecting,verified,rejected),
  warehouse_status ENUM(not_deposited,pending,stored,reserved,sold,released),
  payment_route ENUM(openbox,seller),
  price DECIMAL(12,2), compare_price DECIMAL(12,2),
  sku VARCHAR(100), quantity INT,
  location_id, is_negotiable BOOLEAN,
  published_at, expires_at, subscription_id,
  meta_title, meta_description,
  views_count INT DEFAULT 0,
  created_at, updated_at, deleted_at

product_variants
  id, product_id, sku, price, compare_price, stock, weight,
  status ENUM(active,inactive), created_at, updated_at

variant_attributes
  id, variant_id, attribute_id, attribute_value_id, created_at

product_attribute_values
  id, product_id, attribute_id, attribute_value_id, custom_value, created_at

product_images
  id, product_id, variant_id, path, type ENUM(gallery,thumbnail,verification,warehouse,condition),
  sort_order, is_primary, created_at, updated_at

product_publications
  id, product_id, subscription_id, listing_credit_id, published_at,
  expires_at, created_at, updated_at
```

### Phase 5 — Product Verification

```sql
verification_locations
  id, name, address, city, country, phone, email,
  working_hours JSON, is_active, created_at, updated_at

product_verifications
  id, product_id, saler_id, verifier_id, location_id,
  status ENUM(requested,scheduled,inspecting,verified,rejected,cancelled),
  requested_at, scheduled_at, inspected_at, verified_at,
  rejection_reason, notes, created_at, updated_at

verification_appointments
  id, product_verification_id, appointment_date, appointment_time,
  status ENUM(pending,confirmed,completed,no_show,cancelled),
  created_at, updated_at

verification_checklists
  id, category_id, item_name, description, is_required, sort_order,
  created_at, updated_at

verification_results
  id, product_verification_id, checklist_item_id, result ENUM(pass,fail,na),
  notes, created_at, updated_at

product_grade_assignments
  id, product_id, product_verification_id, verifier_id,
  grade ENUM(A,B,C), grade_notes TEXT, battery_health INT,
  assigned_at, created_at, updated_at
```

### Phase 6 — Warehouse

```sql
warehouses
  id, name, address, city, country, manager_id, phone, email,
  storage_capacity INT, is_active, created_at, updated_at

warehouse_locations
  id, warehouse_id, zone, row, shelf, slot, is_occupied,
  created_at, updated_at

warehouse_products
  id, product_id, warehouse_id, warehouse_location_id, seller_id, seller_type,
  received_at, condition_at_receipt ENUM(A,B,C),
  quantity, storage_status ENUM(pending_delivery,received,stored,reserved,sold,released,returned,damaged,lost),
  released_at, release_reason, created_at, updated_at

warehouse_movements
  id, product_id, warehouse_id, from_location, to_location,
  type ENUM(in,out,transfer,adjustment), quantity,
  reference_type, reference_id, notes, created_by, created_at

warehouse_receipts
  id, warehouse_product_id, receipt_number, received_by,
  condition_notes TEXT, images JSON, created_at, updated_at
```

### Phase 7 — Subscriptions

```sql
subscription_plans
  id, name, slug, type ENUM(saler,business),
  billing_cycle ENUM(one_time,monthly,yearly),
  price DECIMAL(10,2), listing_credits INT,
  duration_days INT, max_products INT,
  features JSON, is_active, sort_order,
  created_at, updated_at

subscriptions
  id, user_id, plan_id, status ENUM(active,expired,cancelled,pending),
  starts_at, ends_at, cancelled_at,
  billing_cycle ENUM(one_time,monthly,yearly),
  auto_renew BOOLEAN, next_billing_at,
  created_at, updated_at

subscription_items
  id, subscription_id, product_id, published_at, expires_at,
  created_at, updated_at

listing_credits
  id, subscription_id, user_id, total_credits INT, used_credits INT,
  remaining_credits INT, expires_at, created_at, updated_at
```

### Phase 8 — Cart & Wishlist

```sql
carts
  id, user_id, session_id, created_at, updated_at

cart_items
  id, cart_id, product_id, variant_id, quantity, price,
  payment_route ENUM(openbox,seller), created_at, updated_at

wishlists
  id, user_id, created_at, updated_at

wishlist_items
  id, wishlist_id, product_id, created_at, updated_at
```

### Phase 9 — Orders

```sql
addresses
  id, user_id, label, name, phone, line1, line2, city, state,
  country, postal_code, is_default, created_at, updated_at

orders
  id, order_number, customer_id, billing_address_id, shipping_address_id,
  status ENUM(pending,confirmed,processing,packed,shipped,out_for_delivery,delivered,cancelled,returned,refunded,failed),
  subtotal, discount, coupon_discount, shipping_total, tax_total, total,
  coupon_id, notes, created_at, updated_at

vendor_orders
  id, order_id, vendor_type ENUM(admin,business,saler), vendor_id,
  vendor_order_number, status ENUM(pending,confirmed,processing,packed,shipped,delivered,cancelled,returned),
  subtotal, shipping, tax, total,
  payment_route ENUM(openbox,seller),
  tracking_number, shipped_at, delivered_at,
  created_at, updated_at

order_items
  id, order_id, vendor_order_id, product_id, variant_id,
  product_title, product_grade, product_condition, sku,
  quantity, unit_price, total_price, payment_route,
  created_at, updated_at

order_status_histories
  id, order_id, vendor_order_id, status, notes, created_by, created_at
```

### Phase 10 — Payments

```sql
payment_methods
  id, user_id, type ENUM(stripe,paypal,cod,manual_bank), is_default,
  details JSON, status, created_at, updated_at

vendor_payment_accounts
  id, user_id, vendor_type, provider ENUM(stripe,paypal),
  account_reference, account_email, status ENUM(pending,active,inactive),
  is_enabled, connected_at, created_at, updated_at

vendor_payment_settings
  id, user_id, stripe_enabled, paypal_enabled, cod_enabled,
  manual_bank_enabled, bank_details JSON, created_at, updated_at

transactions
  id, transaction_number, order_id, vendor_order_id, user_id,
  type ENUM(payment,refund,payout,adjustment),
  payment_route ENUM(openbox,seller),
  provider ENUM(stripe,paypal,cod,manual_bank,openbox_wallet),
  provider_transaction_id, amount, currency, status ENUM(pending,processing,completed,failed,refunded),
  metadata JSON, created_at, updated_at

payment_webhooks
  id, provider, event_type, payload JSON, status,
  processed_at, created_at, updated_at

refunds
  id, order_id, transaction_id, reason, amount, status ENUM(pending,approved,processing,completed,rejected),
  approved_by, processed_at, created_at, updated_at

manual_payment_submissions
  id, order_id, vendor_order_id, amount, reference, bank_name,
  proof_file_path, status ENUM(pending,verified,rejected),
  verified_by, verified_at, notes, created_at, updated_at
```

### Phase 11 — Invoices

```sql
invoices
  id, invoice_number, order_id, vendor_order_id, seller_id, buyer_id,
  seller_type, subtotal, tax, discount, shipping, total,
  payment_status ENUM(unpaid,paid,partial,refunded),
  issued_at, due_at, pdf_path, created_at, updated_at

invoice_items
  id, invoice_id, product_title, sku, grade, quantity,
  unit_price, total_price, created_at, updated_at
```

### Phase 12 — Reviews

```sql
reviews
  id, reviewer_id, reviewable_type ENUM(product,seller,business),
  reviewable_id, order_id, rating TINYINT, title, body,
  status ENUM(pending,approved,rejected), created_at, updated_at

review_images
  id, review_id, path, created_at

review_replies
  id, review_id, replier_id, body, created_at, updated_at

review_reports
  id, review_id, reporter_id, reason, status, created_at, updated_at
```

### Phase 13 — Wallets, Commission, Payout

```sql
seller_wallets
  id, user_id, available_balance DECIMAL(12,2), pending_balance DECIMAL(12,2),
  total_earned DECIMAL(12,2), total_withdrawn DECIMAL(12,2),
  created_at, updated_at

seller_transactions
  id, wallet_id, user_id, type ENUM(credit,debit),
  source ENUM(sale,refund,payout,adjustment,commission),
  amount DECIMAL(12,2), reference_type, reference_id,
  notes, created_at, updated_at

commission_rules
  id, type ENUM(global,category,seller,business,product), reference_id,
  commission_type ENUM(percentage,fixed), value DECIMAL(8,4),
  priority INT, is_active, created_at, updated_at

commission_records
  id, order_item_id, seller_id, sale_amount, commission_rate,
  commission_amount, seller_amount, status, created_at, updated_at

seller_payouts
  id, payout_number, user_id, amount DECIMAL(12,2),
  method ENUM(stripe,paypal,manual_bank),
  status ENUM(requested,approved,processing,completed,rejected),
  approved_by, processed_at, notes, created_at, updated_at
```

### Phase 14 — Chat & Support

```sql
chat_conversations
  id, product_id, buyer_id, seller_id, seller_type,
  last_message_at, created_at, updated_at

chat_messages
  id, conversation_id, sender_id, body TEXT, attachment_path,
  read_at, created_at, updated_at

support_tickets
  id, ticket_number, user_id, subject, category,
  status ENUM(open,in_progress,waiting_customer,resolved,closed),
  priority ENUM(low,normal,high,urgent),
  assigned_to, created_at, updated_at

support_ticket_messages
  id, ticket_id, sender_id, body TEXT, created_at, updated_at

support_ticket_attachments
  id, ticket_message_id, file_path, file_name, created_at
```

### Phase 15 — Inventory

```sql
inventories
  id, product_id, variant_id, location_id, quantity INT,
  reserved_quantity INT, available_quantity INT, created_at, updated_at

inventory_movements
  id, product_id, variant_id, type ENUM(purchase,sale,return,adjustment,warehouse_in,warehouse_out,transfer,reservation,release),
  quantity INT, reference_type, reference_id, notes, created_by, created_at
```

### Phase 16 — Blog, CMS, Settings

```sql
blog_categories
  id, name, slug, description, status, created_at, updated_at

posts
  id, category_id, author_id, title, slug, excerpt, content,
  featured_image, status ENUM(draft,published,scheduled,archived),
  published_at, meta_title, meta_description, created_at, updated_at

tags
  id, name, slug, created_at

post_tags
  post_id, tag_id

pages
  id, title, slug, content, status, meta_title, meta_description,
  created_at, updated_at

banners
  id, title, image, link, position, sort_order, is_active,
  starts_at, ends_at, created_at, updated_at

settings
  id, key VARCHAR(100) UNIQUE, value TEXT, group, created_at, updated_at

activity_logs
  id, user_id, action, subject_type, subject_id, properties JSON,
  ip_address, created_at
```

---

## Laravel Project Architecture

```
app/
├── Actions/
│   ├── Auth/
│   ├── Product/
│   ├── Order/
│   ├── Payment/
│   └── Verification/
├── Enums/
│   ├── UserRole.php
│   ├── ProductStatus.php
│   ├── ProductGrade.php
│   ├── ProductCondition.php
│   ├── VerificationStatus.php
│   ├── WarehouseStatus.php
│   ├── OrderStatus.php
│   ├── PaymentRoute.php
│   ├── SubscriptionType.php
│   └── BillingCycle.php
├── Events/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Business/
│   │   ├── Saler/
│   │   ├── Verifier/
│   │   ├── Customer/
│   │   └── Public/
│   ├── Middleware/
│   └── Requests/
├── Jobs/
│   ├── ExpireListings.php
│   ├── SendNotification.php
│   ├── ProcessPayout.php
│   └── GenerateInvoice.php
├── Listeners/
├── Models/
├── Notifications/
├── Policies/
│   ├── ProductPolicy.php
│   ├── OrderPolicy.php
│   └── VerificationPolicy.php
├── Services/
│   ├── ProductService.php
│   ├── ProductVerificationService.php
│   ├── GradingService.php
│   ├── InventoryService.php
│   ├── OrderService.php
│   ├── CheckoutService.php
│   ├── PaymentService.php
│   ├── PaymentRouterService.php
│   ├── SubscriptionService.php
│   ├── ListingCreditService.php
│   ├── InvoiceService.php
│   ├── WarehouseService.php
│   ├── ReviewService.php
│   ├── CommissionService.php
│   ├── PayoutService.php
│   ├── ChatService.php
│   └── NotificationService.php
└── Support/
    ├── Helpers/
    └── Traits/

resources/views/
├── components/
│   ├── input.blade.php
│   ├── select.blade.php
│   ├── textarea.blade.php
│   ├── checkbox.blade.php
│   ├── file-upload.blade.php
│   ├── button.blade.php
│   ├── badge.blade.php
│   ├── alert.blade.php
│   ├── modal.blade.php
│   ├── card.blade.php
│   ├── table.blade.php
│   ├── pagination.blade.php
│   ├── breadcrumb.blade.php
│   ├── grade-badge.blade.php
│   └── verified-badge.blade.php
├── layouts/
│   ├── app.blade.php       (public)
│   ├── admin.blade.php
│   ├── business.blade.php
│   ├── saler.blade.php
│   ├── verifier.blade.php
│   └── customer.blade.php
├── admin/
├── business/
├── saler/
├── verifier/
├── customer/
└── public/
```

---

## URL Structure

### Public

```
/
/shop
/grading-system
/categories
/categories/{slug}
/products/{slug}
/stores
/store/{slug}              (business store)
/seller/{slug}             (saler store)
/cart
/checkout
/login
/register
/register/business
/register/saler
/blog
/blog/{slug}
/page/{slug}
/search
```

### Customer

```
/account
/account/orders
/account/orders/{id}
/account/wishlist
/account/reviews
/account/addresses
/account/messages
/account/support
/account/settings
```

### Admin

```
/admin                              Dashboard
/admin/users                        Users
/admin/users/{id}/verification      User KYC
/admin/verifications                Verification queue
/admin/products                     All products
/admin/products/pending             Approval queue
/admin/categories
/admin/brands
/admin/attributes
/admin/verification-locations
/admin/verification-appointments
/admin/warehouses
/admin/warehouse/{id}/products
/admin/subscriptions/plans
/admin/subscriptions
/admin/orders
/admin/payments
/admin/transactions
/admin/payouts
/admin/commission-rules
/admin/reviews
/admin/chat                         All conversations
/admin/support                      Support tickets
/admin/blog
/admin/pages
/admin/banners
/admin/reports
/admin/settings
/admin/activity-logs
```

### Business

```
/business                           Dashboard
/business/products
/business/products/create
/business/products/{id}/edit
/business/categories
/business/inventory
/business/orders
/business/orders/{id}
/business/customers
/business/invoices
/business/store
/business/reviews
/business/payment-settings
/business/subscription
/business/payouts
/business/reports
/business/messages
/business/support
/business/settings
/business/verification
```

### Saler

```
/saler                              Dashboard
/saler/products
/saler/products/create
/saler/products/{id}/edit
/saler/subscriptions
/saler/subscriptions/buy
/saler/listing-credits
/saler/orders
/saler/orders/{id}
/saler/invoices
/saler/verification
/saler/verification/{id}
/saler/appointments
/saler/warehouse
/saler/warehouse/deposit
/saler/store
/saler/reviews
/saler/payment-settings
/saler/payouts
/saler/reports
/saler/messages
/saler/support
/saler/settings
```

### Verifier

```
/verifier                           Dashboard
/verifier/appointments
/verifier/appointments/{id}
/verifier/products
/verifier/products/{id}/inspect
/verifier/history
```

---

## Development Phases — Step by Step

---

### PHASE 0 — Business Rules & Setup (No Code)

**Before writing any Laravel code, confirm these rules:**

- [ ] Grading criteria confirmed (A/B/C + battery health thresholds)
- [ ] Which product types require grade assignment
- [ ] Verification appointment booking flow confirmed
- [ ] Product lock fields after verification listed
- [ ] Saler subscription plans and prices confirmed
- [ ] Business subscription plans (monthly/yearly) and prices confirmed
- [ ] Commission rates per category/seller type confirmed
- [ ] COD availability per seller type confirmed
- [ ] Return/refund window and policy confirmed
- [ ] Openbox payment gateway setup (Stripe account for platform)
- [ ] Seller payment gateway setup approach confirmed

---

### PHASE 1 — Project Foundation

**Tasks:**

1. `laravel new openbox`
2. Configure `.env`: DB, Mail, Queue, Storage, Pusher/Reverb
3. Install packages:
   - `laravel/telescope` (dev)
   - `barryvdh/laravel-dompdf`
   - `spatie/laravel-medialibrary` or manual storage
   - `pusher/pusher-php-server` or Laravel Reverb
4. Configure Tailwind CSS via CDN or Vite
5. Create base layouts: `app`, `admin`, `business`, `saler`, `verifier`, `customer`
6. Create Blade components: `input`, `select`, `textarea`, `button`, `badge`, `alert`, `modal`, `card`, `table`, `pagination`, `breadcrumb`, `grade-badge`, `verified-badge`
7. Create public header, footer, mobile left drawer (vanilla JS)
8. Create admin sidebar, header
9. Create seller/business sidebar, header
10. Design system: define Tailwind colors, typography, spacing constants in CSS variables

**Deliverable:** Complete UI shell with no business logic. All layouts navigable.

---

### PHASE 2 — Authentication & Role System

**Migrations:**
- `users`, `user_profiles`, `business_profiles`, `saler_profiles`, `verifier_profiles`

**Tasks:**

1. Registration — three paths:
   - Customer (standard)
   - Business (extra fields: business name, trade license placeholder)
   - Saler (extra fields: display name, location)
2. Login with email or phone
3. Email verification (Laravel built-in)
4. Phone OTP verification (via SMS gateway or placeholder)
5. Forgot/reset password
6. Role-based middleware: `role:admin`, `role:business`, `role:saler`, `role:verifier`, `role:customer`
7. Role-based dashboard redirect after login
8. Admin: user list, view, activate, suspend, block, change role
9. Profile completion flow for business/saler after registration

**Enums:** `UserRole`, `UserStatus`

---

### PHASE 3 — KYC / Verification System

**Migrations:**
- `verification_requirements`, `user_verifications`, `verification_documents`

**Tasks:**

1. Admin: configure verification requirements per role (which documents required)
2. Saler KYC flow:
   - Upload NID / Passport / Driving License (at least one)
   - Submit for review
   - Status tracking
3. Business KYC flow:
   - Upload Trade License, VAT, Tax, Business Registration
   - Submit for review
4. Admin verification dashboard:
   - Pending / Under Review / Approved / Rejected / Expired queue
   - View documents (private, secure URLs)
   - Approve / Reject / Request changes with reason
5. Email notification on approval/rejection
6. KYC status badge on seller profiles

**Security:** Verification documents stored in private disk, never publicly accessible.

---

### PHASE 4 — Categories, Brands, Attributes

**Migrations:**
- `categories`, `brands`, `attributes`, `attribute_values`, `category_attributes`

**Tasks:**

1. Admin: hierarchical category CRUD (parent → child → grandchild)
2. Admin: brand CRUD
3. Admin: attribute CRUD (name, type, unit, is_filterable)
4. Admin: attribute value CRUD per attribute
5. Admin: assign attributes to categories (required/optional, sort order)
6. Category tree display on public frontend
7. Category filter menu for shop page

---

### PHASE 5 — Product Management

**Migrations:**
- `products`, `product_variants`, `variant_attributes`, `product_attribute_values`, `product_images`

**Enums:** `ProductStatus`, `ProductCondition`, `ProductGrade`, `PaymentRoute`

**Tasks:**

1. Product creation form (business/saler):
   - Basic info (title, description, category, brand)
   - Condition: New / Used / Refurbished
   - Grade: shown as informational (assigned by verifier; defaults to `ungraded`)
   - Price, compare price, SKU, quantity
   - Attributes (dynamic based on category)
   - Variants (optional)
   - Images (gallery, thumbnail, condition photos)
   - SEO fields
   - Shipping info
2. `payment_route` auto-set based on owner type
3. Admin: product approval queue (approve/reject with reason)
4. Product status state machine — use `ProductService`
5. Product listing page (saler: requires credit; business: requires active subscription)
6. **Product lock after verification:** `ProductPolicy::update()` checks `verification_status === 'verified'` — locked fields cannot be saved
7. Admin: products from own inventory (no subscription required)

**Grade display:** Grade badge shown on product page only if `verification_status === 'verified'`. Unverified products show no grade.

---

### PHASE 6 — Seller & Business Stores

**Tasks:**

1. Business store page: `/store/{slug}`
   - Cover, logo, name, verification badge, rating, product list
   - About, contact info
2. Saler store page: `/seller/{slug}`
   - Same structure, saler branding
3. Store product filters: newest, price, rating, condition, grade, category
4. Store settings page for business/saler to customize
5. Store follow/unfollow (optional for v1)

---

### PHASE 7 — Grading System

**Migrations:**
- `verification_checklists` (grade-relevant fields), `product_grade_assignments`

**Tasks:**

1. Public page `/grading-system`: display grade table (A/B/C criteria, battery health)
2. Grade badge component `<x-grade-badge grade="A" />`:
   - Grade A: green badge
   - Grade B: blue badge
   - Grade C: amber badge
   - Ungraded: gray badge
3. Grade displayed on: product card, product page, order item, invoice
4. Grade assigned by verifier during inspection (Phase 8 covers the workflow)
5. Grade locked after assignment — cannot be changed by seller

---

### PHASE 8 — Product Verification System

**Migrations:**
- `verification_locations`, `product_verifications`, `verification_appointments`, `verification_checklists`, `verification_results`, `product_grade_assignments`

**Tasks:**

1. Admin: manage verification locations (name, address, working hours)
2. Admin: manage verifier accounts, assign to locations
3. Saler: request product verification
   - Select product
   - Select nearest location
   - Choose appointment slot
4. Verifier dashboard:
   - Today's appointments
   - Product info, saler info
   - Checklist per category (dynamic)
   - Photo upload during inspection
   - Serial/IMEI recording
   - Grade assignment (A/B/C + battery health input)
   - Pass / Fail decision
5. On verification pass:
   - `products.verification_status = verified`
   - `products.grade = assigned_grade`
   - Verified badge unlocked on product
   - Product fields locked (server-side via `ProductPolicy`)
6. On verification fail:
   - `products.verification_status = rejected`
   - Rejection reason sent to saler
   - Saler can re-request after addressing issues
7. Email notifications at each verification status change

---

### PHASE 9 — Warehouse System

**Migrations:**
- `warehouses`, `warehouse_locations`, `warehouse_products`, `warehouse_movements`, `warehouse_receipts`

**Tasks:**

1. Admin: warehouse CRUD (multiple warehouses)
2. Admin: warehouse location/slot management
3. Saler: request to deposit verified product to Openbox warehouse
4. Admin/warehouse staff: receive product, assign storage slot, record condition
5. `products.warehouse_status` updated through lifecycle
6. `products.payment_route` → set to `openbox` when `warehouse_status = stored`
7. Warehouse dashboard: stock levels, movements, receipts
8. Warehouse badge on product page: `🏢 Openbox Warehouse`
9. Movement history log per product
10. Release flow: when product sold, mark as released, update inventory

---

### PHASE 10 — Subscription System

**Migrations:**
- `subscription_plans`, `subscriptions`, `subscription_items`, `listing_credits`

**Tasks:**

#### Saler Subscriptions

1. Admin: create/edit saler plans (credits, duration, price)
2. Saler: view plans, buy plan (via Stripe/PayPal)
3. On purchase: create subscription record, allocate listing credits
4. Saler: publish product = consume 1 listing credit
   - Validate: credits available, product approved
   - Set `published_at`, `expires_at` on product
5. Laravel Scheduler: expire listings daily (`ExpireListings` job)
6. Saler dashboard: subscription status, credits remaining, expiring listings

#### Business Subscriptions

1. Admin: create/edit business plans (monthly/yearly, product limit, price)
2. Business: view plans, subscribe with monthly or yearly billing
3. Stripe recurring subscription for monthly/yearly billing
4. Webhook handling for renewal, cancellation, payment failure
5. Business dashboard: subscription status, renewal date, plan features
6. Product publishing gated on active subscription + product limit check
7. Auto-renewal 7 days before expiry notification

---

### PHASE 11 — Cart & Checkout

**Migrations:**
- `carts`, `cart_items`, `wishlists`, `wishlist_items`

**Tasks:**

1. Guest cart (session-based) + logged-in cart (DB-based), merge on login
2. Add to cart with quantity selector
3. Cart page: items grouped visually by seller, quantities, totals
4. Wishlist: add/remove, move to cart
5. Checkout:
   - Address selection / new address
   - Shipping method selection per vendor
   - Coupon code input
   - Order summary
   - **Payment split**: detect `payment_route` per cart item
     - Openbox items → Openbox Stripe/PayPal session
     - Seller items → seller's connected Stripe/PayPal
   - Display which payment method is used for which items
6. Use `CheckoutService`, `PricingService`, `TaxService`, `ShippingService`

---

### PHASE 12 — Payment System

**Migrations:**
- `payment_methods`, `vendor_payment_accounts`, `vendor_payment_settings`, `transactions`, `payment_webhooks`, `refunds`, `manual_payment_submissions`

**Tasks:**

#### Openbox Stripe/PayPal (for Openbox/warehouse products)

1. Openbox platform Stripe account configured in `.env`
2. Payment intent creation for openbox-route items
3. Webhook: payment success → confirm order, update `transactions`

#### Seller Stripe/PayPal (for business/saler products)

1. Seller onboarding: connect Stripe account (Stripe Express / Standard)
2. Seller PayPal account connection
3. Seller payment settings dashboard: enable/disable methods, COD, manual bank
4. Payment intent creation using seller's connected account
5. Webhook: payment success → confirm vendor order

#### COD

1. Order placed with COD flag
2. Status: `cod_pending` until delivery
3. After delivery: cash collected confirmed by seller → mark as paid
4. Seller triggers cash collection confirmation

#### Manual Bank Payment

1. Show admin bank details at checkout
2. Customer uploads payment proof
3. Admin/seller verifies `manual_payment_submissions`
4. On verification: confirm order

#### Refunds

1. Customer requests refund with reason
2. Admin/seller approves/rejects
3. Stripe/PayPal refund via API or manual bank transfer
4. `refunds` table tracks status

---

### PHASE 13 — Orders

**Migrations:**
- `addresses`, `orders`, `vendor_orders`, `order_items`, `order_status_histories`

**Tasks:**

1. Order creation from checkout (via `OrderService`):
   - One `orders` record
   - Multiple `vendor_orders` — one per seller + one for Openbox if applicable
   - `order_items` linked to each vendor order
2. Customer order history and detail page
3. Vendor (business/saler) order management:
   - View orders, update status (confirmed, packed, shipped, delivered)
   - Tracking number input
   - Print packing slip
4. Admin order overview: all orders, all vendor orders, filter by status
5. Order cancellation flow (time-limited, before processing)
6. Return flow: customer requests return → seller approves → refund triggered
7. Order status email notifications at each step

---

### PHASE 14 — Invoice System

**Migrations:**
- `invoices`, `invoice_items`

**Tasks:**

1. Auto-generate invoice on order confirmation
2. Invoice includes: grade, condition, product details
3. Invoice number format: `INV-2026-000001`
4. PDF generation (DomPDF)
5. Download from customer account
6. Download from seller dashboard
7. Email invoice to customer on order completion
8. Admin invoice listing with export (CSV, PDF)

---

### PHASE 15 — Reviews & Ratings

**Migrations:**
- `reviews`, `review_images`, `review_replies`, `review_reports`

**Tasks:**

1. Only allow review from verified purchase (delivered order)
2. Review types: product, seller, business
3. Customer: submit rating (1-5), title, body, photos
4. Seller: reply to review
5. Admin: approve/reject reviews, handle reports
6. Average rating displayed on product card, store page
7. Review moderation queue in admin

---

### PHASE 16 — Chat & Support

**Migrations:**
- `chat_conversations`, `chat_messages`, `support_tickets`, `support_ticket_messages`, `support_ticket_attachments`

**Tasks:**

#### Buyer ↔ Seller Chat

1. "Ask Seller" button on product page
2. Creates conversation linked to product + seller
3. Real-time messaging via Laravel Echo + Pusher/Reverb
4. Seller chat inbox in dashboard
5. Unread message count in nav badge
6. Admin: view all conversations (moderation)
7. Report conversation option for buyer/seller

#### Support Tickets

1. Customer opens ticket from account (subject, category, body, attachment)
2. Admin/support sees ticket queue with priority
3. Reply thread (admin ↔ customer)
4. Status updates with email notification
5. Close/reopen ticket

---

### PHASE 17 — Notifications

**Tables:** Laravel `notifications` table + `notification_preferences`

**Tasks:**

1. All notification events listed in the Notifications section above implemented
2. Notification bell in nav: unread count + dropdown list
3. Full notifications page: filter by type, mark all read
4. User notification preferences: which events to receive email for
5. Queue all notifications via Laravel Queues

---

### PHASE 18 — Commission & Payout

**Migrations:**
- `seller_wallets`, `seller_transactions`, `commission_rules`, `commission_records`, `seller_payouts`

**Tasks:**

1. Admin: configure commission rules (global → category → seller override)
2. On order item delivery confirmed: calculate commission, credit seller wallet
3. Seller wallet dashboard: available balance, pending balance, transaction history
4. Seller: request payout (minimum amount, method selection)
5. Admin: approve payout, process via Stripe/PayPal or manual
6. Payout history and status
7. Commission report for admin

---

### PHASE 19 — Blog & CMS

**Migrations:**
- `blog_categories`, `posts`, `tags`, `post_tags`, `pages`, `banners`, `settings`

**Tasks:**

1. Admin: blog post CRUD (draft, publish, schedule, archive)
2. Blog categories and tags
3. SEO fields per post
4. Public blog index and post page
5. Admin: CMS pages (About, Privacy, Terms, FAQ, etc.)
6. Admin: banner management (homepage, category pages)
7. Admin: site settings (site name, logo, contact info, social links, maintenance mode)

---

### PHASE 20 — Reports

**Tasks:**

#### Admin Reports

- Sales report (by date, seller type, category)
- Revenue and commission report
- Order report (status breakdown)
- User report (registrations, KYC status)
- Product report (active, verified, warehouse)
- Subscription report (active, expired, revenue)
- Verification report (appointments, pass/fail rate)
- Warehouse report (stock, movements)
- Payout report

#### Business Reports

- Sales, orders, revenue, top products, customer overview

#### Saler Reports

- Sales, listings, subscription usage, views, revenue

**Export:** CSV, Excel, PDF, Print on all tables

---

### PHASE 21 — Security Hardening (Continuous)

**Tasks:**

1. CSRF on all forms
2. XSS protection (Blade auto-escaping enforced)
3. SQL injection protection (Eloquent only, no raw queries with user input)
4. Authorization policies for all resources
5. Rate limiting on login, registration, OTP, API endpoints
6. Secure file uploads: MIME validation, size limits, private disk for documents
7. Private document storage: verification docs, KYC files never on public disk
8. Encrypted sensitive fields (vendor payment account references)
9. Webhook signature validation (Stripe, PayPal)
10. Payment idempotency keys
11. Audit log (`activity_logs`) for all admin/verifier actions
12. Session security (regenerate on login, logout on all devices option)
13. Login activity log (IP, device, timestamp)
14. Role-based access with `ProductPolicy`, `OrderPolicy`, etc.

---

### PHASE 22 — Performance & Production

**Tasks:**

1. Migrate storage to Cloudflare R2 / S3
2. Set up MySQL indexes on all foreign keys and frequently queried columns
3. Implement caching for: categories tree, settings, product listings
4. Set up Laravel Horizon for queue monitoring
5. Set up Laravel Telescope (dev only)
6. Move to Meilisearch or Typesense for product search
7. Set up CDN for images
8. Set up error monitoring (Sentry or Bugsnag)
9. Configure Laravel Scheduler for:
   - `ExpireListings` — daily
   - `SendSubscriptionExpiryReminders` — daily
   - `ProcessPendingPayouts` — daily
   - `CleanupExpiredCarts` — weekly
10. Final security audit
11. Load testing

---

## Development Milestones

| Release | Target |
|---|---|
| V0.1 | Foundation + Auth + Roles |
| V0.2 | KYC + Verification System |
| V0.3 | Categories + Brands + Attributes + Products |
| V0.4 | Grading System + Stores + Marketplace |
| V0.5 | Product Verification + Warehouse |
| V0.6 | Subscriptions (Saler Credits + Business Monthly/Yearly) |
| V0.7 | Cart + Checkout + Payment Routing |
| V0.8 | Payments + Orders + Invoices |
| V0.9 | Reviews + Chat + Support + Commission + Payouts |
| V1.0 | Blog + CMS + Reports + Security + Production |

---

## Dummy Data Setup

For development and testing, a `DummyDataSeeder` is available to populate the database with users, categories, brands, and products. The dummy products feature dynamically generated real-world images from `picsum.photos`.

**Run the Seeder:**
```bash
php artisan migrate:fresh --seed
```

### Generated Accounts
All accounts use the password: `password`

| Role | Email | Status | Verification |
|---|---|---|---|
| **Admin** | `admin@openbox.com` | Active | Fully Verified (`email_verified_at` set) |
| **Business** | `business@openbox.com` | Active | Fully Verified (`email_verified_at` set) |

### Generated Catalog
- **Categories:** Electronics, Smartphones
- **Brands:** Apple, Samsung
- **Products:** 103 items total (3 hardcoded base products + 100 randomly generated live products)

All products are generated via the `ProductFactory` and are assigned placeholder images automatically.

---

## Key Business Rules Summary

| Rule | Detail |
|---|---|
| Grade assignment | Verifier only, during physical inspection |
| Grade lock | Permanent after assignment, seller cannot change |
| Verification lock | Title, condition, description, price, images, serial/IMEI locked after verified |
| Saler subscription | Credit-based (one-time purchase, N credits for N days) |
| Business subscription | Recurring monthly or yearly |
| Openbox payment route | Admin products + warehouse-deposited products |
| Seller payment route | Business products + saler products (their own Stripe/PayPal) |
| Review eligibility | Only customers with confirmed delivered order |
| Warehouse auto-route | Any product with `warehouse_status = stored` → `payment_route = openbox` |
| Commission (Openbox-route sales) | Platform takes commission on openbox-route sales |
| Commission (Seller-route sales) | Seller receives directly; platform commission via subscription revenue |

---

## AI Agent Build Instructions

When building phase by phase, follow this order for each phase:

```
1. Migrations (run and verify)
2. Models (with fillable, relationships, casts)
3. Enums (PHP 8.1+ backed enums)
4. Policies (authorization rules)
5. Form Requests (validation)
6. Service class (business logic)
7. Controller (thin, delegates to service)
8. Routes (grouped by middleware/role)
9. Blade views (use components)
10. JavaScript (only what's needed)
11. Email notifications
12. Tests (feature tests per workflow)
```

Never put business logic in controllers. Controllers call services. Services call models.

---

*Document version: 1.0 | Project: Openbox | Stack: Laravel + Blade + Tailwind*
