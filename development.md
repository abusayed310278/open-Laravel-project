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
