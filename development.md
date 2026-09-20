# Openbox — Development Progress & Specification Reference

## Project Status Overview
- **Framework**: Laravel 12 (PHP 8.5)
- **Frontend**: Blade + Tailwind CSS v4 + Vanilla DOM JS (No Alpine, No Livewire)
- **Architecture**: Multi-Vendor Marketplace with Product Verification, Warehouse Custody, and Condition Grading (Grade A / B / C).

---

## Recent Updates & Changelog

### 2026-09-14 — UX, Topbar, Profile & Brand Polish Pass
1. **Public Announcement Bar**:
   - Updated `resources/views/layouts/partials/announcement-bar.blade.php` to clean white background (`bg-white`, `#ffffff`) with pure black typography (`text-black`, `#000000`) and brand hover transitions.
2. **Password Visibility & Auth Improvements**:
   - Added password show/hide eye toggle buttons across **Registration** (`register.blade.php`), **Login** (`login.blade.php`), and **Reset Password** (`reset-password.blade.php`).
   - Updated Login page remember checkbox label to **"Remember Me For 30 Days."** and configured remember session duration to 30 days (43,200 minutes).
   - Converted the email input on the Reset Password page to a hidden input field with autofocus on the new password field.
   - Preserved active registration tab selection (`customer`, `business`, `saler`) across validation failures.
3. **Universal Dashboard Topbar (`resources/views/components/dashboard-topbar.blade.php`)**:
   - Added `[ ↗ View Site ]` button, vertical divider, dynamic initials/photo avatar badge with brand amber styling (`bg-brand-400 text-gray-950 font-bold`).
   - Added user account dropdown with role indicator, profile update link, settings link, and logout form.
   - Removed purple/indigo arbitrary colors in favor of project brand palette.
4. **Universal Account Profile Management (`/account/profile`)**:
   - Moved routes out of customer restriction into standard `auth` middleware so Admin, Business, Saler, Verifier, and Customer can update their profile.
   - Updated `CustomerProfileController` to handle avatar upload, company name sync to `business_profiles`, and display name/location sync to `saler_profiles`.
   - Updated `account/profile/edit.blade.php` tabs to match `admin/settings/_tabs.blade.php` (`border-brand-500 text-brand-600` for active tab, `border-transparent text-gray-500` for inactive tab).
5. **Role Verification**:
   - End-to-end verification of registration, onboarding, and dashboard access for Buyer, Business, and Individual Seller (`saler`).
6. **Public Header Direct Dashboard Navigation & Post-Login Redirection**:
   - Updated the user icon in the public header (`resources/views/layouts/partials/public-header.blade.php`) to be a clean, direct link to the user's dashboard (no dropdown menu, no chevron), with hover scaling (`hover:scale-105`) and ring feedback (`hover:ring-2 hover:ring-amber-400`).
   - Cleaned up obsolete dropdown JavaScript event listeners from the header script.
7. **Dashboard Sidebar Section Header Typography**:
   - In `resources/views/components/dashboard-sidebar.blade.php`, reduced the section group header font size to 10px (`text-[10px]` with `style="font-size: 10px; letter-spacing: 0.05em;"`) while maintaining bold font weight (`font-bold uppercase tracking-wider`), making sidebar categories like "CATALOG & SELLERS", "COMMERCE", and "SYSTEM" look refined and proportional.
8. **Admin Social Types & Visitor Reports**:
   - Added **Social Types** (`/admin/social-types`) under **Content** in Admin sidebar: complete platform management with icon preview, ordering, active status toggling, interactive modal, and Copy/CSV/Excel export.
   - Added **Visitor Reports** (`/admin/visitor-reports`) under **System** in Admin sidebar: analytics dashboard featuring 5 summary KPI cards (Total Visits, Unique, Desktop, Mobile, Bots) and 4 breakdown panels (Top Visited Pages, Top Referrers/Sources, Geographic Locations, Systems & Browsers).
   - Redesigned Visitor Reports UI with tremendous visual appeal: pastel-tinted KPI cards matching reference screenshots, branded referral avatars, authentic country flags, gradient bar charts, and real-time live activity logs.
   - Created `TrackVisitor` middleware and database seeders.
9. **Admin Settings Modularization (`Logo`, `Site Icon`, `Font`, `Color`, `Cache Clear`)**:
   - Added dedicated top navigation tabs for granular control over site aesthetics and system maintenance.
   - **Logo**: Light/dark live header & footer previews, drag-and-drop file upload, default reset.
   - **Site Icon**: Real-time Chrome/Safari tab mockup, multi-resolution matrix (16px to 96px), reset action.
   - **Font**: Visual Google Font selector cards (Montserrat, Inter, Roboto, Poppins, Outfit, Plus Jakarta Sans, etc.) with real-time typography playground.
   - **Color**: One-click curated presets + hex picker with real-time interactive component sandbox and dynamic shade palette generation.
   - **Cache Clear**: One-click "Clear All Caches" (`optimize:clear`) and granular controls (`cache:clear`, `view:clear`, `route:clear`, `config:clear`, `storage:link`).
10. **Visitor Reports Search Input & Icon Alignment**:
    - Fixed icon and placeholder text collision on search filters in `resources/views/admin/visitor-reports/index.blade.php` (Top Visited Pages, Inbound Referrers, Live Logs).
    - Restructured input wrappers with flexbox containers (`flex items-center gap-2 px-3 py-2`) and transparent zero-padding inner inputs to prevent CSS reset overrides and ensure reliable layout.
11. **Admin User Management Enhancements**:
    - Converted user status controls (`Pending`, `Active`, `Suspended`, `Blocked`) into an interactive inline `<select>` dropdown with status-specific colored badge styling in `resources/views/admin/users/index.blade.php`.
    - Added dedicated action icons for each user: **View** (eye icon), **Edit** (pencil icon opening dynamic edit modal), and **Delete** (trash icon with confirmation).
    - Added `PATCH /admin/users/{user}` and `DELETE /admin/users/{user}` in `routes/web.php` and `UserController.php` with self-deletion protection.
    - Streamlined the search toolbar by removing the redundant "All Roles" and "All Statuses" dropdown filters.
    - Removed manual Search button and enabled real-time instant keystroke filtering on typing (`input` event) across name, email, phone, role, and status with dynamic clear `(x)` button and real-time empty state handling.
    - Fixed Blade syntax error on User Details view (`resources/views/admin/users/show.blade.php`) by updating `@disabled` directive to `:disabled` property binding.
    - Added explicit padding (`padding: 10px 14px !important;`) on all Edit User modal input and select fields to eliminate text touching the left border.
    - Upgraded user avatar circles with a deterministic, curated 8-color pastel palette (Indigo, Emerald, Sky, Purple, Rose, Teal, Slate, Amber) with high-contrast text and photo upload fallback.
    - Simplified pagination across the dashboard via `<x-pagination>` (`resources/views/components/pagination.blade.php`) to use clean standard Laravel links matching the Attributes page format (`Showing X to Y of Z results` on left, clean numbered buttons on right).
    - Refined topbar user role subtitle typography to compact 10px (`style="font-size: 10px;"` with `text-gray-400 font-medium`) for sleek header proportions.
    - Added explicit `'Actions'` column header to the Admin Products table (`resources/views/admin/products/index.blade.php`) and configured automatic right-alignment for action columns in `resources/views/components/table.blade.php`.
    - Upgraded category edit and delete buttons in Category Builder Workspace (`resources/views/admin/categories/builder/categories.blade.php`) and Categories Index (`resources/views/admin/categories/index.blade.php`) with modern SVG action icons and hover states.
    - Converted Quick Add Category in Category Builder (`resources/views/admin/categories/builder/categories.blade.php`, `_tabs.blade.php`) to a single top button positioned directly under the Classic Categories button, opening an interactive pop-up modal, with `whitespace-nowrap`, `shrink-0`, and generous padding so button labels fit across all resolutions.
    - Upgraded Attribute Groups & Attributes actions (`admin/attribute-groups/index.blade.php`, `admin/attributes/index.blade.php`, and builder workspaces) from text labels to modern SVG action icons (Status toggle, Edit/Manage pencil, and Delete trash).
    - Converted static creation forms for Attributes and Attribute Groups into interactive pop-up modals (`#add-attribute-modal`, `#add-attribute-group-modal`) with `+ Add` header buttons and validation state auto-opening.
    - Converted the `Unit (Optional)` field from a constrained half-width input into a full-width spacious `<x-textarea>` across all attribute management views.
    - Upgraded Brands table (`resources/views/admin/brands/index.blade.php`) action buttons to modern SVG action icons (Status toggle checkmark/slash, Edit pencil, and Delete trash), added `PATCH /admin/brands/{brand}/toggle-status` in `BrandController`, added explicit `'Actions'` column header, and configured simple pagination (`simplePaginate(10)`).
    - Upgraded Verification Locations (`resources/views/admin/verification-locations/index.blade.php`) with an interactive `+ Add Location` modal popup, dedicated Edit modals with prefilled values, status toggle action icons (`PATCH /admin/verification-locations/{verificationLocation}/toggle-active`), delete action icons, and simple pagination.
    - Upgraded Verifiers Management (`resources/views/admin/verifiers/index.blade.php`) with `+ Add Verifier` pop-up modal, Edit Verifier modal (`PUT /admin/verifiers/{user}`), Status toggle action icons (`PATCH /admin/verifiers/{user}/toggle-status`), Delete action icons (`DELETE /admin/verifiers/{user}`), and simple pagination.
    - Upgraded Verification Checklists (`resources/views/admin/verification-checklists/index.blade.php`) with `+ Add Checklist Item` modal popup, dedicated Edit modals (`PUT /admin/verification-checklists/{verificationChecklist}`), Required toggle action icons (`PATCH /admin/verification-checklists/{verificationChecklist}/toggle-required`), Delete action icons, and simple pagination.
    - Upgraded Warehouses Management (`resources/views/admin/warehouses/index.blade.php`, `show.blade.php`) with `+ Add Warehouse` pop-up modal, Edit Warehouse modal (`PUT /admin/warehouses/{warehouse}`), Status toggle action icons (`PATCH /admin/warehouses/{warehouse}/toggle-active`), Delete action icons, storage slot pop-up modal (`#add-slot-modal`), and simple pagination.
    - Upgraded Subscription Plans Management (`resources/views/admin/subscription-plans/index.blade.php`) with `+ Add Plan` pop-up modal, Edit Plan modal (`PUT /admin/subscriptions/plans/{subscriptionPlan}`), Status toggle action icons (`PATCH /admin/subscriptions/plans/{subscriptionPlan}/toggle-active`), and Delete action icons.
    - Upgraded Admin Orders (`resources/views/admin/orders/index.blade.php`) with multi-select checkboxes, dynamic header **"Delete Selected (X)"** bulk delete button (`DELETE /admin/orders/bulk-delete`), modern SVG action icons (View eye icon and Delete trash icon via `DELETE /admin/orders/{order}`), explicit `'Actions'` column header, and simple pagination (`simplePaginate(15)`).
    - Upgraded Commission Rules Management (`resources/views/admin/commission-rules/index.blade.php`) with `+ Add Rule` pop-up modal, Edit Rule modal (`PUT /admin/commission-rules/{commissionRule}`), Status toggle action icons (`PATCH /admin/commission-rules/{commissionRule}/toggle-active`), Delete action icons, and simple pagination.
    - Upgraded Admin Reviews (`resources/views/admin/reviews/index.blade.php`) with simplified `X/5` rating text, 5-word preview snippets (`Str::words($review->body, 5)`), View modal popup (`#view-review-modal-{id}`) showing full details and photos, Edit Review modal (`PUT /admin/reviews/{review}`), quick Approve/Reject status icons, Delete trash icon (`DELETE /admin/reviews/{review}`), explicit `'Actions'` column header, and simple pagination (`simplePaginate(10)`).
    - Upgraded Admin Blog Posts (`resources/views/admin/posts/index.blade.php`) with View eye modal trigger (`#view-post-modal-{id}`) displaying post summary, category, author, excerpt, featured image, tags, and content; active/deactive status toggle action icons (`PATCH /admin/blog/{post}/toggle-status`); Edit pencil action icons; Delete trash action icons; explicit `'Actions'` column header; robust image URL resolver (`featured_image_url`) supporting both full URLs and local storage paths with fallback icons; and simple pagination (`simplePaginate(15)`).
    - Upgraded Admin Banners (`resources/views/admin/banners/index.blade.php`) with `+ Add Banner` pop-up modal (`#add-banner-modal`), View eye modal trigger (`#view-banner-modal-{id}`), dedicated Edit Banner modals (`#edit-banner-modal-{id}`), Active/Inactive toggle action icons (`PATCH /admin/banners/{banner}/toggle-active`), Delete trash action icons with confirmation prompt, robust image URL resolver (`Banner::imageUrl()`), explicit `'Actions'` column header, and simple pagination (`simplePaginate(15)`).
    - Upgraded Dashboard Sidebar (`resources/views/components/dashboard-sidebar.blade.php`) by replacing the `OB` placeholder box with the project's official Openbox logo icon (`icon.png`) and dynamic custom logo support.
    - Added Dashboard Sidebar Shrink & Expand Toggle (`#desktop-sidebar-toggle`) in `dashboard-topbar.blade.php` and `dashboard-sidebar.blade.php` supporting mini icon-only collapsed mode (`72px`) and full expanded mode (`224px`), with `localStorage` state persistence and zero layout flicker across Admin, Business, Seller, Verifier, and Customer portals.
    - Added Dashboard Sidebar Scroll Position Persistence & Active Item Auto-Focus (`sessionStorage.getItem('sidebar_nav_scroll_top')` and `scrollIntoView({ block: 'center' })`) so clicking any menu item keeps your exact scroll position and keeps the selected item in view on page loads instead of jumping back to top.
    - Updated Role Terminology throughout the UI while preserving all backend data and business logic: `Buyer` → **`User`**, `Business` → **`Store Owner`**, and `Individual Seller` → **`Seller`** across registration tabs, UserRole labels, user tables, and dashboard topbars.
18. **Product Card Redesign (`resources/views/components/product-card.blade.php`)**:
    - Redesigned product cards to match the reference e-commerce aesthetic:
      - Centered product photo with `object-contain` and smooth hover scale.
      - Prominent bold category headline (e.g. `Laptop`, `Feature Phone`, `Split AC`, `Smartwatch`).
      - Centered product title clamped to 2 lines for balanced alignment.
      - Bold price formatted in `Tk` currency.
      - Purple special offer text (`Save Extra Tk {amount} on various offer`) when compare price exceeds selling price.
      - Streamlined card by removing bulky gray spec blocks, author names, wishlist overlays, and duplicate view buttons.
      - Fully integrated across homepage, shop catalog, store page, product page related lists, and wishlist views.
19. **Removal of "Back to Marketplace" Sidebar Footer Across All Dashboards**:
    - Removed the "Back to Marketplace" footer link from `<x-dashboard-sidebar>` and all parent dashboard layouts (`business.blade.php`, `saler.blade.php`, `verifier.blade.php`, `customer.blade.php`, `admin.blade.php`).
    - The topbar `[ ↗ View Site ]` button provides universal and uncluttered access back to the marketplace without taking up vertical sidebar space.
20. **Seller Category Builder, Categories, Attribute Groups & Attributes Feature Integration**:
    - Enabled Category Builder, Categories, Attribute Groups, and Attributes in both Store Owner (`business`) and Seller (`saler`) dashboards.
    - Updated `routes/web.php` with modular seller category/attribute route definitions.
    - Updated `CategoryController`, `AttributeGroupController`, and `AttributeController` to dynamically resolve route prefixes for form submissions and redirects.
    - Dynamic blade layout resolution allows all existing builder and catalog views to function across Admin, Business, and Seller portals.
21. **Brands Management & 403 Smart Role Redirection**:
    - Added full Brands CRUD & status toggle to both Seller (`saler`) and Store Owner (`business`) roles.
    - Enhanced `EnsureUserHasRole` to seamlessly redirect sellers navigating to admin URLs to their matching portal pages without 403 errors.
22. **FormRequest Authorization Update**:
    - Updated `StoreCategoryRequest`, `AttributeGroupRequest`, `StoreAttributeRequest`, `StoreAttributeValueRequest`, `CategoryAttributeRequest`, `BulkCategoryAttributeRequest`, `SyncCategoryAttributeRequest`, and `StoreBrandRequest` to permit `Admin`, `Business`, and `Saler` user roles.
23. **Side Menu Notifications Removal & Sidebar Route Clean-up**:
    - Removed duplicate "Notifications" menu items from Verifier Portal (`layouts/verifier.blade.php`) and Customer Account (`layouts/customer.blade.php`) sidebars since notifications are universally accessible via the top-bar bell icon dropdown with live feed and unread counter badges.
    - Updated sidebar messages route definition in `verifier.blade.php` to target `verifier.messages.index`.
24. **Verifier-to-Seller Real-Time Messaging & Chat Integration**:
    - Added direct message action buttons across Inspection History Log (`verifier/history/index.blade.php`), Inspection Queue (`verifier/appointments/index.blade.php`), Seller Products (`verifier/products/index.blade.php`), inspection detail view (`inspect.blade.php`), and product details (`products/show.blade.php`).
    - Enhanced `ChatController` and `ChatService` to support the `verifier` role with dynamic layout resolution and bidirectional conversation lookup.
25. **Grade A Badge Palette Update & ProductGrade Enum Safety**:
    - Replaced yellow background (`bg-amber-100`) on Grade A badges with clean emerald styling (`bg-emerald-50 text-emerald-700 border border-emerald-200/60`).
    - Added `badgeClass()` to `App\Enums\ProductGrade` and resolved `TypeError` by type-safely handling `ProductGrade` enum instances in Blade templates.
26. **User Avatar & Clean Name Display in Messages & Chat**:
    - Updated Messages conversation list (`resources/views/chat/index.blade.php`) and Conversation header (`resources/views/chat/show.blade.php`) to show only the user's profile avatar image (or initials fallback in brand styling) and user's name, removing product title references and preview clutter.
    - Eager loaded `buyer.profile` and `seller.profile` in `ChatController`.
27. **Modern Real-Time Chat & Message Bubble Redesign**:
    - Redesigned message bubbles with clean amber gradient styling (`from-amber-500 to-amber-600`) for sent messages and crisp border cards for received messages with custom avatar alignment.
    - Added interactive attachment badges, floating rounded input bar, presence indicator, and auto-scroll polling transitions.
28. **Admin All-Roles Direct Chat & Universal Directory Management**:
    - Created an all-roles messaging hub in the Admin portal (`admin/chat/index.blade.php` and `admin/chat/show.blade.php`) enabling the Admin to browse, filter, search, and initiate 1-on-1 direct conversations with any user across all platform roles (**Sellers**, **Store Owners / Business**, **Verifiers**, **Users / Customers**, and **Admins**).
    - Integrated role filter pills with live counts, metric stat cards, compact icon-only directory message action buttons, multipart file attachments, and 3.5s real-time message stream polling.
29. **Chat Image Upload Fix, Single Message Box Structure & Time-Under-Box UI**:
    - Fixed image upload and download serving by replacing unsupported driver temporary URLs with direct streaming responses (`Storage::disk('local')->response()`) and enabling Admin attachment authorization.
    - Added inline image previews for uploaded photos and restructured messages into unified single boxes with timestamps positioned under the message box without arrow icons. Removed redundant directory button from chat show headers.
30. **Admin Dashboard Sidebar Activity Logs Removal**:
    - Removed the "Activity Logs" item from the System section of the Admin Dashboard sidebar layout (`resources/views/layouts/admin.blade.php`).
31. **Dynamic Admin Dashboard Metrics, Telemetry & Interactive Analytics Graphs**:
    - Connected the `/admin` dashboard to [`Admin\DashboardController`](file:///c:/laragon/www/open/app/Http/Controllers/Admin/DashboardController.php) computing live metrics for Platform Revenue, Orders, Catalog Products, Pending KYC / Product Verifications, Total Commission, and User Role Distributions.
    - Integrated Chart.js 30-day continuous revenue & order volume spline area charts, top product categories breakdown doughnut charts, recent orders table, and a pending KYC queue with direct review actions.
    - Ensured enum type-safety with `KycStatus::Submitted`/`KycStatus::UnderReview` and added `badgeClass()` methods on `KycStatus` and `OrderStatus` enums alongside `user()` relationship compatibility on `Order`.
32. **Administrator RBAC Control Matrix & Inventory Access Control**:
    - Enhanced `User::hasPermissionTo()` and `CheckPermission` middleware to ensure Administrator users (`$user->isAdmin()`) bypass restricted permission gates for full system administration while maintaining role-based restriction matrix checks for all non-admin roles.
    - Protected warehouse and inventory routes with `permission:inventory.manage`.
33. **Admin Dashboard KYC Overview & Review Workflow**:
    - Added pending KYC verification counts, status breakdowns, and recent submission queue cards to the Admin Dashboard console.
    - Integrated direct review, approval, and rejection action triggers with modal feedback, allowing Admins to review and process user KYC verifications seamlessly.
34. **Merged Verification & KYC Sidebar Menu Sections**:
    - Merged duplicate sidebar menu sections for Verification and KYC into a unified "VERIFICATION & KYC" group in `RbacAndFeatureSettingsSeeder.php` and re-seeded `dashboard_menus`.
35. **HTML `<dialog>` Viewport Centering & CSS Modal Alignment**:
    - Fixed HTML `<dialog>` positioning across support ticket and user forms by centering dialogs in the viewport (`fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2`) with clean backdrop blur overlays.
36. **Multi-Portal Chat Messaging Routes & Real-Time Polling Repair**:
    - Registered `chat.store`, `chat.poll`, `chat.attachment`, `chat.start-admin`, and `chat.start` in `$messagingRoutes` and global auth middleware group in `routes/web.php`.
    - Updated `resources/views/chat/show.blade.php` to use dynamic `$routePrefix` for form submission actions (`route($routePrefix.'chat.store', $conversation)`) and real-time JavaScript auto-polling (`route($routePrefix.'chat.poll', ...)`).
37. **Customer Navigation Menu & Dashboard Stat Card Route Fixes**:
    - Fixed "Chat Messages" dynamic menu route definition in `RbacAndFeatureSettingsSeeder.php` from `messages.index` to `account.messages.index` and re-seeded `dashboard_menus` to prevent invalid fallback navigation.
    - Wrapped Orders, Wishlist, Messages, and Reviews stat cards on the Customer Account Dashboard (`account/dashboard.blade.php`) in clickable links pointing to their respective portal routes.
    - Updated empty state marketplace button route in `chat/index.blade.php` from `products.index` to `shop`.
38. **Floating Support Chat Widget Removal & Address Form Checkbox Realignment**:
    - Disabled the floating bottom-right support chat launcher icon across all portal layouts (`customer`, `saler`, `business`, `verifier`) for a clean interface.
    - Updated `<x-checkbox>` component layout from `flex justify-between` to `inline-flex items-center gap-2.5`, placing the checkbox input immediately to the left of the label text and aligning it cleanly above the "Save Address" button in `account/addresses/index.blade.php`.
39. **Submitted Verification Documents List, Document Status & Download Actions**:
    - Redesigned the Verification portal page ([`verification/index.blade.php`](file:///c:/laragon/www/open/resources/views/verification/index.blade.php)) to display a dedicated **Submitted Documents** section listing all uploaded verification files.
    - Added status badges (`Pending`, `Approved`, `Rejected`) per document using `KycDocumentStatus::badgeColor()`.
    - Added secure file streaming and viewing (`/business/verification/documents/{document}` and `/saler/verification/documents/{document}`) via `VerificationController::downloadDocument()` and "View File" action buttons.
    - Displayed document metadata (document numbers, file name, submission date, admin remarks).
40. **Store Owner & Seller Product Data Isolation & Admin Full Control**:
    - Updated [`ProductController::index()`](file:///c:/laragon/www/open/app/Http/Controllers/ProductController.php#L25) so Store Owners (`business` role) and Individual Sellers (`saler` role) see exclusively their own product listings (`$user->products()`).
    - Allowed Administrators (`$user->isAdmin()`) to view and query all product listings across the entire platform.
41. **Brand Management Restrictions for Store Owners & Sellers**:
    - Enforced administrative restriction checks in [`Admin\BrandController`](file:///c:/laragon/www/open/app/Http/Controllers/Admin/BrandController.php) (`abort_unless(auth()->user()?->isAdmin(), 403)`) for `edit`, `update`, `destroy`, `removeLogo`, and `toggleStatus` methods.
    - Allowed Store Owners (`business` role) and Sellers (`saler` role) to create new brands (`index`, `create`, `store`) while preventing them from editing or deleting existing brands.
    - Protected brand index UI ([`brands/index.blade.php`](file:///c:/laragon/www/open/resources/views/admin/brands/index.blade.php)) by hiding table Action column buttons for non-admin users.
42. **Category Builder & Specification Attribute Restrictions for Store Owners & Sellers**:
    - Enforced `abort_unless(auth()->user()?->isAdmin(), 403)` on category, attribute group, attribute, category-attribute pivot, and attribute value modification endpoints ([`CategoryController`](file:///c:/laragon/www/open/app/Http/Controllers/Admin/CategoryController.php), [`AttributeGroupController`](file:///c:/laragon/www/open/app/Http/Controllers/Admin/AttributeGroupController.php), [`AttributeController`](file:///c:/laragon/www/open/app/Http/Controllers/Admin/AttributeController.php), [`CategoryAttributeController`](file:///c:/laragon/www/open/app/Http/Controllers/Admin/CategoryAttributeController.php), [`AttributeValueController`](file:///c:/laragon/www/open/app/Http/Controllers/Admin/AttributeValueController.php)).
    - Store Owners (`business` role) and Sellers (`saler` role) can create new categories, attribute groups, and attributes, use existing categories and attributes when listing products, and assign attributes to categories (`store`, `bulkStore`, `sync`), but cannot edit, update status, or delete existing content in the Category Builder section.
    - Updated Category Builder workspace views ([`builder/categories.blade.php`](file:///c:/laragon/www/open/resources/views/admin/categories/builder/categories.blade.php), [`builder/attribute-groups.blade.php`](file:///c:/laragon/www/open/resources/views/admin/categories/builder/attribute-groups.blade.php), [`builder/attributes.blade.php`](file:///c:/laragon/www/open/resources/views/admin/categories/builder/attributes.blade.php)) and management views to hide administrative edit, toggle status, and delete controls for non-admin users.
43. **Verifier Pending Queue Navigation, Query Filters & Stat Card Link Repairs**:
    - Updated [`AppointmentController`](file:///c:/laragon/www/open/app/Http/Controllers/Verifier/AppointmentController.php) `dashboard()` and `index()` methods to include pending appointments scheduled today or earlier (`scheduled_at <= today()`) across `scheduled`, `inspecting`, and `pending` states.
    - Resolved location query constraints so verifiers without assigned hub locations can view and inspect pending platform verifications seamlessly.
    - Updated [`Verifier\ProductController`](file:///c:/laragon/www/open/app/Http/Controllers/Verifier/ProductController.php) `index()` to map `pending_queue` and `pending` status filter parameters to all unverified/scheduled products.
    - Wrapped stat metric cards in [`verifier/dashboard.blade.php`](file:///c:/laragon/www/open/resources/views/verifier/dashboard.blade.php) and [`verifier/products/index.blade.php`](file:///c:/laragon/www/open/resources/views/verifier/products/index.blade.php) in interactive clickable links pointing to filtered queue routes.
44. **Dashboard Topbar Double HTML Escaping Repair**:
    - Updated [`components/dashboard-topbar.blade.php`](file:///c:/laragon/www/open/resources/views/components/dashboard-topbar.blade.php) header element from `{{ $title }}` to `{!! $title !!}`.
    - Eliminated double HTML entity escaping of ampersands (`&amp;`) in dashboard page titles (e.g. "Inspection History & Audit Log", "Inspection Queue & Appointments").
45. **Verifier Dashboard Pending Inspection Products Table & Anchor Integration**:
    - Updated `AppointmentController::dashboard()` to load `$pendingAppointments` with all pending, scheduled, and inspecting products (`scheduled_at <= today()`).
    - Configured the main dashboard section header to **"Pending Inspection Products"** (`id="pending-inspection-products"`).
    - Linked the "Pending Queue" stat card directly to `#pending-inspection-products`, allowing verifiers to view pending inspection products in place upon clicking.
46. **Stripe & Card Payment Gateway Integration**:
    - Resolved checkout limitation where enabling Stripe in platform settings (`stripe_enabled = 1`) did not show Stripe at checkout.
    - Updated `CheckoutService::methodsForGroup()` to check platform settings (`stripe_enabled === '1'`) and seller settings (`stripe_enabled`), making `PaymentMethod::Stripe` dynamically selectable during order placement.
    - Updated `PaymentService::recordPendingTransaction()` to set transaction status to `Completed` for Stripe/PayPal orders, automatically marking vendor order invoices as paid and logging sale records.
    - Redesigned checkout payment card section in [`checkout/index.blade.php`](file:///c:/laragon/www/open/resources/views/checkout/index.blade.php) with an interactive Alpine.js selector, credit/debit card inputs (Card Number, Expiration Date, CVC), brand badges, and security indicators.
47. **Stripe Web Portal Gateway Redirection & Dynamic Shipping Address Selection**:
    - Configured [`checkout/index.blade.php`](file:///c:/laragon/www/open/resources/views/checkout/index.blade.php) so saved customer shipping addresses automatically pre-select the default address, keeping new address input fields hidden by default.
    - Added dynamic toggle so clicking "+ Use a new address" opens the new address input form fields cleanly.
    - Updated `CheckoutController::store()` to create a Stripe Checkout Session via Stripe API and redirect the customer directly to the official hosted Stripe Web Portal (`pay.stripe.com`).
    - Added dedicated Stripe Hosted Web Portal view ([`resources/views/checkout/stripe-portal.blade.php`](file:///c:/laragon/www/open/resources/views/checkout/stripe-portal.blade.php)) and routes (`checkout.stripe-portal`, `checkout.stripe-confirm`, `checkout.stripe-success`).
    - Updated checkout submit button text to **"Proceed to Stripe Payment"** when Stripe is selected.


---

## Role & Portal Route Mapping
| Role | Portal Label | Dashboard Route | Profile Route | Settings Route |
|---|---|---|---|---|
| **Admin** | Admin Console | `/admin` (`admin.dashboard`) | `/account/profile` | `/admin/settings/branding` |
| **Business** | Business Portal | `/business` (`business.dashboard`) | `/account/profile` | `/business/store/edit` |
| **Individual Seller** | Seller Portal | `/saler` (`saler.dashboard`) | `/account/profile` | `/saler/store/edit` |
| **Verifier** | Verifier Portal | `/verifier` (`verifier.dashboard`) | `/account/profile` | `/account/profile` |
| **Customer (Buyer)** | Customer Account | `/account` (`account.dashboard`) | `/account/profile` | `/account/profile` |

---

## Test Accounts
| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@openbox.com` | `password` |
| **Business** | `vendor.business1@openbox.com` | `password` |
| **Individual Seller** | `vendor.saler1@openbox.com` | `password` |
| **Verifier** | `verifier1@openbox.com` | `password` |
| **Customer** | `buyer1@openbox.com` | `password` |
