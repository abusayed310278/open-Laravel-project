# OpenBox — Vendor Dashboard Design Specification
**Stack:** Laravel 12 · Blade Templates · Tailwind CSS (CDN) · Vanilla JS  
**Theme:** `#F9FAFB` bg · `#111827` text · `#F59E0B` accent · `#FFFFFF` card bg  
**Border radius:** `rounded` (4px) or `rounded-md` (6px) only  
**No gradients · No Alpine · No Livewire · Mobile-first · Left sidebar (drawer on mobile)**

---

## Design System — Dashboard

### Shell Layout
```
┌─────────────────────────────────────────────────────┐
│  [Sidebar 224px fixed]  │  [Topbar] [Main content]  │
│                         │                           │
│  Logo                   │  Page Title    [User]     │
│  ─────────────          │  ───────────────────────  │
│  Navigation             │                           │
│  ─────────────          │  Content area             │
│  Back to Marketplace    │  (scrollable)             │
└─────────────────────────────────────────────────────┘
```
- Mobile: sidebar hidden, hamburger → left drawer (same pattern as frontend)
- Sidebar: `w-56 bg-white border-r border-gray-100 h-screen fixed left-0 top-0 flex flex-col`
- Main: `ml-56 flex flex-col min-h-screen bg-gray-50`

### Sidebar Nav Item States
```html
<!-- Active -->
<a class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium bg-amber-50 text-amber-600">
  <!-- icon + label -->
</a>

<!-- Inactive -->
<a class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-800">
  <!-- icon + label -->
</a>
```

### Topbar
```html
<header class="bg-white border-b border-gray-100 flex items-center justify-between px-8 py-4">
  <div>
    <h1 class="text-lg font-semibold text-gray-900">Page Title</h1>
    <!-- optional breadcrumb -->
  </div>
  <div class="flex items-center gap-4">
    <!-- Search, Notifications bell, Avatar dropdown -->
  </div>
</header>
```

### Stat Cards
```html
<div class="bg-white border border-gray-100 rounded-md p-5">
  <div class="flex items-center justify-between mb-3">
    <div class="w-9 h-9 bg-amber-50 rounded-md flex items-center justify-center">
      <!-- icon text-amber-500 w-5 h-5 -->
    </div>
    <span class="text-xs font-semibold text-green-500">+12.5%</span>
  </div>
  <p class="text-2xl font-bold text-gray-900">37,100 QAR</p>
  <p class="text-sm text-gray-400 mt-1">Revenue</p>
</div>
```

### Tables (Shared Style)
```html
<div class="overflow-x-auto">
  <table class="w-full text-sm">
    <thead>
      <tr class="border-b border-gray-100">
        <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3 bg-gray-50">Column</th>
      </tr>
    </thead>
    <tbody>
      <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
        <td class="px-4 py-3 text-gray-800">Value</td>
      </tr>
    </tbody>
  </table>
</div>
```

### Export Button
```html
<button onclick="exportTable('table-id', 'filename')"
  class="flex items-center gap-2 border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-md text-xs transition-colors">
  <svg class="w-3.5 h-3.5" ...><!-- download icon --></svg>
  Export CSV
</button>
```

```js
function exportTable(tableId, filename) {
  const rows  = Array.from(document.querySelectorAll(`#${tableId} tr`));
  const csv   = rows.map(r =>
    Array.from(r.querySelectorAll('th,td'))
      .map(c => `"${c.innerText.trim()}"`)
      .join(',')
  ).join('\n');
  const blob  = new Blob([csv], { type: 'text/csv' });
  const url   = URL.createObjectURL(blob);
  const a     = Object.assign(document.createElement('a'), { href: url, download: `${filename}.csv` });
  a.click();
  URL.revokeObjectURL(url);
}
```

---

## Dashboard Pages

### Nav Items (Sidebar)
```
Overview
Sales Analytics
Orders
Products
  └─ Add Product
Customers
Messages
Promotions
Reviews
Payouts
Subscription
Settings
───────────
← Back to Marketplace
```

---

## Page D1 — Overview `(/vendor/dashboard)`

### Content Layout
```
[ 4 Stat Cards ]
[ Sales Chart (full width) ]
[ Recent Orders table (half) ]  [ Top Products (half) ]
```

### Stat Cards (4-col → 2-col mobile)
- Revenue (amber icon) | Orders (amber icon) | Products (amber icon) | Views (amber icon)
- Each: icon box top-left, % change badge top-right, large number, label below

### Sales Chart
- `<canvas id="salesChart">` — Chart.js line chart
- Months on X, revenue on Y
- Color: `#7c83d4` line, `rgba(124,131,212,0.12)` fill
- No legend, clean grid lines (`#f3f4f6`)

```js
// Chart.js CDN must be included
new Chart(document.getElementById('salesChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: ['Jan','Feb','Mar','Apr','May','Jun'],
    datasets: [{
      data: [4200, 4900, 6200, 5900, 7000, 8800],
      borderColor: '#7c83d4',
      backgroundColor: 'rgba(124,131,212,0.1)',
      borderWidth: 2, fill: true, tension: 0.4, pointRadius: 0
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, border: { display: false }, ticks: { color: '#9ca3af' } },
      y: { grid: { color: '#f3f4f6' }, border: { display: false }, ticks: { color: '#9ca3af' } }
    }
  }
});
```

### Recent Orders (bottom-left, half width)
- Table: # · Product · Customer · Status badge · Amount
- 5 most recent rows
- "View All Orders" link at bottom

### Top Products (bottom-right, half width)
- Rank | Product name | Units sold | Revenue
- Progress bar per product: `bg-amber-500 h-1 rounded` inside `bg-gray-100 h-1 rounded w-full`

---

## Page D2 — Sales Analytics `(/vendor/analytics)`

### Date Range Filter
- Buttons: Today | 7 Days | 30 Days | 90 Days | Custom (date inputs appear)
- Active button: `bg-amber-500 text-white rounded-md`

### Stat Row (4 cards same as overview)

### Charts Section (2-col grid)
- Left: Revenue line chart (monthly trend)
- Right: Orders bar chart (daily count)

### Revenue by Category (full-width)
- Table: Category | Orders | Revenue | % of Total | Trend
- Export CSV button top-right

### Top Performing Products
- Table with 10 rows: Rank | Image | Name | Views | Orders | Revenue | Conversion
- Export button

### Traffic Sources (table)
- Source | Visits | Orders | Conversion Rate

---

## Page D3 — Orders `(/vendor/orders)`

### Top Controls
```
[ Search orders...input ]  [ Status filter dropdown ]  [ Date range ]  [ Export CSV ]
```

### Status Filter Tabs
- All | Pending | Processing | Shipped | Delivered | Cancelled | Refunded
- Active tab: `border-b-2 border-amber-500 text-amber-600`

### Orders Table (full width, id="orders-table")
```
# Order | Customer | Product(s) | Date | Amount | Status | Action
```
- Status: badge (same color system as frontend)
- Action: `View` link button + `Update Status` dropdown
- Pagination below

### Order Detail Modal (vanilla JS)
- Click "View" → JS adds class `hidden` removal on overlay + modal
- Modal: `fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center`
- Content: order info + items table + status update form + shipping tracking input

```js
document.querySelectorAll('.order-view-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('order-modal').classList.remove('hidden');
  });
});
document.getElementById('modal-close')?.addEventListener('click', () => {
  document.getElementById('order-modal').classList.add('hidden');
});
```

---

## Page D4 — Products List `(/vendor/products)`

### Top Controls
```
[ Search products... ]  [ Category dropdown ]  [ Status dropdown ]  [ Export ]  [ + Add Product ]
```

### Products Table (id="products-table")
```
Image | Name | Category | Price | Stock | Status | Views | Orders | Action
```
- Image: 40×40 `rounded-md bg-gray-50 object-cover`
- Status toggle: Published / Draft — click to toggle (JS + AJAX or form submit)
- Action: Edit · Delete (confirm dialog) · View on site
- Bulk select: checkbox column + bulk action bar appears at top when items checked

```js
// Bulk select
document.getElementById('select-all').addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
  toggleBulkBar();
});
function toggleBulkBar() {
  const any = Array.from(document.querySelectorAll('.row-check')).some(c => c.checked);
  document.getElementById('bulk-bar').classList.toggle('hidden', !any);
}
```

### Bulk Action Bar (appears above table)
- `bg-amber-50 border border-amber-200 rounded-md px-4 py-2 flex items-center gap-4`
- "X items selected" + Publish | Unpublish | Delete buttons

---

## Page D5 — Add / Edit Product `(/vendor/products/create)` & `(/vendor/products/{id}/edit)`

### Form Layout: `md:grid-cols-3 gap-6` (form 2-col + sidebar 1-col)

**Left (col-span-2): Main Form**

**Section: Basic Info**
```
Product Name (text, required)
Category (select, required)
Sub-category (select, populated by JS on category change)
Description (textarea, 6 rows)
```

**Section: Media**
- Drag & drop image upload area: `border-2 border-dashed border-gray-200 rounded-md p-8 text-center`
- Image previews: `grid grid-cols-4 gap-3` after upload
- JS: `input[type=file] change` → FileReader → preview

```js
document.getElementById('img-input').addEventListener('change', function() {
  Array.from(this.files).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.className = 'relative rounded-md overflow-hidden h-20 bg-gray-50';
      div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">
        <button type="button" class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded w-5 h-5 flex items-center justify-center remove-img">×</button>`;
      document.getElementById('preview-grid').appendChild(div);
    };
    reader.readAsDataURL(file);
  });
});
```

**Section: Pricing**
```
Price (QAR)         [ Original Price (QAR) — optional, for sale ]
Condition:  ○ New   ○ Grade A   ○ Grade B   ○ Grade C
```

**Section: Inventory**
```
Stock Quantity (number)
SKU / Model Number (text)
```

**Section: Shipping**
```
Weight (kg)
Dimensions (L × W × H cm)
Shipping: ○ Free   ○ Flat Rate [amount input]   ○ Calculated
```

**Right Sidebar (col-span-1): Publish Panel**
```
┌─────────────────────┐
│ Publish             │
│ Status: [Draft ▼]   │
│ Visibility: [Public]│
│                     │
│ [ Save Draft ]      │
│ [ Publish Now ]     │
└─────────────────────┘

┌─────────────────────┐
│ Category            │
│ (category select)   │
└─────────────────────┘

┌─────────────────────┐
│ Condition Grade     │
│ (radio buttons)     │
└─────────────────────┘

┌─────────────────────┐
│ Tags                │
│ (tag input)         │
└─────────────────────┘
```

Tag input (vanilla JS — Enter to add):
```js
document.getElementById('tag-input').addEventListener('keydown', e => {
  if (e.key === 'Enter') {
    e.preventDefault();
    const val = e.target.value.trim();
    if (!val) return;
    const tag = document.createElement('span');
    tag.className = 'inline-flex items-center gap-1 bg-gray-100 text-gray-700 text-xs px-2.5 py-1 rounded-md mr-1 mb-1';
    tag.innerHTML = `${val} <button type="button" class="text-gray-400 hover:text-red-500" onclick="this.parentElement.remove()">×</button>`;
    document.getElementById('tags-container').appendChild(tag);
    e.target.value = '';
  }
});
```

---

## Page D6 — Customers `(/vendor/customers)`

### Top Controls
```
[ Search customers... ]  [ Date joined filter ]  [ Export CSV ]
```

### Customers Table (id="customers-table")
```
Avatar | Name | Email | Phone | Orders | Total Spent | Last Order | Action
```
- Avatar: initials circle `w-8 h-8 bg-amber-100 text-amber-700 font-semibold text-xs rounded-md flex items-center justify-center`
- Action: View Profile link

### Customer Detail Page `(/vendor/customers/{id})`
- 2-col: Left (customer info card) | Right (stats row)
- Orders table below (same orders table style)
- Contact history (messages list)

---

## Page D7 — Messages `(/vendor/messages)`

### 2-panel layout: `md:grid-cols-3 gap-0`

**Left: Conversation List (col-span-1)**
- `border-r border-gray-100 h-[calc(100vh-64px)] overflow-y-auto`
- Each item: avatar initials + name + last message preview + time + unread badge
- Active: `bg-amber-50 border-l-2 border-amber-500`
- Search input at top

**Right: Message Thread (col-span-2)**
- Customer info bar at top
- Messages area: `flex-1 overflow-y-auto py-4 px-6 space-y-4`
  - Incoming: `justify-start`, bubble `bg-gray-100 text-gray-800 rounded-md px-4 py-2.5 max-w-xs text-sm`
  - Outgoing: `justify-end`, bubble `bg-amber-500 text-white rounded-md px-4 py-2.5 max-w-xs text-sm`
- Input bar at bottom: textarea + Send button

---

## Page D8 — Promotions `(/vendor/promotions)`

### Top: Create Promotion button

### Active Promotions Table
```
Name | Type | Discount | Code | Valid Until | Uses | Status | Action
```
- Type badges: % Discount · Fixed Amount · Free Shipping · Bundle
- Status: Active (green) · Scheduled (blue) · Expired (gray)

### Create Promotion Form (slide-in panel or inline section)
```
Promotion Name
Type: ○ Percentage  ○ Fixed Amount  ○ Free Shipping
Discount Value (if % or fixed)
Promo Code (auto-generate button)
Minimum Order Value (optional)
Valid From / Valid To (date inputs)
Usage Limit (number, optional)
Apply To: ○ All Products  ○ Specific Category  ○ Specific Products
[ Save Promotion ] button
```

---

## Page D9 — Reviews `(/vendor/reviews)`

### Stats Row (3 cards)
- Average Rating (big star + number) | Total Reviews | Response Rate

### Reviews Table / Card List
```
Customer | Product | Rating (stars) | Date | Review Text | Status | Action
```
- Toggle: Table view / Card view (JS)
- Action: Reply (inline textarea expand) | Flag
- Reply form: textarea + "Post Reply" amber button

### Reply expand (vanilla JS)
```js
document.querySelectorAll('.reply-toggle').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.closest('tr').nextElementSibling.classList.toggle('hidden');
  });
});
```

---

## Page D10 — Payouts `(/vendor/payouts)`

### Balance Card (full width)
```
┌────────────────────────────────────────────────────┐
│ Available Balance          Pending Payout           │
│ 4,200 QAR                  1,800 QAR               │
│                                                    │
│ [ Request Payout ]         [ View Bank Details ]   │
└────────────────────────────────────────────────────┘
```
- `grid-cols-2` split inside one card
- Balance numbers: `text-3xl font-bold text-gray-900`

### Request Payout Form (inline, shown on button click)
```
Amount (max: available balance)
Bank Account: [select saved account ▼]
Note (optional)
[ Confirm Payout Request ]
```

### Payout History Table (id="payouts-table")
```
Payout # | Date | Amount | Method | Bank | Status | Action
```
- Export CSV button
- Status: Pending (amber) | Completed (green) | Failed (red)

### Bank Accounts Section
- List saved bank accounts with Edit/Remove
- "Add Bank Account" form

---

## Page D11 — Subscription `(/vendor/subscription)`

### Current Plan Card
```
┌─────────────────────────────────────────┐
│ Current Plan: Pro                        │
│ 99 QAR / month · Renews Jun 30, 2026    │
│ [ Upgrade ]  [ Cancel Plan ]            │
└─────────────────────────────────────────┘
```

### Plan Comparison (same 3-card pricing table as /pricing)
- Current plan card: `border-2 border-amber-500`
- Other plans: outline cards with "Switch to X" button

### Billing History Table (id="billing-table")
```
Invoice # | Date | Plan | Amount | Status | Download
```
- Download: generates printable invoice view
- Export CSV button

### Payment Method
- Current card: last 4 digits + card type + expiry
- "Update Card" form (inline toggle)

---

## Page D12 — Settings `(/vendor/settings)`

### Tabs: Profile | Store | Notifications | Security | Danger Zone
- Tab bar: `border-b border-gray-100` with `data-tab` JS switching

**Profile Tab**
```
Avatar upload (drag & drop or click)
First Name | Last Name
Email (disabled if verified)
Phone
Bio (textarea)
[ Save Changes ]
```

**Store Tab**
```
Store Name
Store Slug (URL preview: openbox.qa/vendors/[slug])
Store Logo upload
Store Banner upload
Store Description
Social Links (Instagram, Twitter, WhatsApp)
Business Type: ○ Individual  ○ Company
[ Save Store Settings ]
```

**Notifications Tab**
- Toggle switches (styled checkboxes, no Alpine):
```html
<label class="flex items-center justify-between py-3 border-b border-gray-50 cursor-pointer">
  <span class="text-sm font-medium text-gray-700">New Order Notifications</span>
  <input type="checkbox" class="w-4 h-4 accent-amber-500" checked>
</label>
```
Options: New Orders · Low Stock Alerts · New Reviews · Payout Processed · Messages · Promotions Performance

**Security Tab**
```
Change Password:
  Current Password
  New Password (strength bar — JS)
  Confirm New Password
  [ Update Password ]

Two-Factor Authentication:
  Status badge + Enable/Disable button

Active Sessions:
  Table: Device | IP | Location | Last Active | [ Revoke ]
```

Password strength (JS):
```js
document.getElementById('new-password').addEventListener('input', function() {
  const strength = calculateStrength(this.value); // 0-4
  const bar = document.getElementById('strength-bar');
  const colors = ['bg-red-500','bg-orange-400','bg-yellow-400','bg-green-400','bg-green-600'];
  const widths = ['w-1/4','w-2/4','w-3/4','w-full'];
  bar.className = `h-1 rounded-md transition-all ${colors[strength]} ${widths[Math.max(0,strength-1)]}`;
});
function calculateStrength(p) {
  let s = 0;
  if (p.length >= 8)           s++;
  if (/[A-Z]/.test(p))         s++;
  if (/[0-9]/.test(p))         s++;
  if (/[^A-Za-z0-9]/.test(p)) s++;
  return s;
}
```

**Danger Zone Tab**
- `bg-red-50 border border-red-100 rounded-md p-6`
- Deactivate Store (keeps data) + Delete Account (irreversible)
- Both require typed confirmation: `type "DELETE" to confirm`

---

## Shared Dashboard JS (`public/js/dashboard.js`)

```js
// Sidebar mobile drawer
const sidebarOpen  = document.getElementById('sidebar-open');
const sidebarClose = document.getElementById('sidebar-close');
const sidebar      = document.getElementById('sidebar');
const sideOverlay  = document.getElementById('sidebar-overlay');

sidebarOpen?.addEventListener('click',  () => { sidebar.classList.remove('-translate-x-full'); sideOverlay.classList.remove('hidden'); });
sidebarClose?.addEventListener('click', () => { sidebar.classList.add('-translate-x-full');    sideOverlay.classList.add('hidden'); });
sideOverlay?.addEventListener('click',  () => { sidebar.classList.add('-translate-x-full');    sideOverlay.classList.add('hidden'); });

// Dashboard tabs
document.querySelectorAll('[data-tab]').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = btn.dataset.tab;
    document.querySelectorAll('[data-tab-content]').forEach(c => {
      c.classList.add('hidden');
      c.classList.remove('block');
    });
    document.querySelectorAll('[data-tab]').forEach(b => {
      b.classList.remove('border-amber-500','text-amber-600');
      b.classList.add('border-transparent','text-gray-500');
    });
    document.querySelector(`[data-tab-content="${target}"]`).classList.remove('hidden');
    btn.classList.add('border-amber-500','text-amber-600');
    btn.classList.remove('border-transparent','text-gray-500');
  });
});

// Notification bell dropdown
document.getElementById('notif-btn')?.addEventListener('click', () => {
  document.getElementById('notif-dropdown').classList.toggle('hidden');
});

// User avatar dropdown
document.getElementById('user-btn')?.addEventListener('click', () => {
  document.getElementById('user-dropdown').classList.toggle('hidden');
});

// Close dropdowns on outside click
document.addEventListener('click', e => {
  if (!e.target.closest('#notif-btn') && !e.target.closest('#notif-dropdown')) {
    document.getElementById('notif-dropdown')?.classList.add('hidden');
  }
  if (!e.target.closest('#user-btn') && !e.target.closest('#user-dropdown')) {
    document.getElementById('user-dropdown')?.classList.add('hidden');
  }
});

// Export CSV
function exportTable(tableId, filename) {
  const rows = Array.from(document.querySelectorAll(`#${tableId} tr`));
  const csv  = rows.map(r =>
    Array.from(r.querySelectorAll('th,td'))
      .map(c => `"${c.innerText.replace(/"/g,'""').trim()}"`)
      .join(',')
  ).join('\n');
  const a = Object.assign(document.createElement('a'), {
    href: URL.createObjectURL(new Blob([csv], { type: 'text/csv' })),
    download: `${filename}.csv`
  });
  a.click();
}

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
  });
});

// Modal open/close
document.querySelectorAll('[data-modal-open]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById(btn.dataset.modalOpen)?.classList.remove('hidden');
  });
});
document.querySelectorAll('[data-modal-close]').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.closest('[data-modal]')?.classList.add('hidden');
  });
});
```

---

## Laravel Dashboard Routes

```php
// routes/web.php — vendor dashboard (auth + verified middleware)
Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
  Route::get('/dashboard',              [DashboardController::class, 'index'])     ->name('dashboard');
  Route::get('/analytics',              [AnalyticsController::class, 'index'])     ->name('analytics');

  // Orders
  Route::get('/orders',                 [OrderController::class, 'index'])         ->name('orders');
  Route::get('/orders/{id}',            [OrderController::class, 'show'])          ->name('orders.show');
  Route::patch('/orders/{id}/status',   [OrderController::class, 'updateStatus'])  ->name('orders.status');

  // Products
  Route::get('/products',               [ProductController::class, 'index'])       ->name('products');
  Route::get('/products/create',        [ProductController::class, 'create'])      ->name('products.create');
  Route::post('/products',              [ProductController::class, 'store'])       ->name('products.store');
  Route::get('/products/{id}/edit',     [ProductController::class, 'edit'])        ->name('products.edit');
  Route::put('/products/{id}',          [ProductController::class, 'update'])      ->name('products.update');
  Route::delete('/products/{id}',       [ProductController::class, 'destroy'])     ->name('products.destroy');
  Route::patch('/products/{id}/toggle', [ProductController::class, 'toggle'])      ->name('products.toggle');

  // Customers
  Route::get('/customers',              [CustomerController::class, 'index'])      ->name('customers');
  Route::get('/customers/{id}',         [CustomerController::class, 'show'])       ->name('customers.show');

  // Messages
  Route::get('/messages',               [MessageController::class, 'index'])       ->name('messages');
  Route::get('/messages/{id}',          [MessageController::class, 'show'])        ->name('messages.show');
  Route::post('/messages/{id}',         [MessageController::class, 'reply'])       ->name('messages.reply');

  // Promotions
  Route::get('/promotions',             [PromotionController::class, 'index'])     ->name('promotions');
  Route::post('/promotions',            [PromotionController::class, 'store'])     ->name('promotions.store');
  Route::delete('/promotions/{id}',     [PromotionController::class, 'destroy'])   ->name('promotions.destroy');

  // Reviews
  Route::get('/reviews',                [ReviewController::class, 'index'])        ->name('reviews');
  Route::post('/reviews/{id}/reply',    [ReviewController::class, 'reply'])        ->name('reviews.reply');

  // Payouts
  Route::get('/payouts',                [PayoutController::class, 'index'])        ->name('payouts');
  Route::post('/payouts/request',       [PayoutController::class, 'request'])      ->name('payouts.request');

  // Subscription & Settings
  Route::get('/subscription',           [SubscriptionController::class, 'index'])  ->name('subscription');
  Route::get('/settings',               [SettingsController::class, 'index'])      ->name('settings');
  Route::post('/settings/profile',      [SettingsController::class, 'profile'])    ->name('settings.profile');
  Route::post('/settings/store',        [SettingsController::class, 'store'])      ->name('settings.store');
  Route::post('/settings/password',     [SettingsController::class, 'password'])   ->name('settings.password');
});
```

---

## Blade View Map — Dashboard

```
resources/views/
├── layouts/
│   └── dashboard.blade.php     ← sidebar + topbar shell
├── vendor/
│   ├── dashboard.blade.php     ← Overview (D1)
│   ├── analytics.blade.php     ← Sales Analytics (D2)
│   ├── orders/
│   │   ├── index.blade.php     ← Orders list (D3)
│   │   └── show.blade.php      ← Order detail
│   ├── products/
│   │   ├── index.blade.php     ← Products list (D4)
│   │   ├── create.blade.php    ← Add product (D5)
│   │   └── edit.blade.php      ← Edit product
│   ├── customers/
│   │   ├── index.blade.php     ← Customers list (D6)
│   │   └── show.blade.php      ← Customer detail
│   ├── messages/
│   │   └── index.blade.php     ← 2-panel messaging (D7)
│   ├── promotions/
│   │   └── index.blade.php     ← Promotions (D8)
│   ├── reviews/
│   │   └── index.blade.php     ← Reviews (D9)
│   ├── payouts/
│   │   └── index.blade.php     ← Payouts (D10)
│   ├── subscription/
│   │   └── index.blade.php     ← Subscription (D11)
│   └── settings/
│       └── index.blade.php     ← Settings (D12)
└── components/
    ├── stat-card.blade.php
    ├── data-table.blade.php
    ├── modal.blade.php
    ├── badge.blade.php
    └── export-btn.blade.php
```

---

## Reusable Blade Components

### `<x-stat-card>` — `components/stat-card.blade.php`
```blade
@props(['icon', 'label', 'value', 'change', 'changeColor' => 'text-green-500'])
<div class="bg-white border border-gray-100 rounded-md p-5">
  <div class="flex items-center justify-between mb-3">
    <div class="w-9 h-9 bg-amber-50 rounded-md flex items-center justify-center">
      {!! $icon !!}
    </div>
    @if($change)
      <span class="text-xs font-semibold {{ $changeColor }}">{{ $change }}</span>
    @endif
  </div>
  <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
  <p class="text-sm text-gray-400 mt-1">{{ $label }}</p>
</div>
```

### `<x-badge>` — `components/badge.blade.php`
```blade
@props(['color' => 'gray'])
@php
  $colors = [
    'green'  => 'bg-green-50 text-green-600',
    'blue'   => 'bg-blue-50 text-blue-600',
    'amber'  => 'bg-amber-50 text-amber-600',
    'red'    => 'bg-red-50 text-red-500',
    'gray'   => 'bg-gray-100 text-gray-500',
  ];
@endphp
<span class="text-xs font-semibold px-2.5 py-1 rounded {{ $colors[$color] ?? $colors['gray'] }}">
  {{ $slot }}
</span>
```

### `<x-modal>` — `components/modal.blade.php`
```blade
@props(['id', 'title'])
<div id="{{ $id }}" data-modal class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center px-4">
  <div class="bg-white rounded-md w-full max-w-lg shadow-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
      <button data-modal-close class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
    </div>
    <div class="px-6 py-5">{{ $slot }}</div>
  </div>
</div>
```

### `<x-export-btn>` — `components/export-btn.blade.php`
```blade
@props(['tableId', 'filename' => 'export'])
<button onclick="exportTable('{{ $tableId }}', '{{ $filename }}')"
  class="flex items-center gap-2 border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-md text-xs transition-colors">
  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
  </svg>
  Export CSV
</button>
```
