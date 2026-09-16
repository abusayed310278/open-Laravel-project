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
