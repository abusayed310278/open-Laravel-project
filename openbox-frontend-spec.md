# OpenBox — Frontend Pages Design Specification
**Stack:** Laravel 12 · Blade Templates · Tailwind CSS (CDN) · Vanilla JS  
**Theme:** `#F5F5F5` bg · `#111827` text · `#F59E0B` accent · `#FFFFFF` card bg  
**Border radius:** `rounded` (4px) or `rounded-md` (6px) only — no `rounded-lg` / `rounded-full`  
**No gradients · No Alpine · No Livewire · Mobile-first · Left-side mobile drawer**

---

## Design System (Global)

### Colors
| Token | Hex | Usage |
|---|---|---|
| `bg-gray-50` | #F9FAFB | Page background |
| `bg-white` | #FFFFFF | Cards, header |
| `text-gray-900` | #111827 | Headings |
| `text-gray-500` | #6B7280 | Body, labels |
| `text-gray-400` | #9CA3AF | Meta, captions |
| `bg-amber-500` | #F59E0B | Primary CTA, accent |
| `text-amber-500` | #F59E0B | Links, highlights |
| `bg-gray-900` | #111827 | Footer, dark CTA |
| `border-gray-100` | #F3F4F6 | Card borders |
| `text-green-600` | #16A34A | Success, delivered |
| `text-blue-500` | #3B82F6 | Links, info |
| `text-red-500` | #EF4444 | Errors, sale badges |

### Typography
- **Font:** Inter (Google Fonts CDN)
- **Headings:** `font-bold` or `font-semibold`, `text-gray-900`
- **Body:** `text-sm` or `text-base`, `text-gray-500`, `leading-relaxed`
- **Labels/Meta:** `text-xs`, `text-gray-400`
- **No ALL CAPS labels**

### Spacing
- Section padding: `py-12` (desktop), `py-8` (mobile)
- Max content width: `max-w-7xl mx-auto px-6` (desktop), `px-4` (mobile)
- Card padding: `p-5` or `p-6`
- Grid gap: `gap-4` or `gap-5`

### Buttons
```html
<!-- Primary -->
<button class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-6 py-2.5 rounded-md text-sm transition-colors">
  Label
</button>

<!-- Secondary / Outline -->
<button class="border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium px-6 py-2.5 rounded-md text-sm transition-colors">
  Label
</button>

<!-- Dark -->
<button class="bg-gray-900 hover:bg-gray-700 text-white font-semibold px-6 py-2.5 rounded-md text-sm transition-colors">
  Label
</button>
```

### Inputs / Form Fields
```html
<label class="block text-sm font-medium text-gray-700 mb-1.5">Field Label</label>
<input type="text"
  class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-800
         focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent"
  placeholder="Placeholder text">
```

### Cards
```html
<div class="bg-white border border-gray-100 rounded-md p-5">
  <!-- content -->
</div>
```

### Status Badges
```html
<span class="text-xs font-semibold bg-green-50 text-green-600 px-2.5 py-1 rounded">Delivered</span>
<span class="text-xs font-semibold bg-blue-50 text-blue-600 px-2.5 py-1 rounded">Shipped</span>
<span class="text-xs font-semibold bg-amber-50 text-amber-600 px-2.5 py-1 rounded">Processing</span>
<span class="text-xs font-semibold bg-red-50 text-red-500 px-2.5 py-1 rounded">Cancelled</span>
```

---

## Layout Shell — `layouts/app.blade.php`

### Header (Sticky, `z-50`)
```
[ Logo ] [ Nav links ] [ Search bar ] [ Wishlist | Cart | Login | Start Selling btn ]
```
- Logo: `OB` square icon + "OpenBox" wordmark
- Nav: Marketplace · Phones · Laptops · Accessories · Gaming · Refurbished Deals · Garage Sale
- Garage Sale: `text-amber-500 font-semibold`
- Search: `flex-1 max-w-sm` input with search icon inside
- Cart: icon with amber badge count
- Mobile: hamburger → left slide-in drawer (full-height, bg-white, z-50, overlay)

### Mobile Drawer (Vanilla JS)
```js
// Toggle via data attributes
document.getElementById('drawer-open').addEventListener('click', () => {
  document.getElementById('mobile-drawer').classList.remove('-translate-x-full');
  document.getElementById('drawer-overlay').classList.remove('hidden');
});
document.getElementById('drawer-close').addEventListener('click', () => {
  document.getElementById('mobile-drawer').classList.add('-translate-x-full');
  document.getElementById('drawer-overlay').classList.add('hidden');
});
```
Drawer: `fixed inset-y-0 left-0 w-72 bg-white z-50 transform -translate-x-full transition-transform duration-300`

### Footer (Dark)
4-col grid (stacks to 2-col mobile):
- Col 1: Logo + tagline
- Col 2: Marketplace links
- Col 3: Sell links
- Col 4: Support links
- Bottom bar: copyright + payment icons
- Background: `bg-gray-900`, text: `text-gray-400`, headers: `text-white`

---

## Page 1 — Homepage `(/)`

### Sections (in order):

**1. Announcement Bar**
- `bg-amber-500 text-white text-xs text-center py-2`
- Flash sale message + dismiss X

**2. Utility Bar**
- `bg-gray-900 text-gray-400 text-xs py-1.5 px-6`
- Left: email + phone | Right: hours + flash note

**3. Header** ← from layout

**4. Hero**
- `bg-gray-50 py-16 text-center`
- Pre-heading: small amber text
- H1: large bold heading (2 lines)
- Subtext: `text-gray-500 max-w-xl mx-auto`
- Search bar: full-width input + amber "Search" button
- Popular tags: text links below

**5. Browse Categories**
- Section header + "View All" link
- 2-row grid, `grid-cols-4 md:grid-cols-8 gap-3`
- Each item: icon square (`bg-*-50 rounded-md`) + label
- Hover: `border-amber-300`

**6. Featured Products** (5-col → 2-col mobile)
- Section header + "View All"
- Product card: image area (`bg-gray-50 h-40`) + brand label + name + rating + price + "Add to Cart" button
- NEW badge: `bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded absolute top-2 left-2`

**7. Refurbished Deals** (`bg-blue-50 py-8`)
- 4-col → 2-col mobile
- Cards show: Grade badge, discounted price, original crossed, star rating

**8. Why Buy on OpenBox** (4 icons, centered)
- `bg-white py-14`
- Icon box: `bg-*-50 rounded-md w-12 h-12`

**9. Grading System** (`bg-gray-50`)
- 3 cards: Grade A (green) · Grade B (blue) · Grade C (red)
- Colored badge, title, description paragraph

**10. Best Deals** (4-col → 2-col mobile)
- Sale badge, price + % off badge, rating

**11. Trusted Vendors** (`bg-gray-50`)
- 6-col → 3-col mobile grid
- Vendor card: avatar circle + name + stars + sales count

**12. Start Selling CTA**
- `bg-gray-900 py-16 text-center`
- H2 white, subtext gray-400, two buttons (amber + outline-white)

**13. Latest Arrivals** (2 rows × 4-col → 2-col mobile)

**14. Community Reviews** (`bg-gray-50`)
- 4-col → 2-col mobile
- Star rating, quote, avatar initials circle + name + city

**15. Popular Brands**
- Centered flex-wrap brand name pills: `bg-gray-50 hover:bg-gray-100 rounded-md px-8 py-5`

**16. Newsletter**
- `bg-amber-500 py-14 text-center`
- Email input + Subscribe button side-by-side

**17. Footer** ← from layout

---

## Page 2 — Product Listing `(/products)` or `(/category/{slug})`

### Layout
- Left sidebar (filter) + Right product grid
- Mobile: filter in a slide-up drawer or collapsible toggle

### Sidebar Filters
```
[ Search within results ]
[ Category — checkboxes ]
[ Condition — New / Grade A / Grade B / Grade C ]
[ Price Range — min/max inputs ]
[ Brand — checkboxes ]
[ Rating — star radio buttons ]
[ Clear Filters button ]
```

### Top Bar (above grid)
- "Showing X results for Y"
- Sort dropdown: Featured · Price Low-High · Price High-Low · Newest · Rating
- View toggle: grid / list (JS swap)

### Product Grid
- `grid-cols-2 md:grid-cols-4 gap-4`
- Same card as homepage
- Pagination: numbered + prev/next, `border border-gray-200 rounded-md px-3 py-1.5 text-sm`

### List View (JS toggle)
- Full-width card: image left, details right
- Show description excerpt

---

## Page 3 — Product Detail `(/products/{slug})`

### Layout: 2-col (`md:grid-cols-2 gap-10`)

**Left: Image Gallery**
- Main image: `bg-gray-50 rounded-md h-80 flex items-center justify-center`
- Thumbnail row: 4 small images, click to swap main (vanilla JS)

**Right: Product Info**
- Brand + Condition badge
- Product name (`text-2xl font-bold`)
- Star rating + review count link
- Price (large) + original crossed if discounted + savings badge
- Condition selector: Grade A / B / C toggle buttons
- Quantity: − / [number] / + input
- Add to Cart (amber, full-width) + Add to Wishlist (outline)
- Divider
- Short specs table: Brand, Model, Storage, Color, Condition, Warranty

**Below: Tabs** (vanilla JS tab switch)
- Description | Specifications | Reviews | Shipping & Returns

**Description tab:** Rich text area with paragraphs

**Specifications tab:** 2-col `dl` definition list, alternating `bg-gray-50` rows

**Reviews tab:**
- Average score + bar chart breakdown (5→1 stars, width % bars)
- Review cards: avatar initials, name, date, stars, text
- "Write a Review" form at bottom (name, rating star-click, textarea, submit)

**Related Products:** 4-col grid same card style

---

## Page 4 — About `(/about)`

### Sections:

**1. Page Hero**
- `bg-gray-50 py-14 text-center`
- H1 + subtext paragraph

**2. Our Story** (2-col: text left, image placeholder right)
- Paragraphs, no decorative flourishes
- Image: `bg-gray-100 rounded-md h-64` placeholder

**3. Key Numbers** (4-col stats grid)
- `bg-white py-12`
- Each: large bold number + small label
- e.g. 12K+ Products · 3,400+ Verified Sellers · 98% Satisfaction · 5 Cities

**4. How It Works** (3-col, numbered)
- Step cards: number (`text-5xl font-black text-amber-500`) + title + description
- Steps: List → Sell → Get Paid

**5. Grading Transparency** (same 3-card block as homepage)

**6. Meet the Team** (4-col grid)
- Card: avatar placeholder square, name, role, short bio

**7. Start Selling CTA** (same dark block)

---

## Page 5 — Contact `(/contact)`

### Layout: 2-col (`md:grid-cols-2 gap-12`)

**Left: Contact Form**
```
Name (text)
Email (email)
Subject (select: General Enquiry / Vendor Support / Order Issue / Partnership)
Message (textarea, 5 rows)
[ Send Message ] button (amber, full-width)
```

**Right: Contact Info**
- Info rows with icon: 📧 Email · 📞 Phone · 📍 Address · 🕐 Hours
- Map placeholder: `bg-gray-100 rounded-md h-48 flex items-center justify-center`
- Social links row

**Below form: FAQ accordion** (vanilla JS toggle)
- 5–8 common questions
- `border-b border-gray-100` rows, click to expand answer

```js
document.querySelectorAll('.faq-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const answer = btn.nextElementSibling;
    answer.classList.toggle('hidden');
    btn.querySelector('.faq-icon').textContent =
      answer.classList.contains('hidden') ? '+' : '−';
  });
});
```

---

## Page 6 — Sell / Start Selling `(/sell)`

### Sections:

**1. Hero**
- `bg-gray-900 py-16 text-center text-white`
- H1 + subtext + "Create Free Seller Account" amber button

**2. Why Sell Here** (4-col benefits)
- Icon + title + description per card

**3. How Selling Works** (3-step horizontal flow)
- Step 1: Create Account → Step 2: List Your Item → Step 3: Get Paid

**4. Pricing / Commission Table**
```
| Plan       | Free      | Pro (99 QAR/mo) | Business (299 QAR/mo) |
|------------|-----------|-----------------|------------------------|
| Listings   | Up to 10  | Unlimited        | Unlimited              |
| Commission | 8%        | 5%              | 3%                     |
| Analytics  | Basic     | Full            | Advanced + Export      |
| Support    | Email     | Priority Email  | Dedicated Manager      |
| Promotions | —         | ✓               | ✓ + Featured           |
| Badge      | —         | Verified        | Top Seller             |
```
- Table: `w-full border border-gray-100 rounded-md overflow-hidden`
- Header row: `bg-gray-900 text-white`
- Pro column highlighted with `bg-amber-50 border border-amber-200`

**5. Seller Testimonials** (3-col cards, same review style)

**6. Registration CTA** (amber bg, email capture form)

---

## Page 7 — Pricing `(/pricing)`

### Header
- `py-12 bg-gray-50 text-center`
- Toggle: Monthly / Yearly (JS — yearly shows 20% discount)

### Pricing Cards (3 cards)

```
┌─────────────┐  ┌─────────────────┐  ┌─────────────┐
│   Free       │  │   Pro  ★ Popular│  │  Business   │
│   0 QAR      │  │   99 QAR/mo     │  │  299 QAR/mo │
│              │  │ (border-amber)  │  │             │
│ Feature list │  │ Feature list    │  │ Feature list│
│  Get Started │  │  Start Pro      │  │  Contact Us │
└─────────────┘  └─────────────────┘  └─────────────┘
```

- Pro card: `border-2 border-amber-500` with `Popular` badge top-right
- Feature list: ✓ green for included, `—` gray for not included
- Toggle JS: swap price between monthly and yearly values

### FAQ Section below cards (accordion, same as contact)

### Comparison Table (full feature matrix, same table style as Sell page)

---

## Page 8 — Authentication Pages

### Login `(/login)` — centered card, max-w-md
```
Logo
H2: "Welcome back"
Email input
Password input (show/hide toggle — vanilla JS)
[ Forgot password? ] link right-aligned
[ Log In ] button amber full-width
Divider "or"
[ Continue as Guest ] outline button
Link: "Don't have an account? Register"
```

### Register `(/register)` — centered card, max-w-lg
```
Logo
H2: "Create your account"
2-col row: First Name | Last Name
Email
Password + Confirm Password (strength bar — vanilla JS)
Account type: Buyer / Seller radio
Terms checkbox
[ Create Account ] button amber full-width
Link: "Already have an account? Log in"
```

### Forgot Password `(/forgot-password)`
```
H2: "Reset your password"
Email input
[ Send Reset Link ] amber button
Back to login link
```

---

## Page 9 — Cart `(/cart)`

### Layout: `md:grid-cols-3 gap-6` (items col spans 2, summary col spans 1)

**Cart Items (left, col-span-2)**
- Table: image | name + details | qty stepper | price | remove
- Row: `border-b border-gray-100 py-4`
- Qty: `− [n] +` buttons, update total live (vanilla JS)
- Remove: trash icon button

**Order Summary (right)**
```
┌──────────────────────┐
│ Order Summary        │
│ Subtotal:  4,299 QAR │
│ Shipping:  Free      │
│ Discount:  -200 QAR  │
│ ─────────────────────│
│ Total:     4,099 QAR │
│                      │
│ [ Proceed to Checkout] │
│ Coupon code input    │
└──────────────────────┘
```

---

## Page 10 — Checkout `(/checkout)` — 2-col

**Left: Form Steps (JS show/hide)**

Step 1 — Contact Info:
- Name, Email, Phone

Step 2 — Shipping Address:
- Address Line 1, City, Area, Postal Code
- Delivery method: Standard / Express radio

Step 3 — Payment:
- Method: Credit Card / Cash on Delivery / Bank Transfer radio
- Card fields (if card selected): Card Number, Expiry, CVV

Step 4 — Review & Place Order

**Right: Order Summary** (sticky, same card as cart)

---

## Page 11 — Order Confirmation `(/orders/{id}/confirmation)`

- Centered, max-w-lg
- Large ✓ icon (`text-green-500 text-6xl`)
- "Order Placed!" heading
- Order number, estimated delivery
- Summary card: items, total
- Two buttons: Track Order | Continue Shopping

---

## Page 12 — Help / FAQ `(/help)`

### Layout: Left category nav + Right content

**Categories:** Getting Started · Buying · Selling · Returns · Payments · Account

**Content:**
- Section H2 + accordion blocks per category
- Search bar at top: `filter FAQ items by keyword (vanilla JS)`

```js
document.getElementById('faq-search').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.faq-item').forEach(item => {
    item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
```

---

## Page 13 — Vendor Public Profile `(/vendors/{slug})`

### Header card
- Cover area: `bg-gray-100 h-32 rounded-md`
- Vendor logo circle (overlapping) + Name + Verified badge + Star rating + sales count
- "View Products" / "Message Seller" buttons

### Stats row (4 inline stats)
- Total Sales · Rating · Response Time · Member Since

### Products grid (same 4-col product card layout)

### Reviews section (same review card style)

---

## Responsive Breakpoints

| Screen | Behavior |
|---|---|
| `< md` (< 768px) | Single column, mobile drawer, stacked sections |
| `md` (768px+) | 2-col layouts, sidebar filters visible |
| `lg` (1024px+) | Full grid layouts, 4–5 col product grids |
| `xl` (1280px+) | Max-width container centered |

---

## Vanilla JS Utilities (`public/js/app.js`)

```js
// Mobile Drawer
const drawerOpen  = document.getElementById('drawer-open');
const drawerClose = document.getElementById('drawer-close');
const drawer      = document.getElementById('mobile-drawer');
const overlay     = document.getElementById('drawer-overlay');

drawerOpen?.addEventListener('click',  () => { drawer.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); });
drawerClose?.addEventListener('click', () => { drawer.classList.add('-translate-x-full');    overlay.classList.add('hidden'); });
overlay?.addEventListener('click',     () => { drawer.classList.add('-translate-x-full');    overlay.classList.add('hidden'); });

// Tabs
document.querySelectorAll('[data-tab]').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = btn.dataset.tab;
    document.querySelectorAll('[data-tab-content]').forEach(c => c.classList.add('hidden'));
    document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('border-amber-500', 'text-amber-500'));
    document.querySelector(`[data-tab-content="${target}"]`).classList.remove('hidden');
    btn.classList.add('border-amber-500', 'text-amber-500');
  });
});

// FAQ Accordion
document.querySelectorAll('.faq-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const answer = btn.nextElementSibling;
    const icon   = btn.querySelector('.faq-icon');
    answer.classList.toggle('hidden');
    icon.textContent = answer.classList.contains('hidden') ? '+' : '−';
  });
});

// Cart qty live update
document.querySelectorAll('.qty-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = btn.parentElement.querySelector('.qty-input');
    let val = parseInt(input.value) || 1;
    if (btn.dataset.dir === 'up')   val = Math.min(val + 1, 99);
    if (btn.dataset.dir === 'down') val = Math.max(val - 1, 1);
    input.value = val;
    updateCartTotal();
  });
});

// Gallery image swap
document.querySelectorAll('.thumb-img').forEach(thumb => {
  thumb.addEventListener('click', () => {
    document.getElementById('main-img').src = thumb.src;
    document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('ring-2', 'ring-amber-500'));
    thumb.classList.add('ring-2', 'ring-amber-500');
  });
});
```

---

## Laravel Routes Reference

```php
// Frontend routes (web.php)
Route::get('/',                    [HomeController::class,    'index'])->name('home');
Route::get('/products',            [ProductController::class, 'index'])->name('products');
Route::get('/products/{slug}',     [ProductController::class, 'show'])->name('products.show');
Route::get('/about',               [PageController::class,    'about'])->name('about');
Route::get('/contact',             [PageController::class,    'contact'])->name('contact');
Route::post('/contact',            [PageController::class,    'contactSubmit'])->name('contact.submit');
Route::get('/sell',                [PageController::class,    'sell'])->name('sell');
Route::get('/pricing',             [PageController::class,    'pricing'])->name('pricing');
Route::get('/help',                [PageController::class,    'help'])->name('help');
Route::get('/vendors/{slug}',      [VendorController::class,  'show'])->name('vendors.show');
Route::get('/cart',                [CartController::class,    'index'])->name('cart');
Route::get('/checkout',            [CheckoutController::class,'index'])->name('checkout');
Route::get('/orders/{id}/confirm', [OrderController::class,   'confirm'])->name('orders.confirm');

// Auth (use Laravel Breeze or manual)
Route::get('/login',               [AuthController::class, 'loginForm'])->name('login');
Route::post('/login',              [AuthController::class, 'login']);
Route::get('/register',            [AuthController::class, 'registerForm'])->name('register');
Route::post('/register',           [AuthController::class, 'register']);
Route::get('/forgot-password',     [AuthController::class, 'forgotForm'])->name('password.request');
```

---

## Blade Component Map

```
resources/views/
├── layouts/
│   ├── app.blade.php          ← header + footer shell
│   └── auth.blade.php         ← centered card shell for login/register
├── components/
│   ├── product-card.blade.php
│   ├── review-card.blade.php
│   ├── vendor-card.blade.php
│   ├── faq-item.blade.php
│   └── breadcrumb.blade.php
├── pages/
│   ├── home.blade.php
│   ├── about.blade.php
│   ├── contact.blade.php
│   ├── sell.blade.php
│   ├── pricing.blade.php
│   └── help.blade.php
├── products/
│   ├── index.blade.php
│   └── show.blade.php
├── vendors/
│   └── show.blade.php
├── cart/
│   └── index.blade.php
├── checkout/
│   └── index.blade.php
└── auth/
    ├── login.blade.php
    ├── register.blade.php
    └── forgot-password.blade.php
```
